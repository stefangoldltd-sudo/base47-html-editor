<?php
/**
 * Special Widgets Admin Page
 * 
 * Lists all special widgets with shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function base47_special_widgets_page() {
    $widgets = base47_he_get_special_widgets_registry();
    ?>
    <div class="wrap base47-he-wrap">
        <h1 style="margin-bottom:20px;">Special Widgets</h1>

        <p style="font-size:15px;color:#555;margin-bottom:25px;">
            Below is a list of all special widgets discovered in the
            <code>special-widgets</code> folder (only folders that contain <code>widget.json</code>).
            Copy the shortcode to insert in any Base47 HTML template.
        </p>

        <?php if ( empty( $widgets ) ) : ?>

            <p style="margin-top:15px;color:#777;">
                No special widgets found. To add one, create a folder in
                <code>special-widgets/</code> with a <code>widget.json</code> file.
            </p>

        <?php else : ?>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:200px;">Widget</th>
                    <th>Description</th>
                    <th style="width:220px;">Shortcode</th>
                    <th style="width:100px;">Preview</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $plugin_url = plugin_dir_url( BASE47_HE_PATH . 'base47-html-editor.php' );
            foreach ( $widgets as $w ) :
                $folder  = $w['folder'];
                $html    = $w['html'];
                $name    = $w['name'];
                $desc    = $w['description'];
                $slug    = $w['slug'];
                $shortcode = '[base47_widget slug="' . esc_attr( $slug ) . '"]';
                $preview  = $plugin_url . 'special-widgets/' . $folder . '/' . $html;
            ?>
                <tr>
                    <td><strong><?php echo esc_html( $name ); ?></strong></td>
                    <td><?php echo esc_html( $desc ); ?></td>
                    <td><code><?php echo esc_html( $shortcode ); ?></code></td>
                    <td>
                        <a href="<?php echo esc_url( $preview ); ?>"
                           target="_blank"
                           class="button button-primary button-small">
                           Preview
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>

        <p style="margin-top:25px;color:#777;font-size:13px;">
            This list is generated automatically from folders in
            <code>special-widgets/</code>. Only folders with a <code>widget.json</code> file are shown.
        </p>
    </div>
    <?php
}
