<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fleet extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login'))
            redirect('login');

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'fleet_staff']))
            show_error('Access Denied', 403);

        $this->load->model('M_unit', 'unit');
        $this->load->model('M_unit_document', 'doc');
        $this->load->model('M_unit_maintenance', 'maintenance');
        $this->load->model('M_unit_fuel', 'fuel');
        $this->data['aktif'] = 'fleet';
    }

    // ══════════════════════════════════════════
    //  INDEX — Dashboard utama
    // ══════════════════════════════════════════
    public function index()
    {
        // ── Filter rentang waktu ──
        $range = (int) ($this->input->get('range') ?? 30);
        $valid_range = [7, 30, 90];
        if (!in_array($range, $valid_range))
            $range = 30;
        $date_from = date('Y-m-d', strtotime("-{$range} days"));

        $units = $this->unit->lihat();

        // ── Status counts ──
        $total = $aktif = $maintenance = $rusak = $dijual = $nonaktif = 0;
        $tipe_chart = [];

        foreach ($units as $u) {
            $total++;
            $status = $u->status_unit ?? 'aktif';
            switch ($status) {
                case 'aktif':
                    $aktif++;
                    break;
                case 'maintenance':
                    $maintenance++;
                    break;
                case 'rusak':
                    $rusak++;
                    break;
                case 'dijual':
                    $dijual++;
                    break;
                case 'nonaktif':
                    $nonaktif++;
                    break;
            }
            $tipe = $u->tipe_unit ?? 'Lainnya';
            $tipe_chart[$tipe] = ($tipe_chart[$tipe] ?? 0) + 1;
        }
        arsort($tipe_chart);

        // ── Auto sync status expired ──
        $this->doc->sync_status();

        // ── Alert dokumen (per dokumen, bukan per unit) ──
        $all_docs = $this->doc->lihat_semua();
        $doc_expired = 0;
        $doc_soon = 0;

        foreach ($all_docs as $d) {
            $diff = !empty($d->tanggal_expired)
                ? (strtotime($d->tanggal_expired) - time()) / 86400
                : 999;
            if ($diff <= 0 || $d->status == 'expired')
                $doc_expired++;
            elseif ($diff <= 30)
                $doc_soon++;
        }

        $doc_alerts = $this->doc->get_alerts(30);

        // ── Alert service ──
        $service_due = 0;
        $service_alerts = [];
        foreach ($units as $u) {
            $current = $u->current_km ?? 0;
            $next = $u->next_service_km ?? 0;
            if (!$next || !$current)
                continue;
            if (($next - $current) <= 5000) {
                $service_due++;
                $service_alerts[] = $u;
            }
        }

        // ── Statistik BBM periode terpilih ──
        $bbm_period = $this->db
            ->select('SUM(liter) as total_liter, SUM(total_biaya) as total_biaya, COUNT(*) as total_isi')
            ->where('tanggal_isi >=', $date_from)
            ->get('unit_fuel')->row();

        // ── Statistik service periode terpilih ──
        $service_period = $this->db
            ->select('SUM(biaya) as total_biaya, COUNT(*) as total_service')
            ->where('tanggal_service >=', $date_from)
            ->get('unit_maintenance')->row();

        // ── Tren BBM (per minggu jika range >= 30, per hari jika range = 7) ──
        $bbm_tren = $this->_get_bbm_tren($range, $date_from);

        // ── Top Driver BBM ──
        $top_drivers = $this->db
            ->select('driver_nama, SUM(liter) as total_liter, SUM(total_biaya) as total_biaya, COUNT(*) as total_isi, AVG(konsumsi) as avg_konsumsi')
            ->where('tanggal_isi >=', $date_from)
            ->where('driver_nama IS NOT NULL', null, false)
            ->where('driver_nama !=', '')
            ->group_by('driver_nama')
            ->order_by('total_liter', 'DESC')
            ->limit(7)
            ->get('unit_fuel')->result();

        // ── 5 service & BBM terbaru ──
        $recent_services = $this->db
            ->select('um.*, u.no_polisi, u.tipe_unit')
            ->from('unit_maintenance um')
            ->join('units u', 'u.id = um.unit_id')
            ->order_by('um.tanggal_service', 'DESC')
            ->limit(5)->get()->result();

        $recent_fuels = $this->db
            ->select('uf.*, u.no_polisi, u.tipe_unit')
            ->from('unit_fuel uf')
            ->join('units u', 'u.id = uf.unit_id')
            ->order_by('uf.tanggal_isi', 'DESC')
            ->limit(5)->get()->result();

        $this->data = array_merge($this->data, [
            'title' => 'Fleet Dashboard',
            'range' => $range,
            'units' => $units,
            'total' => $total,
            'aktif' => $aktif,
            'maintenance' => $maintenance,
            'rusak' => $rusak,
            'dijual' => $dijual,
            'nonaktif' => $nonaktif,
            'doc_expired' => $doc_expired,
            'doc_soon' => $doc_soon,
            'doc_alerts' => $doc_alerts,
            'service_due' => $service_due,
            'service_alerts' => $service_alerts,
            'tipe_chart' => $tipe_chart,
            'bbm_period' => $bbm_period,
            'service_period' => $service_period,
            'bbm_tren' => $bbm_tren,
            'top_drivers' => $top_drivers,
            'recent_services' => $recent_services,
            'recent_fuels' => $recent_fuels,
        ]);

        $this->load->view('fleet/dashboard', $this->data);
    }

    // ══════════════════════════════════════════
    //  UBAH STATUS CEPAT (AJAX)
    // ══════════════════════════════════════════
    public function ubah_status()
    {
        if (!$this->input->is_ajax_request()) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Invalid request']));
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational'])) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Tidak ada akses']));
        }

        $id = (int) $this->input->post('id');
        $status = $this->input->post('status');
        $valid = ['aktif', 'maintenance', 'rusak', 'dijual', 'nonaktif'];

        if (!$id || !in_array($status, $valid)) {
            return $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Data tidak valid']));
        }

        $ok = $this->db->where('id', $id)->update('units', [
            'status_unit' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => $ok]));
    }

    // ══════════════════════════════════════════
    //  EXPORT EXCEL SEMUA UNIT
    // ══════════════════════════════════════════
    public function export_excel()
    {
        $units = $this->unit->lihat();

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="fleet_units_' . date('Ymd') . '.xls"');
        header('Cache-Control: max-age=0');
        ?>
        <table border="1">
            <thead>
                <tr style="background:#4e73df;color:white;font-weight:bold;">
                    <th>No</th>
                    <th>No Polisi</th>
                    <th>Tipe Unit</th>
                    <th>Tipe Box</th>
                    <th>Tahun</th>
                    <th>Tonase (Ton)</th>
                    <th>Kapasitas (KG)</th>
                    <th>Panjang (m)</th>
                    <th>Lebar (m)</th>
                    <th>Tinggi (m)</th>
                    <th>Jenis BBM</th>
                    <th>KM Saat Ini</th>
                    <th>Next Service KM</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($units as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= strtoupper($u->no_polisi ?? '') ?></td>
                        <td><?= $u->tipe_unit ?? '' ?></td>
                        <td><?= $u->tipe_box ?? '' ?></td>
                        <td><?= $u->tahun_unit ?? '' ?></td>
                        <td><?= $u->tonase ?? '' ?></td>
                        <td><?= $u->kapasitas_kg ?? '' ?></td>
                        <td><?= $u->panjang ?? '' ?></td>
                        <td><?= $u->lebar ?? '' ?></td>
                        <td><?= $u->tinggi ?? '' ?></td>
                        <td><?= strtoupper($u->bahan_bakar ?? '') ?></td>
                        <td><?= $u->current_km ?? 0 ?></td>
                        <td><?= $u->next_service_km ?? '' ?></td>
                        <td><?= strtoupper($u->status_unit ?? 'aktif') ?></td>
                        <td><?= htmlspecialchars($u->keterangan ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        exit;
    }

    // ══════════════════════════════════════════
    //  LAPORAN BBM — Print / Excel
    // ══════════════════════════════════════════
    public function laporan_bbm()
    {
        $dari = $this->input->get('dari') ?? date('Y-m-01');
        $sampai = $this->input->get('sampai') ?? date('Y-m-d');
        $unit_id = (int) ($this->input->get('unit_id') ?? 0);
        $format = $this->input->get('format') ?? 'print';

        $this->db
            ->select('uf.*, u.no_polisi, u.tipe_unit')
            ->from('unit_fuel uf')
            ->join('units u', 'u.id = uf.unit_id')
            ->where('uf.tanggal_isi >=', $dari)
            ->where('uf.tanggal_isi <=', $sampai);

        if ($unit_id)
            $this->db->where('uf.unit_id', $unit_id);

        $fuels = $this->db->order_by('uf.tanggal_isi', 'ASC')->get()->result();

        $total_liter = array_sum(array_column((array) $fuels, 'liter'));
        $total_biaya = array_sum(array_column((array) $fuels, 'total_biaya'));

        if ($format == 'excel') {
            $this->_laporan_bbm_excel($fuels, $dari, $sampai, $total_liter, $total_biaya);
            return;
        }

        // ── Print view ──
        $this->data['fuels'] = $fuels;
        $this->data['dari'] = $dari;
        $this->data['sampai'] = $sampai;
        $this->data['total_liter'] = $total_liter;
        $this->data['total_biaya'] = $total_biaya;
        $this->data['unit_filter'] = $unit_id ? $this->unit->lihat_id($unit_id) : null;

        $this->load->view('fleet/laporan_bbm_print', $this->data);
    }

    // ══════════════════════════════════════════
    //  PRIVATE: Export Excel Laporan BBM
    // ══════════════════════════════════════════
    private function _laporan_bbm_excel($fuels, $dari, $sampai, $total_liter, $total_biaya)
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="laporan_bbm_' . $dari . '_sd_' . $sampai . '.xls"');
        header('Cache-Control: max-age=0');
        ?>
        <table border="1">
            <tr>
                <td colspan="11" style="font-size:14pt;font-weight:bold;">LAPORAN PENGISIAN BBM</td>
            </tr>
            <tr>
                <td colspan="11">Periode: <?= date('d/m/Y', strtotime($dari)) ?> s/d <?= date('d/m/Y', strtotime($sampai)) ?>
                </td>
            </tr>
            <tr>
                <td colspan="11"></td>
            </tr>
            <thead>
                <tr style="background:#1cc88a;color:white;font-weight:bold;">
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No Polisi</th>
                    <th>Tipe Unit</th>
                    <th>Driver</th>
                    <th>Jenis BBM</th>
                    <th>Liter</th>
                    <th>Harga/L</th>
                    <th>Total Biaya</th>
                    <th>KM Saat Isi</th>
                    <th>Konsumsi (km/L)</th>
                    <th>SPBU</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fuels as $i => $f): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= date('d/m/Y', strtotime($f->tanggal_isi)) ?></td>
                        <td><?= strtoupper($f->no_polisi) ?></td>
                        <td><?= $f->tipe_unit ?></td>
                        <td><?= htmlspecialchars($f->driver_nama ?? '-') ?></td>
                        <td><?= strtoupper($f->jenis_bbm) ?></td>
                        <td><?= number_format($f->liter, 2) ?></td>
                        <td><?= number_format($f->harga_per_liter, 0, ',', '.') ?></td>
                        <td><?= number_format($f->total_biaya, 0, ',', '.') ?></td>
                        <td><?= $f->km_saat_isi ? number_format($f->km_saat_isi) : '-' ?></td>
                        <td><?= $f->konsumsi ? number_format($f->konsumsi, 2) : '-' ?></td>
                        <td><?= htmlspecialchars($f->spbu ?? '-') ?></td>
                        <td><?= htmlspecialchars($f->lokasi ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="font-weight:bold;background:#f8f9fc;">
                    <td colspan="6">TOTAL</td>
                    <td><?= number_format($total_liter, 2) ?> L</td>
                    <td>-</td>
                    <td>Rp <?= number_format($total_biaya, 0, ',', '.') ?></td>
                    <td colspan="4">-</td>
                </tr>
            </tbody>
        </table>
        <?php
        exit;
    }

    // ══════════════════════════════════════════
    //  PRIVATE: Generate data tren BBM untuk chart
    // ══════════════════════════════════════════
    private function _get_bbm_tren($range, $date_from)
    {
        $tren = [];

        if ($range == 7) {
            // Per hari
            for ($i = $range - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $row = $this->db
                    ->select('SUM(liter) as liter, SUM(total_biaya) as biaya')
                    ->where('DATE(tanggal_isi)', $date)
                    ->get('unit_fuel')->row();
                $tren[] = [
                    'label' => date('d/m', strtotime($date)),
                    'liter' => (float) ($row->liter ?? 0),
                    'biaya' => (float) ($row->biaya ?? 0),
                ];
            }
        } else {
            // Per minggu (grouping manual tiap 7 hari dari date_from)
            $weeks = (int) ceil($range / 7);
            for ($i = $weeks - 1; $i >= 0; $i--) {
                $week_start = date('Y-m-d', strtotime("-" . (($i + 1) * 7 - 1) . " days"));
                $week_end = date('Y-m-d', strtotime("-" . ($i * 7) . " days"));
                $row = $this->db
                    ->select('SUM(liter) as liter, SUM(total_biaya) as biaya')
                    ->where('tanggal_isi >=', $week_start)
                    ->where('tanggal_isi <=', $week_end)
                    ->get('unit_fuel')->row();
                $tren[] = [
                    'label' => date('d/m', strtotime($week_start)) . '-' . date('d/m', strtotime($week_end)),
                    'liter' => (float) ($row->liter ?? 0),
                    'biaya' => (float) ($row->biaya ?? 0),
                ];
            }
        }

        return $tren;
    }
}