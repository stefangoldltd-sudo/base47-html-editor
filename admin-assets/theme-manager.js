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

    /* --------------------------
       LOADER / MANIFEST SWITCH
    --------------------------- */

    $('.base47-tm-grid').on('change', 'input[type=radio]', function () {

        let $radio  = $(this);
        let mode    = $radio.val();

        // Get entire card
        let $card   = $radio.closest('.base47-tm-card');
        let $hidden = $card.find('.tm-hidden-manifest');

        // If manifest selected → check hidden field  
        if (mode === 'manifest') {
            $hidden.prop('checked', true);
        } else {
            $hidden.prop('checked', false);
        }
    });

});

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

            alert(response.success ? "Caches rebuilt successfully!" : "Failed: " + response.data.message);

            $("#base47-rebuild-caches-btn")
                .prop("disabled", false)
                .text("Rebuild All Caches");
        });

    });

});