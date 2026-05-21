<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_customer extends CI_Model {

    public function lihat() {
        return $this->db->order_by('nama', 'ASC')->get('customer')->result();
    }

    public function lihat_id($id) {
        return $this->db->get_where('customer', ['id' => $id])->row();
    }

    public function tambah($data) {
        return $this->db->insert('customer', $data);
    }

    public function ubah($data, $id) {
        $this->db->where('id', $id);
        return $this->db->update('customer', $data);
    }

    public function hapus($id) {
        return $this->db->delete('customer', ['id' => $id]);
    }

    public function get_by_id($id) {
        $this->db->where('id', $id);
        return $this->db->get('customer')->row();
    }

    // Alias untuk get_all() - dibutuhkan controller Transaksi_order
    public function get_all() {
        return $this->lihat();
    }

    public function kode_customer_otomatis() {
        $this->db->select('kode');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('customer');
        if ($query->num_rows() > 0) {
            $last = $query->row()->kode;
            $num = (int) substr($last, 5) + 1;
            return 'CUST-' . sprintf('%04d', $num);
        }
        return 'CUST-0001';
    }

    // Daftar opsi PPH
    public function get_pph_options() {
        return ['0.5%', '2%', '2.5%'];
    }

    // Daftar opsi PPN
    public function get_ppn_options() {
        return ['1.1%', '11%'];
    }
}