<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WAM_DB {

    const TABLE = 'wam_accounts';

    public static function install() {
        global $wpdb;
        $table      = $wpdb->prefix . self::TABLE;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name        VARCHAR(100)    NOT NULL DEFAULT '',
            phone       VARCHAR(30)     NOT NULL DEFAULT '',
            message     TEXT                     DEFAULT '',
            badge       TINYINT(1)      NOT NULL DEFAULT 1,
            agent_label VARCHAR(100)    NOT NULL DEFAULT 'WhatsApp Support',
            agent_desc  VARCHAR(255)    NOT NULL DEFAULT 'We are here to help you with any questions you may have.',
            header_text VARCHAR(100)    NOT NULL DEFAULT 'Chat with us',
            header_sub  VARCHAR(150)    NOT NULL DEFAULT 'We typically reply in a few minutes',
            btn_text    VARCHAR(100)    NOT NULL DEFAULT 'Start Chat on WhatsApp',
            page_ids    LONGTEXT                 DEFAULT '',
            is_global   TINYINT(1)      NOT NULL DEFAULT 0,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        update_option( 'wam_db_version', WAM_DB_VERSION );
    }

    public static function deactivate() {}

    public static function get_all() {
        global $wpdb;
        return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}" . self::TABLE . " ORDER BY id DESC" );
    }

    public static function get( $id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}" . self::TABLE . " WHERE id = %d", $id
        ) );
    }

    public static function save( $data, $id = 0 ) {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE;

        $fields = array(
            'name'        => sanitize_text_field( $data['name'] ?? '' ),
            'phone'       => sanitize_text_field( $data['phone'] ?? '' ),
            'message'     => sanitize_textarea_field( $data['message'] ?? '' ),
            'badge'       => isset( $data['badge'] ) ? 1 : 0,
            'agent_label' => sanitize_text_field( $data['agent_label'] ?? 'WhatsApp Support' ),
            'agent_desc'  => sanitize_textarea_field( $data['agent_desc'] ?? '' ),
            'header_text' => sanitize_text_field( $data['header_text'] ?? 'Chat with us' ),
            'header_sub'  => sanitize_text_field( $data['header_sub'] ?? '' ),
            'btn_text'    => sanitize_text_field( $data['btn_text'] ?? 'Start Chat on WhatsApp' ),
            'page_ids'    => sanitize_text_field( $data['page_ids'] ?? '' ),
            'is_global'   => isset( $data['is_global'] ) ? 1 : 0,
        );

        if ( $id > 0 ) {
            $wpdb->update( $table, $fields, array( 'id' => $id ), null, array( '%d' ) );
            return $id;
        } else {
            $wpdb->insert( $table, $fields );
            return $wpdb->insert_id;
        }
    }

    public static function delete( $id ) {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . self::TABLE, array( 'id' => $id ), array( '%d' ) );
    }

    /** Return the account that should appear on the current page */
    public static function get_for_page( $page_id ) {
        $all = self::get_all();
        $fallback = null;

        foreach ( $all as $account ) {
            if ( $account->is_global ) {
                $fallback = $account;
                continue;
            }
            $ids = array_filter( array_map( 'trim', explode( ',', $account->page_ids ) ) );
            if ( in_array( (string) $page_id, $ids, true ) ) {
                return $account;
            }
        }
        return $fallback;
    }
}
