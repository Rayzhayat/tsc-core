<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu!');
            redirect('login');
        }
    }

    public function index()
    {
        $data['title'] = 'Dashboard';
        $data['aktif'] = 'home';

        $login = $this->session->userdata('login');
        $data['nama'] = $login['nama'] ?? 'User';
        $data['level'] = $login['user_level'] ?? '';

        $data['is_superadmin'] = ($data['level'] === 'superadmin');

        // ── Shared defaults ──────────────────────────────────────────────
        $data['overdue_invoices'] = [];
        $data['upcoming_due_invoices'] = [];
        $data['total_vendor'] = 0;
        $data['total_customer'] = 0;
        $data['total_invoice_month'] = 0;
        $data['total_outstanding'] = 0;
        $data['total_outstanding_invoices'] = 0;
        $data['total_unpaid_bills'] = 0;
        $data['recent_invoices'] = [];
        $data['recent_vendors'] = [];
        $data['recent_customers'] = [];
        $data['total_pengguna'] = 0;
        $data['total_akun_biaya'] = 0;

        // ── Birthday ─────────────────────────────────────────────────────
        $this->load->model('M_pengguna');
        $data['birthday_today'] = $this->M_pengguna->ulang_tahun_hari_ini();

        // ── Broadcast banner ─────────────────────────────────────────────
        $this->load->model('M_broadcast');
        $user_row = $this->db
            ->where('id', $login['id'])
            ->get('pengguna')->row();

        $data['broadcasts_banner'] = $this->M_broadcast->get_active_for_user(
            $login['id'],
            $user_row->group_karyawan ?? '',
            $login['user_level'] ?? ''
        );

        // ── Org Chart widget ─────────────────────────────────────────────
        $this->load->model('M_org_chart');
        $data['show_orgchart'] = $this->M_org_chart->can_view($data['level']);
        $data['orgchart_tree'] = $data['show_orgchart']
            ? $this->M_org_chart->build_tree($this->M_org_chart->get_all_nodes())
            : [];

        // ════════════════════════════════════════════════════════════════
        // SUPERADMIN
        // ════════════════════════════════════════════════════════════════
        if ($data['is_superadmin']) {

            $data['total_vendor'] = $this->db->count_all('tb_vendor');
            $data['total_customer'] = $this->db->count_all('customer');
            $data['total_pengguna'] = $this->db->count_all('pengguna');
            $data['total_akun_biaya'] = $this->db->count_all('tb_akunbiaya');

            if ($this->db->table_exists('tb_invoice_tsc')) {

                // Overdue invoices
                try {
                    $data['overdue_invoices'] = $this->db
                        ->select('id, no_invoice, customer_nama, due_date, grand_total, DATEDIFF(CURDATE(), due_date) as days_overdue')
                        ->from('tb_invoice_tsc')
                        ->where('status !=', 'paid')
                        ->where('status !=', 'cancelled')
                        ->where('due_date <', date('Y-m-d'))
                        ->order_by('due_date', 'ASC')
                        ->limit(10)
                        ->get()->result();
                } catch (Exception $e) {
                    log_message('error', 'Dashboard superadmin overdue: ' . $e->getMessage());
                }

                // Upcoming H-1 H-2
                try {
                    $today = date('Y-m-d');
                    $hplus2 = date('Y-m-d', strtotime('+2 days'));
                    $data['upcoming_due_invoices'] = $this->db
                        ->select('id, no_invoice, customer_nama, due_date, grand_total, DATEDIFF(due_date, CURDATE()) as days_left')
                        ->from('tb_invoice_tsc')
                        ->where('status !=', 'paid')
                        ->where('status !=', 'cancelled')
                        ->where('due_date >=', $today)
                        ->where('due_date <=', $hplus2)
                        ->order_by('due_date', 'ASC')
                        ->limit(10)
                        ->get()->result();
                } catch (Exception $e) {
                    log_message('error', 'Dashboard superadmin upcoming: ' . $e->getMessage());
                }

                // Invoice bulan ini
                try {
                    $this->db->where('MONTH(invoice_date)', date('m'));
                    $this->db->where('YEAR(invoice_date)', date('Y'));
                    $data['total_invoice_month'] = $this->db->count_all_results('tb_invoice_tsc');
                } catch (Exception $e) {
                    log_message('error', 'Dashboard invoice month: ' . $e->getMessage());
                }

                // Outstanding amount
                try {
                    $this->db->select_sum('grand_total');
                    $this->db->where_in('status', ['sent', 'draft']);
                    $res = $this->db->get('tb_invoice_tsc')->row();
                    $data['total_outstanding'] = $res ? ($res->grand_total ?? 0) : 0;
                } catch (Exception $e) {
                    log_message('error', 'Dashboard outstanding: ' . $e->getMessage());
                }

                // Recent invoices
                try {
                    $data['recent_invoices'] = $this->db
                        ->select('no_invoice, grand_total, status, customer_nama')
                        ->from('tb_invoice_tsc')
                        ->order_by('created_at', 'DESC')
                        ->limit(5)
                        ->get()->result();
                } catch (Exception $e) {
                    log_message('error', 'Dashboard recent inv: ' . $e->getMessage());
                }

                // Count unpaid invoices
                try {
                    $this->db->where_in('status', ['sent', 'draft']);
                    $data['total_outstanding_invoices'] = $this->db->count_all_results('tb_invoice_tsc');
                } catch (Exception $e) {
                    log_message('error', 'Dashboard unpaid inv: ' . $e->getMessage());
                }
            }

            // Unpaid vendor bills
            if ($this->db->table_exists('tb_tagihan_vendor')) {
                try {
                    $this->db->where('status_payment', 'Waiting Payment');
                    $data['total_unpaid_bills'] = $this->db->count_all_results('tb_tagihan_vendor');
                } catch (Exception $e) {
                    log_message('error', 'Dashboard unpaid bills: ' . $e->getMessage());
                }
            }

            // Recent vendors & customers
            try {
                $data['recent_vendors'] = $this->db
                    ->select('kode, nama_vendor, pic_vendor')
                    ->from('tb_vendor')
                    ->order_by('created_at', 'DESC')
                    ->limit(5)
                    ->get()->result();
            } catch (Exception $e) {
                log_message('error', 'Dashboard recent vendors: ' . $e->getMessage());
            }

            try {
                $data['recent_customers'] = $this->db
                    ->select('id, kode, nama_npwp, nama')
                    ->from('customer')
                    ->order_by('id', 'DESC')
                    ->limit(5)
                    ->get()->result();
            } catch (Exception $e) {
                log_message('error', 'Dashboard recent customers: ' . $e->getMessage());
            }
        }

        // ════════════════════════════════════════════════════════════════
        // FINANCE STAFF
        // ════════════════════════════════════════════════════════════════
        if ($data['level'] === 'finance_staff') {

            $data['total_vendor'] = $this->db->count_all('tb_vendor');
            $data['total_customer'] = $this->db->count_all('customer');

            if ($this->db->table_exists('tb_invoice_tsc')) {

                try {
                    $this->db->where('MONTH(invoice_date)', date('m'));
                    $this->db->where('YEAR(invoice_date)', date('Y'));
                    $data['total_invoice_month'] = $this->db->count_all_results('tb_invoice_tsc');
                } catch (Exception $e) {
                    log_message('error', 'FS invoice month: ' . $e->getMessage());
                }

                try {
                    $this->db->select_sum('grand_total');
                    $this->db->where_in('status', ['sent', 'draft']);
                    $res = $this->db->get('tb_invoice_tsc')->row();
                    $data['total_outstanding'] = $res ? ($res->grand_total ?? 0) : 0;
                } catch (Exception $e) {
                    log_message('error', 'FS outstanding: ' . $e->getMessage());
                }

                try {
                    $data['recent_invoices'] = $this->db
                        ->select('no_invoice, grand_total, status, customer_nama')
                        ->from('tb_invoice_tsc')
                        ->order_by('created_at', 'DESC')
                        ->limit(5)
                        ->get()->result();
                } catch (Exception $e) {
                    log_message('error', 'FS recent inv: ' . $e->getMessage());
                }

                try {
                    $this->db->where_in('status', ['sent', 'draft']);
                    $data['total_outstanding_invoices'] = $this->db->count_all_results('tb_invoice_tsc');
                } catch (Exception $e) {
                    log_message('error', 'FS unpaid inv count: ' . $e->getMessage());
                }

                try {
                    $data['overdue_invoices'] = $this->db
                        ->select('id, no_invoice, customer_nama, due_date, grand_total, DATEDIFF(CURDATE(), due_date) as days_overdue')
                        ->from('tb_invoice_tsc')
                        ->where('status !=', 'paid')
                        ->where('status !=', 'cancelled')
                        ->where('due_date <', date('Y-m-d'))
                        ->order_by('due_date', 'ASC')
                        ->limit(10)
                        ->get()->result();
                } catch (Exception $e) {
                    log_message('error', 'FS overdue: ' . $e->getMessage());
                }

                try {
                    $today = date('Y-m-d');
                    $hplus2 = date('Y-m-d', strtotime('+2 days'));
                    $data['upcoming_due_invoices'] = $this->db
                        ->select('id, no_invoice, customer_nama, due_date, grand_total, DATEDIFF(due_date, CURDATE()) as days_left')
                        ->from('tb_invoice_tsc')
                        ->where('status !=', 'paid')
                        ->where('status !=', 'cancelled')
                        ->where('due_date >=', $today)
                        ->where('due_date <=', $hplus2)
                        ->order_by('due_date', 'ASC')
                        ->limit(10)
                        ->get()->result();
                } catch (Exception $e) {
                    log_message('error', 'FS upcoming: ' . $e->getMessage());
                }
            }

            if ($this->db->table_exists('tb_tagihan_vendor')) {
                try {
                    $this->db->where('status_payment', 'Waiting Payment');
                    $data['total_unpaid_bills'] = $this->db->count_all_results('tb_tagihan_vendor');
                } catch (Exception $e) {
                    log_message('error', 'FS unpaid bills: ' . $e->getMessage());
                }
            }
        }

        $this->load->view('home', $data);
    }
}