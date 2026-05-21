<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Customer extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        // ✅ UPDATED: Add finance_staff
        $level = $this->session->userdata('login')['user_level'];
        $allowed = ['superadmin', 'admin_document', 'finance_staff'];
        if (!in_array($level, $allowed)) {
            show_error('Akses ditolak! Hanya Superadmin, Admin Document & Finance Staff.', 403);
        }

        $this->load->model('M_customer', 'm_customer');
        $this->data['aktif'] = 'customer';
    }

    public function detail($id)
    {
        $data['title'] = 'Detail Customer';
        $data['aktif'] = 'customer';

        // ✅ FIX: Pakai lowercase (sesuai alias di constructor)
        $data['customer'] = $this->m_customer->lihat_id($id); // Ganti get_by_id jadi lihat_id

        if (!$data['customer']) {
            $this->session->set_flashdata('error', 'Customer tidak ditemukan!');
            redirect('customer');
        }

        // ✅ NEW: Load invoice model & get data
        $this->load->model('M_invoice_tsc');

        // Get recent invoices (limit 10)
        $data['invoices'] = $this->M_invoice_tsc->get_by_customer($data['customer']->kode, 10);

        // Get statistics
        $stats = $this->M_invoice_tsc->get_customer_stats($data['customer']->kode);
        $data['total_invoices'] = $stats->total_invoice ?? 0;
        $data['total_outstanding'] = $stats->outstanding_amount ?? 0;
        $data['paid_amount'] = $stats->paid_amount ?? 0;

        // Get user level for permission
        $data['user_level'] = $this->session->userdata('login')['user_level'] ?? '';

        $this->load->view('customer/detail', $data);
    }

    public function index()
    {
        $this->data['title'] = 'Data Customer';
        $this->data['all_customer'] = $this->m_customer->lihat();
        $this->data['no'] = 1;
        $this->data['aktif'] = 'customer';

        // ✅ UPDATED: Pass permission flags to view
        $level = $this->session->userdata('login')['user_level'];
        $this->data['can_edit'] = in_array($level, ['superadmin', 'admin_document', 'finance_staff']);
        $this->data['can_delete'] = ($level == 'superadmin');

        $this->load->view('customer/lihat', $this->data);
    }

    // ✅ NEW: FILTER + SEARCH + PAGINATION (AJAX)
    public function filter()
    {
        $keyword = $this->input->post('keyword') ?? '';
        $ppn     = $this->input->post('ppn') ?? '';
        $pph     = $this->input->post('pph') ?? '';
        $limit   = (int)($this->input->post('limit') ?? 25);
        $offset  = (int)($this->input->post('offset') ?? 0);

        $total     = $this->m_customer->hitung_filter($keyword, $ppn, $pph);
        $customers = $this->m_customer->filter($keyword, $ppn, $pph, $limit, $offset);

        // ✅ Permission checks
        $level = $this->session->userdata('login')['user_level'] ?? '';
        $can_edit = in_array($level, ['superadmin', 'admin_document', 'finance_staff']);
        $can_delete = ($level == 'superadmin');

        $no = $offset + 1;

        ob_start();
        if (empty($customers)) {
            echo '<tr><td colspan="' . ($can_edit ? '11' : '11') . '" class="text-center text-muted"><em>Tidak ada data customer.</em></td></tr>';
        } else {
            foreach ($customers as $c):
?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($c->kode) ?></strong></td>
                    <td><?= htmlspecialchars($c->nama) ?></td>
                    <td><?= $c->telepon ?: '<em class="text-muted">—</em>' ?></td>
                    <td><?= $c->pic ?: '<em class="text-muted">—</em>' ?></td>
                    <td class="small"><?= $c->npwp ?: '<em class="text-muted">—</em>' ?></td>
                    <td><?= $c->nama_npwp ?: '<em class="text-muted">—</em>' ?></td>
                    <td>
                        <?php if ($c->pph): ?>
                            <span class="badge badge-warning"><?= htmlspecialchars($c->pph) ?></span>
                        <?php else: ?>
                            <em class="text-muted">—</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($c->ppn): ?>
                            <span class="badge badge-success"><?= htmlspecialchars($c->ppn) ?></span>
                        <?php else: ?>
                            <em class="text-muted">—</em>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= character_limiter($c->alamat, 50) ?></td>
                    <?php if ($can_edit): ?>
                        <td class="text-center">
                            <!-- Detail Button -->
                            <a href="<?= base_url('customer/detail/' . $c->id) ?>" 
                               class="btn btn-info btn-sm" title="Detail">
                                <i class="fa fa-eye"></i>
                            </a>
                            
                            <!-- Edit Button -->
                            <a href="<?= base_url('customer/ubah/' . $c->id) ?>" 
                               class="btn btn-warning btn-sm" title="Edit">
                                <i class="fa fa-edit"></i>
                            </a>
                            
                            <!-- Delete Button (Superadmin only) -->
                            <?php if ($can_delete): ?>
                                <a onclick="return confirm('Yakin hapus customer <?= htmlspecialchars($c->nama) ?>?')" 
                                   href="<?= base_url('customer/hapus/' . $c->id) ?>" 
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
                            <!-- Viewer: Detail only -->
                            <a href="<?= base_url('customer/detail/' . $c->id) ?>" 
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
        $this->data['title'] = 'Tambah Customer';
        $this->data['kode_customer'] = $this->m_customer->kode_customer_otomatis();
        $this->data['pph_options'] = $this->m_customer->get_pph_options();
        $this->data['ppn_options'] = $this->m_customer->get_ppn_options();
        $this->data['aktif'] = 'customer';

        $this->load->view('customer/tambah', $this->data);
    }

    public function proses_tambah()
    {
        $kode = $this->m_customer->kode_customer_otomatis();

        $data = [
            'kode' => $kode,
            'nama' => $this->input->post('nama'),
            'telepon' => $this->input->post('telepon'),
            'pic' => $this->input->post('pic'),
            'npwp' => $this->input->post('npwp'),
            'nama_npwp' => $this->input->post('nama_npwp'),
            'pph' => $this->input->post('pph') ?: null,
            'ppn' => $this->input->post('ppn') ?: null,
            'alamat' => $this->input->post('alamat')
        ];

        if ($this->m_customer->tambah($data)) {
            $this->session->set_flashdata('success', 'Data Customer <strong>Berhasil</strong> Ditambahkan!');
        } else {
            $this->session->set_flashdata('error', 'Data Customer <strong>Gagal</strong> Ditambahkan!');
        }
        redirect('customer');
    }

    public function ubah($id)
    {
        $this->data['title'] = 'Ubah Customer';
        $this->data['customer'] = $this->m_customer->lihat_id($id);
        $this->data['pph_options'] = $this->m_customer->get_pph_options();
        $this->data['ppn_options'] = $this->m_customer->get_ppn_options();
        $this->data['aktif'] = 'customer';

        if (!$this->data['customer']) {
            $this->session->set_flashdata('error', 'Data Customer tidak ditemukan!');
            redirect('customer');
        }

        $this->load->view('customer/ubah', $this->data);
    }

    public function proses_ubah($id)
    {
        $data = [
            'nama' => $this->input->post('nama'),
            'telepon' => $this->input->post('telepon'),
            'pic' => $this->input->post('pic'),
            'npwp' => $this->input->post('npwp'),
            'nama_npwp' => $this->input->post('nama_npwp'),
            'pph' => $this->input->post('pph') ?: null,
            'ppn' => $this->input->post('ppn') ?: null,
            'alamat' => $this->input->post('alamat')
        ];

        if ($this->m_customer->ubah($id, $data)) {
            $this->session->set_flashdata('success', 'Data Customer <strong>Berhasil</strong> Diubah!');
        } else {
            $this->session->set_flashdata('error', 'Data Customer <strong>Gagal</strong> Diubah!');
        }
        redirect('customer');
    }

    public function hapus($id)
    {
        // ✅ UPDATED: Only superadmin can delete
        $level = $this->session->userdata('login')['user_level'];
        if ($level != 'superadmin') {
            $this->session->set_flashdata('error', 'Hanya Superadmin yang bisa menghapus customer!');
            redirect('customer');
        }

        if ($this->m_customer->hapus($id)) {
            $this->session->set_flashdata('success', 'Data Customer <strong>Berhasil</strong> Dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Data Customer <strong>Gagal</strong> Dihapus!');
        }
        redirect('customer');
    }

    public function export()
    {
        $dompdf = new Dompdf();
        $this->data['title'] = 'Laporan Data Customer';
        $this->data['all_customer'] = $this->m_customer->lihat();
        $this->data['no'] = 1;

        $html = $this->load->view('customer/report', $this->data, true);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream('Laporan_Customer_' . date('d-m-Y') . '.pdf', ['Attachment' => false]);
    }
}