<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Flash;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

final class SettingsController
{
    public function index(): void
    {
        if (!Auth::check()) redirect('/login');

        $business = Database::first("SELECT * FROM app_settings WHERE id = 1");
        $tax      = Database::first("SELECT * FROM tax_settings WHERE id = 1");

        view('settings/index', [
            'layout'    => 'app',
            'pageTitle' => 'Settings',
            'user'      => Auth::user(),
            'business'  => $business,
            'tax'       => $tax,
            'isAdmin'   => auth_role() === 'ADMIN',
        ]);
    }

    // -------- Save business info --------
    public function saveBusiness(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $data = [
            'business_name'    => trim($_POST['business_name'] ?? ''),
            'business_tagline' => trim($_POST['business_tagline'] ?? ''),
            'business_address' => trim($_POST['business_address'] ?? ''),
            'business_phone'   => trim($_POST['business_phone'] ?? ''),
            'business_email'   => trim($_POST['business_email'] ?? ''),
            'business_tin'     => trim($_POST['business_tin'] ?? ''),
            'business_vrn'     => trim($_POST['business_vrn'] ?? ''),
        ];

        $v = new Validator($data);
        $v->required('business_name', 'Business name')
          ->email('business_email', 'Email');
        if ($v->fails()) {
            foreach ($v->errors() as $m) Flash::error($m);
            redirect('/settings');
        }

        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        Database::execute(
            "UPDATE app_settings SET $set, last_updated = ? WHERE id = 1",
            [...array_values($data), now()]
        );

        Flash::success('Business info saved.');
        redirect('/settings');
    }

    // -------- Upload logo --------
    public function uploadLogo(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
            Flash::error('No file uploaded.');
            redirect('/settings');
        }

        $file = $_FILES['logo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            Flash::error('Upload error.');
            redirect('/settings');
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            Flash::error('Logo must be under 2 MB.');
            redirect('/settings');
        }

        $mime = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            Flash::error('Logo must be JPG, PNG, or WEBP.');
            redirect('/settings');
        }

        $name = 'logo_' . time() . '.' . $allowed[$mime];
        $dir = dirname(__DIR__, 2) . '/public/uploads/logos';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $path = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            Flash::error('Could not save logo.');
            redirect('/settings');
        }

        // Delete old logo
        $old = Database::scalar("SELECT business_logo_path FROM app_settings WHERE id = 1");
        if ($old) {
            $oldFull = dirname(__DIR__, 2) . '/public/uploads/' . $old;
            if (is_file($oldFull)) @unlink($oldFull);
        }

        Database::execute(
            "UPDATE app_settings SET business_logo_path = ?, last_updated = ? WHERE id = 1",
            ['logos/' . $name, now()]
        );

        Flash::success('Logo updated.');
        redirect('/settings');
    }

    // -------- Save tax settings --------
    public function saveTax(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $entityType = $_POST['entity_type'] ?? 'SOLE_PROPRIETOR';
        if (!in_array($entityType, ['SOLE_PROPRIETOR', 'LIMITED_COMPANY'], true)) {
            Flash::error('Invalid entity type.');
            redirect('/settings');
        }

        Database::execute(
            "UPDATE tax_settings SET
                entity_type = ?,
                corporate_rate = ?,
                vat_rate = ?,
                vat_registration_threshold = ?,
                last_updated = ?,
                updated_by = ?
             WHERE id = 1",
            [
                $entityType,
                (float)($_POST['corporate_rate'] ?? 30),
                (float)($_POST['vat_rate'] ?? 18),
                (float)($_POST['vat_threshold'] ?? 100000000),
                now(),
                Auth::user()['full_name'] ?? 'SYSTEM',
            ]
        );

        Flash::success('Tax settings saved.');
        redirect('/settings');
    }

    // -------- Change own password --------
    public function changePassword(): void
    {
        if (!Auth::check()) redirect('/login');

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = Auth::user();
        if (!$user || !password_verify($current, $user['password_hash'])) {
            Flash::error('Current password is incorrect.');
            redirect('/settings');
        }
        if (strlen($new) < 6) {
            Flash::error('New password must be at least 6 characters.');
            redirect('/settings');
        }
        if ($new !== $confirm) {
            Flash::error('Passwords do not match.');
            redirect('/settings');
        }

        User::resetPassword((int)$user['employee_id'], $new);
        Flash::success('Password changed successfully.');
        redirect('/settings');
    }

    // -------- Download database backup --------
    public function downloadBackup(): void
    {
        if (!Auth::check()) redirect('/login');
        require_role('ADMIN');

        $cfg = config('db');
        $filename = 'kodafirca-backup-' . date('Ymd-His') . '.sql';

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Try mysqldump
        $cmd = sprintf(
            'mysqldump --single-transaction --quick --host=%s --port=%d --user=%s --password=%s %s 2>&1',
            escapeshellarg($cfg['host']),
            $cfg['port'],
            escapeshellarg($cfg['user']),
            escapeshellarg($cfg['pass']),
            escapeshellarg($cfg['name'])
        );

        $output = shell_exec($cmd);
        if ($output && strpos($output, 'mysqldump:') === false && strpos($output, 'Access denied') === false) {
            echo $output;
            exit;
        }

        // Fallback: manual PHP dump
        echo "-- Kodafirca POS Backup\n-- Generated: " . now() . "\n\n";
        echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

        $tables = Database::select("SHOW TABLES");
        foreach ($tables as $row) {
            $tableName = array_values($row)[0];
            $create = Database::first("SHOW CREATE TABLE `$tableName`");
            if ($create) {
                echo "\n-- Table: $tableName\n";
                echo "DROP TABLE IF EXISTS `$tableName`;\n";
                echo $create['Create Table'] . ";\n\n";

                $rows = Database::select("SELECT * FROM `$tableName`");
                if (!empty($rows)) {
                    $cols = array_keys($rows[0]);
                    $colList = '`' . implode('`,`', $cols) . '`';
                    foreach ($rows as $r) {
                        $vals = array_map(function($v) {
                            if ($v === null) return 'NULL';
                            return "'" . addslashes((string)$v) . "'";
                        }, array_values($r));
                        echo "INSERT INTO `$tableName` ($colList) VALUES (" . implode(',', $vals) . ");\n";
                    }
                }
                echo "\n";
            }
        }
        echo "SET FOREIGN_KEY_CHECKS = 1;\n";
        exit;
    }
}
