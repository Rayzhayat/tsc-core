<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absensi extends CI_Controller
{
    const JAM_COOLDOWN_SETELAH_OUT = 6;

    public function __construct()
    {
        parent::__construct();

        $excluded = ['rfid_submit'];
        $method = $this->router->fetch_method();

        if (!in_array($method, $excluded)) {
            if (!$this->session->userdata('login')) {
                redirect('login');
            }

            $level = $this->session->userdata('login')['user_level'] ?? '';

            $allowed_levels = [
                'superadmin',
                'viewer',
                'head_of_departemen',
                'operational_lead',
                'administration_lead',
                'hr_staff',
                'admin_operational',
                'operational_staff',
                'finance_staff',
                'fleet_staff',
                'admin_document',
                'yamazaki',
                'tsf',
                'sinar_boga',
                'rorotan',
            ];

            if (!in_array($level, $allowed_levels)) {
                show_error('Akses ditolak! Anda tidak memiliki akses ke sistem absensi.', 403);
            }

            $this->user_level = $level;
            $this->user_id = $this->session->userdata('login')['id'];
        } else {
            $this->user_level = '';
            $this->user_id = null;
        }

        $user_data = $this->db->where('id', $this->user_id)
            ->get('pengguna')->row();

        $this->user_group = $user_data->group_karyawan ?? null;
        $this->can_view_laporan = (bool) ($user_data->can_view_laporan ?? false);

        $this->load->model('M_absensi');
        $this->load->model('M_pengguna');
        $this->load->library('upload');
    }

    // =====================================================
    // PRIVATE HELPERS
    // =====================================================

    private function get_laporan_groups()
    {
        if (!$this->can_view_laporan) {
            return [];
        }
        $groups = $this->M_pengguna->get_group_akses($this->user_id);
        return $groups;
    }

    private function can_see_group_laporan()
    {
        if (!$this->can_view_laporan)
            return false;
        $groups = $this->get_laporan_groups();
        return !empty($groups);
    }

    private function require_laporan_access()
    {
        if (!$this->is_admin() && !$this->can_see_group_laporan()) {
            show_error('Akses ditolak! Anda tidak memiliki izin melihat laporan.', 403);
        }
    }

    private function is_admin()
    {
        return in_array($this->user_level, [
            'superadmin',
            'admin_operational',
            'finance_staff',
            'hr_staff',
        ]);
    }

    private function require_admin()
    {
        if (!$this->is_admin()) {
            show_error('Akses ditolak! Fitur ini hanya untuk Superadmin, Admin Operational, Finance Staff, dan HR Staff.', 403);
        }
    }

    private function count_total_days($start_date, $end_date)
    {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        return $start->diff($end)->days + 1;
    }

    private function resolve_tipe($user_id)
    {
        $last_in = $this->M_absensi->get_last_in_within_24h($user_id);

        if ($last_in) {
            return ['tipe' => 'out'];
        }

        $since = date('Y-m-d H:i:s', time() - 86400);

        $this->db->where('user_id', $user_id);
        $this->db->where('tipe', 'in');
        $this->db->where('CONCAT(tanggal, " ", waktu) >=', $since);
        $this->db->order_by('tanggal', 'DESC');
        $this->db->order_by('waktu', 'DESC');
        $this->db->limit(1);
        $prev_in = $this->db->get('absensi')->row();

        if ($prev_in) {
            $in_dt = $prev_in->tanggal . ' ' . $prev_in->waktu;
            $max_dt = date('Y-m-d H:i:s', strtotime($in_dt) + 86400);

            $this->db->where('user_id', $user_id);
            $this->db->where('tipe', 'out');
            $this->db->where('CONCAT(tanggal, " ", waktu) >', $in_dt);
            $this->db->where('CONCAT(tanggal, " ", waktu) <=', $max_dt);
            $this->db->order_by('tanggal', 'ASC');
            $this->db->order_by('waktu', 'ASC');
            $this->db->limit(1);
            $prev_out = $this->db->get('absensi')->row();

            if ($prev_out) {
                $out_dt = $prev_out->tanggal . ' ' . $prev_out->waktu;
                $hours_since_out = (time() - strtotime($out_dt)) / 3600;

                if ($hours_since_out < self::JAM_COOLDOWN_SETELAH_OUT) {
                    return [
                        'error' => true,
                        'code' => 'ALREADY_COMPLETE',
                        'data' => [
                            'in' => $prev_in->waktu,
                            'out' => $prev_out->waktu,
                        ],
                    ];
                }

                return ['tipe' => 'in'];
            }
        }

        return ['tipe' => 'in'];
    }

    // =====================================================
    // PUBLIC METHODS
    // =====================================================

    public function index()
    {
        $data['title'] = 'Sistem Absensi';
        $data['aktif'] = 'absensi';
        $user = $this->session->userdata('login');

        $data['user_id'] = $user['id'];
        $data['user_name'] = $user['nama'];
        $data['user_nik'] = $user['nik'] ?? '-';
        $data['user_level'] = $user['user_level'];
        $data['user_foto_profil'] = $user['foto_profil'] ?? 'default-1.png';
        $data['is_admin'] = $this->is_admin();
        $data['can_see_laporan'] = $this->is_admin() || $this->can_see_group_laporan();

        $data['today_attendance'] = $this->M_absensi->get_by_user_today($user['id']);
        $data['today_in'] = $this->M_absensi->get_today_in($user['id']);
        $data['today_out'] = $this->M_absensi->get_today_out($user['id']);

        $resolved = $this->resolve_tipe($user['id']);
        $data['already_complete'] = isset($resolved['error']) && $resolved['error'];
        $data['current_tipe'] = $data['already_complete'] ? 'out' : ($resolved['tipe'] ?? 'in');

        $data['last_out_is_auto'] = !empty($data['today_out']) && ($data['today_out']->metode ?? '') === 'auto';

        $this->load->view('absensi/index', $data);
    }

    public function submit()
    {
        header('Content-Type: application/json');

        $user = $this->session->userdata('login');
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not authenticated']);
            return;
        }

        $resolved = $this->resolve_tipe($user['id']);

        if (isset($resolved['error']) && $resolved['error']) {
            echo json_encode([
                'success' => false,
                'message' => 'Absensi hari ini sudah lengkap (IN & OUT)! Silakan absen lagi setelah ' . self::JAM_COOLDOWN_SETELAH_OUT . ' jam.',
                'already_complete' => true,
                'in' => $resolved['data']['in'] ?? null,
                'out' => $resolved['data']['out'] ?? null,
            ]);
            return;
        }

        $tipe = $resolved['tipe'];
        $photo_base64 = $this->input->post('photo');
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
        $address = $this->input->post('address');

        if (empty($photo_base64) || empty($latitude) || empty($longitude)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
            return;
        }

        $upload_path = './uploads/absensi/';
        if (!is_dir($upload_path))
            mkdir($upload_path, 0777, true);

        $photo_data = explode(',', $photo_base64);
        $photo_decoded = base64_decode($photo_data[1]);
        $filename = 'absen_' . $user['id'] . '_' . $tipe . '_' . date('YmdHis') . '.jpg';
        $filepath = $upload_path . $filename;

        if (!file_put_contents($filepath, $photo_decoded)) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan foto']);
            return;
        }

        $data = [
            'user_id' => $user['id'],
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
            'foto' => $filename,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'alamat' => $address,
            'metode' => 'selfie',
            'tipe' => $tipe,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_absensi->insert($data)) {
            echo json_encode([
                'success' => true,
                'message' => 'Absensi ' . strtoupper($tipe) . ' berhasil disimpan!',
                'tipe' => $tipe,
                'data' => [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'photo_url' => base_url($filepath),
                ],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
        }
    }

    public function rfid_submit()
    {
        header('Content-Type: application/json');

        $api_key = $this->input->post('api_key') ?: $this->input->server('HTTP_X_API_KEY');
        $valid_key = $this->config->item('rfid_api_key');

        if ($api_key !== $valid_key) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $uid = strtoupper(trim($this->input->post('uid')));
        if (empty($uid)) {
            echo json_encode(['success' => false, 'message' => 'UID tidak boleh kosong']);
            return;
        }

        $this->load->model('M_rfid');

        $card = $this->M_rfid->get_by_uid($uid);
        if (!$card) {
            echo json_encode(['success' => false, 'message' => 'Kartu tidak terdaftar', 'code' => 'CARD_NOT_FOUND']);
            return;
        }

        if (!$card->is_active) {
            echo json_encode(['success' => false, 'message' => 'Kartu tidak aktif', 'code' => 'CARD_INACTIVE', 'nama' => $card->nama]);
            return;
        }

        $resolved = $this->resolve_tipe($card->user_id);

        if (isset($resolved['error']) && $resolved['error']) {
            echo json_encode([
                'success' => false,
                'message' => 'Absensi sudah lengkap',
                'code' => 'ALREADY_COMPLETE',
                'nama' => $card->nama,
                'in' => $resolved['data']['in'] ?? null,
                'out' => $resolved['data']['out'] ?? null,
            ]);
            return;
        }

        $tipe = $resolved['tipe'];

        $data = [
            'user_id' => $card->user_id,
            'tanggal' => date('Y-m-d'),
            'waktu' => date('H:i:s'),
            'foto' => 'rfid_no_photo.jpg',
            'latitude' => '-6.1751',
            'longitude' => '106.8650',
            'alamat' => 'Kantor TSC - Absensi RFID',
            'metode' => 'rfid',
            'tipe' => $tipe,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->M_absensi->insert($data)) {
            echo json_encode([
                'success' => true,
                'message' => 'Absensi ' . strtoupper($tipe) . ' berhasil',
                'code' => 'SUCCESS',
                'tipe' => $tipe,
                'nama' => $card->nama,
                'nik' => $card->nik,
                'waktu' => date('H:i:s'),
                'tanggal' => date('d/m/Y'),
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal simpan ke database', 'code' => 'DB_ERROR']);
        }
    }

    public function get_history()
    {
        header('Content-Type: application/json');

        $user = $this->session->userdata('login');
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not authenticated']);
            return;
        }

        $date = $this->input->get('date') ?: date('Y-m-d');
        $user_id = $this->is_admin() ? $this->input->get('user_id') : $user['id'];

        $records = $user_id
            ? $this->M_absensi->get_by_user_date($user_id, $date)
            : $this->M_absensi->get_by_date($date);

        foreach ($records as &$record) {
            $record->photo_url = base_url('uploads/absensi/' . $record->foto);
            $record->maps_url = "https://www.google.com/maps?q={$record->latitude},{$record->longitude}";
        }

        echo json_encode(['success' => true, 'data' => $records]);
    }

    public function detail($id)
    {
        $this->require_admin();

        $data['title'] = 'Detail Absensi';
        $data['aktif'] = 'absensi';

        $this->db->select('absensi.*, pengguna.nama as user_nama, pengguna.nik as user_nik, pengguna.user_level, pengguna.foto_profil as user_foto_profil');
        $this->db->from('absensi');
        $this->db->join('pengguna', 'pengguna.id = absensi.user_id');
        $this->db->where('absensi.id', $id);
        $data['record'] = $this->db->get()->row();

        if (!$data['record'])
            show_404();

        $this->load->view('absensi/detail', $data);
    }

    public function laporan()
    {
        $this->require_laporan_access();

        $data['title'] = 'Laporan Absensi';
        $data['aktif'] = 'absensi';
        $data['start_date'] = $this->input->get('start_date') ?: date('Y-m-01');
        $data['end_date'] = $this->input->get('end_date') ?: date('Y-m-t');
        $data['selected_user_id'] = $this->input->get('user_id') ?: null;
        $data['selected_group'] = $this->input->get('group') ?: null;
        $data['total_days'] = $this->count_total_days($data['start_date'], $data['end_date']);
        $data['is_admin'] = $this->is_admin();
        $data['allowed_groups'] = $this->is_admin() ? null : $this->get_laporan_groups();
        $data['locked_group'] = !$this->is_admin();

        $this->load->model('M_pengguna');
        $data['users'] = $this->is_admin() ? $this->M_pengguna->lihat() : [];

        $data['summary'] = $this->M_absensi->get_summary_stats(
            $data['start_date'],
            $data['end_date'],
            $data['selected_group'],
            $data['selected_user_id'],
            $data['allowed_groups']
        );

        $this->load->view('absensi/laporan', $data);
    }

    public function laporan_data()
    {
        header('Content-Type: application/json');
        $this->require_laporan_access();

        $start = $this->input->get('start_date') ?: date('Y-m-01');
        $end = $this->input->get('end_date') ?: date('Y-m-t');
        $group = $this->input->get('group') ?: null;
        $user_id = $this->is_admin() ? ($this->input->get('user_id') ?: null) : null;
        $tipe = $this->input->get('tipe') ?: null;
        $allowed_groups = $this->is_admin() ? null : $this->get_laporan_groups();

        $draw = (int) $this->input->get('draw');
        $offset = (int) $this->input->get('start');
        $limit = (int) $this->input->get('length');
        $search = $this->input->get('search')['value'] ?? '';

        // ── SORTING: baca parameter dari DataTables ──────────────────────────
        $order_raw = $this->input->get('order');
        $order_col_idx = isset($order_raw[0]['column']) ? (int) $order_raw[0]['column'] : 5;
        $order_dir = isset($order_raw[0]['dir']) ? strtolower($order_raw[0]['dir']) : 'desc';
        $order_dir = in_array($order_dir, ['asc', 'desc']) ? $order_dir : 'desc';

        // Map index kolom (sesuai urutan columns: di view) → kolom DB
        $column_map = [
            0 => 'absensi.id',          // No
            1 => 'absensi.tipe',        // Tipe
            2 => 'absensi.foto',        // Foto  (orderable: false di view, tapi tetap di-map)
            3 => 'pengguna.nama',       // Nama
            4 => 'pengguna.nik',        // NIK
            5 => 'absensi.tanggal',     // Tanggal
            6 => 'absensi.waktu',       // Waktu
            7 => 'absensi.alamat',      // Alamat
            8 => 'absensi.latitude',    // Lokasi (orderable: false)
            9 => 'absensi.id',          // Aksi   (orderable: false, fallback id)
        ];

        $order_col = $column_map[$order_col_idx] ?? 'absensi.tanggal';
        // ─────────────────────────────────────────────────────────────────────

        $result = $this->M_absensi->get_paginated(
            $start,
            $end,
            $group,
            $user_id,
            $tipe,
            $allowed_groups,
            $offset,
            $limit,
            $search,
            $order_col,
            $order_dir
        );

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $result['rows'],
        ]);
    }

    public function export_excel()
    {
        $this->require_admin();

        $start_date = $this->input->get('start_date') ?: date('Y-m-01');
        $end_date = $this->input->get('end_date') ?: date('Y-m-t');
        $user_id = $this->input->get('user_id') ?: null;

        $records = $this->M_absensi->get_by_period($start_date, $end_date, $user_id);
        $total_days = $this->count_total_days($start_date, $end_date);

        require_once APPPATH . '../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);

        $row = 1;

        $sheet->setCellValue('A' . $row, 'LAPORAN ABSENSI KARYAWAN');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF667eea');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $sheet->setCellValue('A' . $row, 'Periode: ' . date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date)));
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;

        $sheet->setCellValue('A' . $row, 'Total Hari dalam Periode: ' . $total_days . ' hari');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row += 2;

        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Nama Karyawan');
        $sheet->setCellValue('C' . $row, 'NIK');
        $sheet->setCellValue('D' . $row, 'Level');
        $sheet->setCellValue('E' . $row, 'Total Hadir');
        $sheet->setCellValue('F' . $row, 'Persentase');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF36b9cc');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $row++;

        $user_stats = [];
        foreach ($records as $record) {
            $uid = $record->user_id;
            if (!isset($user_stats[$uid])) {
                $user_stats[$uid] = ['nama' => $record->user_nama, 'nik' => $record->user_nik, 'level' => $record->user_level, 'count' => 0];
            }
            if ($record->tipe === 'in')
                $user_stats[$uid]['count']++;
        }
        uasort($user_stats, fn($a, $b) => $b['count'] - $a['count']);

        $no = 1;
        foreach ($user_stats as $stat) {
            $percentage = ($total_days > 0) ? ($stat['count'] / $total_days) * 100 : 0;
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $stat['nama']);
            $sheet->setCellValue('C' . $row, $stat['nik']);
            $sheet->setCellValue('D' . $row, ucwords(str_replace('_', ' ', $stat['level'])));
            $sheet->setCellValue('E' . $row, $stat['count'] . ' / ' . $total_days . ' hari');
            $sheet->setCellValue('F' . $row, number_format($percentage, 1) . '%');

            $color = $percentage >= 90 ? 'FF1cc88a' : ($percentage >= 75 ? 'FF36b9cc' : ($percentage >= 50 ? 'FFf6c23e' : 'FFe74a3b'));
            $sheet->getStyle('F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($color);
            $sheet->getStyle('F' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $summary_end_row = $row - 1;
        $row += 2;

        $sheet->setCellValue('A' . $row, 'DETAIL ABSENSI HARIAN');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF4e73df');
        $sheet->getStyle('A' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row += 2;

        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(50);
        $sheet->getColumnDimension('F')->setWidth(12);

        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Nama Karyawan');
        $sheet->setCellValue('C' . $row, 'NIK');
        $sheet->setCellValue('D' . $row, 'Tanggal');
        $sheet->setCellValue('E' . $row, 'Alamat');
        $sheet->setCellValue('F' . $row, 'Waktu');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF36b9cc');
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
        $row++;

        $no = 1;
        foreach ($records as $record) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $record->user_nama);
            $sheet->setCellValue('C' . $row, $record->user_nik);
            $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime($record->tanggal)));
            $sheet->setCellValue('E' . $row, $record->alamat);
            $sheet->setCellValue('F' . $row, $record->waktu);
            $row++;
        }

        $sheet->getStyle('A5:F' . $summary_end_row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $detail_start = $summary_end_row + 5;
        $sheet->getStyle('A' . $detail_start . ':F' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $filename = 'Laporan_Absensi_' . date('Ymd', strtotime($start_date)) . '_' . date('Ymd', strtotime($end_date)) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function delete($id)
    {
        $this->require_admin();

        $record = $this->M_absensi->get_by_id($id);
        if (!$record) {
            $this->session->set_flashdata('error', 'Data absensi tidak ditemukan!');
            redirect('absensi/laporan');
        }

        $foto_path = FCPATH . 'uploads/absensi/' . $record->foto;
        if (file_exists($foto_path))
            @unlink($foto_path);

        if ($this->M_absensi->delete($id)) {
            $this->session->set_flashdata('success', 'Data absensi berhasil dihapus!');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data absensi!');
        }

        redirect('absensi/laporan');
    }

    public function edit($id)
    {
        $this->require_admin();

        $data['title'] = 'Edit Data Absensi';
        $data['aktif'] = 'absensi';
        $data['record'] = $this->M_absensi->get_by_id($id);

        if (!$data['record'])
            show_404();

        $this->load->view('absensi/edit', $data);
    }

    public function update($id)
    {
        $this->require_admin();

        $record = $this->M_absensi->get_by_id($id);
        if (!$record) {
            $this->session->set_flashdata('error', 'Data tidak ditemukan!');
            redirect('absensi/laporan');
        }

        $tipe = $this->input->post('tipe');
        if (!in_array($tipe, ['in', 'out'])) {
            $this->session->set_flashdata('error', 'Tipe absensi tidak valid!');
            redirect('absensi/edit/' . $id);
        }

        $data = [
            'tanggal' => $this->input->post('tanggal'),
            'waktu' => $this->input->post('waktu'),
            'alamat' => $this->input->post('alamat'),
            'latitude' => $this->input->post('latitude'),
            'longitude' => $this->input->post('longitude'),
            'tipe' => $tipe,
        ];

        if ($this->M_absensi->update($id, $data)) {
            $this->session->set_flashdata('success', 'Data absensi berhasil diupdate!');
        } else {
            $this->session->set_flashdata('error', 'Gagal mengupdate data!');
        }

        redirect('absensi/laporan');
    }
}
