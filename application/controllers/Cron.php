<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Hanya bisa diakses dari CLI atau dengan secret key via URL
        if (!$this->input->is_cli_request()) {
            $key = $this->input->get('key');
            $valid_key = $this->config->item('cron_secret_key');

            if (!$valid_key || $key !== $valid_key) {
                show_error('Forbidden', 403);
            }
        }

        $this->load->model('M_absensi');
    }

    /**
     * Auto-close semua IN yang sudah > 16 jam tanpa OUT
     *
     * Jalankan via cron cPanel:
     *   /usr/local/bin/php /home/tsct1296/public_html/app.php cron auto_close_out >/dev/null 2>&1
     *
     * Atau via URL (dengan secret key):
     *   https://yourdomain.com/cron/auto_close_out?key=YOUR_SECRET_KEY
     */
    public function auto_close_out()
    {
        $cutoff = date('Y-m-d H:i:s', time() - (16 * 3600));

        // Ambil semua IN yang sudah > 16 jam dan belum punya OUT pasangannya
        $this->db->select('
            absensi.id,
            absensi.user_id,
            absensi.tanggal,
            absensi.waktu,
            absensi.alamat,
            absensi.latitude,
            absensi.longitude,
            pengguna.nama
        ');
        $this->db->from('absensi');
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.tipe', 'in');
        $this->db->where('CONCAT(absensi.tanggal, " ", absensi.waktu) <=', $cutoff);
        $this->db->where('NOT EXISTS (
            SELECT 1 FROM absensi out2
            WHERE out2.user_id = absensi.user_id
              AND out2.tipe = "out"
              AND CONCAT(out2.tanggal, " ", out2.waktu) > CONCAT(absensi.tanggal, " ", absensi.waktu)
              AND CONCAT(out2.tanggal, " ", out2.waktu) <= DATE_ADD(CONCAT(absensi.tanggal, " ", absensi.waktu), INTERVAL 24 HOUR)
        )', null, false);

        $orphaned_ins = $this->db->get()->result();

        $count = 0;
        foreach ($orphaned_ins as $in) {
            $in_datetime = strtotime($in->tanggal . ' ' . $in->waktu);
            $auto_out_dt = $in_datetime + (16 * 3600); // IN + 16 jam

            $out_data = [
                'user_id' => $in->user_id,
                'tanggal' => date('Y-m-d', $auto_out_dt),
                'waktu' => date('H:i:s', $auto_out_dt),
                'foto' => 'auto_out.jpg',
                'latitude' => $in->latitude,
                'longitude' => $in->longitude,
                'alamat' => $in->alamat . ' (Auto OUT - sistem)',
                'metode' => 'auto',
                'tipe' => 'out',
                'keterangan' => 'Auto OUT setelah 16 jam - lupa absen keluar',
                'is_auto_out' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->M_absensi->insert($out_data)) {
                $count++;
                log_message('info', "[CRON] Auto OUT: user_id={$in->user_id} ({$in->nama}), IN={$in->tanggal} {$in->waktu}, AUTO_OUT=" . date('Y-m-d H:i:s', $auto_out_dt));
            }
        }

        $msg = "[CRON auto_close_out] Selesai: {$count} record AUTO OUT dibuat dari " . count($orphaned_ins) . " IN yang ditemukan. (" . date('Y-m-d H:i:s') . ")\n";
        log_message('info', $msg);
        echo $msg;
    }

    public function overdue_notif()
    {
        $today = date('Y-m-d');
        $h1 = date('Y-m-d', strtotime('+1 day'));
        $h3 = date('Y-m-d', strtotime('+3 days'));

        // ── Ambil invoice yang perlu dinotif ──
        $this->db->select('
        i.id, i.no_invoice, i.invoice_date, i.due_date,
        i.grand_total, i.status, i.customer_nama,
        DATEDIFF(CURDATE(), i.due_date) as overdue_days
    ');
        $this->db->from('tb_invoice_tsc i');
        $this->db->where_in('i.status', ['sent', 'draft', 'unsent']);
        $this->db->group_start();
        $this->db->where('i.due_date <', $today);
        $this->db->or_where('i.due_date', $h1);
        $this->db->or_where('i.due_date', $h3);
        $this->db->group_end();
        $this->db->order_by('i.due_date', 'ASC');
        $invoices = $this->db->get()->result();

        if (empty($invoices)) {
            $msg = "[CRON overdue_notif] Tidak ada invoice yang perlu dinotif. (" . date('Y-m-d H:i:s') . ")\n";
            log_message('info', $msg);
            echo $msg;
            return;
        }

        // ── Kelompokkan per kategori ──
        $overdue = [];
        $due_h1 = [];
        $due_h3 = [];

        foreach ($invoices as $inv) {
            if ($inv->due_date < $today)
                $overdue[] = $inv;
            elseif ($inv->due_date === $h1)
                $due_h1[] = $inv;
            elseif ($inv->due_date === $h3)
                $due_h3[] = $inv;
        }

        $total_overdue = array_sum(array_column($overdue, 'grand_total'));
        $total_h1 = array_sum(array_column($due_h1, 'grand_total'));
        $total_h3 = array_sum(array_column($due_h3, 'grand_total'));
        $total_all = $total_overdue + $total_h1 + $total_h3;
        $total_count = count($overdue) + count($due_h1) + count($due_h3);

        // ── Subject ──
        $parts = [];
        if (!empty($overdue))
            $parts[] = count($overdue) . ' invoice OVERDUE';
        if (!empty($due_h1))
            $parts[] = count($due_h1) . ' jatuh tempo BESOK';
        if (!empty($due_h3))
            $parts[] = count($due_h3) . ' jatuh tempo 3 hari lagi';
        $subject = '[TSC] ' . implode(' · ', $parts) . ' — ' . date('d/m/Y');

        // ── Build & kirim ──
        $html = $this->_build_overdue_email(
            $overdue,
            $due_h1,
            $due_h3,
            $total_overdue,
            $total_h1,
            $total_h3,
            $total_all,
            $total_count
        );
        $sent = $this->_send_overdue_email($subject, $html);

        $msg = "[CRON overdue_notif] " . ($sent ? "Email terkirim" : "GAGAL kirim email")
            . " | Overdue: " . count($overdue)
            . " | H-1: " . count($due_h1)
            . " | H-3: " . count($due_h3)
            . " | Total: Rp " . number_format($total_all, 0, ',', '.')
            . " (" . date('Y-m-d H:i:s') . ")\n";

        log_message($sent ? 'info' : 'error', $msg);
        echo $msg;
    }

    private function _build_overdue_email($overdue, $due_h1, $due_h3, $total_overdue, $total_h1, $total_h3, $total_all, $total_count)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f4f6f9;
                    margin: 0;
                    padding: 20px
                }

                .wrap {
                    max-width: 680px;
                    margin: 0 auto;
                    background: #fff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, .1)
                }

                .hdr {
                    background: linear-gradient(135deg, #1e3a5f, #2563ff);
                    padding: 24px 28px;
                    color: #fff
                }

                .hdr h1 {
                    margin: 0 0 4px;
                    font-size: 20px
                }

                .hdr p {
                    margin: 0;
                    opacity: .8;
                    font-size: 13px
                }

                .body {
                    padding: 20px 28px
                }

                .cards {
                    display: flex;
                    gap: 10px;
                    margin-bottom: 20px
                }

                .card {
                    flex: 1;
                    border-radius: 6px;
                    padding: 12px;
                    text-align: center
                }

                .card .v {
                    font-size: 20px;
                    font-weight: 700;
                    margin-bottom: 2px
                }

                .card .l {
                    font-size: 10px;
                    text-transform: uppercase;
                    letter-spacing: .5px;
                    opacity: .75
                }

                .card.red {
                    background: #fff0f0;
                    border: 1px solid #fecaca;
                    color: #dc2626
                }

                .card.orange {
                    background: #fff7ed;
                    border: 1px solid #fed7aa;
                    color: #ea580c
                }

                .card.yellow {
                    background: #fefce8;
                    border: 1px solid #fde047;
                    color: #ca8a04
                }

                .card.blue {
                    background: #eff6ff;
                    border: 1px solid #bfdbfe;
                    color: #2563eb
                }

                .sec-title {
                    font-size: 13px;
                    font-weight: 700;
                    padding: 7px 12px;
                    border-radius: 5px;
                    margin: 18px 0 8px
                }

                .sec-title.red {
                    background: #fee2e2;
                    color: #dc2626
                }

                .sec-title.orange {
                    background: #ffedd5;
                    color: #ea580c
                }

                .sec-title.yellow {
                    background: #fef9c3;
                    color: #ca8a04
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 12px
                }

                th {
                    background: #f8fafc;
                    padding: 7px 9px;
                    text-align: left;
                    border-bottom: 2px solid #e2e8f0;
                    color: #64748b;
                    font-size: 10px;
                    text-transform: uppercase
                }

                td {
                    padding: 7px 9px;
                    border-bottom: 1px solid #f1f5f9
                }

                tr:last-child td {
                    border-bottom: none
                }

                .b-red {
                    background: #dc2626;
                    color: #fff;
                    padding: 2px 7px;
                    border-radius: 99px;
                    font-size: 10px;
                    font-weight: 600;
                    white-space: nowrap
                }

                .amt {
                    font-weight: 700;
                    text-align: right;
                    white-space: nowrap
                }

                .btn {
                    display: inline-block;
                    margin-top: 18px;
                    padding: 10px 22px;
                    background: #2563ff;
                    color: #fff !important;
                    border-radius: 6px;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: 600
                }

                .ftr {
                    background: #f8fafc;
                    padding: 14px 28px;
                    text-align: center;
                    font-size: 11px;
                    color: #94a3b8;
                    border-top: 1px solid #e2e8f0
                }
            </style>
        </head>

        <body>
            <div class="wrap">
                <div class="hdr">
                    <h1>📋 Invoice Alert — TSC Core</h1>
                    <p><?= date('d F Y') ?> · <?= $total_count ?> invoice memerlukan perhatian</p>
                </div>
                <div class="body">
                    <div class="cards">
                        <?php if (!empty($overdue)): ?>
                            <div class="card red">
                                <div class="v"><?= count($overdue) ?></div>
                                <div class="l">Overdue</div>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($due_h1)): ?>
                            <div class="card orange">
                                <div class="v"><?= count($due_h1) ?></div>
                                <div class="l">Besok</div>
                            </div>
                        <?php endif ?>
                        <?php if (!empty($due_h3)): ?>
                            <div class="card yellow">
                                <div class="v"><?= count($due_h3) ?></div>
                                <div class="l">3 Hari Lagi</div>
                            </div>
                        <?php endif ?>
                        <div class="card blue">
                            <div class="v">Rp <?= number_format($total_all / 1e6, 1) ?>jt</div>
                            <div class="l">Total</div>
                        </div>
                    </div>

                    <?php if (!empty($overdue)): ?>
                        <div class="sec-title red">🔴 OVERDUE — <?= count($overdue) ?> invoice · Rp
                            <?= number_format($total_overdue, 0, ',', '.') ?></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Customer</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Telat</th>
                                    <th style="text-align:right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdue as $inv): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($inv->no_invoice) ?></strong></td>
                                        <td><?= htmlspecialchars($inv->customer_nama) ?></td>
                                        <td><?= date('d/m/Y', strtotime($inv->due_date)) ?></td>
                                        <td><span class="b-red">+<?= $inv->overdue_days ?> hari</span></td>
                                        <td class="amt">Rp <?= number_format($inv->grand_total, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php endif ?>

                    <?php if (!empty($due_h1)): ?>
                        <div class="sec-title orange">🟠 JATUH TEMPO BESOK — <?= count($due_h1) ?> invoice · Rp
                            <?= number_format($total_h1, 0, ',', '.') ?></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Customer</th>
                                    <th>Jatuh Tempo</th>
                                    <th style="text-align:right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($due_h1 as $inv): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($inv->no_invoice) ?></strong></td>
                                        <td><?= htmlspecialchars($inv->customer_nama) ?></td>
                                        <td><?= date('d/m/Y', strtotime($inv->due_date)) ?></td>
                                        <td class="amt">Rp <?= number_format($inv->grand_total, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php endif ?>

                    <?php if (!empty($due_h3)): ?>
                        <div class="sec-title yellow">🟡 JATUH TEMPO 3 HARI LAGI — <?= count($due_h3) ?> invoice · Rp
                            <?= number_format($total_h3, 0, ',', '.') ?></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Invoice</th>
                                    <th>Customer</th>
                                    <th>Jatuh Tempo</th>
                                    <th style="text-align:right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($due_h3 as $inv): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($inv->no_invoice) ?></strong></td>
                                        <td><?= htmlspecialchars($inv->customer_nama) ?></td>
                                        <td><?= date('d/m/Y', strtotime($inv->due_date)) ?></td>
                                        <td class="amt">Rp <?= number_format($inv->grand_total, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php endif ?>

                    <div style="text-align:center">
                        <a href="<?= base_url('invoice_tsc#aging') ?>" class="btn">Buka Invoice TSC →</a>
                    </div>
                </div>
                <div class="ftr">
                    Dikirim otomatis oleh TSC Core System · <?= date('d/m/Y H:i') ?><br>Jangan reply email ini.
                </div>
            </div>
        </body>

        </html>
        <?php
        return ob_get_clean();
    }

    private function _send_overdue_email($subject, $html)
    {
        $this->load->library('email');
        $config = [
            'protocol' => 'smtp',
            'smtp_host' => 'ssl://smtp.gmail.com',
            'smtp_port' => 465,
            'smtp_user' => $this->config->item('smtp_user'),
            'smtp_pass' => $this->config->item('smtp_pass'),
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n",
            'crlf' => "\r\n",
        ];
        $this->email->initialize($config);
        $this->email->clear();
        $this->email->from($this->config->item('smtp_user'), 'TSC Core System');
        $this->email->to($this->config->item('superadmin_email'));
        $this->email->subject($subject);
        $this->email->message($html);
        return $this->email->send();
    }
}