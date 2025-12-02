jQuery(function ($) {

    /* ==========================================================
       1) THEME ACTIVE / INACTIVE TOGGLE
       ========================================================== */
    $('.base47-tm-grid').on('change', '.base47-tm-toggle-input', function () {

        let $toggle = $(this);
        let slug    = $toggle.data('theme');
        let active  = $toggle.is(':checked') ? 1 : 0;

        // Prevent disabling the last active theme
        // Count how many would remain AFTER this change
        let activeCount = $('.base47-tm-toggle-input:checked').length;
        if (!active && activeCount === 0) {
            alert("At least one theme must stay active.");
            $toggle.prop('checked', true);
            return;
        }

        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action: 'base47_toggle_theme',
                nonce:  base47ThemeManager.nonce,
                theme:  slug,
                active: active
            },
            function (res) {
                if (!res || !res.success) {
                    alert('Failed to update theme state.');
                }
            }
        );
    });



    /* ==========================================================
       2) DEFAULT THEME SELECT
       ========================================================== */
    $('#base47_default_theme').on('change', function () {

        let value = $(this).val();

        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action:  'base47_set_default_theme',
                nonce:   base47ThemeManager.nonce,
                theme:   value
            },
            function (res) {
                if (!res || !res.success) {
                    alert('Failed to save default theme.');
                }
            }
        );
    });



    /* ==========================================================
       3) ASSET MODE: loader / manifest / smart
       ========================================================== */
    $('.base47-tm-grid').on('change', '.tm-mode input[type=radio]', function () {

        let $radio = $(this);
        let mode   = $radio.val();
        let $card  = $radio.closest('.base47-tm-card');
        let slug   = $card.data('theme');

        // hidden fields
        let $hiddenManifest = $card.find('.tm-hidden-manifest');
        let $hiddenSmart    = $card.find('.tm-hidden-smart');

        // Reset
        $hiddenManifest.prop('checked', false);
        $hiddenSmart.prop('checked', false);

        if (mode === 'manifest') {
            $hiddenManifest.prop('checked', true);
        }
        else if (mode === 'smart') {
            $hiddenSmart.prop('checked', true);
        }

        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action: 'base47_set_asset_mode',
                nonce:  base47ThemeManager.nonce,
                theme:  slug,
                mode:   mode
            }
        ).fail(function () {
            alert('Error: could not save asset mode.');
        });
    });



    /* ==========================================================
       4) REBUILD ALL CACHES
       ========================================================== */
    $('#base47-rebuild-caches-btn').on('click', function () {

        if (!confirm("Rebuild all Base47 caches?")) {
            return;
        }

        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action: 'base47_rebuild_caches',
                nonce:  base47ThemeManager.nonce
            },
            function (response) {
                if (response.success) {
                    alert("All Base47 caches rebuilt successfully!");
                    location.reload();
                } else {
                    alert("Cache rebuild failed.");
                }
            }
        ).fail(function () {
            alert("AJAX failed — rebuild not executed.");
        });
    });

});