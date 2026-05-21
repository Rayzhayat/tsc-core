<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

class Tagihan_vendor extends CI_Controller {

    public function __construct() {
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
        
        $this->load->model('M_tagihan_vendor');
        $this->load->model('M_vendorr');
        $this->load->library('form_validation');
    }
    
    public function index() {
        $data['title'] = 'Tagihan Vendor';
        $data['aktif'] = 'tagihan_vendor';
        
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword')
        ];
        
        $data['tagihan'] = $this->get_filtered_data($filters);
        $data['summary'] = $this->calculate_summary($filters);
        
        $this->load->view('tagihan_vendor/lihat', $data);
    }

    private function get_filtered_data($filters) {
        // ✅ FIXED: tb_vendor uses 'kode' as primary key, not 'id'
        // ✅ FIXED: vendor_id in tb_tagihan_vendor stores 'kode' value
        $sql = "SELECT tv.*, v.nama_vendor, v.kode as vendor_kode
                FROM tb_tagihan_vendor tv 
                LEFT JOIN tb_vendor v ON v.kode = tv.vendor_id 
                WHERE 1=1 ";
        
        $params = [];
        
        if (!empty($filters['tanggal_mulai'])) {
            $sql .= " AND tv.invoice_date >= ? ";
            $params[] = $filters['tanggal_mulai'];
        }
        if (!empty($filters['tanggal_akhir'])) {
            $sql .= " AND tv.invoice_date <= ? ";
            $params[] = $filters['tanggal_akhir'];
        }
        
        if (!empty($filters['status'])) {
            if ($filters['status'] == 'belum_bayar') {
                $sql .= " AND tv.status_payment = 'Waiting Payment' ";
            } elseif ($filters['status'] == 'lunas') {
                $sql .= " AND tv.status_payment = 'Paid' ";
            } elseif ($filters['status'] == 'partial') {
                $sql .= " AND tv.status_payment = 'Partial Payment' ";
            }
        }
        
        if (!empty($filters['keyword'])) {
            $sql .= " AND (tv.no_invoice LIKE ? OR v.nama_vendor LIKE ?) ";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        $sql .= " ORDER BY tv.invoice_date DESC ";
        
        return $this->db->query($sql, $params)->result();
    }

    private function calculate_summary($filters) {
        $data = $this->get_filtered_data($filters);
        
        $summary = [
            'total' => 0,
            'belum_bayar' => 0,
            'sudah_bayar' => 0,
            'count' => count($data)
        ];
        
        foreach ($data as $item) {
            $summary['total'] += $item->nominal;
            
            if ($item->status_payment == 'Paid') {
                $summary['sudah_bayar'] += $item->nominal;
            } elseif ($item->status_payment == 'Waiting Payment') {
                $summary['belum_bayar'] += $item->nominal;
            } elseif ($item->status_payment == 'Partial Payment') {
                $summary['belum_bayar'] += $item->nominal / 2;
                $summary['sudah_bayar'] += $item->nominal / 2;
            }
        }
        
        return $summary;
    }

    public function tambah() {
        $data['title'] = 'Tambah Tagihan Vendor';
        $data['aktif'] = 'tagihan_vendor';
        $data['vendors'] = $this->M_vendorr->get_all();
        $this->load->view('tagihan_vendor/tambah', $data);
    }

    public function proses_tambah() {
        $this->form_validation->set_rules('vendor_id', 'Vendor', 'required');
        $this->form_validation->set_rules('no_invoice', 'No Invoice', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('tagihan_vendor/tambah');
            return;
        }

        $vendor_id = $this->input->post('vendor_id'); // This is 'kode' value
        $vendor = $this->M_vendorr->get_by_id($vendor_id); // Model should handle 'kode'
        $nama_vendor = $vendor ? $vendor->nama_vendor : null;

        $nominal = (float)str_replace(['.', ','], '', $this->input->post('nominal'));

        $data = [
            'vendor_id' => $vendor_id, // Store 'kode' value
            'nama_vendor' => $nama_vendor,
            'no_invoice' => $this->input->post('no_invoice'),
            'invoice_date' => $this->input->post('invoice_date'),
            'invoice_recieve_date' => $this->input->post('invoice_recieve_date'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'nominal' => $nominal,
            'status_payment' => 'Waiting Payment',
            'kode_payment' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if ($this->M_tagihan_vendor->insert($data)) {
            $this->session->set_flashdata('success', 'Tagihan vendor berhasil ditambahkan!');
            redirect('tagihan_vendor');
        } else {
            $this->session->set_flashdata('error', 'Gagal menyimpan data');
            redirect('tagihan_vendor/tambah');
        }
    }

    public function ubah($id) {
        $data['title'] = 'Ubah Tagihan Vendor';
        $data['aktif'] = 'tagihan_vendor';
        $data['tagihan'] = $this->M_tagihan_vendor->get_by_id($id);
        $data['vendors'] = $this->M_vendorr->get_all();

        if (!$data['tagihan']) {
            show_404();
        }

        $this->load->view('tagihan_vendor/ubah', $data);
    }

    public function proses_ubah() {
        $id = $this->input->post('id');

        $this->form_validation->set_rules('vendor_id', 'Vendor', 'required');
        $this->form_validation->set_rules('no_invoice', 'No Invoice', 'required');
        $this->form_validation->set_rules('nominal', 'Nominal', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('tagihan_vendor/ubah/' . $id);
            return;
        }

        $vendor_id = $this->input->post('vendor_id');
        $vendor = $this->M_vendorr->get_by_id($vendor_id);
        $nama_vendor = $vendor ? $vendor->nama_vendor : null;

        $nominal = (float)str_replace(['.', ','], '', $this->input->post('nominal'));

        $data = [
            'vendor_id' => $vendor_id,
            'nama_vendor' => $nama_vendor,
            'no_invoice' => $this->input->post('no_invoice'),
            'invoice_date' => $this->input->post('invoice_date'),
            'invoice_recieve_date' => $this->input->post('invoice_recieve_date'),
            'bulan_shipment' => $this->input->post('bulan_shipment'),
            'nominal' => $nominal,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('login')['user_id'] ?? 'admin'
        ];

        if ($this->M_tagihan_vendor->update($id, $data)) {
            $this->session->set_flashdata('success', 'Tagihan vendor berhasil diupdate!');
            redirect('tagihan_vendor');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data');
            redirect('tagihan_vendor/ubah/' . $id);
        }
    }

    public function hapus($id) {
        if ($this->M_tagihan_vendor->delete($id)) {
            $this->session->set_flashdata('success', 'Tagihan vendor berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data');
        }
        redirect('tagihan_vendor');
    }

    public function export_excel() {
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
        
        $sheet->setCellValue('A1', 'LAPORAN TAGIHAN VENDOR');
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
        
        $headers = ['No', 'Tanggal', 'No Invoice', 'Vendor', 'Nominal', 'Status', 'Bulan Shipment', 'Kode Payment'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('4e73df');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setRGB('ffffff');
            $col++;
        }
        $row++;
        
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($item->invoice_date)));
            $sheet->setCellValue('C' . $row, $item->no_invoice);
            $sheet->setCellValue('D' . $row, $item->nama_vendor);
            $sheet->setCellValue('E' . $row, $item->nominal);
            $sheet->setCellValue('F' . $row, $item->status_payment);
            $sheet->setCellValue('G' . $row, $item->bulan_shipment);
            $sheet->setCellValue('H' . $row, $item->kode_payment);
            
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $row++;
        }
        
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'Tagihan_Vendor_' . date('YmdHis') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function export_pdf() {
        $filters = [
            'tanggal_mulai' => $this->input->get('tanggal_mulai'),
            'tanggal_akhir' => $this->input->get('tanggal_akhir'),
            'status' => $this->input->get('status'),
            'keyword' => $this->input->get('keyword')
        ];
        
        $data['tagihan'] = $this->get_filtered_data($filters);
        $data['summary'] = $this->calculate_summary($filters);
        $data['filters'] = $filters;
        
        $html = $this->load->view('tagihan_vendor/report_pdf', $data, true);
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Tagihan_Vendor_' . date('YmdHis') . '.pdf', ['Attachment' => false]);
    }
}