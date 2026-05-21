<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Feedback extends CI_Controller
{
    private $allowed_levels = ['superadmin', 'finance_staff', 'head_of_departemen', 'operational_lead', 'operational_staff'];
    private $upload_path;

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if (!in_array($level, $this->allowed_levels)) {
            show_error('Akses ditolak', 403);
        }

        $this->load->model('M_feedback');
        $this->load->helper(['url', 'form']);
        $this->load->library(['upload', 'session']);

        $this->upload_path = FCPATH . 'uploads/feedback_tmp/';
        if (!is_dir($this->upload_path))
            mkdir($this->upload_path, 0755, true);
    }

    // ════════════════════════════════════════════════════════════════
    // Helper: simpan hasil ke file JSON (bukan session)
    // ════════════════════════════════════════════════════════════════
    private function _save_result($results, $vendor_summary)
    {
        $path = $this->upload_path . 'result_' . session_id() . '.json';
        file_put_contents($path, json_encode([
            'results' => $results,
            'vendor_summary' => $vendor_summary,
        ]));
        $this->session->set_userdata('feedback_result_path', $path);
        $this->session->unset_userdata('feedback_result');
        $this->session->unset_userdata('feedback_vendor_summary');
    }

    private function _load_result()
    {
        $path = $this->session->userdata('feedback_result_path') ?? '';
        if (empty($path) || !file_exists($path)) {
            return null;
        }
        $data = json_decode(file_get_contents($path), true);
        return $data ?: null;
    }

    // ════════════════════════════════════════════════════════════════
    // Helper: load merged lookup dari semua file yang sudah diupload
    // ════════════════════════════════════════════════════════════════
    private function _load_merged_lookup()
    {
        $lookup = [];
        $lookup_nopol = [];
        $total_lt = 0;

        // Support multiple lookup JSON files (master data 1, 2, dst.)
        $json_paths = $this->session->userdata('feedback_lookup_jsons') ?? [];

        // Backward compat: single lookup JSON lama
        $single = $this->session->userdata('feedback_lookup_json') ?? '';
        if (!empty($single) && file_exists($single) && !in_array($single, $json_paths)) {
            $json_paths[] = $single;
        }

        foreach ($json_paths as $json_path) {
            if (empty($json_path) || !file_exists($json_path))
                continue;

            $raw = json_decode(file_get_contents($json_path), true);
            if (!is_array($raw))
                continue;

            // Backward compat: format lama flat array
            if (isset($raw['lookup']) && is_array($raw['lookup'])) {
                $chunk_lt = $raw['lookup'];
                $chunk_nopol = $raw['lookup_nopol'] ?? [];
            } else {
                $chunk_lt = $raw;
                $chunk_nopol = [];
            }

            // Merge — jangan timpa yang sudah ada (file pertama prioritas)
            foreach ($chunk_lt as $lt_key => $val) {
                if (!isset($lookup[$lt_key])) {
                    $lookup[$lt_key] = $val;
                    $total_lt++;
                }
            }
            foreach ($chunk_nopol as $nk => $val) {
                if (!isset($lookup_nopol[$nk])) {
                    $lookup_nopol[$nk] = $val;
                }
            }
        }

        return [
            'lookup' => $lookup,
            'lookup_nopol' => $lookup_nopol,
            'total_lt' => $total_lt,
            'ready' => !empty($lookup),
        ];
    }

    // ════════════════════════════════════════════════════════════════
    // GET /feedback
    // ════════════════════════════════════════════════════════════════
    public function index()
    {
        $login = $this->session->userdata('login');

        $data['title'] = 'Feedback SPX — Vendor Lookup';
        $data['aktif'] = 'feedback';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['message'] = $this->session->flashdata('message');
        $data['error'] = $this->session->flashdata('error');
        $data['xl_path'] = $this->session->userdata('feedback_xl_path') ?? '';
        $data['sheet_names'] = [];

        // Kumpulkan info semua master data yang sudah diupload
        $json_paths = $this->session->userdata('feedback_lookup_jsons') ?? [];
        $master_infos = $this->session->userdata('feedback_master_infos') ?? [];

        // Backward compat: single old key
        $single_json = $this->session->userdata('feedback_lookup_json') ?? '';
        if (!empty($single_json) && file_exists($single_json) && !in_array($single_json, $json_paths)) {
            $json_paths[] = $single_json;
            $master_infos[] = [
                'name' => 'Master Data (lama)',
                'total_lt' => $this->session->userdata('feedback_lookup_total') ?? 0,
                'path' => $single_json,
            ];
        }

        $data['master_infos'] = $master_infos;
        $data['master_count'] = count($json_paths);
        $data['lookup_ready'] = !empty($json_paths) && !empty($master_infos);
        $data['lookup_total'] = array_sum(array_column($master_infos, 'total_lt'));

        if (!empty($data['xl_path']) && file_exists($data['xl_path'])) {
            $res = $this->M_feedback->get_sheet_names($data['xl_path']);
            if ($res['success']) {
                $data['sheet_names'] = array_filter(
                    $res['sheets'],
                    fn($s) => stripos($s, 'leadtime') !== false
                );
                if (empty($data['sheet_names'])) {
                    $data['sheet_names'] = $res['sheets'];
                }
                $data['sheet_names'] = array_values($data['sheet_names']);
            }
        }

        $this->load->view('partials/head', $data);
        $this->load->view('feedback/index', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ════════════════════════════════════════════════════════════════
    // POST /feedback/upload_csv  (master data — bisa multiple kali)
    // ════════════════════════════════════════════════════════════════
    public function upload_csv()
    {
        $config = [
            'upload_path' => $this->upload_path,
            'allowed_types' => 'csv|xlsx|xls',
            'max_size' => 50120,
            'overwrite' => false,
            'file_name' => 'dailyrent_' . session_id() . '_' . time(),
        ];
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('csv_file')) {
            $this->session->set_flashdata('error', 'Upload gagal: ' . $this->upload->display_errors('', ''));
            redirect('feedback');
        }

        $file = $this->upload->data();
        $filepath = $this->upload_path . $file['file_name'];
        $ext = strtolower($file['file_ext']);

        if (in_array($ext, ['.xlsx', '.xls'])) {
            $result = $this->M_feedback->build_lookup_from_excel($filepath);
        } else {
            $result = $this->M_feedback->build_lookup_from_csv($filepath);
        }

        if (!$result['success']) {
            @unlink($filepath);
            $this->session->set_flashdata('error', 'Gagal parse master data: ' . $result['message']);
            redirect('feedback');
        }

        // Simpan lookup ke JSON baru (tidak menghapus yang lama)
        $json_path = $this->upload_path . 'lookup_' . session_id() . '_' . time() . '.json';
        file_put_contents($json_path, json_encode([
            'lookup' => $result['lookup'],
            'lookup_nopol' => $result['lookup_nopol'] ?? [],
        ]));
        @unlink($filepath);

        // Append ke daftar (multiple support)
        $json_paths = $this->session->userdata('feedback_lookup_jsons') ?? [];
        $master_infos = $this->session->userdata('feedback_master_infos') ?? [];

        $json_paths[] = $json_path;
        $master_infos[] = [
            'name' => $file['orig_name'],
            'total_lt' => $result['total_lt'],
            'path' => $json_path,
        ];

        $this->session->set_userdata('feedback_lookup_jsons', $json_paths);
        $this->session->set_userdata('feedback_master_infos', $master_infos);

        // Hitung total gabungan
        $total_all = array_sum(array_column($master_infos, 'total_lt'));
        $this->session->set_userdata('feedback_lookup_total', $total_all);

        // Pesan sukses
        $nth = count($master_infos);
        $msg = 'Master data #' . $nth . ' berhasil diparse: <strong>' . number_format($result['total_lt']) .
            ' LT Number</strong> dari <strong>' . $file['orig_name'] . '</strong>';

        if (!empty($result['sheet_stats'])) {
            $msg .= '<ul class="mb-0 mt-1">';
            foreach ($result['sheet_stats'] as $sname => $sstat) {
                $icon = strpos($sstat, '0 LT') !== false ? '⚠️' : '✅';
                $msg .= '<li><small>' . $icon . ' <b>' . htmlspecialchars($sname) . '</b>: ' . $sstat . '</small></li>';
            }
            $msg .= '</ul>';
        }

        if ($nth > 1) {
            $msg .= '<br><small class="text-info">📦 Total gabungan semua master data: <strong>' . number_format($total_all) . ' LT Number</strong></small>';
        }

        $this->session->set_flashdata('message', $msg);
        redirect('feedback');
    }

    // ════════════════════════════════════════════════════════════════
    // POST /feedback/remove_master  (hapus salah satu master data)
    // ════════════════════════════════════════════════════════════════
    public function remove_master()
    {
        $idx = (int) $this->input->post('idx');

        $json_paths = $this->session->userdata('feedback_lookup_jsons') ?? [];
        $master_infos = $this->session->userdata('feedback_master_infos') ?? [];

        if (isset($json_paths[$idx])) {
            @unlink($json_paths[$idx]);
            array_splice($json_paths, $idx, 1);
            array_splice($master_infos, $idx, 1);
        }

        $this->session->set_userdata('feedback_lookup_jsons', $json_paths);
        $this->session->set_userdata('feedback_master_infos', $master_infos);
        $this->session->set_userdata('feedback_lookup_total', array_sum(array_column($master_infos, 'total_lt')));

        $this->session->set_flashdata('message', 'Master data berhasil dihapus dari daftar.');
        redirect('feedback');
    }

    // ════════════════════════════════════════════════════════════════
    // POST /feedback/upload_excel
    // ════════════════════════════════════════════════════════════════
    public function upload_excel()
    {
        $config = [
            'upload_path' => $this->upload_path,
            'allowed_types' => 'xlsx|xls',
            'max_size' => 30720,
            'overwrite' => false,
            'file_name' => 'spx_feedback_' . session_id() . '_' . time(),
        ];
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('xl_file')) {
            $this->session->set_flashdata('error', 'Upload Excel gagal: ' . $this->upload->display_errors('', ''));
            redirect('feedback');
        }

        $file = $this->upload->data();
        $old = $this->session->userdata('feedback_xl_path');
        if ($old && file_exists($old))
            @unlink($old);

        $this->session->set_userdata('feedback_xl_path', $this->upload_path . $file['file_name']);
        $this->session->set_flashdata('message', 'Excel SPX berhasil diupload: <strong>' . $file['orig_name'] . '</strong>');
        redirect('feedback');
    }

    // ════════════════════════════════════════════════════════════════
    // POST /feedback/process
    // ════════════════════════════════════════════════════════════════
    public function process()
    {
        $xl_path = $this->session->userdata('feedback_xl_path') ?? '';
        $sheet_name = $this->input->post('sheet_name');

        // Load merged lookup dari semua master data yang sudah diupload
        $merged = $this->_load_merged_lookup();

        if (!$merged['ready']) {
            $this->session->set_flashdata('error', 'Upload minimal satu master data Dailyrent dulu di Step 1.');
            redirect('feedback');
        }
        if (empty($xl_path) || !file_exists($xl_path)) {
            $this->session->set_flashdata('error', 'Upload Excel SPX dulu di Step 2.');
            redirect('feedback');
        }
        if (empty($sheet_name)) {
            $this->session->set_flashdata('error', 'Pilih sheet Leadtime terlebih dahulu.');
            redirect('feedback');
        }

        $result = $this->M_feedback->process_feedback_excel(
            $xl_path,
            $sheet_name,
            $merged['lookup'],
            $merged['lookup_nopol']
        );

        if (!$result['success']) {
            $this->session->set_flashdata('error', $result['message']);
            redirect('feedback');
        }

        $this->_save_result($result['results'], $result['vendor_summary']);

        $login = $this->session->userdata('login');

        $data['title'] = 'Hasil Lookup — Feedback SPX';
        $data['aktif'] = 'feedback';
        $data['level'] = $login['user_level'] ?? '';
        $data['nama'] = $login['nama'] ?? '';
        $data['sheet_name'] = $sheet_name;
        $data['results'] = $result['results'];
        $data['total_open'] = $result['total_open'];
        $data['total_matched'] = $result['total_matched'];
        $data['total_not_found'] = $result['total_not_found'];
        $data['vendor_summary'] = $result['vendor_summary'];
        $data['total_lt_csv'] = $merged['total_lt'];
        $data['master_infos'] = $this->session->userdata('feedback_master_infos') ?? [];

        $this->load->view('partials/head', $data);
        $this->load->view('feedback/result', $data);
        $this->load->view('partials/footer');
        $this->load->view('partials/js');
    }

    // ════════════════════════════════════════════════════════════════
    // GET /feedback/export
    // ════════════════════════════════════════════════════════════════
    public function export()
    {
        $data = $this->_load_result();

        if (empty($data)) {
            $this->session->set_flashdata('error', 'Tidak ada data hasil. Lakukan proses dulu.');
            redirect('feedback');
        }

        $filename = 'SPX_Feedback_Vendor_' . date('Ymd_His') . '.xlsx';
        session_write_close();
        $this->M_feedback->export_to_excel($data['results'], $data['vendor_summary'], $filename);
    }

    // ════════════════════════════════════════════════════════════════
    // GET /feedback/export_vendor
    // ════════════════════════════════════════════════════════════════
    public function export_vendor()
    {
        $data = $this->_load_result();
        $vendor_name = $this->input->get('vendor');

        if (empty($data) || empty($vendor_name)) {
            $this->session->set_flashdata('error', 'Tidak ada data atau vendor tidak valid.');
            redirect('feedback');
        }

        $filtered = array_filter($data['results'], function ($r) use ($vendor_name) {
            return strtolower($r['vendor_tsc']) === strtolower($vendor_name)
                && strtolower($r['status']) === 'open';
        });

        $vendor_sum_filtered = [];
        if (isset($data['vendor_summary'][$vendor_name])) {
            $vendor_sum_filtered[$vendor_name] = $data['vendor_summary'][$vendor_name];
        }

        $safe_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $vendor_name);
        $filename = 'SPX_Feedback_' . $safe_name . '_' . date('Ymd_His') . '.xlsx';

        session_write_close();
        $this->M_feedback->export_to_excel(array_values($filtered), $vendor_sum_filtered, $filename);
    }

    // ════════════════════════════════════════════════════════════════
    // GET /feedback/reset
    // ════════════════════════════════════════════════════════════════
    public function reset()
    {
        // Hapus semua lookup JSON (multiple)
        $json_paths = $this->session->userdata('feedback_lookup_jsons') ?? [];
        foreach ($json_paths as $path) {
            if ($path && file_exists($path))
                @unlink($path);
        }

        // Backward compat: single old key
        $old_single = $this->session->userdata('feedback_lookup_json');
        if ($old_single && file_exists($old_single))
            @unlink($old_single);

        // Hapus file Excel SPX
        $xl_path = $this->session->userdata('feedback_xl_path');
        if ($xl_path && file_exists($xl_path))
            @unlink($xl_path);

        // Hapus result
        $result_path = $this->session->userdata('feedback_result_path');
        if ($result_path && file_exists($result_path))
            @unlink($result_path);

        // Unset semua session key
        $keys = [
            'feedback_lookup_jsons',
            'feedback_master_infos',
            'feedback_lookup_json',
            'feedback_lookup_total',
            'feedback_xl_path',
            'feedback_result_path',
            'feedback_result',
            'feedback_vendor_summary',
            'feedback_csv_path',
        ];
        foreach ($keys as $k) {
            $this->session->unset_userdata($k);
        }

        $this->session->set_flashdata('message', 'Data direset. Silakan upload file baru.');
        redirect('feedback');
    }
}