<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WAM_Frontend {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'wp_footer',          array( $this, 'render' ) );
    }

    public function enqueue() {
        $account = WAM_DB::get_for_page( get_the_ID() );
        if ( ! $account ) return;
        wp_enqueue_style(  'wam-widget', WAM_PLUGIN_URL . 'public/css/widget.css', array(), WAM_VERSION );
        wp_enqueue_script( 'wam-widget', WAM_PLUGIN_URL . 'public/js/widget.js', array(), WAM_VERSION, true );
    }

    public function render() {
        $account = WAM_DB::get_for_page( get_the_ID() );
        if ( ! $account ) return;

        $phone   = esc_attr( preg_replace( '/[^0-9]/', '', $account->phone ) );
        $message = esc_attr( $account->message );
        $link    = 'https://api.whatsapp.com/send?phone=' . $phone . ( $message ? '&text=' . rawurlencode( $message ) : '' );

        $header_text = esc_html( $account->header_text );
        $header_sub  = esc_html( $account->header_sub );
        $agent_label = esc_html( $account->agent_label );
        $agent_desc  = esc_html( $account->agent_desc );
        $btn_text    = esc_html( $account->btn_text );
        $show_badge  = $account->badge ? 'flex' : 'none';
        ?>
        <div class="wam-wrap" id="wamWrap">
          <div class="wam-popup" id="wamPopup" aria-hidden="true" role="dialog" aria-label="WhatsApp Chat">
            <div class="wam-header">
              <div class="wam-header-icon"><?php echo self::wa_svg(22); ?></div>
              <div class="wam-header-text">
                <h3><?php echo $header_text; ?></h3>
                <p><?php echo $header_sub; ?></p>
              </div>
              <button class="wam-close" id="wamClose" aria-label="Close chat">&#x2715;</button>
            </div>
            <div class="wam-body">
              <div class="wam-agent-card">
                <div class="wam-agent-avatar"><?php echo self::wa_svg(26); ?></div>
                <div class="wam-agent-info">
                  <h4><?php echo $agent_label; ?> <span class="wam-online-badge"><span class="wam-dot"></span>Online</span></h4>
                  <p><?php echo nl2br( $agent_desc ); ?></p>
                </div>
              </div>
              <div class="wam-sep"></div>
              <a class="wam-start-btn" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                <?php echo $btn_text; ?>
              </a>
            </div>
          </div>
          <div class="wam-tail" id="wamTail"></div>
          <button class="wam-float-btn" id="wamFloatBtn" aria-label="Open WhatsApp chat">
            <?php echo self::wa_svg(30); ?>
            <span class="wam-badge" id="wamBadge" style="display:<?php echo $show_badge; ?>">1</span>
          </button>
        </div>
        <?php
    }

    private static function wa_svg( $size ) {
        return '<svg viewBox="0 0 32 32" width="' . $size . '" height="' . $size . '" fill="white" aria-hidden="true">
          <path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.736 5.471 2.027 7.775L0 32l8.454-2.01A15.938 15.938 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0zm0 29.333a13.26 13.26 0 01-6.756-1.845l-.484-.287-5.018 1.194 1.216-4.89-.316-.504A13.267 13.267 0 012.667 16C2.667 8.636 8.636 2.667 16 2.667S29.333 8.636 29.333 16 23.364 29.333 16 29.333zm7.274-9.874c-.398-.199-2.354-1.162-2.72-1.294-.365-.133-.63-.199-.896.199-.265.398-1.028 1.294-1.26 1.56-.232.265-.464.298-.862.1-.398-.199-1.681-.62-3.203-1.977-1.184-1.056-1.983-2.36-2.215-2.758-.232-.398-.025-.613.174-.811.179-.178.398-.464.597-.697.199-.232.265-.398.398-.663.133-.265.066-.497-.033-.697-.1-.199-.896-2.16-1.228-2.957-.323-.776-.651-.671-.896-.683l-.763-.013c-.265 0-.697.1-1.062.497-.365.398-1.394 1.362-1.394 3.32s1.427 3.851 1.626 4.116c.199.265 2.808 4.286 6.805 6.012.951.41 1.693.655 2.272.838.954.303 1.823.26 2.51.158.766-.114 2.354-.962 2.686-1.891.332-.929.332-1.726.232-1.891-.099-.166-.365-.265-.763-.464z"/>
        </svg>';
    }
}
