<?php
/**
 * WAM_Updater — checks GitHub releases and hooks into WordPress update system.
 *
 * How it works:
 *  1. On the WP "update_plugins" transient check, we hit the GitHub Releases API.
 *  2. If the latest release tag is newer than the installed version, we inject
 *     an update object so WP shows "Update Available" on the Plugins page.
 *  3. When the admin clicks Update, WP downloads the release ZIP from GitHub
 *     and replaces the plugin folder automatically — exactly like a .org plugin.
 *
 * To release a new version:
 *  1. Bump WAM_VERSION in wa-manager.php  (e.g. 1.0.0 → 1.1.0)
 *  2. Push to GitHub.
 *  3. Create a GitHub Release with a tag matching the version (e.g. v1.1.0).
 *     GitHub auto-generates a ZIP of that tag — WP will download it.
 *  4. Every WordPress site running the plugin will see "Update Available" within 12 h.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class WAM_Updater {

    private $plugin_file;
    private $github_user;
    private $github_repo;
    private $current_version;
    private $plugin_slug;
    private $plugin_basename;
    private $cache_key;
    private $cache_ttl = 43200; // 12 hours

    public function __construct( $plugin_file, $github_user, $github_repo, $current_version ) {
        $this->plugin_file     = $plugin_file;
        $this->github_user     = $github_user;
        $this->github_repo     = $github_repo;
        $this->current_version = $current_version;
        $this->plugin_slug     = $github_repo;
        $this->plugin_basename = plugin_basename( $plugin_file );
        $this->cache_key       = 'wam_github_release_' . $github_repo;

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api',                           array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'upgrader_post_install',                 array( $this, 'after_install' ), 10, 3 );
        add_action( 'admin_notices',                         array( $this, 'maybe_show_update_notice' ) );
    }

    /* -----------------------------------------------------------------------
     * 1. Fetch latest release info from GitHub (cached)
     * -------------------------------------------------------------------- */
    private function get_release_info() {
        $cached = get_transient( $this->cache_key );
        if ( $cached !== false ) return $cached;

        $url      = "https://api.github.com/repos/{$this->github_user}/{$this->github_repo}/releases/latest";
        $response = wp_remote_get( $url, array(
            'headers'   => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
            ),
            'timeout'   => 15,
            'sslverify' => true,
        ) );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            set_transient( $this->cache_key, false, 3600 ); // cache failure for 1 h
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ) );
        set_transient( $this->cache_key, $body, $this->cache_ttl );
        return $body;
    }

    /* -----------------------------------------------------------------------
     * 2. Inject update into WP transient
     * -------------------------------------------------------------------- */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) return $transient;

        $release = $this->get_release_info();
        if ( ! $release || empty( $release->tag_name ) ) return $transient;

        // Strip leading 'v' from tag (v1.1.0 → 1.1.0)
        $latest = ltrim( $release->tag_name, 'vV' );

        if ( version_compare( $latest, $this->current_version, '>' ) ) {
            $transient->response[ $this->plugin_basename ] = (object) array(
                'id'            => "github.com/{$this->github_user}/{$this->github_repo}",
                'slug'          => $this->plugin_slug,
                'plugin'        => $this->plugin_basename,
                'new_version'   => $latest,
                'url'           => "https://github.com/{$this->github_user}/{$this->github_repo}",
                'package'       => $release->zipball_url,  // GitHub auto-ZIP of release tag
                'icons'         => array(),
                'banners'       => array(),
                'requires'      => '5.6',
                'tested'        => '6.7',
                'requires_php'  => '7.4',
            );
        }

        return $transient;
    }

    /* -----------------------------------------------------------------------
     * 3. Populate plugin info popup (the "View version X.X details" modal)
     * -------------------------------------------------------------------- */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) return $result;
        if ( ! isset( $args->slug ) || $args->slug !== $this->plugin_slug ) return $result;

        $release = $this->get_release_info();
        if ( ! $release ) return $result;

        $latest  = ltrim( $release->tag_name, 'vV' );
        $body    = isset( $release->body ) ? nl2br( esc_html( $release->body ) ) : 'See GitHub for full changelog.';

        return (object) array(
            'name'          => 'WA Manager – WhatsApp Per Page',
            'slug'          => $this->plugin_slug,
            'version'       => $latest,
            'author'        => '<a href="https://github.com/aqdushammad">Aqdus Hammad</a>',
            'homepage'      => "https://github.com/{$this->github_user}/{$this->github_repo}",
            'requires'      => '5.6',
            'tested'        => '6.7',
            'requires_php'  => '7.4',
            'downloaded'    => 0,
            'last_updated'  => isset( $release->published_at ) ? date( 'Y-m-d', strtotime( $release->published_at ) ) : '',
            'sections'      => array(
                'description' => 'Assign different WhatsApp numbers to different pages with a beautiful floating chat widget. Manage all accounts from one clean dashboard.',
                'changelog'   => '<h4>' . esc_html( $release->tag_name ) . '</h4><p>' . $body . '</p>',
                'installation'=> '<ol><li>Upload the plugin ZIP via <strong>Plugins → Add New → Upload</strong>.</li><li>Activate the plugin.</li><li>Go to <strong>WA Manager</strong> in the sidebar and add your first WhatsApp account.</li></ol>',
            ),
            'download_link' => $release->zipball_url,
        );
    }

    /* -----------------------------------------------------------------------
     * 4. After install: rename the unzipped GitHub folder to our plugin slug
     *    (GitHub names the folder  aqdushammad-wa-manager-{hash} — we fix it)
     * -------------------------------------------------------------------- */
    public function after_install( $response, $hook_extra, $result ) {
        if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
            return $response;
        }

        global $wp_filesystem;
        $install_dir    = trailingslashit( WP_PLUGIN_DIR ) . $this->plugin_slug;
        $wp_filesystem->move( $result['destination'], $install_dir, true );
        $result['destination'] = $install_dir;

        // Re-activate the plugin
        activate_plugin( $this->plugin_basename );

        return $result;
    }

    /* -----------------------------------------------------------------------
     * 5. Admin notice on Plugins page if update available
     * -------------------------------------------------------------------- */
    public function maybe_show_update_notice() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'plugins' ) return;

        $release = $this->get_release_info();
        if ( ! $release ) return;

        $latest = ltrim( $release->tag_name, 'vV' );
        if ( ! version_compare( $latest, $this->current_version, '>' ) ) return;

        $update_url = wp_nonce_url(
            admin_url( 'update.php?action=upgrade-plugin&plugin=' . urlencode( $this->plugin_basename ) ),
            'upgrade-plugin_' . $this->plugin_basename
        );

        echo '<div class="notice notice-warning is-dismissible" style="border-left-color:#25D366">';
        echo '<p><strong>WA Manager</strong> — A new version <strong>v' . esc_html( $latest ) . '</strong> is available. ';
        echo '<a href="' . esc_url( $update_url ) . '">Update now</a> or ';
        echo '<a href="https://github.com/' . esc_attr( $this->github_user ) . '/' . esc_attr( $this->github_repo ) . '/releases/latest" target="_blank">view changelog on GitHub ↗</a></p>';
        echo '</div>';
    }

    /** Force-clear the cached release info (useful after pushing a new release) */
    public static function clear_cache() {
        delete_transient( 'wam_github_release_' . WAM_GITHUB_REPO );
    }
}
