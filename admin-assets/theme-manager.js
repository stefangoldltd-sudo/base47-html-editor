jQuery(function ($) {

    /* ==========================================================
       1) THEME ACTIVE / INACTIVE TOGGLE (Soft UI)
       ========================================================== */
    $('.base47-tm-grid-soft, .base47-tm-grid').on('change', '.base47-tm-toggle-input', function () {

        let $toggle = $(this);
        let slug    = $toggle.data('theme');
        let active  = $toggle.is(':checked') ? 1 : 0;

        // Prevent disabling the last active theme
        let activeCount = $('.base47-tm-toggle-input:checked').length;
        if (!active && activeCount === 0) {
            alert("At least one theme must stay active.");
            $toggle.prop('checked', true);
            return;
        }

        // Update UI immediately
        let $card = $toggle.closest('.base47-tm-card-soft, .base47-tm-card');
        let $badge = $card.find('.base47-tm-status-badge, .base47-tm-badge-text');
        let $label = $card.find('.base47-tm-toggle-label');

        if (active) {
            $card.removeClass('is-inactive').addClass('is-active');
            $card.attr('data-active', '1');
            $badge.text('Active');
            $label.text('Enabled');
        } else {
            $card.removeClass('is-active').addClass('is-inactive');
            $card.attr('data-active', '0');
            $badge.text('Inactive');
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
                        $badge.text('Inactive');
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
       3) ASSET MODE: loader / manifest / smart (Soft UI)
       ========================================================== */
    $('.base47-tm-grid-soft, .base47-tm-grid').on('change', '.base47-tm-mode-option input[type=radio], .tm-mode input[type=radio]', function () {

        let $radio = $(this);
        let mode   = $radio.val();
        let $card  = $radio.closest('.base47-tm-card-soft, .base47-tm-card');
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
       4) UNINSTALL THEME
       ========================================================== */
    $('.base47-tm-grid-soft, .base47-tm-grid').on('click', '.base47-tm-uninstall-btn', function() {
        let $btn = $(this);
        let slug = $btn.data('theme');
        let $card = $btn.closest('.base47-tm-card-soft, .base47-tm-card');
        
        if (!slug) {
            alert('Error: Theme slug not found.');
            return;
        }
        
        // Get theme name for confirmation
        let themeName = $card.find('.base47-tm-theme-name, .base47-tm-name').text() || slug;
        
        if (!confirm('Are you sure you want to uninstall "' + themeName + '"?\n\nThis will permanently delete all theme files.')) {
            return;
        }
        
        // Disable button and show loading
        $btn.prop('disabled', true).text('Uninstalling...');
        
        $.post(
            base47ThemeManager.ajaxUrl,
            {
                action: 'base47_he_uninstall_theme',
                nonce:  base47ThemeManager.nonce,
                theme:  slug
            },
            function(res) {
                if (res && res.success) {
                    // Remove card with animation
                    $card.fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    // Show success notification if available
                    if (typeof base47ShowNotification === 'function') {
                        base47ShowNotification('success', 'Theme Uninstalled', themeName + ' has been removed successfully.');
                    }
                } else {
                    // Re-enable button
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span>');
                    
                    let errorMsg = (res && res.data && res.data.message) ? res.data.message : 'Failed to uninstall theme.';
                    
                    // Show error notification if available
                    if (typeof base47ShowNotification === 'function') {
                        base47ShowNotification('error', 'Uninstall Failed', errorMsg);
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            }
        ).fail(function() {
            // Re-enable button
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span>');
            
            if (typeof base47ShowNotification === 'function') {
                base47ShowNotification('error', 'Network Error', 'Failed to connect to server.');
            } else {
                alert('Network error: Could not uninstall theme.');
            }
        });
    });

    /* ==========================================================
       5) REBUILD ALL CACHES
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

/*
 ==========================================================
   SOFT UI NOTIFICATION SYSTEM (v2.9.6.6)
   ========================================================== */

/**
 * Show Soft UI Notification Toast
 * @param {string} type - success, error, warning, info
 * @param {string} title - Notification title
 * @param {string} message - Notification message
 * @param {number} duration - Auto-close duration in ms (0 = no auto-close)
 */
function base47ShowNotification(type, title, message, duration = 5000) {
    // Remove any existing notifications
    $('.base47-notification').remove();
    
    // Icon map
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    const icon = icons[type] || icons.info;
    
    // Create notification HTML
    const $notification = $(`
        <div class="base47-notification base47-notification-${type}">
            <div class="base47-notification-icon">${icon}</div>
            <div class="base47-notification-content">
                <div class="base47-notification-title">${title}</div>
                <div class="base47-notification-message">${message}</div>
            </div>
            <button class="base47-notification-close" aria-label="Close">×</button>
        </div>
    `);
    
    // Add to page
    $('body').append($notification);
    
    // Close button
    $notification.find('.base47-notification-close').on('click', function() {
        $notification.fadeOut(200, function() {
            $(this).remove();
        });
    });
    
    // Auto-close
    if (duration > 0) {
        setTimeout(function() {
            $notification.fadeOut(200, function() {
                $(this).remove();
            });
        }, duration);
    }
}

/**
 * Show Soft UI Modal
 * @param {string} type - success, error, warning, info
 * @param {string} title - Modal title
 * @param {string} message - Modal message
 * @param {array} buttons - Array of button objects {text, class, callback}
 */
function base47ShowModal(type, title, message, buttons = []) {
    // Remove any existing modals
    $('.base47-modal-overlay').remove();
    
    // Icon map
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    const icon = icons[type] || icons.info;
    
    // Default button if none provided
    if (buttons.length === 0) {
        buttons = [{
            text: 'OK',
            class: 'btn-soft-primary',
            callback: function() {
                base47CloseModal();
            }
        }];
    }
    
    // Create buttons HTML
    let buttonsHTML = '';
    buttons.forEach(function(btn, index) {
        buttonsHTML += `<button class="base47-modal-btn ${btn.class}" data-index="${index}">${btn.text}</button>`;
    });
    
    // Create modal HTML
    const $overlay = $(`
        <div class="base47-modal-overlay">
            <div class="base47-modal">
                <div class="base47-modal-header">
                    <div class="base47-modal-title">
                        <div class="base47-modal-icon ${type}">${icon}</div>
                        ${title}
                    </div>
                    <button class="base47-modal-close" aria-label="Close">×</button>
                </div>
                <div class="base47-modal-body">${message}</div>
                <div class="base47-modal-footer">${buttonsHTML}</div>
            </div>
        </div>
    `);
    
    // Add to page
    $('body').append($overlay);
    
    // Close button
    $overlay.find('.base47-modal-close').on('click', function() {
        base47CloseModal();
    });
    
    // Click outside to close
    $overlay.on('click', function(e) {
        if ($(e.target).hasClass('base47-modal-overlay')) {
            base47CloseModal();
        }
    });
    
    // Button callbacks
    $overlay.find('.base47-modal-btn').on('click', function() {
        const index = $(this).data('index');
        if (buttons[index] && buttons[index].callback) {
            buttons[index].callback();
        }
    });
    
    // ESC key to close
    $(document).on('keydown.base47modal', function(e) {
        if (e.key === 'Escape') {
            base47CloseModal();
        }
    });
}

/**
 * Close Soft UI Modal
 */
function base47CloseModal() {
    $('.base47-modal-overlay').fadeOut(200, function() {
        $(this).remove();
    });
    $(document).off('keydown.base47modal');
}

/* ==========================================================
   ENHANCED UPLOAD HANDLING (v2.9.6.6)
   ========================================================== */

jQuery(function($) {
    
    // Intercept form submission for better error handling
    $('form[enctype="multipart/form-data"]').on('submit', function(e) {
        const $form = $(this);
        const $fileInput = $form.find('input[type="file"]');
        
        // Check if file is selected
        if ($fileInput.length && !$fileInput.val()) {
            e.preventDefault();
            base47ShowNotification(
                'warning',
                'No File Selected',
                'Please choose a ZIP file before uploading.',
                4000
            );
            return false;
        }
        
        // Check file extension
        if ($fileInput.length && $fileInput.val()) {
            const fileName = $fileInput.val();
            const ext = fileName.split('.').pop().toLowerCase();
            
            if (ext !== 'zip') {
                e.preventDefault();
                base47ShowModal(
                    'error',
                    'Invalid File Type',
                    'Please upload a ZIP file. Other file types are not supported.',
                    [{
                        text: 'OK',
                        class: 'btn-soft-primary',
                        callback: base47CloseModal
                    }]
                );
                return false;
            }
        }
        
        // Show loading notification
        if ($fileInput.length && $fileInput.val()) {
            base47ShowNotification(
                'info',
                'Uploading Theme',
                'Please wait while your theme is being uploaded and installed...',
                0 // Don't auto-close
            );
        }
    });
    
    // Handle WordPress notices and convert to Soft UI notifications
    $('.notice.notice-success, .notice.notice-error').each(function() {
        const $notice = $(this);
        const message = $notice.find('p').text();
        const isError = $notice.hasClass('notice-error');
        
        if (message) {
            // Show Soft UI notification
            base47ShowNotification(
                isError ? 'error' : 'success',
                isError ? 'Error' : 'Success',
                message,
                6000
            );
            
            // Hide WordPress notice
            $notice.hide();
        }
    });
    
});
