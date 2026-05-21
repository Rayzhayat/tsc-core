<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Database Backup Controller
 * 
 * Strategy:
 * 1. Coba mysqldump via shell_exec (reliable, full data)
 * 2. Fallback ke CI3 dbutil kalau shell_exec disabled
 * 
 * Backup disimpan di APPPATH.'backups/' (di luar web root)
 * Download via PHP stream, bukan direct URL
 */
class Database_backup extends CI_Controller
{
    private $backup_path;
    private $max_backups = 10;

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('login')) {
            $this->session->set_flashdata('error', 'Silakan login terlebih dahulu!');
            redirect('login');
        }

        $level = $this->session->userdata('login')['user_level'] ?? '';
        if ($level !== 'superadmin') {
            $this->session->set_flashdata('error', 'Akses ditolak! Hanya Superadmin yang dapat mengakses fitur ini.');
            redirect('dashboard');
        }

        $this->load->dbutil();
        $this->load->helper('file');
        $this->load->helper('download');

        // Simpan di luar web root — lebih aman
        $this->backup_path = APPPATH . 'backups/';

        if (!is_dir($this->backup_path)) {
            mkdir($this->backup_path, 0755, true);
        }
    }

    // =========================================================
    // INDEX — Halaman utama backup manager
    // =========================================================
    public function index()
    {
        $data['title'] = 'Database Backup Manager';
        $data['aktif'] = 'backup';
        $data['backups'] = $this->_get_backup_list();
        $data['db_name'] = $this->db->database;
        $data['db_size'] = $this->_get_database_size();
        $data['table_count'] = count($this->db->list_tables());
        $data['method'] = $this->_check_method(); // 'mysqldump' atau 'dbutil'

        $this->load->view('backup/index', $data);
    }

    // =========================================================
    // DEBUG — Cek kenapa mysqldump gagal (hapus setelah fixed)
    // =========================================================
    public function debug_mysqldump()
    {
        echo "<pre>";

        $db_name = $this->db->database;
        $host_raw = $this->db->hostname;

        // Shared hosting: kalau hostname adalah domain publik atau localhost,
        // MySQL server-nya lokal — pakai 127.0.0.1
        $public_host = (
            $host_raw === 'localhost' ||
            filter_var($host_raw, FILTER_VALIDATE_IP) === false // bukan IP = domain publik
        );
        $host = $public_host ? '127.0.0.1' : $host_raw;

        $user = $this->db->username;
        $pass = $this->db->password;
        $port = $this->db->port ?: 3306;

        echo "DB Name  : $db_name\n";
        echo "Host raw : $host_raw\n";
        echo "Host used: $host\n";
        echo "User     : $user\n";
        echo "Port     : $port\n";
        echo "backup_path: " . $this->backup_path . "\n";
        echo "backup_path writable: " . (is_writable($this->backup_path) ? 'YES' : 'NO') . "\n\n";

        $mysqldump = '/usr/bin/mysqldump';
        echo "mysqldump path: $mysqldump\n";
        echo "mysqldump exists: " . (file_exists($mysqldump) ? 'YES' : 'NO') . "\n\n";

        // Buat cnf sementara
        $cnf_file = $this->backup_path . '.test_' . uniqid() . '.cnf';
        $cnf_content = "[client]\nhost={$host}\nport={$port}\nuser={$user}\npassword={$pass}\n";
        file_put_contents($cnf_file, $cnf_content);
        chmod($cnf_file, 0600);
        echo "CNF file created: $cnf_file\n\n";

        // Test 1: cek versi mysqldump
        $cmd_ver = escapeshellcmd($mysqldump) . ' --version 2>&1';
        echo "CMD version: $cmd_ver\n";
        echo "Output: " . shell_exec($cmd_ver) . "\n";

        // Test 2: dry run — dump satu tabel kecil saja
        $cmd_test = escapeshellcmd($mysqldump)
            . ' --defaults-extra-file=' . escapeshellarg($cnf_file)
            . ' --single-transaction'
            . ' --default-character-set=utf8mb4'
            . ' ' . escapeshellarg($db_name)
            . ' pengguna'   // tabel kecil buat test
            . ' 2>&1';
        echo "CMD test: $cmd_test\n\n";
        $output = shell_exec($cmd_test);
        echo "Output (500 char pertama):\n" . substr($output, 0, 500) . "\n";

        @unlink($cnf_file);
        echo "\nDone.";
        echo "</pre>";
    }

    // =========================================================
    // CREATE — Buat backup, simpan di server
    // =========================================================
    public function create()
    {
        $result = $this->_do_backup();

        if ($result['success']) {
            $this->session->set_flashdata('success', 'Backup berhasil dibuat via ' . $result['method'] . '! File: ' . $result['filename']);
        } else {
            $this->session->set_flashdata('error', 'Backup gagal: ' . $result['error']);
        }

        redirect('database_backup');
    }

    // =========================================================
    // QUICK BACKUP — Buat backup + langsung download
    // =========================================================
    public function quick_backup()
    {
        $result = $this->_do_backup();

        if ($result['success']) {
            $this->_stream_download($result['filepath'], $result['filename']);
        } else {
            $this->session->set_flashdata('error', 'Backup gagal: ' . $result['error']);
            redirect('database_backup');
        }
    }

    // =========================================================
    // DOWNLOAD — Download file backup yang sudah ada
    // =========================================================
    public function download($filename = '')
    {
        if (empty($filename)) {
            $this->session->set_flashdata('error', 'Filename tidak valid');
            redirect('database_backup');
            return;
        }

        $filename = basename($filename); // Cegah directory traversal
        $filepath = $this->backup_path . $filename;

        if (!file_exists($filepath)) {
            $this->session->set_flashdata('error', 'File backup tidak ditemukan');
            redirect('database_backup');
            return;
        }

        log_message('info', 'Backup downloaded: ' . $filename . ' by ' . $this->session->userdata('login')['nama']);
        $this->_stream_download($filepath, $filename);
    }

    // =========================================================
    // DELETE — Hapus file backup
    // =========================================================
    public function delete($filename = '')
    {
        if (empty($filename)) {
            $this->session->set_flashdata('error', 'Filename tidak valid');
            redirect('database_backup');
            return;
        }

        $filename = basename($filename);
        $filepath = $this->backup_path . $filename;

        if (!file_exists($filepath)) {
            $this->session->set_flashdata('error', 'File backup tidak ditemukan');
            redirect('database_backup');
            return;
        }

        if (unlink($filepath)) {
            log_message('info', 'Backup deleted: ' . $filename . ' by ' . $this->session->userdata('login')['nama']);
            $this->session->set_flashdata('success', 'Backup berhasil dihapus');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus backup');
        }

        redirect('database_backup');
    }

    // =========================================================
    // PRIVATE: Core backup logic
    // =========================================================

    /**
     * Lakukan backup — coba mysqldump dulu, fallback ke dbutil
     * Return array ['success', 'filename', 'filepath', 'method', 'error']
     */
    private function _do_backup()
    {
        $db_name = $this->db->database;
        $timestamp = date('Y-m-d_His');
        $operator = $this->session->userdata('login')['nama'] ?? 'unknown';

        // --- Coba mysqldump ---
        if ($this->_is_shell_available()) {
            $result = $this->_backup_mysqldump($db_name, $timestamp);
            if ($result['success']) {
                $this->_cleanup_old_backups();
                log_message('info', "Backup via mysqldump: {$result['filename']} by {$operator}");
                return $result;
            }
            // Kalau mysqldump gagal, log dan lanjut ke fallback
            log_message('error', 'mysqldump failed: ' . ($result['error'] ?? 'unknown'));
        }

        // --- Fallback: CI3 dbutil ---
        $result = $this->_backup_dbutil($db_name, $timestamp);
        if ($result['success']) {
            $this->_cleanup_old_backups();
            log_message('info', "Backup via dbutil: {$result['filename']} by {$operator}");
        } else {
            log_message('error', 'dbutil backup failed: ' . ($result['error'] ?? 'unknown'));
        }

        return $result;
    }

    /**
     * Backup via mysqldump — paling reliable, semua data masuk
     */
    private function _backup_mysqldump($db_name, $timestamp)
    {
        // Shared hosting: kalau hostname adalah domain publik atau localhost,
        // MySQL server-nya lokal — pakai 127.0.0.1
        $host_raw = $this->db->hostname;
        $public_host = (
            $host_raw === 'localhost' ||
            filter_var($host_raw, FILTER_VALIDATE_IP) === false
        );
        $host = $public_host ? '127.0.0.1' : $host_raw;

        $user = $this->db->username;
        $pass = $this->db->password;
        $port = $this->db->port ?: 3306;

        $filename = $db_name . '_backup_' . $timestamp . '.sql';
        $filepath = $this->backup_path . $filename;

        // Buat file kredensial sementara (hindari password di command line)
        $cnf_file = $this->backup_path . '.tmp_' . uniqid() . '.cnf';
        $cnf_content = "[client]\nhost={$host}\nport={$port}\nuser={$user}\npassword={$pass}\n";
        file_put_contents($cnf_file, $cnf_content);
        chmod($cnf_file, 0600);

        // Cari path mysqldump
        $mysqldump = $this->_find_mysqldump();

        // stderr diarahkan ke file terpisah — bukan dicampur ke .sql
        // Ini penting agar output SQL bersih dan error bisa dicek terpisah
        $err_file = $this->backup_path . '.tmp_err_' . uniqid() . '.txt';

        $cmd = escapeshellcmd($mysqldump)
            . ' --defaults-extra-file=' . escapeshellarg($cnf_file)
            . ' --single-transaction'
            . ' --routines'
            . ' --triggers'
            . ' --add-drop-table'
            . ' --complete-insert'
            . ' --extended-insert'
            . ' --default-character-set=utf8mb4'
            . ' ' . escapeshellarg($db_name)
            . ' > ' . escapeshellarg($filepath)
            . ' 2> ' . escapeshellarg($err_file);

        shell_exec($cmd);

        // Baca stderr
        $stderr = file_exists($err_file) ? trim(file_get_contents($err_file)) : '';
        @unlink($err_file);

        // Hapus file cnf sementara
        @unlink($cnf_file);

        // Validasi: file harus ada dan tidak kosong
        if (!file_exists($filepath) || filesize($filepath) < 100) {
            return [
                'success' => false,
                'error' => 'mysqldump output kosong. Stderr: ' . ($stderr ?: 'none')
            ];
        }

        // Cek stderr — error fatal biasanya mengandung kata ini
        $fatal_keywords = ['Access denied', 'Can\'t connect', 'Unknown database', 'ERROR 1'];
        foreach ($fatal_keywords as $kw) {
            if (stripos($stderr, $kw) !== false) {
                @unlink($filepath);
                return ['success' => false, 'error' => 'mysqldump error: ' . $stderr];
            }
        }

        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'method' => 'mysqldump'
        ];
    }

    /**
     * Backup via CI3 dbutil — fallback
     * Note: CI3 dbutil punya limitation, data bisa tidak complete untuk tabel besar
     */
    private function _backup_dbutil($db_name, $timestamp)
    {
        try {
            $tables = $this->db->list_tables();

            if (empty($tables)) {
                return ['success' => false, 'error' => 'Tidak ada tabel ditemukan'];
            }

            $prefs = [
                'tables' => $tables,
                'ignore' => [],
                'format' => 'zip',
                'filename' => $db_name . '_backup_' . $timestamp . '.sql',
                'add_drop' => TRUE,
                'add_insert' => TRUE,
                'newline' => "\n"
            ];

            $backup = $this->dbutil->backup($prefs);

            if (!$backup || strlen($backup) < 100) {
                return ['success' => false, 'error' => 'dbutil menghasilkan output kosong'];
            }

            $filename = $db_name . '_backup_' . $timestamp . '_dbutil.zip';
            $filepath = $this->backup_path . $filename;

            if (!write_file($filepath, $backup)) {
                return ['success' => false, 'error' => 'Gagal menulis file backup'];
            }

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'method' => 'CI3 dbutil'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // =========================================================
    // PRIVATE: Helper methods
    // =========================================================

    /**
     * Cek apakah shell_exec tersedia
     */
    private function _is_shell_available()
    {
        if (!function_exists('shell_exec'))
            return false;

        $disabled = ini_get('disable_functions');
        if (strpos($disabled, 'shell_exec') !== false)
            return false;

        // Skip echo test — redirect operator kadang diblok di shared hosting
        // Cukup cek mysqldump binary langsung ada dan executable
        return file_exists('/usr/bin/mysqldump') && is_executable('/usr/bin/mysqldump');
    }

    /**
     * Cari path mysqldump yang valid
     */
    private function _find_mysqldump()
    {
        $paths = [
            '/usr/bin/mysqldump',        // Confirmed ada di Radarhost
            '/bin/mysqldump',            // Symlink di Radarhost (via which)
            '/usr/local/bin/mysqldump',
            '/usr/mysql/bin/mysqldump',
            '/opt/local/bin/mysqldump',
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        // Coba via which — tanpa redirect operator
        $which = trim((string) shell_exec('which mysqldump'));
        if (!empty($which) && file_exists($which)) {
            return $which;
        }

        return 'mysqldump'; // Last resort, pake PATH
    }

    /**
     * Cek method yang akan dipakai (untuk tampilan di view)
     */
    public function _check_method()
    {
        if ($this->_is_shell_available()) {
            $mysqldump = $this->_find_mysqldump();
            $test = shell_exec(escapeshellcmd($mysqldump) . ' --version 2>&1');
            if ($test && stripos($test, 'mysqldump') !== false) {
                return 'mysqldump';
            }
        }
        return 'dbutil';
    }

    /**
     * Stream download file langsung dari filepath (tidak expose URL)
     */
    private function _stream_download($filepath, $filename)
    {
        if (!file_exists($filepath)) {
            show_error('File tidak ditemukan', 404);
            return;
        }

        $filesize = filesize($filepath);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = ($ext === 'zip') ? 'application/zip' : 'application/octet-stream';

        // Bersihkan buffer output
        while (ob_get_level())
            ob_end_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $filesize);

        // Stream file dalam chunks — aman untuk file besar
        $handle = fopen($filepath, 'rb');
        while (!feof($handle)) {
            echo fread($handle, 8192);
            flush();
        }
        fclose($handle);
        exit;
    }

    /**
     * Ambil daftar file backup
     */
    private function _get_backup_list()
    {
        $backups = [];

        if (!is_dir($this->backup_path))
            return $backups;

        $files = glob($this->backup_path . '*.{zip,sql,gz}', GLOB_BRACE);
        if (!$files)
            return $backups;

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'size_formatted' => $this->_format_bytes(filesize($file)),
                'date' => date('d M Y, H:i:s', filemtime($file)),
                'timestamp' => filemtime($file),
                'age_days' => floor((time() - filemtime($file)) / 86400),
                'method' => strpos(basename($file), '_dbutil') !== false ? 'dbutil' : 'mysqldump',
            ];
        }

        return $backups;
    }

    /**
     * Hapus backup lama, keep hanya $max_backups
     */
    private function _cleanup_old_backups()
    {
        $backups = $this->_get_backup_list();

        if (count($backups) > $this->max_backups) {
            $to_delete = array_slice($backups, $this->max_backups);
            foreach ($to_delete as $b) {
                if (file_exists($b['filepath'])) {
                    unlink($b['filepath']);
                    log_message('info', 'Old backup auto-deleted: ' . $b['filename']);
                }
            }
        }
    }

    /**
     * Ambil ukuran database dari information_schema
     */
    private function _get_database_size()
    {
        $query = $this->db->query("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.TABLES
            WHERE table_schema = ?
        ", [$this->db->database]);

        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            return ($row->size_mb ?? 0) . ' MB';
        }

        return 'Unknown';
    }

    /**
     * Format bytes ke human-readable
     */
    private function _format_bytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}