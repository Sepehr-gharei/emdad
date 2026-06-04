<?php
if (!defined('ABSPATH')) {
    exit;
}

class EmdadCamera_Login_DB {
    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'emdadcamera_otp_codes';
    }

    public static function create_table() {
        global $wpdb;
        $table_name = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            mobile VARCHAR(50) NOT NULL,
            otp_code VARCHAR(20) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            last_error VARCHAR(100) NOT NULL DEFAULT '',
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            verified_at DATETIME NULL DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY mobile (mobile),
            KEY expires_at (expires_at),
            KEY created_at (created_at),
            KEY status (status)
        ) {$charset_collate};";

        dbDelta($sql);
        return empty($wpdb->last_error);
    }

    public static function ensure_table() {
        global $wpdb;
        $table_name = self::table_name();
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name));
        if ($exists !== $table_name) {
            self::create_table();
            $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table_name));
        }
        return $exists === $table_name;
    }

    public static function drop_table() {
        global $wpdb;
        $table_name = self::table_name();
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }

    public static function should_keep_records() {
        return (int) get_option('emdadcamera_notify_keep_otp_records', 0) === 1;
    }

    public static function retention_days() {
        $days = (int) get_option('emdadcamera_notify_otp_record_retention_days', 1);
        return in_array($days, array(1, 2), true) ? $days : 1;
    }

    public static function insert_otp($mobile, $otp) {
        global $wpdb;

        self::cleanup_expired_otps();

        if (!self::ensure_table()) {
            return array(
                'success' => false,
                'reason' => 'table_missing',
                'db_error' => (string) $wpdb->last_error,
            );
        }

        $table_name = self::table_name();
        $expires_at = gmdate('Y-m-d H:i:s', time() + 300);
        $created_at = gmdate('Y-m-d H:i:s');

        $result = $wpdb->replace(
            $table_name,
            array(
                'mobile' => $mobile,
                'otp_code' => $otp,
                'status' => 'pending',
                'last_error' => '',
                'expires_at' => $expires_at,
                'created_at' => $created_at,
                'verified_at' => null,
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        return array(
            'success' => $result !== false,
            'reason' => $result !== false ? 'stored' : 'db_replace_failed',
            'db_error' => (string) $wpdb->last_error,
            'expires_at' => $expires_at,
            'created_at' => $created_at,
            'affected_rows' => (int) $result,
        );
    }

    public static function get_otp_row($mobile) {
        global $wpdb;

        if (!self::ensure_table()) {
            return false;
        }

        $table_name = self::table_name();
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE mobile = %s LIMIT 1",
                $mobile
            ),
            ARRAY_A
        );
    }

    public static function verify_otp($mobile, $otp) {
        global $wpdb;

        self::cleanup_expired_otps();

        if (!self::ensure_table()) {
            return array(
                'success' => false,
                'reason' => 'table_missing',
                'db_error' => (string) $wpdb->last_error,
            );
        }

        $row = self::get_otp_row($mobile);
        if (!$row) {
            return array(
                'success' => false,
                'reason' => 'mobile_not_found',
                'db_error' => (string) $wpdb->last_error,
            );
        }

        $now = gmdate('Y-m-d H:i:s');
        $table_name = self::table_name();

        if (!isset($row['otp_code']) || (string) $row['otp_code'] !== (string) $otp) {
            if (self::should_keep_records()) {
                $wpdb->update($table_name, array('status' => 'mismatch', 'last_error' => 'code_mismatch'), array('mobile' => $mobile), array('%s', '%s'), array('%s'));
            }
            return array(
                'success' => false,
                'reason' => 'code_mismatch',
                'db_error' => (string) $wpdb->last_error,
            );
        }

        if (empty($row['expires_at']) || (string) $row['expires_at'] <= $now) {
            if (self::should_keep_records()) {
                $wpdb->update($table_name, array('status' => 'expired', 'last_error' => 'expired'), array('mobile' => $mobile), array('%s', '%s'), array('%s'));
            } else {
                $wpdb->delete($table_name, array('mobile' => $mobile), array('%s'));
            }
            return array(
                'success' => false,
                'reason' => 'expired',
                'db_error' => (string) $wpdb->last_error,
                'expires_at' => isset($row['expires_at']) ? (string) $row['expires_at'] : '',
                'now' => $now,
            );
        }

        if (self::should_keep_records()) {
            $updated = $wpdb->update(
                $table_name,
                array(
                    'status' => 'verified',
                    'last_error' => '',
                    'verified_at' => $now,
                ),
                array('mobile' => $mobile),
                array('%s', '%s', '%s'),
                array('%s')
            );
            if ($updated === false) {
                return array(
                    'success' => false,
                    'reason' => 'update_failed_after_match',
                    'db_error' => (string) $wpdb->last_error,
                );
            }
        } else {
            $deleted = $wpdb->delete($table_name, array('mobile' => $mobile), array('%s'));
            if ($deleted === false) {
                return array(
                    'success' => false,
                    'reason' => 'delete_failed_after_match',
                    'db_error' => (string) $wpdb->last_error,
                );
            }
        }

        return array(
            'success' => true,
            'reason' => 'verified',
            'db_error' => (string) $wpdb->last_error,
            'expires_at' => isset($row['expires_at']) ? (string) $row['expires_at'] : '',
            'now' => $now,
        );
    }

    public static function cleanup_expired_otps() {
        global $wpdb;
        if (!self::ensure_table()) {
            return;
        }
        $table_name = self::table_name();
        $now = gmdate('Y-m-d H:i:s');

        if (self::should_keep_records()) {
            $wpdb->query($wpdb->prepare("UPDATE {$table_name} SET status = 'expired', last_error = 'expired' WHERE expires_at <= %s AND status = 'pending'", $now));
            $cutoff = gmdate('Y-m-d H:i:s', time() - (DAY_IN_SECONDS * self::retention_days()));
            $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE created_at < %s", $cutoff));
        } else {
            $wpdb->query($wpdb->prepare("DELETE FROM {$table_name} WHERE expires_at <= %s OR status IN ('verified','expired','mismatch')", $now));
        }
    }

    public static function clear_all_records() {
        global $wpdb;
        if (!self::ensure_table()) {
            return false;
        }
        $table_name = self::table_name();
        $result = $wpdb->query("TRUNCATE TABLE {$table_name}");
        return $result !== false;
    }

    public static function count_records() {
        global $wpdb;
        if (!self::ensure_table()) {
            return 0;
        }
        $table_name = self::table_name();
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    }

    public static function get_logs($limit = 100) {
        if (function_exists('emdadcamera_notify_read_logs')) {
            return emdadcamera_notify_read_logs($limit);
        }
        return array();
    }
}
