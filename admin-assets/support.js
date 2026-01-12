/**
 * Support System JavaScript
 * 
 * Handle ticket submission, replies, and filtering
 * 
 * @package Base47_HTML_Editor
 * @since 2.9.9.3.13
 */

jQuery(document).ready(function($) {
    
    // Initialize support system
    initSupportSystem();
    
    function initSupportSystem() {
        // Handle new ticket form submission
        $('#new-support-ticket-form').on('submit', handleTicketSubmission);
        
        // Handle ticket reply form submission
        $('#ticket-reply-form').on('submit', handleTicketReply);
        
        // Handle ticket status filter
        $('#ticket-status-filter').on('change', handleTicketFilter);
        
        // Handle system info toggle
        $('#include-system-info').on('change', toggleSystemInfo);
        
        // Initialize tooltips if available
        if (typeof tippy !== 'undefined') {
            initTooltips();
        }
        
        // Auto-hide success messages
        setTimeout(function() {
            $('.notice-success').fadeOut();
        }, 5000);
    }
    
    /**
     * Handle new ticket form submission
     */
    function handleTicketSubmission(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        // Validate form
        if (!validateTicketForm($form)) {
            return;
        }
        
        // Show loading state
        $submitBtn.prop('disabled', true).html(
            '<span class="dashicons dashicons-update-alt"></span> Submitting...'
        );
        
        // Prepare form data
        const formData = new FormData($form[0]);
        formData.append('action', 'base47_he_submit_ticket');
        
        // Submit ticket
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        showNotification('success', data.message);
                        
                        // Redirect to ticket view after short delay
                        setTimeout(function() {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        showNotification('error', data.message || 'Failed to submit ticket');
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                } catch (error) {
                    console.error('Error parsing response:', error);
                    showNotification('error', 'An unexpected error occurred');
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                showNotification('error', 'Network error. Please try again.');
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    }
    
    /**
     * Handle ticket reply form submission
     */
    function handleTicketReply(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const $textarea = $form.find('textarea[name="reply_message"]');
        const originalText = $submitBtn.html();
        
        // Validate reply
        if (!$textarea.val().trim()) {
            showNotification('error', 'Please enter a reply message');
            $textarea.focus();
            return;
        }
        
        // Show loading state
        $submitBtn.prop('disabled', true).html(
            '<span class="dashicons dashicons-update-alt"></span> Sending...'
        );
        
        // Submit reply
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=base47_he_reply_ticket',
            success: function(response) {
                try {
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        showNotification('success', data.message);
                        
                        // Reload page to show new reply
                        if (data.reload) {
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            // Clear form
                            $textarea.val('');
                            $submitBtn.prop('disabled', false).html(originalText);
                        }
                    } else {
                        showNotification('error', data.message || 'Failed to send reply');
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                } catch (error) {
                    console.error('Error parsing response:', error);
                    showNotification('error', 'An unexpected error occurred');
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                showNotification('error', 'Network error. Please try again.');
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    }
    
    /**
     * Handle ticket status filter
     */
    function handleTicketFilter(e) {
        const status = $(this).val();
        const $ticketItems = $('.ticket-item');
        
        if (status === 'all') {
            $ticketItems.show();
        } else {
            $ticketItems.each(function() {
                const ticketStatus = $(this).data('status');
                if (ticketStatus === status) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        // Update visible count
        updateTicketCount();
    }
    
    /**
     * Toggle system info visibility
     */
    function toggleSystemInfo(e) {
        const $systemInfo = $('#system-info-preview');
        
        if ($(this).is(':checked')) {
            $systemInfo.slideDown();
        } else {
            $systemInfo.slideUp();
        }
    }
    
    /**
     * Validate ticket form
     */
    function validateTicketForm($form) {
        let isValid = true;
        const requiredFields = $form.find('[required]');
        
        // Clear previous errors
        $('.form-error').remove();
        
        requiredFields.each(function() {
            const $field = $(this);
            const value = $field.val().trim();
            
            if (!value) {
                isValid = false;
                showFieldError($field, 'This field is required');
            }
        });
        
        // Validate email if present
        const $email = $form.find('input[type="email"]');
        if ($email.length && $email.val()) {
            if (!isValidEmail($email.val())) {
                isValid = false;
                showFieldError($email, 'Please enter a valid email address');
            }
        }
        
        // Validate message length
        const $message = $form.find('#ticket-message');
        if ($message.val().trim().length < 10) {
            isValid = false;
            showFieldError($message, 'Please provide more details (at least 10 characters)');
        }
        
        return isValid;
    }
    
    /**
     * Show field error
     */
    function showFieldError($field, message) {
        const $error = $('<div class="form-error" style="color: #ef4444; font-size: 0.8125rem; margin-top: 0.25rem;">' + message + '</div>');
        $field.closest('.form-group').append($error);
        $field.addClass('error').css('border-color', '#ef4444');
        
        // Remove error on focus
        $field.one('focus', function() {
            $(this).removeClass('error').css('border-color', '');
            $(this).closest('.form-group').find('.form-error').remove();
        });
    }
    
    /**
     * Validate email format
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Update ticket count display
     */
    function updateTicketCount() {
        const $visibleTickets = $('.ticket-item:visible');
        const count = $visibleTickets.length;
        
        // Update count in header if element exists
        const $countElement = $('.tickets-count');
        if ($countElement.length) {
            $countElement.text(count + ' ticket' + (count !== 1 ? 's' : ''));
        }
    }
    
    /**
     * Show notification
     */
    function showNotification(type, message) {
        // Remove existing notifications
        $('.support-notification').remove();
        
        const typeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const icon = type === 'success' ? 'yes-alt' : 'warning';
        
        const $notification = $(`
            <div class="notice ${typeClass} support-notification" style="margin: 1rem 0; padding: 1rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span class="dashicons dashicons-${icon}"></span>
                <span>${message}</span>
            </div>
        `);
        
        // Insert at top of form or page
        const $target = $('.ticket-form-container, .ticket-conversation, .support-header').first();
        $target.after($notification);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Scroll to notification
        $('html, body').animate({
            scrollTop: $notification.offset().top - 100
        }, 500);
    }
    
    /**
     * Initialize tooltips
     */
    function initTooltips() {
        // Add tooltips to help icons
        tippy('[data-tippy-content]', {
            theme: 'base47',
            placement: 'top',
            arrow: true,
            animation: 'fade'
        });
    }
    
    /**
     * Handle form auto-save (future feature)
     */
    function initAutoSave() {
        const $forms = $('.ticket-form, .reply-form');
        
        $forms.find('input, textarea, select').on('input change', function() {
            const formId = $(this).closest('form').attr('id');
            const fieldName = $(this).attr('name');
            const fieldValue = $(this).val();
            
            // Save to localStorage
            const storageKey = 'base47_support_' + formId + '_' + fieldName;
            localStorage.setItem(storageKey, fieldValue);
        });
        
        // Restore saved values on page load
        $forms.each(function() {
            const formId = $(this).attr('id');
            const $form = $(this);
            
            $form.find('input, textarea, select').each(function() {
                const fieldName = $(this).attr('name');
                const storageKey = 'base47_support_' + formId + '_' + fieldName;
                const savedValue = localStorage.getItem(storageKey);
                
                if (savedValue && !$(this).val()) {
                    $(this).val(savedValue);
                }
            });
        });
        
        // Clear saved data on successful submission
        $(document).on('supportTicketSubmitted supportReplySubmitted', function(e, formId) {
            const keys = Object.keys(localStorage);
            keys.forEach(function(key) {
                if (key.startsWith('base47_support_' + formId + '_')) {
                    localStorage.removeItem(key);
                }
            });
        });
    }
    
    /**
     * Handle keyboard shortcuts
     */
    function initKeyboardShortcuts() {
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + Enter to submit forms
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
                const $activeForm = $('form:has(:focus)');
                if ($activeForm.length) {
                    $activeForm.find('button[type="submit"]').click();
                }
            }
            
            // Escape to close modals (future feature)
            if (e.keyCode === 27) {
                $('.modal-overlay').fadeOut();
            }
        });
    }
    
    /**
     * Initialize character counters
     */
    function initCharacterCounters() {
        const $textareas = $('textarea[maxlength]');
        
        $textareas.each(function() {
            const $textarea = $(this);
            const maxLength = parseInt($textarea.attr('maxlength'));
            
            if (maxLength) {
                const $counter = $('<div class="character-counter" style="text-align: right; font-size: 0.8125rem; color: #71717a; margin-top: 0.25rem;"></div>');
                $textarea.after($counter);
                
                function updateCounter() {
                    const currentLength = $textarea.val().length;
                    const remaining = maxLength - currentLength;
                    
                    $counter.text(remaining + ' characters remaining');
                    
                    if (remaining < 50) {
                        $counter.css('color', '#f59e0b');
                    } else if (remaining < 10) {
                        $counter.css('color', '#ef4444');
                    } else {
                        $counter.css('color', '#71717a');
                    }
                }
                
                $textarea.on('input', updateCounter);
                updateCounter();
            }
        });
    }
    
    // Initialize additional features
    initAutoSave();
    initKeyboardShortcuts();
    initCharacterCounters();
    
    // Update ticket count on page load
    updateTicketCount();
    
    // Handle responsive navigation
    $(window).on('resize', function() {
        // Adjust layout for mobile if needed
        if ($(window).width() < 768) {
            $('.support-header').addClass('mobile-layout');
        } else {
            $('.support-header').removeClass('mobile-layout');
        }
    }).trigger('resize');
    
});