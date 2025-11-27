jQuery(function ($) {
    function updateCardState($card, isActive) {
        $card
            .attr('data-active', isActive ? '1' : '0')
            .toggleClass('is-active', !!isActive)
            .toggleClass('is-inactive', !isActive);

        var $label = $card.find('.base47-tm-toggle-label');
        var $badgeText = $card.find('.base47-tm-badge-text');

        $label.text(isActive ? 'Enabled' : 'Disabled');
        $badgeText.text(isActive ? 'Active' : 'Disabled');
    }

    $('.base47-tm-grid').on('change', '.base47-tm-toggle-input', function () {
        var $checkbox = $(this);
        var theme = $checkbox.data('theme');
        var isActive = $checkbox.is(':checked') ? 1 : 0;
        var $card = $checkbox.closest('.base47-tm-card');

        if (!theme) {
            return;
        }

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
                    // Revert checkbox
                    $checkbox.prop('checked', !isActive);
                    var msg = (response && response.data) ? response.data : 'Error saving theme state.';
                    window.alert(msg);
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
});