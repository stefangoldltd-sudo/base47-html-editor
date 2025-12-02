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

        // Update UI immediately
        let $card = $toggle.closest('.base47-tm-card');
        let $badge = $card.find('.base47-tm-badge-text');
        let $label = $card.find('.base47-tm-toggle-label');

        if (active) {
            $card.removeClass('is-inactive').addClass('is-active');
            $card.attr('data-active', '1');
            $badge.text('Active');
            $label.text('Enabled');
        } else {
            $card.removeClass('is-active').addClass('is-inactive');
            $card.attr('data-active', '0');
            $badge.text('Disabled');
            $label.text('Disabled');
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
                    // Revert UI on error
                    $toggle.prop('checked', !active);
                    if (!active) {
                        $card.removeClass('is-inactive').addClass('is-active');
                        $card.attr('data-active', '1');
                        $badge.text('Active');
                        $label.text('Enabled');
                    } else {
                        $card.removeClass('is-active').addClass('is-inactive');
                        $card.attr('data-active', '0');
                        $badge.text('Disabled');
                        $label.text('Disabled');
                    }
                }
            }
        );
    });



    /* ==========================================================
       2) DEFAULT THEME SELECT
       ========================================================== */
    $('#base47_default_theme').on('change', function () {

        let value = $(this).val();
        let $select = $(this);
        let originalBg = $select.css('background-color');
        
        // Show saving indicator (yellow)
        $select.css('background-color', '#fff3cd');

        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action:  'base47_set_default_theme',
                nonce:   base47ThemeManager.nonce,
                theme:   value
            },
            function (res) {
                if (res && res.success) {
                    // Flash success (green)
                    $select.css('background-color', '#d4edda');
                    setTimeout(function() {
                        $select.css('background-color', originalBg);
                    }, 800);
                } else {
                    // Flash error (red)
                    $select.css('background-color', '#f8d7da');
                    setTimeout(function() {
                        $select.css('background-color', originalBg);
                    }, 800);
                    alert('Failed to save default theme.');
                }
            }
        ).fail(function() {
            // Flash error (red)
            $select.css('background-color', '#f8d7da');
            setTimeout(function() {
                $select.css('background-color', originalBg);
            }, 800);
            alert('Failed to save default theme.');
        });
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

        // Show visual feedback
        let $modeContainer = $card.find('.base47-tm-asset-modes');
        let originalBg = $modeContainer.css('background-color');
        $modeContainer.css({
            'background-color': '#fff3cd',
            'transition': 'background-color 0.3s'
        });

        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action: 'base47_set_asset_mode',
                nonce:  base47ThemeManager.nonce,
                theme:  slug,
                mode:   mode
            },
            function(res) {
                if (res && res.success) {
                    // Flash success (green)
                    $modeContainer.css('background-color', '#d4edda');
                    setTimeout(function() {
                        $modeContainer.css('background-color', originalBg);
                    }, 800);
                } else {
                    // Flash error (red)
                    $modeContainer.css('background-color', '#f8d7da');
                    setTimeout(function() {
                        $modeContainer.css('background-color', originalBg);
                    }, 800);
                    alert('Error: could not save asset mode.');
                }
            }
        ).fail(function () {
            // Flash error (red)
            $modeContainer.css('background-color', '#f8d7da');
            setTimeout(function() {
                $modeContainer.css('background-color', originalBg);
            }, 800);
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