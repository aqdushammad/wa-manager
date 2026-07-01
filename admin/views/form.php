<?php if ( ! defined('ABSPATH') ) exit;
$is_edit = ! is_null($account);
$v = function( $key, $default = '' ) use ( $account ) {
    if ( $account && isset($account->$key) ) return esc_attr($account->$key);
    return esc_attr($default);
};

$selected_page_ids = $is_edit ? array_filter(array_map('trim', explode(',', $account->page_ids))) : array();
?>
<div class="wrap wam-admin">
  <div class="wam-top-bar">
    <div class="wam-top-bar-left">
      <div class="wam-logo">
        <span class="wam-logo-icon">
          <svg viewBox="0 0 32 32" width="22" height="22" fill="white"><path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.736 5.471 2.027 7.775L0 32l8.454-2.01A15.938 15.938 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0z"/></svg>
        </span>
        <span class="wam-logo-text">WA Manager</span>
      </div>
    </div>
    <a href="<?php echo admin_url('admin.php?page=wa-manager'); ?>" class="wam-btn-sm">← Back to accounts</a>
  </div>

  <div class="wam-form-wrap">
    <h2 class="wam-form-title"><?php echo $is_edit ? '✏️ Edit Account' : '➕ Add New Account'; ?></h2>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
      <?php wp_nonce_field('wam_save_account'); ?>
      <input type="hidden" name="action" value="wam_save">
      <input type="hidden" name="account_id" value="<?php echo $is_edit ? $account->id : 0; ?>">

      <!-- SECTION: Identity -->
      <div class="wam-section">
        <h3 class="wam-section-title">📋 Account Identity</h3>
        <div class="wam-row-2">
          <div class="wam-field">
            <label>Account Name <span class="req">*</span></label>
            <input type="text" name="name" value="<?php echo $v('name'); ?>" placeholder="e.g. Sales Team UAE" required>
            <p class="wam-help">Internal label — not shown on website.</p>
          </div>
          <div class="wam-field">
            <label>WhatsApp Number <span class="req">*</span></label>
            <input type="text" name="phone" value="<?php echo $v('phone'); ?>" placeholder="971526132864" required>
            <p class="wam-help">Country code + number, no + or spaces. e.g. 971526132864</p>
          </div>
        </div>
        <div class="wam-field">
          <label>Pre-filled Message (optional)</label>
          <textarea name="message" rows="2" placeholder="Hello, I am interested in your service!"><?php echo esc_textarea($account->message ?? ''); ?></textarea>
          <p class="wam-help">This text opens pre-typed in WhatsApp when the visitor taps the button.</p>
        </div>
      </div>

      <!-- SECTION: Widget Text -->
      <div class="wam-section">
        <h3 class="wam-section-title">💬 Widget Text</h3>
        <div class="wam-row-2">
          <div class="wam-field">
            <label>Header Title</label>
            <input type="text" name="header_text" value="<?php echo $v('header_text','Chat with us'); ?>" placeholder="Chat with us">
          </div>
          <div class="wam-field">
            <label>Header Subtitle</label>
            <input type="text" name="header_sub" value="<?php echo $v('header_sub','We typically reply in a few minutes'); ?>" placeholder="We typically reply in a few minutes">
          </div>
        </div>
        <div class="wam-row-2">
          <div class="wam-field">
            <label>Agent / Team Label</label>
            <input type="text" name="agent_label" value="<?php echo $v('agent_label','WhatsApp Support'); ?>" placeholder="WhatsApp Support">
          </div>
          <div class="wam-field">
            <label>Button Text</label>
            <input type="text" name="btn_text" value="<?php echo $v('btn_text','Start Chat on WhatsApp'); ?>" placeholder="Start Chat on WhatsApp">
          </div>
        </div>
        <div class="wam-field">
          <label>Agent Description</label>
          <textarea name="agent_desc" rows="2" placeholder="We're here to help you with any questions you may have."><?php echo esc_textarea($account->agent_desc ?? "We're here to help you with any questions you may have."); ?></textarea>
        </div>
        <div class="wam-field wam-check-row">
          <label class="wam-toggle">
            <input type="checkbox" name="badge" value="1" <?php checked($v('badge','1'), '1'); ?>>
            <span class="wam-toggle-slider"></span>
            Show notification badge (red dot with "1") on the floating button
          </label>
        </div>
      </div>

      <!-- SECTION: Page Assignment -->
      <div class="wam-section">
        <h3 class="wam-section-title">📄 Page Assignment</h3>
        <div class="wam-field wam-check-row">
          <label class="wam-toggle">
            <input type="checkbox" name="is_global" value="1" id="wamIsGlobal" <?php checked($v('is_global','0'), '1'); ?>>
            <span class="wam-toggle-slider"></span>
            Show on all pages (global fallback) — shown only if no specific account matches the page
          </label>
        </div>
        <div class="wam-field" id="wamPageField">
          <label>Assign to Specific Pages</label>
          <div class="wam-page-search-wrap">
            <input type="text" id="wamPageSearch" placeholder="Search pages by name…" autocomplete="off">
            <div class="wam-page-suggestions" id="wamSuggestions"></div>
          </div>
          <input type="hidden" name="page_ids" id="wamPageIds" value="<?php echo esc_attr(implode(',', $selected_page_ids)); ?>">
          <div class="wam-selected-pages" id="wamSelectedPages">
            <?php foreach ($selected_page_ids as $pid) :
              $pg = get_post((int)$pid);
              $title = $pg ? esc_html($pg->post_title) : 'ID:'.$pid;
            ?>
            <span class="wam-page-tag" data-id="<?php echo $pid; ?>"><?php echo $title; ?> <button type="button">×</button></span>
            <?php endforeach; ?>
          </div>
          <p class="wam-help">Search and select one or more pages. This account's widget will show only on these pages.</p>
        </div>
      </div>

      <div class="wam-form-actions">
        <button type="submit" class="wam-btn-primary">💾 Save Account</button>
        <a href="<?php echo admin_url('admin.php?page=wa-manager'); ?>" class="wam-btn-sm">Cancel</a>
      </div>
    </form>
  </div>
</div>
