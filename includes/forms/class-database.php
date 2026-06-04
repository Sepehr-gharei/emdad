<?php
class Emdad_Forms_Database {
    private $table;
    public function __construct() { global $wpdb; $this->table = $wpdb->prefix . 'emdad_form_entries'; }

    public function create_table() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id BIGINT(20) AUTO_INCREMENT PRIMARY KEY,
            form_id VARCHAR(50),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) DEFAULT 0,
            data LONGTEXT
        ) {$wpdb->get_charset_collate()};";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function insert($fid, $data) {
        global $wpdb;
        return $wpdb->insert($this->table, ['form_id' => $fid, 'data' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
    }

    public function get_unread_count($form_id) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE form_id = %s AND is_read = 0", 
            $form_id
        ));
    }

    public function get_entries($fid) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table} WHERE form_id = %s ORDER BY id DESC", $fid), ARRAY_A);
    }

    public function delete($id) {
        global $wpdb;
        return $wpdb->delete($this->table, ['id' => intval($id)], ['%d']);
    }

    // این متد جا افتاده بود و باعث خطا می‌شد
    public function mark_read($id) {
        global $wpdb;
        return $wpdb->update($this->table, ['is_read' => 1], ['id' => intval($id)], ['%d'], ['%d']);
    }
}