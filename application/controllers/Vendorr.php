<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Vendorr extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // ✅ UPDATED: Add finance_staff
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'admin_document', 'finance_staff'])) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('dashboard');
        }

        $this->load->model('M_vendorr', 'vendorr');
        $this->data['aktif'] = 'vendorr';
    }

    public function index()
    {
        $this->data['title'] = 'Daftar Vendor';
        $this->data['all_vendorr'] = $this->vendorr->lihat();
        
        // ✅ UPDATED: Pass permission flags to view
        $level = $this->session->userdata('login')['user_level'] ?? '';
        $this->data['can_edit'] = in_array($level, ['superadmin', 'admin_document', 'finance_staff']);
        $this->data['can_delete'] = ($level == 'superadmin');
        
        $this->load->view('vendorr/lihat', $this->data);
    }
    
    public function detail($kode)
    {
        $this->data['title'] = 'Detail Vendor';
        $this->data['vendor'] = $this->vendorr->lihat_kode($kode);
        $this->data['aktif'] = 'vendorr';
        
        if (!$this->data['vendor']) {
            $this->session->set_flashdata('error', 'Data Vendor tidak ditemukan!');
            redirect('vendorr');
        }
        
        $this->load->view('vendorr/detail', $this->data);
    }

   // FILTER + SEARCH + PAGINATION (AJAX)
    public function filter()
    {
        $keyword = $this->input->post('keyword') ?? '';
        $ppn     = $this->input->post('ppn') ?? '';
        $pph     = $this->input->post('pph') ?? '';
        $limit   = (int)($this->input->post('limit') ?? 25);
        $offset  = (int)($this->input->post('offset') ?? 0);
    
        $total   = $this->vendorr->hitung_filter($keyword, $ppn, $pph);
        $vendors = $this->vendorr->filter($keyword, $ppn, $pph, $limit, $offset);
    
        // ✅ UPDATED: Permission checks
        $level = $this->session->userdata('login')['user_level'] ?? '';
        $can_edit = in_array($level, ['superadmin', 'admin_document', 'finance_staff']);
        $can_delete = ($level == 'superadmin');
        
        $no = $offset + 1;
    
        ob_start();
        if (empty($vendors)) {
            echo '<tr><td colspan="' . ($can_edit ? '10' : '10') . '" class="text-center text-muted"><em>Tidak ada data vendor.</em></td></tr>';
        } else {
            foreach ($vendors as $v):
    ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($v->nama_vendor) ?></td>
                    <td class="small">
                        <?= character_limiter(htmlspecialchars($v->alamat_vendor), 50) ?>
                    </td>
                    <td><?= $v->npwp_vendor ?: '<em class="text-muted">—</em>' ?></td>
                    <td><?= $v->pic_vendor ?: '<em class="text-muted">—</em>' ?></td>
                    <td><?= $v->no_telp_vendor ?: '<em class="text-muted">—</em>' ?></td>
                    <td>
                        <span class="badge badge-<?= $v->ppn_vendor == 'Belum PPN' ? 'secondary' : 'success' ?>">
                            <?= htmlspecialchars($v->ppn_vendor) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?= $v->pph_vendor == 'Belum PPH' ? 'secondary' : 'warning' ?>">
                            <?= htmlspecialchars($v->pph_vendor) ?>
                        </span>
                    </td>
                    <td class="text-center small">
                        <?php if (!empty($v->file_npwp)): ?>
                            <a href="<?= base_url('assets/uploads/vendor/' . $v->file_npwp) ?>"
                                target="_blank"
                                class="badge badge-success"
                                title="Lihat NPWP">
                                <i class="fa fa-file-pdf"></i> NPWP
                            </a><br>
                        <?php endif; ?>
                        <?php if (!empty($v->file_skb)): ?>
                            <a href="<?= base_url('assets/uploads/vendor/' . $v->file_skb) ?>"
                                target="_blank"
                                class="badge badge-info mt-1"
                                title="Lihat SKB">
                                <i class="fa fa-file-pdf"></i> SKB
                            </a><br>
                        <?php endif; ?>
                        <?php if (!empty($v->file_sppkp)): ?>
                            <a href="<?= base_url('assets/uploads/vendor/' . $v->file_sppkp) ?>"
                                target="_blank"
                                class="badge badge-warning mt-1"
                                title="Lihat SPPKP">
                                <i class="fa fa-file-pdf"></i> SPPKP
                            </a>
                        <?php endif; ?>
                        <?php if (empty($v->file_npwp) && empty($v->file_skb) && empty($v->file_sppkp)): ?>
                            <em class="text-muted">—</em>
                        <?php endif; ?>
                    </td>
                    <?php if ($can_edit): ?>
                        <td class="text-center">
                            <!-- ✅ NEW: Detail Button -->
                            <a href="<?= base_url('vendorr/detail/' . $v->kode) ?>"
                                class="btn btn-info btn-sm" title="Detail">
                                <i class="fa fa-eye"></i>
                            </a>
                            
                            <a href="<?= base_url('vendorr/ubah/' . $v->kode) ?>"
                                class="btn btn-warning btn-sm" title="Ubah">
                                <i class="fa fa-edit"></i>
                            </a>
                            
                            <!-- ✅ UPDATED: Delete button with permission check -->
                            <?php if ($can_delete): ?>
                                <a onclick="return confirm('Yakin hapus vendor <?= htmlspecialchars($v->nama_vendor) ?>?')"
                                   href="<?= base_url('vendorr/hapus/' . $v->kode) ?>"
                                   class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled title="Hanya Superadmin">
                                    <i class="fa fa-lock"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td class="text-center">
                            <!-- ✅ Viewer: Detail only -->
                            <a href="<?= base_url('vendorr/detail/' . $v->kode) ?>"
                                class="btn btn-info btn-sm" title="Detail">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    <?php endif ?>
                </tr>
    <?php
            endforeach;
        }
        $html = ob_get_clean();
    
        $start = $offset + 1;
        $end   = min($offset + $limit, $total);
    
        echo json_encode([
            'html'   => $html,
            'total'  => $total,
            'start'  => $total > 0 ? $start : 0,
            'end'    => $total > 0 ? $end : 0
        ]);
    }

    public function tambah()
    {
        $this->data['title'] = 'Tambah Vendor';
        $this->load->view('vendorr/tambah', $this->data);
    }

    public function proses_tambah()
    {
        $data = [
            'kode'           => $this->input->post('kode'),
            'nama_vendor'    => $this->input->post('nama_vendor'),
            'alamat_vendor'  => $this->input->post('alamat_vendor'),
            'npwp_vendor'    => $this->input->post('npwp_vendor'),
            'pic_vendor'     => $this->input->post('pic_vendor'),
            'no_telp_vendor' => $this->input->post('no_telp_vendor'),
            'ppn_vendor'     => $this->input->post('ppn_vendor'),
            'pph_vendor'     => $this->input->post('pph_vendor')
        ];

        // Handle file uploads
        $upload_path = './assets/uploads/vendor/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size']      = 2048; // 2MB
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);

        // Upload File NPWP (*wajib)
        if (!empty($_FILES['file_npwp']['name'])) {
            if ($this->upload->do_upload('file_npwp')) {
                $data['file_npwp'] = $this->upload->data('file_name');
            } else {
                $this->session->set_flashdata('error', 'Gagal upload file NPWP: ' . $this->upload->display_errors());
                redirect('vendorr/tambah');
                return;
            }
        }

        // Upload File SKB (optional)
        if (!empty($_FILES['file_skb']['name'])) {
            if ($this->upload->do_upload('file_skb')) {
                $data['file_skb'] = $this->upload->data('file_name');
            }
        }

        // Upload File SPPKP (optional)
        if (!empty($_FILES['file_sppkp']['name'])) {
            if ($this->upload->do_upload('file_sppkp')) {
                $data['file_sppkp'] = $this->upload->data('file_name');
            }
        }

        if ($this->vendorr->tambah($data)) {
            $this->session->set_flashdata('success', 'Vendor <strong>' . $data['nama_vendor'] . '</strong> berhasil ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan vendor!');
        }
        redirect('vendorr');
    }

    public function ubah($kode)
    {
        $this->data['title'] = 'Ubah Vendor';
        $this->data['vendor'] = $this->vendorr->lihat_kode($kode);
        if (!$this->data['vendor']) show_404();
        $this->load->view('vendorr/ubah', $this->data);
    }

    public function proses_ubah($kode)
    {
        $data = [
            'nama_vendor'    => $this->input->post('nama_vendor'),
            'alamat_vendor'  => $this->input->post('alamat_vendor'),
            'npwp_vendor'    => $this->input->post('npwp_vendor'),
            'pic_vendor'     => $this->input->post('pic_vendor'),
            'no_telp_vendor' => $this->input->post('no_telp_vendor'),
            'ppn_vendor'     => $this->input->post('ppn_vendor'),
            'pph_vendor'     => $this->input->post('pph_vendor')
        ];

        // Handle file uploads
        $upload_path = './assets/uploads/vendor/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config['upload_path']   = $upload_path;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size']      = 2048; // 2MB
        $config['encrypt_name']  = TRUE;

        $this->load->library('upload', $config);

        // Get existing vendor data
        $existing = $this->vendorr->lihat_kode($kode);

        // Upload File NPWP
        if (!empty($_FILES['file_npwp']['name'])) {
            if ($this->upload->do_upload('file_npwp')) {
                // Delete old file if exists
                if (!empty($existing->file_npwp) && file_exists($upload_path . $existing->file_npwp)) {
                    unlink($upload_path . $existing->file_npwp);
                }
                $data['file_npwp'] = $this->upload->data('file_name');
            }
        }

        // Upload File SKB (optional)
        if (!empty($_FILES['file_skb']['name'])) {
            if ($this->upload->do_upload('file_skb')) {
                // Delete old file if exists
                if (!empty($existing->file_skb) && file_exists($upload_path . $existing->file_skb)) {
                    unlink($upload_path . $existing->file_skb);
                }
                $data['file_skb'] = $this->upload->data('file_name');
            }
        }

        // Upload File SPPKP (optional)
        if (!empty($_FILES['file_sppkp']['name'])) {
            if ($this->upload->do_upload('file_sppkp')) {
                // Delete old file if exists
                if (!empty($existing->file_sppkp) && file_exists($upload_path . $existing->file_sppkp)) {
                    unlink($upload_path . $existing->file_sppkp);
                }
                $data['file_sppkp'] = $this->upload->data('file_name');
            }
        }

        if ($this->vendorr->ubah($data, $kode)) {
            $this->session->set_flashdata('success', 'Vendor berhasil diubah!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengubah vendor!');
        }
        redirect('vendorr');
    }

    public function hapus($kode)
    {
        // ✅ UPDATED: Only superadmin can delete
        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level != 'superadmin') {
            $this->session->set_flashdata('error', 'Hanya Superadmin yang bisa menghapus vendor!');
            redirect('vendorr');
        }
        
        // Get vendor data to delete files
        $vendor = $this->vendorr->lihat_kode($kode);
        $upload_path = './assets/uploads/vendor/';

        if ($this->vendorr->hapus($kode)) {
            // Delete associated files
            if (!empty($vendor->file_npwp) && file_exists($upload_path . $vendor->file_npwp)) {
                unlink($upload_path . $vendor->file_npwp);
            }
            if (!empty($vendor->file_skb) && file_exists($upload_path . $vendor->file_skb)) {
                unlink($upload_path . $vendor->file_skb);
            }
            if (!empty($vendor->file_sppkp) && file_exists($upload_path . $vendor->file_sppkp)) {
                unlink($upload_path . $vendor->file_sppkp);
            }

            $this->session->set_flashdata('success', 'Vendor berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus vendor!');
        }
        redirect('vendorr');
    }

    public function export()
    {
        $dompdf = new Dompdf();
        $this->data['all_vendorr'] = $this->vendorr->lihat();
        $this->data['title'] = 'Laporan Data Vendor';
        $this->data['no'] = 1;

        $dompdf->setPaper('A4', 'landscape');
        $html = $this->load->view('vendorr/report', $this->data, true);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Laporan_Vendor_' . date('d_F_Y') . '.pdf', ['Attachment' => false]);
    }
}