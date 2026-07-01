<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WAM_Admin {

    public function __construct() {
        add_action( 'admin_menu',             array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue' ) );
        add_action( 'admin_post_wam_save',    array( $this, 'handle_save' ) );
        add_action( 'admin_post_wam_delete',  array( $this, 'handle_delete' ) );
        add_action( 'wp_ajax_wam_search_pages', array( $this, 'ajax_search_pages' ) );
        add_action( 'admin_init', array( $this, 'handle_clear_cache' ) );
    }

    public function register_menu() {
        add_menu_page(
            'WA Manager',
            'WA Manager',
            'manage_options',
            'wa-manager',
            array( $this, 'page_list' ),
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.117.554 4.1 1.52 5.832L0 24l6.335-1.508A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm5.455 14.595c-.299-.149-1.766-.872-2.04-.971-.274-.1-.473-.149-.672.149-.2.298-.771.97-.945 1.17-.174.199-.348.224-.647.075-.299-.15-1.262-.465-2.402-1.483-.888-.792-1.487-1.77-1.661-2.068-.174-.299-.019-.46.13-.608.134-.134.299-.348.448-.523.15-.174.2-.298.299-.497.1-.2.05-.373-.025-.523-.075-.149-.672-1.62-.921-2.217-.242-.582-.488-.503-.672-.512l-.572-.01c-.2 0-.523.075-.797.373-.274.299-1.045 1.021-1.045 2.49s1.07 2.889 1.219 3.088c.149.199 2.106 3.214 5.104 4.509.713.308 1.27.491 1.704.628.716.227 1.368.195 1.883.118.574-.086 1.766-.721 2.015-1.418.249-.697.249-1.295.174-1.419-.075-.124-.274-.199-.572-.348z"/></svg>'),
            30
        );
        add_submenu_page( 'wa-manager', 'All Accounts', 'All Accounts', 'manage_options', 'wa-manager', array( $this, 'page_list' ) );
        add_submenu_page( 'wa-manager', 'Add Account',  'Add Account',  'manage_options', 'wa-manager-add', array( $this, 'page_form' ) );
    }

    public function enqueue( $hook ) {
        if ( strpos( $hook, 'wa-manager' ) === false ) return;
        wp_enqueue_style(  'wam-admin', WAM_PLUGIN_URL . 'admin/css/admin.css', array(), WAM_VERSION );
        wp_enqueue_script( 'wam-admin', WAM_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), WAM_VERSION, true );
        wp_localize_script( 'wam-admin', 'WAM', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wam_nonce'),
        ));
    }

    public function ajax_search_pages() {
        check_ajax_referer( 'wam_nonce', 'nonce' );
        $search = sanitize_text_field( $_GET['q'] ?? '' );
        $pages  = get_pages( array( 'search' => $search, 'number' => 30 ) );
        $result = array();
        foreach ( $pages as $p ) {
            $result[] = array( 'id' => $p->ID, 'text' => $p->post_title . ' (ID: ' . $p->ID . ')' );
        }
        wp_send_json( $result );
    }

    public function handle_save() {
        if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
        check_admin_referer('wam_save_account');
        $id = intval( $_POST['account_id'] ?? 0 );
        WAM_DB::save( $_POST, $id );
        wp_redirect( admin_url('admin.php?page=wa-manager&saved=1') );
        exit;
    }

    public function handle_delete() {
        if ( ! current_user_can('manage_options') ) wp_die('Unauthorized');
        check_admin_referer('wam_delete_account');
        WAM_DB::delete( intval( $_GET['id'] ?? 0 ) );
        wp_redirect( admin_url('admin.php?page=wa-manager&deleted=1') );
        exit;
    }

    public function page_list() {
        $accounts = WAM_DB::get_all();
        include WAM_PLUGIN_DIR . 'admin/views/list.php';
    }

    public function page_form() {
        $id      = intval( $_GET['id'] ?? 0 );
        $account = $id ? WAM_DB::get( $id ) : null;
        include WAM_PLUGIN_DIR . 'admin/views/form.php';
    }
}

    public function handle_clear_cache() {
        if ( ! isset( $_GET['wam_clear_cache'] ) || ! current_user_can('manage_options') ) return;
        check_admin_referer('wam_clear_cache');
        WAM_Updater::clear_cache();
        wp_redirect( admin_url('admin.php?page=wa-manager&cache_cleared=1') );
        exit;
    }
}
