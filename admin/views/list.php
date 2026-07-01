<?php if ( ! defined('ABSPATH') ) exit; ?>
<div class="wrap wam-admin">
  <div class="wam-top-bar">
    <div class="wam-top-bar-left">
      <div class="wam-logo">
        <span class="wam-logo-icon">
          <svg viewBox="0 0 32 32" width="22" height="22" fill="white"><path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.736 5.471 2.027 7.775L0 32l8.454-2.01A15.938 15.938 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0zm7.274 19.459c-.398-.199-2.354-1.162-2.72-1.294-.365-.133-.63-.199-.896.199-.265.398-1.028 1.294-1.26 1.56-.232.265-.464.298-.862.1-.398-.199-1.681-.62-3.203-1.977-1.184-1.056-1.983-2.36-2.215-2.758-.232-.398-.025-.613.174-.811.179-.178.398-.464.597-.697.199-.232.265-.398.398-.663.133-.265.066-.497-.033-.697-.1-.199-.896-2.16-1.228-2.957-.323-.776-.651-.671-.896-.683l-.763-.013c-.265 0-.697.1-1.062.497-.365.398-1.394 1.362-1.394 3.32s1.427 3.851 1.626 4.116c.199.265 2.808 4.286 6.805 6.012.951.41 1.693.655 2.272.838.954.303 1.823.26 2.51.158.766-.114 2.354-.962 2.686-1.891.332-.929.332-1.726.232-1.891-.099-.166-.365-.265-.763-.464z"/></svg>
        </span>
        <span class="wam-logo-text">WA Manager</span>
      </div>
    </div>
    <a href="<?php echo admin_url('admin.php?page=wa-manager-add'); ?>" class="wam-btn-primary">+ Add Account</a>
  </div>

  <?php if ( isset($_GET['cache_cleared']) ) : ?>
    <div class="wam-notice success">🔄 Update cache cleared. WordPress will re-check GitHub now.</div>
  <?php elseif ( isset($_GET['saved']) ) : ?>
    <div class="wam-notice success">✅ Account saved successfully.</div>
  <?php elseif ( isset($_GET['deleted']) ) : ?>
    <div class="wam-notice success">🗑️ Account deleted.</div>
  <?php endif; ?>

  <?php if ( empty($accounts) ) : ?>
    <div class="wam-empty">
      <div class="wam-empty-icon">
        <svg viewBox="0 0 32 32" width="48" height="48" fill="#25D366"><path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.736 5.471 2.027 7.775L0 32l8.454-2.01A15.938 15.938 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0z"/></svg>
      </div>
      <h2>No accounts yet</h2>
      <p>Add your first WhatsApp account to get started.</p>
      <a href="<?php echo admin_url('admin.php?page=wa-manager-add'); ?>" class="wam-btn-primary">+ Add First Account</a>
    </div>
  <?php else : ?>
    <div class="wam-cards">
      <?php foreach ( $accounts as $a ) :
        $page_ids = array_filter( array_map('trim', explode(',', $a->page_ids)) );
        $page_names = array();
        foreach ( $page_ids as $pid ) {
          $p = get_post( (int)$pid );
          $page_names[] = $p ? esc_html($p->post_title) : 'ID:'.$pid;
        }
      ?>
      <div class="wam-card">
        <div class="wam-card-header">
          <div class="wam-card-icon">
            <svg viewBox="0 0 32 32" width="20" height="20" fill="white"><path d="M16 0C7.163 0 0 7.163 0 16c0 2.822.736 5.471 2.027 7.775L0 32l8.454-2.01A15.938 15.938 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0z"/></svg>
          </div>
          <div class="wam-card-title">
            <h3><?php echo esc_html($a->name); ?></h3>
            <span class="wam-phone">+<?php echo esc_html($a->phone); ?></span>
          </div>
          <?php if ( $a->is_global ) : ?>
            <span class="wam-tag global">Global</span>
          <?php else : ?>
            <span class="wam-tag page"><?php echo count($page_ids); ?> page<?php echo count($page_ids) !== 1 ? 's' : ''; ?></span>
          <?php endif; ?>
        </div>
        <div class="wam-card-body">
          <?php if ( $a->message ) : ?>
            <div class="wam-meta"><span class="wam-label">Pre-fill message:</span> <?php echo esc_html( mb_strimwidth($a->message, 0, 60, '…') ); ?></div>
          <?php endif; ?>
          <?php if ( ! $a->is_global && $page_names ) : ?>
            <div class="wam-meta"><span class="wam-label">Assigned pages:</span>
              <?php foreach ($page_names as $pn) : ?><span class="wam-pill"><?php echo $pn; ?></span><?php endforeach; ?>
            </div>
          <?php elseif ( $a->is_global ) : ?>
            <div class="wam-meta"><span class="wam-label">Applies to:</span> All pages (fallback)</div>
          <?php endif; ?>
        </div>
        <div class="wam-card-footer">
          <a href="<?php echo admin_url('admin.php?page=wa-manager-add&id='.$a->id); ?>" class="wam-btn-sm">✏️ Edit</a>
          <a href="<?php echo wp_nonce_url( admin_url('admin-post.php?action=wam_delete&id='.$a->id), 'wam_delete_account' ); ?>"
             class="wam-btn-sm danger"
             onclick="return confirm('Delete this account?')">🗑️ Delete</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div style="margin-top:32px; padding:14px 20px; background:#fff; border-radius:10px; box-shadow:0 1px 4px rgba(0,0,0,.07); display:flex; align-items:center; justify-content:space-between; font-size:13px; color:#666;">
  <span>WA Manager v<?php echo WAM_VERSION; ?> · by <a href="https://github.com/aqdushammad" target="_blank" style="color:#25D366;text-decoration:none;font-weight:600;">Aqdus Hammad</a> · <a href="https://github.com/aqdushammad/wa-manager/releases" target="_blank" style="color:#25D366;text-decoration:none;">GitHub ↗</a></span>
  <a href="<?php echo wp_nonce_url( admin_url('admin.php?page=wa-manager&wam_clear_cache=1'), 'wam_clear_cache' ); ?>" style="background:#f5f5f5;border:1px solid #ddd;border-radius:6px;padding:6px 12px;color:#555;text-decoration:none;font-size:12px;">🔄 Check for Updates Now</a>
</div>
