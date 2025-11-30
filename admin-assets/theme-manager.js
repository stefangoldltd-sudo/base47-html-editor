jQuery(function ($) {

    /* --------------------------
       ACTIVE / DISABLED SWITCH
    --------------------------- */
    function updateCardState($card, isActive) {
        $card
            .attr('data-active', isActive ? '1' : '0')
            .toggleClass('is-active', !!isActive)
            .toggleClass('is-inactive', !isActive);

        var $label     = $card.find('.base47-tm-toggle-label');
        var $badgeText = $card.find('.base47-tm-badge-text');

        $label.text(isActive ? 'Enabled' : 'Disabled');
        $badgeText.text(isActive ? 'Active' : 'Disabled');
    }

    $('.base47-tm-grid').on('change', '.base47-tm-toggle-input', function () {
        var $checkbox = $(this);
        var theme     = $checkbox.data('theme');
        var isActive  = $checkbox.is(':checked') ? 1 : 0;
        var $card     = $checkbox.closest('.base47-tm-card');

        if (!theme) return;

        $card.addClass('is-saving');

        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action: 'base47_toggle_theme',
                nonce: base47ThemeManager.nonce,
                theme: theme,
                active: isActive
            }
        )
        .done(function (response) {
            if (response && response.success) {
                updateCardState($card, !!isActive);
            } else {
                $checkbox.prop('checked', !isActive);
                window.alert((response && response.data) ? response.data : 'Error saving theme state.');
            }
        })
        .fail(function () {
            $checkbox.prop('checked', !isActive);
            window.alert('Network error while saving theme state.');
        })
        .always(function () {
            $card.removeClass('is-saving');
        });
    });
/* ---------------------------------------------------------
   ASSET MODES — ONE UNIFIED HANDLER (loader/manifest/smart)
---------------------------------------------------------- */
$('.base47-tm-grid').on('change', '.tm-mode input[type=radio]', function () {

    let $radio = $(this);
    let mode   = $radio.val();        // loader / manifest / smart
    let $card  = $radio.closest('.base47-tm-card');
    let theme  = $card.data('theme');

    // Hidden fields for saving (stay hidden in UI)
    let $hiddenManifest = $card.find('.tm-hidden-manifest');
    let $hiddenSmart    = $card.find('.tm-hidden-smart');

    // Reset both hidden checkboxes
    $hiddenManifest.prop('checked', false);
    $hiddenSmart.prop('checked', false);

    // Apply new mode to hidden fields
    if (mode === 'manifest') {
        $hiddenManifest.prop('checked', true);
    }
    else if (mode === 'smart') {
        $hiddenSmart.prop('checked', true);
    }
    // loader = both unchecked

    // AJAX SAVE MODE
    $.post(
        base47ThemeManager.ajaxUrl,
        {
            action: 'base47_set_asset_mode',
            nonce:  base47ThemeManager.nonce,
            theme:  theme,
            mode:   mode
        },
        function(response) {
            if (!response || !response.success) {
                alert('Error saving asset mode.');
            }
        }
    ).fail(function () {
        alert('Failed to save asset mode.');
    });
});
	
	/* --------------------------
   SAVE ASSET MODE (AJAX)
--------------------------- */
$('.base47-tm-grid').on('change', '.tm-mode input[type=radio]', function () {

    let $radio = $(this);
    let mode   = $radio.val();
    let $card  = $radio.closest('.base47-tm-card');
    let theme  = $card.data('theme');

    // Hidden fields
    let $hiddenManifest = $card.find('.tm-hidden-manifest');
    let $hiddenSmart    = $card.find('.tm-hidden-smart');

    // Reset both first
    $hiddenManifest.prop('checked', false);
    $hiddenSmart.prop('checked', false);

    // Apply correct one
    if (mode === 'manifest') {
        $hiddenManifest.prop('checked', true);
    }
    else if (mode === 'smart') {
        $hiddenSmart.prop('checked', true);
    }

    // AJAX SAVE
    $.post(
        base47ThemeManager.ajaxUrl,
        {
            action: 'base47_set_asset_mode',
            nonce:  base47ThemeManager.nonce,
            theme:  theme,
            mode:   mode
        }
    )
    .fail(function () {
        alert('Failed to save asset mode.');
    });
});

/* --------------------------
   REBUILD CACHES BUTTON
--------------------------- */
jQuery(document).ready(function($) {

    $("#base47-rebuild-caches-btn").on("click", function () {

        if (!confirm("Rebuild all caches now?")) {
            return;
        }

        $(this).prop("disabled", true).text("Rebuilding...");

        $.post(base47ThemeManager.ajaxUrl, {
            action: "base47_rebuild_caches",
            nonce: base47ThemeManager.nonce
        }, function (response) {

            alert(response.success ? "Caches rebuilt successfully!" :
                "Failed: " + response.data.message);

            $("#base47-rebuild-caches-btn")
                .prop("disabled", false)
                .text("Rebuild All Caches");
        });

    });

});