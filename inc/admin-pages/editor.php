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
                <textarea id="base47-he-code" style="width:100%;height:520px;"><?php echo esc_textarea( $content ); ?></textarea>
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
    <?php
}
