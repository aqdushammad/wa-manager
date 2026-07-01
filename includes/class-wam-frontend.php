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
        wp_enqueue_script( 'wam-widget', WAM_PLUGIN_URL . 'public/js/widget.js',   array(), WAM_VERSION, true );
    }

    public function render() {
        $account = WAM_DB::get_for_page( get_the_ID() );
        if ( ! $account ) return;

        $phone   = esc_attr( preg_replace( '/[^0-9]/', '', $account->phone ) );
        $message = esc_attr( $account->message );
        $link    = 'https://api.whatsapp.com/send?phone=' . $phone . ( $message ? '&text=' . rawurlencode( $account->message ) : '' );

        $header_text = esc_html( $account->header_text );
        $header_sub  = esc_html( $account->header_sub );
        $agent_label = esc_html( $account->agent_label );
        $agent_desc  = esc_html( $account->agent_desc );
        $btn_text    = esc_html( $account->btn_text );
        $show_badge  = $account->badge ? 'flex' : 'none';

        // Design settings (with safe defaults for old DB rows)
        $position     = ( isset( $account->widget_position ) && $account->widget_position === 'left' ) ? 'left' : 'right';
        $btn_color    = isset( $account->btn_color )    && $account->btn_color    ? esc_attr( $account->btn_color )    : '#25D366';
        $header_color = isset( $account->header_color ) && $account->header_color ? esc_attr( $account->header_color ) : '#1a7c3e';
        $agent_color  = isset( $account->agent_color )  && $account->agent_color  ? esc_attr( $account->agent_color )  : '#25D366';
        $icon_style   = isset( $account->icon_style )   && $account->icon_style   ? $account->icon_style               : 'whatsapp';

        // Position class
        $pos_class = $position === 'left' ? ' wam-pos-left' : '';

        // Inline CSS variables for this widget instance
        $inline_style = sprintf(
            '--wam-btn:%s;--wam-header:%s;--wam-avatar:%s;',
            $btn_color, $header_color, $agent_color
        );
        ?>
        <div class="wam-wrap<?php echo $pos_class; ?>" id="wamWrap" style="<?php echo esc_attr( $inline_style ); ?>">
          <div class="wam-popup" id="wamPopup" aria-hidden="true" role="dialog" aria-label="WhatsApp Chat">
            <div class="wam-header">
              <div class="wam-header-icon"><?php echo self::get_icon( $icon_style, 22 ); ?></div>
              <div class="wam-header-text">
                <h3><?php echo $header_text; ?></h3>
                <p><?php echo $header_sub; ?></p>
              </div>
              <button class="wam-close" id="wamClose" aria-label="Close chat">&#x2715;</button>
            </div>
            <div class="wam-body">
              <!-- Agent card is now a clickable link to WhatsApp -->
              <a class="wam-agent-card" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp">
                <div class="wam-agent-avatar"><?php echo self::get_icon( $icon_style, 26 ); ?></div>
                <div class="wam-agent-info">
                  <h4><?php echo $agent_label; ?> <span class="wam-online-badge"><span class="wam-dot"></span>Online</span></h4>
                  <p><?php echo nl2br( $agent_desc ); ?></p>
                </div>
                <div class="wam-agent-arrow">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                </div>
              </a>
              <div class="wam-sep"></div>
              <a class="wam-start-btn" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" width="17" height="17" fill="white"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                <?php echo $btn_text; ?>
              </a>
            </div>
          </div>
          <div class="wam-tail" id="wamTail"></div>
          <button class="wam-float-btn" id="wamFloatBtn" aria-label="Open WhatsApp chat">
            <?php echo self::get_icon( $icon_style, 30 ); ?>
            <span class="wam-badge" id="wamBadge" style="display:<?php echo $show_badge; ?>">1</span>
          </button>
        </div>
        <?php
    }

    /**
     * Return the correct SVG icon based on icon_style setting.
     */
    private static function get_icon( $style, $size ) {
        switch ( $style ) {
            case 'chat':
                return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="white" aria-hidden="true">
                  <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                </svg>';

            case 'phone':
                return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="white" aria-hidden="true">
                  <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                </svg>';

            case 'whatsapp':
            default:
                return '<svg viewBox="0 0 32 32" width="' . $size . '" height="' . $size . '" fill="white" aria-hidden="true">
                  <path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.736 5.471 2.027 7.775L0 32l8.454-2.01A15.938 15.938 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0zm0 29.333a13.26 13.26 0 01-6.756-1.845l-.484-.287-5.018 1.194 1.216-4.89-.316-.504A13.267 13.267 0 012.667 16C2.667 8.636 8.636 2.667 16 2.667S29.333 8.636 29.333 16 23.364 29.333 16 29.333zm7.274-9.874c-.398-.199-2.354-1.162-2.72-1.294-.365-.133-.63-.199-.896.199-.265.398-1.028 1.294-1.26 1.56-.232.265-.464.298-.862.1-.398-.199-1.681-.62-3.203-1.977-1.184-1.056-1.983-2.36-2.215-2.758-.232-.398-.025-.613.174-.811.179-.178.398-.464.597-.697.199-.232.265-.398.398-.663.133-.265.066-.497-.033-.697-.1-.199-.896-2.16-1.228-2.957-.323-.776-.651-.671-.896-.683l-.763-.013c-.265 0-.697.1-1.062.497-.365.398-1.394 1.362-1.394 3.32s1.427 3.851 1.626 4.116c.199.265 2.808 4.286 6.805 6.012.951.41 1.693.655 2.272.838.954.303 1.823.26 2.51.158.766-.114 2.354-.962 2.686-1.891.332-.929.332-1.726.232-1.891-.099-.166-.365-.265-.763-.464z"/>
                </svg>';
        }
    }
}
