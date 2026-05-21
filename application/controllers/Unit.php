<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Dompdf\Dompdf;

class Unit extends CI_Controller
{
    // ═══════════════════════════════════════
    // Level yang boleh akses modul Unit:
    // superadmin, admin_operational, fleet_staff
    // ═══════════════════════════════════════
    private $allowed_levels = ['superadmin', 'admin_operational', 'fleet_staff'];

    // Tambah & Ubah: semua level boleh
    private $edit_levels = ['superadmin', 'admin_operational', 'fleet_staff'];

    // Hapus: hanya superadmin & admin_operational
    private $delete_levels = ['superadmin', 'admin_operational'];

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->allowed_levels)) {
            show_error('Access Denied - You do not have permission to access this module', 403);
        }

        $this->load->model('M_unit', 'unit');
        $this->load->library('permission_lib');
        $this->load->library('upload');
        $this->data['aktif'] = 'unit';
    }

    public function index()
    {
        $this->data['title'] = 'Daftar Unit Kendaraan';
        $this->data['units'] = $this->unit->lihat();
        $this->load->view('unit/lihat', $this->data);
    }
    public function detail($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'fleet_staff', 'viewer']))
            show_error('Access Denied', 403);

        $this->load->model('M_unit_document', 'doc');
        $this->load->model('M_unit_maintenance', 'maintenance');
        $this->load->model('M_unit_fuel', 'fuel');

        $unit = $this->unit->lihat_id($id);
        if (!$unit)
            show_404();

        // ── Ambil expired terakhir STNK dari unit_documents ──
        // ── Jika kosong, fallback ke kolom stnk_expired di tabel units ──
        $stnk_last = $this->db->where('unit_id', $id)
            ->where('jenis_dokumen', 'stnk')
            ->order_by('tanggal_expired', 'DESC')
            ->limit(1)
            ->get('unit_documents')->row();

        $stnk_expired_last = $stnk_last->tanggal_expired
            ?? ($unit->stnk_expired ?? '');  // ← fallback ke kolom units

        // ── Ambil expired terakhir KIR dari unit_documents ──
        // ── Jika kosong, fallback ke kolom kir_expired di tabel units ──
        $kir_last = $this->db->where('unit_id', $id)
            ->where('jenis_dokumen', 'kir')
            ->order_by('tanggal_expired', 'DESC')
            ->limit(1)
            ->get('unit_documents')->row();

        $kir_expired_last = $kir_last->tanggal_expired
            ?? ($unit->kir_expired ?? '');   // ← fallback ke kolom units

        $this->data['title'] = 'Detail Unit — ' . strtoupper($unit->no_polisi);
        $this->data['unit'] = $unit;
        $this->data['dokumens'] = $this->doc->lihat_per_unit($id);
        $this->data['maintenances'] = $this->maintenance->lihat_per_unit($id);
        $this->data['fuels'] = $this->fuel->lihat_per_unit($id);
        $this->data['stnk_expired_last'] = $stnk_expired_last;
        $this->data['kir_expired_last'] = $kir_expired_last;

        $this->load->view('unit/detail', $this->data);
    }

    public function search()
    {
        $keyword = $this->input->post('keyword') ?? '';
        $units = $this->unit->cari($keyword);

        $level = $this->session->userdata('login')['user_level'] ?? '';
        $can_edit = in_array($level, $this->edit_levels);    // semua level bisa edit
        $can_delete = in_array($level, $this->delete_levels); // hanya superadmin + admin_operational

        $no = 1;

        if (empty($units)) {
            echo '<tr><td colspan="' . ($can_edit ? '12' : '11') . '" class="text-center text-muted"><em>Tidak ada data unit kendaraan.</em></td></tr>';
            return;
        }

        foreach ($units as $unit):
            $status = $unit->status_unit ?? 'aktif';
            $status_class = 'status-' . $status;
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                    <strong class="text-primary"><?= htmlspecialchars(strtoupper($unit->no_polisi ?? '')) ?></strong>
                    <?php if (!empty($unit->kapasitas_kg)): ?>
                        <br><small class="text-muted">Cap: <?= number_format($unit->kapasitas_kg) ?> kg</small>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($unit->tipe_unit ?? '-') ?></td>
                <td><?= htmlspecialchars($unit->tipe_box ?? '-') ?></td>
                <td class="text-center"><?= $unit->tahun_unit ?? '-' ?></td>
                <td>
                    <?php
                    $p = $unit->panjang ?? 0;
                    $l = $unit->lebar ?? 0;
                    $t = $unit->tinggi ?? 0;
                    if ($p && $l && $t):
                        $cbm = $p * $l * $t; ?>
                        <small><?= "$p × $l × $t" ?> m</small>
                        <br><small class="text-muted">CBM: <?= number_format($cbm, 2) ?></small>
                        <br><small class="text-info"><?= $unit->tonase ?? 0 ?> Ton</small>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif ?>
                </td>
                <td class="text-center">
                    <span class="badge badge-status <?= $status_class ?>"><?= strtoupper($status) ?></span>
                </td>
                <td>
                    <?php
                    if (!empty($unit->stnk_expired)):
                        $diff = (strtotime($unit->stnk_expired) - time()) / 86400;
                        if ($diff <= 0): $stnk_class = 'expiry-expired';
                            $stnk_text = '❌ STNK Expired';
                        elseif ($diff < 30):
                            $stnk_class = 'expiry-soon';
                            $stnk_text = '⚠️ ' . ceil($diff) . ' hari';
                        else:
                            $stnk_class = 'expiry-ok';
                            $stnk_text = '✓ ' . date('d/m/Y', strtotime($unit->stnk_expired));
                        endif; ?>
                        <small class="expiry-warning <?= $stnk_class ?>">STNK: <?= $stnk_text ?></small>
                    <?php else: ?>
                        <small class="text-muted">STNK: -</small>
                    <?php endif; ?>
                    <br>
                    <?php
                    if (!empty($unit->kir_expired)):
                        $diff = (strtotime($unit->kir_expired) - time()) / 86400;
                        if ($diff <= 0): $kir_class = 'expiry-expired';
                            $kir_text = '❌ KIR Expired';
                        elseif ($diff < 30):
                            $kir_class = 'expiry-soon';
                            $kir_text = '⚠️ ' . ceil($diff) . ' hari';
                        else:
                            $kir_class = 'expiry-ok';
                            $kir_text = '✓ ' . date('d/m/Y', strtotime($unit->kir_expired));
                        endif; ?>
                        <small class="expiry-warning <?= $kir_class ?>">KIR: <?= $kir_text ?></small>
                    <?php else: ?>
                        <small class="text-muted">KIR: -</small>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if (!empty($unit->current_km)): ?>
                        <span class="km-badge"><i class="fas fa-tachometer-alt"></i> <?= number_format($unit->current_km) ?> km</span>
                        <?php if (!empty($unit->next_service_km)): ?>
                            <br><small class="text-muted">Service: <?= number_format($unit->next_service_km) ?> km</small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($unit->bahan_bakar)): ?>
                        <small>
                            <strong><?= strtoupper($unit->bahan_bakar) ?></strong>
                            <?php if (!empty($unit->konsumsi_bbm)): ?>
                                <br><span class="text-muted"><?= $unit->konsumsi_bbm ?> km/L</span>
                            <?php endif; ?>
                        </small>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if ($unit->foto_stnk): ?>
                        <a href="<?= base_url('uploads/stnk/' . $unit->foto_stnk) ?>" target="_blank">
                            <img src="<?= base_url('uploads/stnk/' . $unit->foto_stnk) ?>" alt="STNK" width="40" class="img-thumbnail">
                        </a>
                    <?php endif; ?>
                    <?php if ($unit->foto_kir): ?>
                        <a href="<?= base_url('uploads/kir/' . $unit->foto_kir) ?>" target="_blank">
                            <img src="<?= base_url('uploads/kir/' . $unit->foto_kir) ?>" alt="KIR" width="40" class="img-thumbnail">
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($unit->foto_barcode_solar)): ?>
                        <a href="<?= base_url('uploads/barcode_solar/' . $unit->foto_barcode_solar) ?>" target="_blank"
                            title="Barcode Solar">
                            <img src="<?= base_url('uploads/barcode_solar/' . $unit->foto_barcode_solar) ?>" alt="Barcode" width="40"
                                class="img-thumbnail">
                        </a>
                    <?php endif; ?>
                    <?php if (!$unit->foto_stnk && !$unit->foto_kir && empty($unit->foto_barcode_solar)): ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>

                <?php if ($can_edit): ?>
                    <td class="text-center">
                        <a href="<?= base_url('unit/ubah/' . $unit->id) ?>" class="btn btn-success btn-sm" title="Ubah">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($can_delete): ?>
                            <a onclick="return confirm('Yakin hapus unit <?= htmlspecialchars($unit->no_polisi ?? '') ?>?')"
                                href="<?= base_url('unit/hapus/' . $unit->id) ?>" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled title="Tidak ada akses hapus">
                                <i class="fas fa-lock"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                <?php endif ?>
            </tr>
        <?php endforeach;
    }

    // fleet_staff hanya VIEW — tidak bisa tambah
    public function tambah()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->edit_levels)) {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya Superadmin & Admin Operational yang dapat menambah unit.');
            redirect('unit');
        }
        $this->data['title'] = 'Tambah Unit Kendaraan';
        $this->load->view('unit/tambah', $this->data);
    }

    public function proses_tambah()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_operational', 'fleet_staff']))
            show_error('Access Denied', 403);

        $this->load->model('M_unit', 'unit');
        $this->load->model('M_unit_document', 'doc');
        $this->load->library('upload');

        // ── Upload foto helper ──
        $upload_foto = function ($field, $folder) {
            if (empty($_FILES[$field]['name']))
                return null;
            $path = FCPATH . 'uploads/' . $folder . '/';
            if (!is_dir($path))
                mkdir($path, 0755, true);
            $config = [
                'upload_path' => $path,
                'allowed_types' => 'jpg|jpeg|png',
                'max_size' => 2048,
                'file_name' => $folder . '_' . time() . '_' . rand(100, 999),
                'file_ext_tolower' => true,
            ];
            $this->upload->initialize($config);
            if ($this->upload->do_upload($field))
                return $this->upload->data('file_name');
            return null;
        };

        $data = [
            'no_polisi' => strtoupper(trim($this->input->post('no_polisi'))),
            'tipe_unit' => $this->input->post('tipe_unit'),
            'tipe_box' => $this->input->post('tipe_box'),
            'tahun_unit' => $this->input->post('tahun_unit'),
            'tonase' => $this->input->post('tonase'),
            'kapasitas_kg' => $this->input->post('kapasitas_kg') ?: null,
            'panjang' => $this->input->post('panjang'),
            'lebar' => $this->input->post('lebar'),
            'tinggi' => $this->input->post('tinggi'),
            'status_unit' => $this->input->post('status_unit') ?: 'aktif',
            'bahan_bakar' => $this->input->post('bahan_bakar') ?: null,
            'current_km' => $this->input->post('current_km') ?: 0,
            'keterangan' => $this->input->post('keterangan') ?: null,
            'foto_stnk' => $upload_foto('foto_stnk', 'stnk'),
            'foto_kir' => $upload_foto('foto_kir', 'kir'),
            'foto_barcode_solar' => $upload_foto('foto_barcode_solar', 'barcode_solar'),
            'created_by' => $this->session->userdata('login')['username'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->unit->tambah($data)) {
            $unit_id = $this->db->insert_id();

            // ── Auto-insert dokumen STNK & KIR jika diisi ──
            $dok_auto = [];
            $stnk_exp = $this->input->post('stnk_expired');
            $kir_exp = $this->input->post('kir_expired');

            if (!empty($stnk_exp)) {
                $dok_auto[] = [
                    'unit_id' => $unit_id,
                    'jenis_dokumen' => 'stnk',
                    'nomor_dokumen' => null,
                    'tanggal_terbit' => null,
                    'tanggal_expired' => $stnk_exp,
                    'biaya' => 0,
                    'reminder_days' => 30,
                    'status' => strtotime($stnk_exp) < time() ? 'expired' : 'aktif',
                    'file_dokumen' => null,
                    'keterangan' => 'Auto-dibuat saat tambah unit',
                    'created_by' => $this->session->userdata('login')['username'] ?? '',
                ];
            }

            if (!empty($kir_exp)) {
                $dok_auto[] = [
                    'unit_id' => $unit_id,
                    'jenis_dokumen' => 'kir',
                    'nomor_dokumen' => null,
                    'tanggal_terbit' => null,
                    'tanggal_expired' => $kir_exp,
                    'biaya' => 0,
                    'reminder_days' => 30,
                    'status' => strtotime($kir_exp) < time() ? 'expired' : 'aktif',
                    'file_dokumen' => null,
                    'keterangan' => 'Auto-dibuat saat tambah unit',
                    'created_by' => $this->session->userdata('login')['username'] ?? '',
                ];
            }

            if (!empty($dok_auto)) {
                $this->db->insert_batch('unit_documents', $dok_auto);
            }

            $this->session->set_flashdata('success', 'Unit <strong>' . $data['no_polisi'] . '</strong> berhasil ditambahkan!');
            redirect('unit/detail/' . $unit_id);
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan unit!');
            redirect('unit/tambah');
        }
    }

    // fleet_staff hanya VIEW — tidak bisa ubah
    public function ubah($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->edit_levels)) {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya Superadmin & Admin Operational yang dapat mengubah unit.');
            redirect('unit');
        }
        $this->data['title'] = 'Ubah Unit Kendaraan';
        $this->data['unit'] = $this->unit->lihat_id($id);
        if (!$this->data['unit'])
            show_404();
        $this->load->view('unit/ubah', $this->data);
    }

    public function proses_ubah($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->edit_levels)) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('unit');
        }

        $unit_lama = $this->unit->lihat_id($id);
        if (!$unit_lama) {
            $this->session->set_flashdata('error', 'Unit tidak ditemukan!');
            redirect('unit');
        }

        $no_polisi_baru = strtoupper($this->input->post('no_polisi'));
        if ($no_polisi_baru != $unit_lama->no_polisi && $this->unit->cek_no_polisi($no_polisi_baru)) {
            $this->session->set_flashdata('error', 'No Polisi <strong>' . $no_polisi_baru . '</strong> sudah digunakan!');
            redirect('unit/ubah/' . $id);
        }

        $foto_stnk = $unit_lama->foto_stnk;
        $foto_kir = $unit_lama->foto_kir;
        $foto_barcode_solar = $unit_lama->foto_barcode_solar ?? null;

        if (!empty($_FILES['foto_stnk']['name'])) {
            $baru = $this->upload_foto('foto_stnk', 'stnk');
            if ($baru) {
                if ($unit_lama->foto_stnk)
                    @unlink(FCPATH . 'uploads/stnk/' . $unit_lama->foto_stnk);
                $foto_stnk = $baru;
            } else {
                redirect('unit/ubah/' . $id);
                return;
            }
        }

        if (!empty($_FILES['foto_kir']['name'])) {
            $baru = $this->upload_foto('foto_kir', 'kir');
            if ($baru) {
                if ($unit_lama->foto_kir)
                    @unlink(FCPATH . 'uploads/kir/' . $unit_lama->foto_kir);
                $foto_kir = $baru;
            } else {
                redirect('unit/ubah/' . $id);
                return;
            }
        }

        if (!empty($_FILES['foto_barcode_solar']['name'])) {
            $baru = $this->upload_foto('foto_barcode_solar', 'barcode_solar');
            if ($baru) {
                if (!empty($unit_lama->foto_barcode_solar))
                    @unlink(FCPATH . 'uploads/barcode_solar/' . $unit_lama->foto_barcode_solar);
                $foto_barcode_solar = $baru;
            } else {
                redirect('unit/ubah/' . $id);
                return;
            }
        }

        $data = [
            'no_polisi' => $no_polisi_baru,
            'tipe_unit' => $this->input->post('tipe_unit'),
            'tipe_box' => $this->input->post('tipe_box'),
            'tahun_unit' => $this->input->post('tahun_unit'),
            'tonase' => $this->input->post('tonase'),
            'kapasitas_kg' => $this->input->post('kapasitas_kg') ?: null,
            'panjang' => $this->input->post('panjang'),
            'lebar' => $this->input->post('lebar'),
            'tinggi' => $this->input->post('tinggi'),
            'stnk_expired' => $this->input->post('stnk_expired') ?: null,
            'kir_expired' => $this->input->post('kir_expired') ?: null,
            'foto_stnk' => $foto_stnk,
            'foto_kir' => $foto_kir,
            'foto_barcode_solar' => $foto_barcode_solar,
            'status_unit' => $this->input->post('status_unit') ?: 'aktif',
            'bahan_bakar' => $this->input->post('bahan_bakar') ?: null,
            'konsumsi_bbm' => $this->input->post('konsumsi_bbm') ?: null,
            'current_km' => $this->input->post('current_km') ?: 0,
            'last_service_date' => $this->input->post('last_service_date') ?: null,
            'last_service_km' => $this->input->post('last_service_km') ?: null,
            'next_service_km' => $this->input->post('next_service_km') ?: null,
            'keterangan' => $this->input->post('keterangan') ?: null,
        ];

        if ($this->unit->ubah($data, $id)) {
            $this->session->set_flashdata('success', 'Unit <strong>' . $no_polisi_baru . '</strong> berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah data!');
        }
        redirect('unit');
    }

    // Hapus: hanya superadmin & admin_operational
    public function hapus($id)
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->delete_levels)) {
            $this->session->set_flashdata('error', 'Access Denied! Hanya Superadmin & Admin Operational yang dapat menghapus unit.');
            redirect('unit');
        }

        $unit = $this->unit->lihat_id($id);
        if ($unit) {
            if (!empty($unit->foto_stnk) && file_exists(FCPATH . 'uploads/stnk/' . $unit->foto_stnk))
                @unlink(FCPATH . 'uploads/stnk/' . $unit->foto_stnk);
            if (!empty($unit->foto_kir) && file_exists(FCPATH . 'uploads/kir/' . $unit->foto_kir))
                @unlink(FCPATH . 'uploads/kir/' . $unit->foto_kir);
            if (!empty($unit->foto_barcode_solar) && file_exists(FCPATH . 'uploads/barcode_solar/' . $unit->foto_barcode_solar))
                @unlink(FCPATH . 'uploads/barcode_solar/' . $unit->foto_barcode_solar);

            if ($this->unit->hapus($id)) {
                $this->session->set_flashdata('success', 'Unit <strong>' . $unit->no_polisi . '</strong> berhasil dihapus!');
            } else {
                $this->session->set_flashdata('error', 'Gagal menghapus unit!');
            }
        } else {
            $this->session->set_flashdata('error', 'Unit tidak ditemukan!');
        }
        redirect('unit');
    }

    public function export()
    {
        $dompdf = new Dompdf();
        $this->data['units'] = $this->unit->lihat();
        $this->data['title'] = 'Laporan Data Unit Kendaraan';
        $dompdf->setPaper('A4', 'landscape');
        $html = $this->load->view('unit/report', $this->data, true);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Laporan_Unit_' . date('d_F_Y') . '.pdf', ['Attachment' => false]);
    }

    public function proses_import()
    {
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->edit_levels)) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('unit');
        }

        $file = $_FILES['file_excel']['tmp_name'] ?? null;
        if (!$file || !file_exists($file)) {
            $this->session->set_flashdata('error', 'Pilih file Excel terlebih dahulu!');
            redirect('unit');
        }

        try {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($file);
            $sheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $data = [];
            $numrow = 1;

            foreach ($sheet as $row) {
                if ($numrow++ <= 1)
                    continue;
                $no_polisi = strtoupper(trim($row['A'] ?? ''));
                if (empty($no_polisi) || $this->unit->cek_no_polisi($no_polisi))
                    continue;
                $data[] = [
                    'no_polisi' => $no_polisi,
                    'tipe_unit' => $row['B'] ?? '',
                    'tipe_box' => $row['C'] ?? '',
                    'tahun_unit' => $row['D'] ?? '',
                    'panjang' => $row['E'] ?? 0,
                    'lebar' => $row['F'] ?? 0,
                    'tinggi' => $row['G'] ?? 0,
                    'tonase' => $row['H'] ?? 0,
                    'foto_stnk' => '',
                    'foto_kir' => '',
                    'status_unit' => 'aktif',
                    'current_km' => 0,
                ];
            }

            if (!empty($data)) {
                $this->db->insert_batch('units', $data);
                $this->session->set_flashdata('success', count($data) . ' unit berhasil diimport!');
            } else {
                $this->session->set_flashdata('error', 'Tidak ada data valid untuk diimport!');
            }
        } catch (Exception $e) {
            $this->session->set_flashdata('error', 'Error: ' . $e->getMessage());
        }
        redirect('unit');
    }

    private function upload_foto($field, $folder)
    {
        $path = FCPATH . 'uploads/' . $folder . '/';
        if (!is_dir($path))
            mkdir($path, 0755, true);
        if (!is_writable($path)) {
            $this->session->set_flashdata('error', "Folder $folder tidak bisa ditulis!");
            return false;
        }

        $config = [
            'upload_path' => $path,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size' => 2048,
            'file_name' => $folder . '_' . time() . '_' . rand(100, 999),
            'file_ext_tolower' => true,
        ];

        $this->upload->initialize($config);
        if ($this->upload->do_upload($field)) {
            return $this->upload->data('file_name');
        }
        $this->session->set_flashdata('error', "Upload $folder gagal: " . strip_tags($this->upload->display_errors()));
        return false;
    }


    // ═══════════════════════════════════════════════════════════════════
    // METHOD: export_excel()
    // Tambahkan ke class Unit di application/controllers/Unit.php
    // sebelum kurung kurawal penutup class }
    // ═══════════════════════════════════════════════════════════════════

    public function export_excel()
    {
        $this->load->model('M_unit_document', 'doc');
        $this->load->model('M_unit_maintenance', 'maintenance');
        $this->load->model('M_unit_fuel', 'fuel');

        // Sync status expired dokumen
        $this->doc->sync_status();

        $units = $this->unit->lihat();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Laporan Fleet Unit Kendaraan')
            ->setSubject('Fleet Report')
            ->setCreator('TSC TMS System');

        // ════════════════════════════════════════
        // SHEET 1: RINGKASAN UNIT
        // ════════════════════════════════════════
        $ws1 = $spreadsheet->getActiveSheet();
        $ws1->setTitle('Data Unit');

        // Style helpers
        $styleTitleMain = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6b']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ];
        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2e59d9']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'FFFFFF']]],
        ];
        $styleBorderData = [
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D1D3E2']]],
            'font' => ['size' => 9],
            'alignment' => ['vertical' => 'center'],
        ];
        $styleCenter = ['alignment' => ['horizontal' => 'center']];
        $styleRight = ['alignment' => ['horizontal' => 'right']];

        // Title
        $ws1->mergeCells('A1:O1');
        $ws1->setCellValue('A1', 'LAPORAN DATA FLEET UNIT KENDARAAN - PT TATA SANJAYA CAKRAWALA');
        $ws1->getStyle('A1')->applyFromArray($styleTitleMain);
        $ws1->getRowDimension(1)->setRowHeight(28);

        $ws1->mergeCells('A2:O2');
        $ws1->setCellValue('A2', 'Digenerate pada: ' . date('d F Y H:i:s'));
        $ws1->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2e59d9']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // Headers row 3
        $headers = [
            'A' => 'No',
            'B' => 'No Polisi',
            'C' => 'Tipe Unit',
            'D' => 'Tipe Box',
            'E' => 'Tahun',
            'F' => 'Status',
            'G' => 'Tonase (Ton)',
            'H' => 'Dimensi (P×L×T)',
            'I' => 'CBM (m³)',
            'J' => 'Odometer (km)',
            'K' => 'Next Service (km)',
            'L' => 'BBM',
            'M' => 'Konsumsi (km/L)',
            'N' => 'STNK Expired',
            'O' => 'KIR Expired',
        ];
        foreach ($headers as $col => $val) {
            $ws1->setCellValue($col . '3', $val);
        }
        $ws1->getStyle('A3:O3')->applyFromArray($styleHeader);
        $ws1->getRowDimension(3)->setRowHeight(32);

        // Data rows
        $row = 4;
        $no = 1;
        $totalAktif = $totalMaint = $totalRusak = 0;

        foreach ($units as $u) {
            $status = $u->status_unit ?? 'aktif';
            if ($status == 'aktif')
                $totalAktif++;
            elseif ($status == 'maintenance')
                $totalMaint++;
            elseif ($status == 'rusak')
                $totalRusak++;

            $cbm = ($u->panjang && $u->lebar && $u->tinggi)
                ? round($u->panjang * $u->lebar * $u->tinggi, 2)
                : 0;
            $dimensi = ($u->panjang && $u->lebar && $u->tinggi)
                ? "{$u->panjang}×{$u->lebar}×{$u->tinggi}"
                : '-';

            // Status expired STNK
            $stnk_val = '-';
            if (!empty($u->stnk_expired)) {
                $diff = ceil((strtotime($u->stnk_expired) - time()) / 86400);
                $stnk_val = date('d/m/Y', strtotime($u->stnk_expired));
                if ($diff <= 0)
                    $stnk_val .= ' ❌ EXPIRED';
                elseif ($diff <= 30)
                    $stnk_val .= " ⚠️ ({$diff}hr)";
            }
            $kir_val = '-';
            if (!empty($u->kir_expired)) {
                $diff = ceil((strtotime($u->kir_expired) - time()) / 86400);
                $kir_val = date('d/m/Y', strtotime($u->kir_expired));
                if ($diff <= 0)
                    $kir_val .= ' ❌ EXPIRED';
                elseif ($diff <= 30)
                    $kir_val .= " ⚠️ ({$diff}hr)";
            }

            $ws1->setCellValue('A' . $row, $no++);
            $ws1->setCellValue('B' . $row, strtoupper($u->no_polisi ?? ''));
            $ws1->setCellValue('C' . $row, $u->tipe_unit ?? '-');
            $ws1->setCellValue('D' . $row, $u->tipe_box ?? '-');
            $ws1->setCellValue('E' . $row, $u->tahun_unit ?? '-');
            $ws1->setCellValue('F' . $row, strtoupper($status));
            $ws1->setCellValue('G' . $row, $u->tonase ?? 0);
            $ws1->setCellValue('H' . $row, $dimensi);
            $ws1->setCellValue('I' . $row, $cbm ?: '-');
            $ws1->setCellValue('J' . $row, $u->current_km ?? 0);
            $ws1->setCellValue('K' . $row, $u->next_service_km ?? '-');
            $ws1->setCellValue('L' . $row, strtoupper($u->bahan_bakar ?? '-'));
            $ws1->setCellValue('M' . $row, $u->konsumsi_bbm ?? '-');
            $ws1->setCellValue('N' . $row, $stnk_val);
            $ws1->setCellValue('O' . $row, $kir_val);

            // Row styling
            $fillColor = ($row % 2 == 0) ? 'F8F9FC' : 'FFFFFF';
            $ws1->getStyle("A{$row}:O{$row}")->applyFromArray($styleBorderData);
            $ws1->getStyle("A{$row}:O{$row}")->getFill()
                ->setFillType('solid')->getStartColor()->setRGB($fillColor);
            $ws1->getStyle("A{$row}")->applyFromArray($styleCenter);
            $ws1->getStyle("E{$row}:G{$row}")->applyFromArray($styleCenter);
            $ws1->getStyle("I{$row}:M{$row}")->applyFromArray($styleCenter);

            // Status cell color
            $statusColors = [
                'aktif' => ['bg' => 'D4EDDA', 'fg' => '155724'],
                'maintenance' => ['bg' => 'FFF3CD', 'fg' => '856404'],
                'rusak' => ['bg' => 'F8D7DA', 'fg' => '721c24'],
                'dijual' => ['bg' => 'E2E3E5', 'fg' => '383d41'],
                'nonaktif' => ['bg' => 'E2E3E5', 'fg' => '383d41'],
            ];
            if (isset($statusColors[$status])) {
                $ws1->getStyle("F{$row}")->getFill()->setFillType('solid')
                    ->getStartColor()->setRGB($statusColors[$status]['bg']);
                $ws1->getStyle("F{$row}")->getFont()
                    ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($statusColors[$status]['fg']))
                    ->setBold(true);
            }

            // Highlight STNK/KIR expired
            foreach (['N', 'O'] as $col) {
                $cellVal = $ws1->getCell($col . $row)->getValue();
                if (strpos($cellVal, 'EXPIRED') !== false) {
                    $ws1->getStyle($col . $row)->getFill()->setFillType('solid')
                        ->getStartColor()->setRGB('F8D7DA');
                    $ws1->getStyle($col . $row)->getFont()->setColor(
                        new \PhpOffice\PhpSpreadsheet\Style\Color('721c24')
                    )->setBold(true);
                } elseif (strpos($cellVal, '⚠️') !== false) {
                    $ws1->getStyle($col . $row)->getFill()->setFillType('solid')
                        ->getStartColor()->setRGB('FFF3CD');
                }
            }

            $ws1->getRowDimension($row)->setRowHeight(18);
            $row++;
        }

        // Summary footer
        $row++;
        $ws1->mergeCells("A{$row}:E{$row}");
        $ws1->setCellValue("A{$row}", 'RINGKASAN: Total ' . count($units) . ' unit | Aktif: ' . $totalAktif . ' | Maintenance: ' . $totalMaint . ' | Rusak: ' . $totalRusak);
        $ws1->getStyle("A{$row}:O{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6b']],
        ]);
        $ws1->getRowDimension($row)->setRowHeight(18);

        // Column widths Sheet1
        $colWidths1 = ['A' => 5, 'B' => 14, 'C' => 14, 'D' => 14, 'E' => 7, 'F' => 13, 'G' => 12, 'H' => 18, 'I' => 10, 'J' => 13, 'K' => 15, 'L' => 9, 'M' => 13, 'N' => 16, 'O' => 16];
        foreach ($colWidths1 as $col => $w) {
            $ws1->getColumnDimension($col)->setWidth($w);
        }

        $ws1->getStyle('A3:O3')->getAlignment()->setWrapText(true);
        $ws1->freezePane('A4');
        $ws1->setAutoFilter('A3:O3');
        $ws1->getSheetView()->setZoomScale(90);

        // ════════════════════════════════════════
        // SHEET 2: HISTORI DOKUMEN
        // ════════════════════════════════════════
        $ws2 = $spreadsheet->createSheet();
        $ws2->setTitle('Histori Dokumen');

        $ws2->mergeCells('A1:J1');
        $ws2->setCellValue('A1', 'HISTORI DOKUMEN UNIT KENDARAAN');
        $ws2->getStyle('A1')->applyFromArray($styleTitleMain);
        $ws2->getRowDimension(1)->setRowHeight(24);

        $hdrDok = ['A' => 'No', 'B' => 'No Polisi', 'C' => 'Tipe Unit', 'D' => 'Jenis Dokumen', 'E' => 'No Dokumen', 'F' => 'Tgl Terbit', 'G' => 'Tgl Expired', 'H' => 'Sisa Hari', 'I' => 'Biaya (Rp)', 'J' => 'Status'];
        foreach ($hdrDok as $col => $val) {
            $ws2->setCellValue($col . '2', $val);
        }
        $ws2->getStyle('A2:J2')->applyFromArray($styleHeader);
        $ws2->getRowDimension(2)->setRowHeight(28);

        $row2 = 3;
        $no2 = 1;
        foreach ($units as $u) {
            $dokumens = $this->doc->lihat_per_unit($u->id);
            foreach ($dokumens as $d) {
                $diff = !empty($d->tanggal_expired)
                    ? ceil((strtotime($d->tanggal_expired) - time()) / 86400)
                    : 999;
                $sisaText = $diff >= 999 ? '-' : ($diff <= 0 ? 'EXPIRED' : $diff . ' hari');

                $ws2->setCellValue('A' . $row2, $no2++);
                $ws2->setCellValue('B' . $row2, strtoupper($u->no_polisi ?? ''));
                $ws2->setCellValue('C' . $row2, $u->tipe_unit ?? '-');
                $ws2->setCellValue('D' . $row2, strtoupper($d->jenis_dokumen ?? '-'));
                $ws2->setCellValue('E' . $row2, $d->nomor_dokumen ?? '-');
                $ws2->setCellValue('F' . $row2, !empty($d->tanggal_terbit) ? date('d/m/Y', strtotime($d->tanggal_terbit)) : '-');
                $ws2->setCellValue('G' . $row2, !empty($d->tanggal_expired) ? date('d/m/Y', strtotime($d->tanggal_expired)) : '-');
                $ws2->setCellValue('H' . $row2, $sisaText);
                $ws2->setCellValue('I' . $row2, $d->biaya ?? 0);
                $ws2->setCellValue('J' . $row2, strtoupper($d->status ?? 'aktif'));

                $fillColor = ($row2 % 2 == 0) ? 'F8F9FC' : 'FFFFFF';
                $ws2->getStyle("A{$row2}:J{$row2}")->applyFromArray($styleBorderData);
                $ws2->getStyle("A{$row2}:J{$row2}")->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB($fillColor);
                $ws2->getStyle("A{$row2}")->applyFromArray($styleCenter);
                $ws2->getStyle("D{$row2}:D{$row2}")->applyFromArray($styleCenter);
                $ws2->getStyle("H{$row2}")->applyFromArray($styleCenter);
                $ws2->getStyle("I{$row2}")->getNumberFormat()->setFormatCode('#,##0');

                // Color by status
                $dokStatus = strtolower($d->status ?? 'aktif');
                if ($dokStatus == 'expired' || $diff <= 0) {
                    $ws2->getStyle("A{$row2}:J{$row2}")->getFill()->setFillType('solid')
                        ->getStartColor()->setRGB('F8D7DA');
                    $ws2->getStyle("H{$row2}:J{$row2}")->getFont()
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('721c24'))->setBold(true);
                } elseif ($diff <= 30) {
                    $ws2->getStyle("H{$row2}")->getFill()->setFillType('solid')
                        ->getStartColor()->setRGB('FFF3CD');
                    $ws2->getStyle("H{$row2}")->getFont()
                        ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('856404'))->setBold(true);
                }

                $ws2->getRowDimension($row2)->setRowHeight(17);
                $row2++;
            }
        }

        // Empty state
        if ($row2 == 3) {
            $ws2->mergeCells("A3:J3");
            $ws2->setCellValue('A3', 'Belum ada data dokumen');
            $ws2->getStyle('A3')->getAlignment()->setHorizontal('center');
        }

        $colWidths2 = ['A' => 5, 'B' => 14, 'C' => 12, 'D' => 14, 'E' => 18, 'F' => 12, 'G' => 12, 'H' => 10, 'I' => 14, 'J' => 12];
        foreach ($colWidths2 as $col => $w) {
            $ws2->getColumnDimension($col)->setWidth($w);
        }
        $ws2->freezePane('A3');
        $ws2->setAutoFilter('A2:J2');
        $ws2->getSheetView()->setZoomScale(90);

        // ════════════════════════════════════════
        // SHEET 3: HISTORI SERVICE
        // ════════════════════════════════════════
        $ws3 = $spreadsheet->createSheet();
        $ws3->setTitle('Histori Service');

        $ws3->mergeCells('A1:J1');
        $ws3->setCellValue('A1', 'HISTORI SERVICE / MAINTENANCE UNIT KENDARAAN');
        $ws3->getStyle('A1')->applyFromArray($styleTitleMain);
        $ws3->getRowDimension(1)->setRowHeight(24);

        $hdrSvc = ['A' => 'No', 'B' => 'No Polisi', 'C' => 'Tipe Unit', 'D' => 'Tgl Service', 'E' => 'Jenis Service', 'F' => 'KM Saat Service', 'G' => 'Next Service KM', 'H' => 'Bengkel', 'I' => 'Biaya (Rp)', 'J' => 'Parts Diganti'];
        foreach ($hdrSvc as $col => $val) {
            $ws3->setCellValue($col . '2', $val);
        }
        $ws3->getStyle('A2:J2')->applyFromArray($styleHeader);
        $ws3->getRowDimension(2)->setRowHeight(28);

        $jenisLabels = [
            'service_rutin' => 'Service Rutin',
            'perbaikan' => 'Perbaikan',
            'ganti_oli' => 'Ganti Oli',
            'ganti_ban' => 'Ganti Ban',
            'ganti_aki' => 'Ganti Aki',
            'tune_up' => 'Tune Up',
            'lainnya' => 'Lainnya',
        ];

        $row3 = 3;
        $no3 = 1;
        $totalBiayaService = 0;
        foreach ($units as $u) {
            $maintenances = $this->maintenance->lihat_per_unit($u->id);
            foreach ($maintenances as $m) {
                $totalBiayaService += ($m->biaya ?? 0);

                $ws3->setCellValue('A' . $row3, $no3++);
                $ws3->setCellValue('B' . $row3, strtoupper($u->no_polisi ?? ''));
                $ws3->setCellValue('C' . $row3, $u->tipe_unit ?? '-');
                $ws3->setCellValue('D' . $row3, !empty($m->tanggal_service) ? date('d/m/Y', strtotime($m->tanggal_service)) : '-');
                $ws3->setCellValue('E' . $row3, $jenisLabels[$m->jenis_service] ?? $m->jenis_service);
                $ws3->setCellValue('F' . $row3, $m->km_saat_service ?? '-');
                $ws3->setCellValue('G' . $row3, $m->next_service_km ?? '-');
                $ws3->setCellValue('H' . $row3, $m->bengkel ?? '-');
                $ws3->setCellValue('I' . $row3, $m->biaya ?? 0);
                $ws3->setCellValue('J' . $row3, $m->parts_diganti ?? '-');

                $fillColor = ($row3 % 2 == 0) ? 'F8F9FC' : 'FFFFFF';
                $ws3->getStyle("A{$row3}:J{$row3}")->applyFromArray($styleBorderData);
                $ws3->getStyle("A{$row3}:J{$row3}")->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB($fillColor);
                $ws3->getStyle("A{$row3}")->applyFromArray($styleCenter);
                $ws3->getStyle("D{$row3}")->applyFromArray($styleCenter);
                $ws3->getStyle("F{$row3}:G{$row3}")->applyFromArray($styleCenter);
                $ws3->getStyle("I{$row3}")->getNumberFormat()->setFormatCode('#,##0');
                $ws3->getStyle("J{$row3}")->getAlignment()->setWrapText(true);
                $ws3->getRowDimension($row3)->setRowHeight(18);
                $row3++;
            }
        }

        // Total row
        if ($row3 > 3) {
            $ws3->mergeCells("A{$row3}:H{$row3}");
            $ws3->setCellValue("A{$row3}", 'TOTAL BIAYA SERVICE');
            $ws3->setCellValue("I{$row3}", $totalBiayaService);
            $ws3->getStyle("A{$row3}:J{$row3}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6b']],
            ]);
            $ws3->getStyle("I{$row3}")->getNumberFormat()->setFormatCode('#,##0');
            $ws3->getRowDimension($row3)->setRowHeight(18);
        }

        $colWidths3 = ['A' => 5, 'B' => 14, 'C' => 12, 'D' => 12, 'E' => 14, 'F' => 14, 'G' => 15, 'H' => 18, 'I' => 14, 'J' => 30];
        foreach ($colWidths3 as $col => $w) {
            $ws3->getColumnDimension($col)->setWidth($w);
        }
        $ws3->freezePane('A3');
        $ws3->setAutoFilter('A2:J2');
        $ws3->getSheetView()->setZoomScale(90);

        // ════════════════════════════════════════
        // SHEET 4: HISTORI BBM
        // ════════════════════════════════════════
        $ws4 = $spreadsheet->createSheet();
        $ws4->setTitle('Histori BBM');

        $ws4->mergeCells('A1:K1');
        $ws4->setCellValue('A1', 'HISTORI PENGISIAN BBM UNIT KENDARAAN');
        $ws4->getStyle('A1')->applyFromArray($styleTitleMain);
        $ws4->getRowDimension(1)->setRowHeight(24);

        $hdrBBM = ['A' => 'No', 'B' => 'No Polisi', 'C' => 'Tipe Unit', 'D' => 'Tanggal', 'E' => 'Jenis BBM', 'F' => 'Liter', 'G' => 'Harga/Liter (Rp)', 'H' => 'Total Biaya (Rp)', 'I' => 'KM Saat Isi', 'J' => 'Konsumsi (km/L)', 'K' => 'SPBU'];
        foreach ($hdrBBM as $col => $val) {
            $ws4->setCellValue($col . '2', $val);
        }
        $ws4->getStyle('A2:K2')->applyFromArray($styleHeader);
        $ws4->getRowDimension(2)->setRowHeight(28);

        $row4 = 3;
        $no4 = 1;
        $totalLiter = $totalBiayaBBM = 0;
        foreach ($units as $u) {
            $fuels = $this->fuel->lihat_per_unit($u->id);
            foreach ($fuels as $f) {
                $totalLiter += ($f->liter ?? 0);
                $totalBiayaBBM += ($f->total_biaya ?? 0);

                $ws4->setCellValue('A' . $row4, $no4++);
                $ws4->setCellValue('B' . $row4, strtoupper($u->no_polisi ?? ''));
                $ws4->setCellValue('C' . $row4, $u->tipe_unit ?? '-');
                $ws4->setCellValue('D' . $row4, !empty($f->tanggal_isi) ? date('d/m/Y', strtotime($f->tanggal_isi)) : '-');
                $ws4->setCellValue('E' . $row4, strtoupper($f->jenis_bbm ?? '-'));
                $ws4->setCellValue('F' . $row4, $f->liter ?? 0);
                $ws4->setCellValue('G' . $row4, $f->harga_per_liter ?? 0);
                $ws4->setCellValue('H' . $row4, $f->total_biaya ?? 0);
                $ws4->setCellValue('I' . $row4, $f->km_saat_isi ?? '-');
                $ws4->setCellValue('J' . $row4, !empty($f->konsumsi) ? round($f->konsumsi, 2) : '-');
                $ws4->setCellValue('K' . $row4, $f->spbu ?? '-');

                $fillColor = ($row4 % 2 == 0) ? 'F8F9FC' : 'FFFFFF';
                $ws4->getStyle("A{$row4}:K{$row4}")->applyFromArray($styleBorderData);
                $ws4->getStyle("A{$row4}:K{$row4}")->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB($fillColor);
                $ws4->getStyle("A{$row4}")->applyFromArray($styleCenter);
                $ws4->getStyle("D{$row4}:F{$row4}")->applyFromArray($styleCenter);
                $ws4->getStyle("I{$row4}:J{$row4}")->applyFromArray($styleCenter);
                $ws4->getStyle("G{$row4}:H{$row4}")->getNumberFormat()->setFormatCode('#,##0');
                $ws4->getStyle("F{$row4}")->getNumberFormat()->setFormatCode('0.00');
                $ws4->getRowDimension($row4)->setRowHeight(17);
                $row4++;
            }
        }

        // Total row BBM
        if ($row4 > 3) {
            $ws4->mergeCells("A{$row4}:E{$row4}");
            $ws4->setCellValue("A{$row4}", 'TOTAL');
            $ws4->setCellValue("F{$row4}", round($totalLiter, 2));
            $ws4->setCellValue("H{$row4}", $totalBiayaBBM);
            $ws4->getStyle("A{$row4}:K{$row4}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6b']],
            ]);
            $ws4->getStyle("F{$row4}")->getNumberFormat()->setFormatCode('0.00');
            $ws4->getStyle("H{$row4}")->getNumberFormat()->setFormatCode('#,##0');
            $ws4->getRowDimension($row4)->setRowHeight(18);
        }

        $colWidths4 = ['A' => 5, 'B' => 14, 'C' => 12, 'D' => 12, 'E' => 10, 'F' => 9, 'G' => 16, 'H' => 16, 'I' => 13, 'J' => 14, 'K' => 18];
        foreach ($colWidths4 as $col => $w) {
            $ws4->getColumnDimension($col)->setWidth($w);
        }
        $ws4->freezePane('A3');
        $ws4->setAutoFilter('A2:K2');
        $ws4->getSheetView()->setZoomScale(90);

        // ════════════════════════════════════════
        // Set default sheet ke Sheet 1
        // ════════════════════════════════════════
        $spreadsheet->setActiveSheetIndex(0);

        // Output
        $filename = 'Laporan_Fleet_Unit_' . date('d-m-Y_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}