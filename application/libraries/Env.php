<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Library: Env
 * Baca & tulis file .env di root proyek (untuk halaman admin "Pengaturan").
 * Format selaras dengan loader di index.php: KEY = value, komentar diawali #.
 * Penulisan atomik (temp + rename) dengan backup ke .env.bak.
 */
class Env {

    private $path;
    private $bak_path;

    public function __construct() {
        $this->path     = FCPATH . '.env';
        $this->bak_path = FCPATH . '.env.bak';
    }

    /** Path absolut file .env (untuk ditampilkan di UI). */
    public function path() {
        return $this->path;
    }

    /** Apakah .env bisa ditulis (atau foldernya writable bila .env belum ada). */
    public function is_writable() {
        if (file_exists($this->path)) {
            return is_writable($this->path);
        }
        return is_writable(FCPATH);
    }

    /** Baca seluruh .env menjadi array key => value (tanpa komentar). */
    public function read() {
        $data = array();
        if (!is_readable($this->path)) {
            return $data;
        }
        foreach (file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            if (strlen($val) > 1 && ($val[0] === '"' || $val[0] === "'") && $val[0] === substr($val, -1)) {
                $val = substr($val, 1, -1);
            }
            $data[$key] = $val;
        }
        return $data;
    }

    /** Ambil satu nilai (default bila key tidak ada). */
    public function get($key, $default = null) {
        $data = $this->read();
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * Tulis (merge) nilai baru ke .env. Mempertahankan komentar & urutan baris
     * yang sudah ada. Backup ke .env.bak, lalu tulis atomik.
     *
     * @param array $updates key => value
     * @return bool
     */
    public function write($updates) {
        if (!is_array($updates) || empty($updates)) {
            return false;
        }

        $existing = is_readable($this->path)
            ? file($this->path, FILE_IGNORE_NEW_LINES)
            : array();

        $out   = array();
        $found = array();

        foreach ($existing as $line) {
            $trimmed    = trim($line);
            $is_comment = ($trimmed === '' || $trimmed[0] === '#');
            $parts      = explode('=', $trimmed, 2);
            if (!$is_comment && count($parts) === 2) {
                $key = trim($parts[0]);
                if (array_key_exists($key, $updates)) {
                    $out[]        = $key . ' = ' . $this->format_value($updates[$key]);
                    $found[$key]  = true;
                    continue;
                }
            }
            $out[] = $line;
        }

        // Tambahkan key baru yang belum ada di file
        foreach ($updates as $k => $v) {
            if (!isset($found[$k])) {
                $out[] = $k . ' = ' . $this->format_value($v);
            }
        }

        $content = implode("\n", $out) . "\n";

        // Backup file lama (best effort)
        if (file_exists($this->path)) {
            @copy($this->path, $this->bak_path);
        }

        // Tulis atomik: temp + rename (hindari file korup saat proses terputus)
        $tmp = $this->path . '.tmp';
        if (@file_put_contents($tmp, $content) === false) {
            return false;
        }
        if (!@rename($tmp, $this->path)) {
            @unlink($tmp);
            return false;
        }
        return true;
    }

    /** Format nilai: kutip bila mengandung karakter non-aman; kosongkan bila kosong. */
    private function format_value($value) {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }
        if (preg_match('/^[A-Za-z0-9_.\/:@\-]+$/', $value)) {
            return $value;
        }
        return '"' . str_replace('"', '\"', $value) . '"';
    }
}
