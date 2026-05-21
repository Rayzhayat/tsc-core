<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Tagihan_customer extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu!');
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, ['superadmin', 'finance_staff'])) {
            $this->session->set_flashdata('error', 'Akses ditolak!');
            redirect('dashboard');
        }

        $this->load->model('M_tagihan_customer');
        $this->load->model('M_customer');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $data['title'] = 'Tagihan Customer';
        $data['aktif'] = 'tagihan_customer';

        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword')
        ];

        $data['tagihan'] = $this->get_filtered_data($filters);
        $data['summary'] = $this->calculate_summary($filters);

        $this->load->view('tagihan_customer/lihat', $data);
    }

    private function get_filtered_data($filters)
    {
        // Note: tb_customer uses 'kode' as identifier
        $sql = "SELECT tc.*, c.nama as customer_nama, c.kode as customer_kode
                FROM tb_tagihan_customer tc 
                LEFT JOIN customer c ON c.kode = tc.customer_id 
                WHERE 1=1 ";

        $params = [];

        if (!empty($filters['tanggal_mulai'])) {
            $sql .= " AND tc.tanggal >= ? ";
            $params[] = $filters['tanggal_mulai'];
        }
        if (!empty($filters['tanggal_akhir'])) {
            $sql .= " AND tc.tanggal <= ? ";
            $params[] = $filters['tanggal_akhir'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] == 'belum_bayar') {
                $sql .= " AND tc.status_payment = 'Waiting Payment' ";
            } elseif ($filters['status'] == 'lunas') {
                $sql .= " AND tc.status_payment = 'Paid' ";
            } elseif ($filters['status'] == 'partial') {
                $sql .= " AND tc.status_payment = 'Partial Payment' ";
            }
        }

        if (!empty($filters['keyword'])) {
            $sql .= " AND (tc.no_invoice LIKE ? OR c.nama LIKE ?) ";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $sql .= " ORDER BY tc.tanggal DESC ";

        return $this->db->query($sql, $params)->result();
    }

    private function calculate_summary($filters)
    {
        $data = $this->get_filtered_data($filters);

        $summary = [
            'total' => 0,
            'belum_bayar' => 0,
            'sudah_bayar' => 0,
            'count' => count($data)
        ];

        foreach ($data as $item) {
            $summary['total'] += $item->total_tagihan;

            if ($item->status_payment == 'Paid') {
                $summary['sudah_bayar'] += $item->total_tagihan;
            } elseif ($item->status_payment == 'Waiting Payment') {
                $summary['belum_bayar'] += $item->total_tagihan;
            } elseif ($item->status_payment == 'Partial Payment') {
                $summary['belum_bayar'] += $item->total_tagihan / 2;
                $summary['sudah_bayar'] += $item->total_tagihan / 2;
            }
        }

        return $summary;
    }

    public function tambah()
    {
        $data['title'] = 'Tambah Tagihan Customer';
        $data['aktif'] = 'tagihan_customer';
        $data['customers'] = $this->M_customer->get_all();
        $this->load->view('tagihan_customer/tambah', $data);
    }

    public function proses_tambah()
    {
        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_invoice', 'No Invoice', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('tagihan_customer/tambah');
            return;
        }

        $customer_id = $this->input->post('customer_id'); // This is 'kode' value like 'CUST-0001'

        // ✅ FIXED: Get customer by kode (not id)
        $customer = $this->db->where('kode', $customer_id)->get('customer')->row();

        // ✅ OR use model method if exists
        // $customer = $this->M_customer->get_by_kode($customer_id);

        $nama_customer = $customer ? $customer->nama : null;

        $nominal = (float) str_replace(['.', ','], '', $this->input->post('nominal'));

        $data = [
            'customer_id' => $customer_id, // Store kode (CUST-0001)
            'nama_customer' => $nama_customer,
            'no_invoice' => $this->input->post('no_invoice'),
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'nominal' => $nominal,
            'ppn' => 0,
            'pph' => 0,
            'total_tagihan' => $nominal,
            'status_payment' => 'Waiting Payment',
            'kode_payment' => null,
            'deskripsi' => $this->input->post('deskripsi'),
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if ($this->M_tagihan_customer->insert($data)) {
            $this->session->set_flashdata('success', 'Tagihan customer berhasil ditambahkan!');
            redirect('tagihan_customer');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('tagihan_customer/tambah');
        }
    }

    public function ubah($id)
    {
        $data['title'] = 'Ubah Tagihan Customer';
        $data['aktif'] = 'tagihan_customer';
        $data['tagihan'] = $this->M_tagihan_customer->get_by_id($id);
        $data['customers'] = $this->M_customer->get_all();

        if (!$data['tagihan']) {
            show_404();
        }

        $this->load->view('tagihan_customer/ubah', $data);
    }

    public function proses_ubah()
    {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('customer_id', 'Customer', 'required');
        $this->form_validation->set_rules('no_invoice', 'No Invoice', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('tagihan_customer/ubah/' . $id);
            return;
        }

        $customer_id = $this->input->post('customer_id');

        // ✅ FIXED: Get customer by kode
        $customer = $this->db->where('kode', $customer_id)->get('customer')->row();

        $nama_customer = $customer ? $customer->nama : null;

        $nominal = (float) str_replace(['.', ','], '', $this->input->post('nominal'));

        $data = [
            'customer_id' => $customer_id,
            'nama_customer' => $nama_customer,
            'no_invoice' => $this->input->post('no_invoice'),
            'tanggal' => $this->input->post('tanggal'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'nominal' => $nominal,
            'total_tagihan' => $nominal,
            'deskripsi' => $this->input->post('deskripsi'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if ($this->M_tagihan_customer->update($id, $data)) {
            $this->session->set_flashdata('success', 'Tagihan customer berhasil diupdate!');
            redirect('tagihan_customer');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('tagihan_customer/ubah/' . $id);
        }
    }

    public function hapus($id)
    {
        if ($this->M_tagihan_customer->delete($id)) {
            $this->session->set_flashdata('success', 'Tagihan customer berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        redirect('tagihan_customer');
    }

    public function export_excel()
    {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword')
        ];

        $data = $this->get_filtered_data($filters);
        $summary = $this->calculate_summary($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'LAPORAN TAGIHAN CUSTOMER');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $row = 2;
        if (!empty($filters['tanggal_mulai']) || !empty($filters['tanggal_akhir'])) {
            $periode = 'Periode: ';
            $periode .= !empty($filters['tanggal_mulai']) ? date('d/m/Y', strtotime($filters['tanggal_mulai'])) : '...';
            $periode .= ' s/d ';
            $periode .= !empty($filters['tanggal_akhir']) ? date('d/m/Y', strtotime($filters['tanggal_akhir'])) : '...';
            $sheet->setCellValue('A' . $row, $periode);
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $row++;
        }

        $sheet->setCellValue('A' . $row, 'Total Tagihan: Rp ' . number_format($summary['total'], 0, ',', '.'));
        $sheet->mergeCells('A' . $row . ':C' . $row);
        $sheet->setCellValue('D' . $row, 'Belum Bayar: Rp ' . number_format($summary['belum_bayar'], 0, ',', '.'));
        $sheet->mergeCells('D' . $row . ':F' . $row);
        $sheet->setCellValue('G' . $row, 'Sudah Bayar: Rp ' . number_format($summary['sudah_bayar'], 0, ',', '.'));
        $sheet->mergeCells('G' . $row . ':H' . $row);
        $row += 2;

        $headers = ['No', 'Tanggal', 'No Invoice', 'Customer', 'Nominal', 'Total', 'Status', 'Kode Payment'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1cc88a');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('ffffff');
            $col++;
        }
        $row++;

        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($item->tanggal)));
            $sheet->setCellValue('C' . $row, $item->no_invoice);
            $sheet->setCellValue('D' . $row, $item->customer_nama);
            $sheet->setCellValue('E' . $row, $item->nominal);
            $sheet->setCellValue('F' . $row, $item->total_tagihan);
            $sheet->setCellValue('G' . $row, $item->status_payment);
            $sheet->setCellValue('H' . $row, $item->kode_payment);

            $sheet->getStyle('E' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Tagihan_Customer_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function export_pdf()
    {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword')
        ];

        $data['tagihan'] = $this->get_filtered_data($filters);
        $data['summary'] = $this->calculate_summary($filters);
        $data['filters'] = $filters;

        $html = $this->load->view('tagihan_customer/report_pdf', $data, true);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Tagihan_Customer_' . date('YmdHis') . '.pdf', ['Attachment' => false]);
    }
}