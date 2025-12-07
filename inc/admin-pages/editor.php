<?php
/**
 * Live Editor Admin Page
 * 
 * Edit HTML templates with live preview
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_he_editor_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $sets_all   = base47_he_get_template_sets();
    $active     = base47_he_get_active_sets();

    if ( empty( $active ) ) {
        echo '<div class="wrap"><h1>Live Editor</h1><p>No active themes. Enable at least one in <strong>Theme Manager</strong>.</p></div>';
        return;
    }

    $current_set = isset( $_GET['set'] ) ? sanitize_text_field( wp_unslash( $_GET['set'] ) ) : $active[0];
    if ( ! in_array( $current_set, $active, true ) ) {
        $current_set = $active[0];
    }

    $files = [];
    if ( isset( $sets_all[ $current_set ] ) && is_dir( $sets_all[ $current_set ]['path'] ) ) {
        foreach ( new DirectoryIterator( $sets_all[ $current_set ]['path'] ) as $f ) {
            if ( $f->isFile() ) {
                $ext = strtolower( pathinfo( $f->getFilename(), PATHINFO_EXTENSION ) );
                if ( in_array( $ext, ['html','htm'], true ) ) {
                    $files[] = $f->getFilename();
                }
            }
        }
    }
    sort( $files, SORT_NATURAL | SORT_FLAG_CASE );

    $selected = isset( $_GET['file'] ) ? sanitize_text_field( wp_unslash( $_GET['file'] ) ) : ( $files[0] ?? '' );
    $content  = '';
    if ( $selected && isset( $sets_all[ $current_set ] ) && file_exists( $sets_all[ $current_set ]['path'] . $selected ) ) {
        $content = file_get_contents( $sets_all[ $current_set ]['path'] . $selected );
    }

  $preview = $selected
    ? admin_url(
        'admin-ajax.php?action=base47_he_preview&file='
        . rawurlencode( $selected )
        . '&set=' . rawurlencode( $current_set )
        . '&_wpnonce=' . wp_create_nonce( 'base47_he' )
    )
    : '';

    ?>
    <div class="wrap base47-he-wrap">
        <h1>Live Editor</h1>
        
        <?php if ( ! base47_he_has_feature( 'monaco_editor' ) ) : ?>
        <div class="base47-pro-notice" style="margin: 20px 0;">
            <div class="pro-notice-icon">
                <span class="dashicons dashicons-lock"></span>
            </div>
            <div class="pro-notice-content">
                <h4>Monaco Editor is a Pro Feature</h4>
                <p>Upgrade to Pro to unlock the Monaco Editor (VS Code experience) with IntelliSense, multi-cursor editing, and advanced features.</p>
                <a href="<?php echo esc_url( base47_he_get_pro_url() ); ?>" class="button button-primary" target="_blank">
                    Upgrade to Pro
                    <span class="dashicons dashicons-external"></span>
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="base47-he-editor-topbar">
            <form method="get">
                <input type="hidden" name="page" value="base47-he-editor">
                <select name="set" onchange="this.form.submit()">
                    <?php foreach ( $active as $set_slug ) : ?>
                        <option value="<?php echo esc_attr( $set_slug ); ?>" <?php selected( $set_slug, $current_set ); ?>>
                            <?php echo esc_html( $set_slug ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="file" onchange="this.form.submit()">
                    <?php foreach ( $files as $f ) : ?>
                        <option value="<?php echo esc_attr( $f ); ?>" <?php selected( $f, $selected ); ?>>
                            <?php echo esc_html( $f ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
             <?php if ( $selected ) : ?>
    <button id="base47-he-save" class="button button-primary">Save</button>
    <button id="base47-he-restore" class="button">Restore</button>
    <button id="base47-he-open-preview" class="button">Open Preview</button>
<?php endif; ?>
        </div>

        <div id="base47-he-editor-shell" class="base47-he-editor-shell">
            <div id="base47-he-editor-left" class="base47-he-editor-left">
                <!-- Editor Mode Switcher -->
                <div class="base47-he-editor-mode-switcher">
                    <button type="button" id="base47-he-mode-advanced" class="button base47-he-mode-btn">
                        <span class="dashicons dashicons-editor-code"></span> Advanced Editor
                    </button>
                    <button type="button" id="base47-he-mode-classic" class="button base47-he-mode-btn">
                        <span class="dashicons dashicons-edit"></span> Classic Editor
                    </button>
                </div>
                
                <!-- Monaco Editor Container -->
                <div id="base47-monaco-editor" class="base47-he-monaco-container"></div>
                
                <!-- Classic Editor (Textarea) -->
                <textarea id="base47-he-code" style="width:100%;height:520px;display:none;"><?php echo esc_textarea( $content ); ?></textarea>
            </div>
            <div id="base47-he-resizer" class="base47-he-resizer"></div>
            <div class="base47-he-editor-right">
                <div class="base47-he-preview-toolbar">
                    <button type="button" class="button preview-size-btn active" data-size="100%">Full</button>
                    <button type="button" class="button preview-size-btn" data-size="1024">Desktop</button>
                    <button type="button" class="button preview-size-btn" data-size="768">Tablet</button>
                    <button type="button" class="button preview-size-btn" data-size="375">Mobile</button>
                </div>
                <div class="base47-he-preview-wrap">
                    <iframe id="base47-he-preview" src="<?php echo esc_url( $preview ); ?>"></iframe>
                </div>
            </div>
        </div>

     </div> <!-- close base47-he-editor-shell --> 
      
   <div class="base47-he-shortcuts-panel">
    <h2 class="base47-he-shortcuts-title">Keyboard Shortcuts</h2>

    <div class="base47-he-shortcuts-grid">

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + S</span>
            <span class="base47-he-shortcut-desc">Save template</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + P</span>
            <span class="base47-he-shortcut-desc">Open preview in new tab</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + 1</span>
            <span class="base47-he-shortcut-desc">Desktop preview</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + 2</span>
            <span class="base47-he-shortcut-desc">Tablet preview</span>
        </div>

        <div class="base47-he-shortcut">
            <span class="base47-he-shortcut-keys">Ctrl / Cmd + 3</span>
            <span class="base47-he-shortcut-desc">Mobile preview</span>
        </div>

    </div>
</div>
      

        <input type="hidden" id="base47-he-current-file" value="<?php echo esc_attr( $selected ); ?>">
        <input type="hidden" id="base47-he-current-set" value="<?php echo esc_attr( $current_set ); ?>">
          <?php wp_nonce_field( 'base47_he', 'nonce' ); ?>
    </div>

    <!-- Restore Backup Modal -->
    <div id="base47-he-restore-modal" class="base47-he-modal" style="display:none;">
        <div class="base47-he-modal-content">
            <div class="base47-he-modal-header">
                <h2>Restore Previous Version</h2>
                <span class="base47-he-modal-close">&times;</span>
            </div>
            <div class="base47-he-modal-body">
                <div id="base47-he-backup-list" class="base47-he-backup-list">
                    <p class="base47-he-loading">Loading backups...</p>
                </div>
                <div id="base47-he-backup-preview" class="base47-he-backup-preview" style="display:none;">
                    <h3>Preview</h3>
                    <textarea id="base47-he-backup-preview-content" readonly style="width:100%;height:300px;font-family:monospace;font-size:12px;"></textarea>
                </div>
            </div>
            <div class="base47-he-modal-footer">
                <button id="base47-he-restore-selected" class="button button-primary" disabled>Restore Selected</button>
                <button id="base47-he-download-selected" class="button" disabled>Download Backup</button>
                <button class="button base47-he-modal-close">Cancel</button>
            </div>
        </div>
    </div>

    <style>
    .base47-he-modal {
        display: none;
        position: fixed;
        z-index: 100000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    .base47-he-modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 0;
        border: 1px solid #ccc;
        width: 80%;
        max-width: 800px;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .base47-he-modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .base47-he-modal-header h2 {
        margin: 0;
        font-size: 20px;
    }
    .base47-he-modal-close {
        color: #666;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        line-height: 1;
    }
    .base47-he-modal-close:hover {
        color: #000;
    }
    .base47-he-modal-body {
        padding: 20px;
        max-height: 500px;
        overflow-y: auto;
    }
    .base47-he-modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        text-align: right;
    }
    .base47-he-modal-footer .button {
        margin-left: 10px;
    }
    .base47-he-backup-list {
        margin-bottom: 20px;
    }
    .base47-he-backup-item {
        padding: 12px;
        border: 2px solid #ddd;
        border-radius: 4px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .base47-he-backup-item:hover {
        border-color: #0073aa;
        background-color: #f0f8ff;
    }
    .base47-he-backup-item.selected {
        border-color: #0073aa;
        background-color: #e5f5ff;
    }
    .base47-he-backup-date {
        font-weight: 600;
        color: #333;
    }
    .base47-he-backup-size {
        color: #666;
        font-size: 12px;
    }
    .base47-he-backup-badge {
        background: #0073aa;
        color: #fff;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
    }
    .base47-he-loading {
        text-align: center;
        color: #666;
        padding: 20px;
    }
    .base47-he-no-backups {
        text-align: center;
        color: #999;
        padding: 40px 20px;
        font-style: italic;
    }
    .base47-he-backup-preview {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
    }
    .base47-he-backup-preview h3 {
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 16px;
    }
    
    /* Editor Mode Switcher */
    .base47-he-editor-mode-switcher {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        padding: 8px;
        background: #f5f5f5;
        border-radius: 4px;
    }
    .base47-he-mode-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 4px;
        transition: all 0.2s;
        font-weight: 500;
    }
    .base47-he-mode-btn .dashicons {
        font-size: 18px;
        width: 18px;
        height: 18px;
        line-height: 1;
        vertical-align: middle;
        margin-top: -2px;
    }
    .base47-he-mode-btn.active {
        background: #0073aa;
        color: white;
        border-color: #005a87;
        box-shadow: 0 2px 4px rgba(0,115,170,0.3);
    }
    .base47-he-mode-btn:not(.active):hover {
        background: #e0e0e0;
    }
    
    /* Monaco Editor Container */
    .base47-he-monaco-container {
        width: 100%;
        height: 100%;
        min-height: 520px;
        border: 1px solid #ddd;
        border-radius: 4px;
        overflow: hidden;
    }
    
    /* Classic Editor Dark Theme */
    body.base47-he-dark #base47-he-code {
        background: #1e1e1e;
        color: #d4d4d4;
        border-color: #3e3e3e;
    }
    
    /* Unsaved changes indicator */
    .base47-he-unsaved {
        background: #d63638 !important;
        border-color: #d63638 !important;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    </style>
    <?php
}
