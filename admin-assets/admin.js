jQuery(function ($) {

    const $code    = $('#base47-he-code');
    const $file    = $('#base47-he-current-file');
    const $set     = $('#base47-he-current-set');
    const $preview = $('#base47-he-preview');

    /* ==========================
       COPY SHORTCODE
    ========================== */
    $(document).on('click', '.base47-he-copy', function () {
        const sc = $(this).data('shortcode');
        if (!sc) return;

        const btn      = $(this);
        const original = btn.html();

        navigator.clipboard.writeText(sc).then(() => {
            // Check if we're on Soft UI page
            if ($('.base47-sc-soft-ui').length) {
                // Show Soft UI toast
                showSoftUIToast('Shortcode Copied!', '✓');
            } else {
                // Old style feedback
                btn.text('Copied').css('background', '#2ecc71');
                setTimeout(() => {
                    btn.html(original).css('background', '');
                }, 1200);
            }
        });
    });
    
    /* ==========================
       SOFT UI TOAST NOTIFICATION
    ========================== */
    function showSoftUIToast(message, icon = '✓') {
        // Remove existing toast
        $('.base47-sc-toast').remove();
        
        // Create toast
        const $toast = $('<div class="base47-sc-toast">' +
            '<span class="base47-sc-toast-icon">' + icon + '</span>' +
            '<span class="base47-sc-toast-message">' + message + '</span>' +
            '</div>');
        
        $('body').append($toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            $toast.css('animation', 'slideOutRight 0.3s ease');
            setTimeout(() => $toast.remove(), 300);
        }, 3000);
    }
    
    /* ==========================
       SHORTCODES PAGE SEARCH
    ========================== */
    $('#base47-sc-search').on('input', function() {
        const query = $(this).val().toLowerCase().trim();
        
        $('.base47-sc-card').each(function() {
            const templateName = $(this).data('template-name') || '';
            const matches = templateName.includes(query);
            
            $(this).toggle(matches);
        });
        
        // Hide/show theme sections if all cards are hidden
        $('.base47-sc-theme-section').each(function() {
            const $section = $(this);
            const visibleCards = $section.find('.base47-sc-card:visible').length;
            $section.toggle(visibleCards > 0);
        });
    });

    /* ==========================
       ACTIVE SET HELPER
    ========================== */
function getActiveSet() {
    let setVal = $set.val();

    if (!setVal || setVal === 'undefined') {
        setVal = (window.BASE47_HE && BASE47_HE.default_set)
            ? BASE47_HE.default_set
            : 'base47-templates';
    }

    return setVal;
}
    /* ==========================
       LIVE PREVIEW (EDITOR)
    ========================== */
    let timer;
    if ($code.length) {
        $code.on('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                $.post(BASE47_HE.ajax_url, {
	action: 'base47_he_live_preview',
                    nonce:  BASE47_HE.nonce,
                    file:   $file.val(),
                    set:    getActiveSet(),
                    content: $code.val()
                }, function (resp) {
                    if (resp.success && resp.data && resp.data.html) {
                        const iframe = $preview.get(0);
                        iframe.contentWindow.document.open();
                        iframe.contentWindow.document.write(resp.data.html);
                        iframe.contentWindow.document.close();
                    }
                });
            }, 700);
        });
    }

    /* ==========================
       SAVE TEMPLATE (EDITOR)
    ========================== */
    $('#base47-he-save').on('click', function (e) {
        e.preventDefault();

        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_save_template',
            nonce:  BASE47_HE.nonce,
            file:   $file.val(),
            set:    getActiveSet(),
            content: $code.val()
        }, function (resp) {
            if (resp.success) {

                // Refresh preview with pure HTML (not WordPress page)
                $.post(BASE47_HE.ajax_url, {
                    action: 'base47_he_live_preview',
                    nonce:  BASE47_HE.nonce,
                    file:   $file.val(),
                    set:    getActiveSet(),
                    content: $code.val()
                }, function (previewResp) {
                    if (previewResp.success && previewResp.data && previewResp.data.html) {
                        const iframe = $preview.get(0);
                        iframe.contentWindow.document.open();
                        iframe.contentWindow.document.write(previewResp.data.html);
                        iframe.contentWindow.document.close();
                    }
                });

                $('#base47-he-save').text('Saved ✓');
                setTimeout(() => $('#base47-he-save').text('Save'), 900);
            }
        });
    });

    /* ==========================
       DUPLICATE TEMPLATE (EDITOR)
    ========================== */
    $('#base47-he-duplicate').on('click', function (e) {
        e.preventDefault();
        
        // Open duplicate modal
        $('#base47-he-duplicate-modal').fadeIn(200);
        
        // Reset form
        $('#base47-he-duplicate-name').val('');
        $('#base47-he-duplicate-error').hide();
        $('#base47-he-duplicate-confirm').prop('disabled', false);
        
        // Focus on input
        setTimeout(() => {
            $('#base47-he-duplicate-name').focus();
        }, 250);
    });

    // Duplicate confirmation
    $('#base47-he-duplicate-confirm').on('click', function (e) {
        e.preventDefault();
        
        const newName = $('#base47-he-duplicate-name').val().trim();
        const $error = $('#base47-he-duplicate-error');
        const $btn = $(this);
        
        // Reset error state
        $error.hide();
        
        // Validate input
        if (!newName) {
            $error.find('p').text('Please enter a template name.');
            $error.show();
            return;
        }
        
        // Validate filename format
        if (!/^[a-zA-Z0-9_-]+\.html?$/i.test(newName)) {
            $error.find('p').text('Invalid filename. Use only letters, numbers, hyphens, underscores, and .html extension.');
            $error.show();
            return;
        }
        
        // Disable button and show loading
        $btn.prop('disabled', true).text('Creating...');
        
        // Send duplication request
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_duplicate_template',
            nonce: BASE47_HE.nonce,
            file: $file.val(),
            set: getActiveSet(),
            new_name: newName,
            content: $code.val() // Include current editor content
        }, function (resp) {
            if (resp.success) {
                // Close modal
                $('#base47-he-duplicate-modal').fadeOut(200);
                
                // Show success message
                if ($('.base47-sc-soft-ui').length) {
                    showSoftUIToast('Template duplicated successfully!', '✓');
                } else {
                    alert('Template duplicated successfully!');
                }
                
                // Redirect to the new template
                if (resp.data && resp.data.redirect_url) {
                    window.location.href = resp.data.redirect_url;
                }
            } else {
                // Show error
                $error.find('p').text(resp.data || 'Failed to duplicate template.');
                $error.show();
                
                // Re-enable button
                $btn.prop('disabled', false).text('Create Duplicate');
            }
        }).fail(function() {
            $error.find('p').text('Network error. Please try again.');
            $error.show();
            $btn.prop('disabled', false).text('Create Duplicate');
        });
    });

    // Handle Enter key in duplicate name input
    $('#base47-he-duplicate-name').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            $('#base47-he-duplicate-confirm').click();
        }
    });

    /* ==========================
       RESTORE BACKUP MODAL (EDITOR)
    ========================== */
    let selectedBackup = null;

    $('#base47-he-restore').on('click', function (e) {
        e.preventDefault();
        
        // Open modal
        $('#base47-he-restore-modal').fadeIn(200);
        
        // Reset state
        selectedBackup = null;
        $('#base47-he-restore-selected, #base47-he-download-selected').prop('disabled', true);
        $('#base47-he-backup-preview').hide();
        $('#base47-he-backup-list').html('<p class="base47-he-loading">Loading backups...</p>');
        
        // Fetch backup list
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_list_backups',
            nonce:  BASE47_HE.nonce,
            file:   $file.val(),
            set:    getActiveSet()
        }, function (resp) {
            if (resp.success && resp.data && resp.data.length > 0) {
                let html = '';
                resp.data.forEach(function(backup, index) {
                    const badge = index === 0 ? '<span class="base47-he-backup-badge">Latest</span>' : '';
                    const size = (backup.size / 1024).toFixed(1) + ' KB';
                    html += `
                        <div class="base47-he-backup-item" data-backup="${backup.filename}">
                            <div>
                                <div class="base47-he-backup-date">${backup.display_date}</div>
                                <div class="base47-he-backup-size">${size}</div>
                            </div>
                            ${badge}
                        </div>
                    `;
                });
                $('#base47-he-backup-list').html(html);
            } else {
                $('#base47-he-backup-list').html('<p class="base47-he-no-backups">No backups available for this file.</p>');
            }
        });
    });

    // Close modal
    $(document).on('click', '.base47-he-modal-close', function() {
        $('#base47-he-restore-modal').fadeOut(200);
    });

    // Select backup
    $(document).on('click', '.base47-he-backup-item', function() {
        $('.base47-he-backup-item').removeClass('selected');
        $(this).addClass('selected');
        
        selectedBackup = $(this).data('backup');
        $('#base47-he-restore-selected, #base47-he-download-selected').prop('disabled', false);
        
        // Load preview
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_ajax_restore_backup',
            nonce:  BASE47_HE.nonce,
            file:   $file.val(),
            set:    getActiveSet(),
            backup_filename: selectedBackup
        }, function (resp) {
            if (resp.success && resp.data && resp.data.content) {
                $('#base47-he-backup-preview-content').val(resp.data.content);
                $('#base47-he-backup-preview').slideDown(200);
            }
        });
    });

    // Restore selected backup
    $('#base47-he-restore-selected').on('click', function() {
        if (!selectedBackup) return;
        
        const btn = $(this);
        const originalText = btn.text();
        btn.prop('disabled', true).text('Restoring...');
        
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_ajax_restore_backup',
            nonce:  BASE47_HE.nonce,
            file:   $file.val(),
            set:    getActiveSet(),
            backup_filename: selectedBackup
        }, function (resp) {
            if (resp.success && resp.data && resp.data.content) {
                // Load content into editor
                $code.val(resp.data.content);
                
                // Update preview
                $.post(BASE47_HE.ajax_url, {
                    action: 'base47_he_live_preview',
                    nonce:  BASE47_HE.nonce,
                    file:   $file.val(),
                    set:    getActiveSet(),
                    content: resp.data.content
                }, function (previewResp) {
                    if (previewResp.success && previewResp.data && previewResp.data.html) {
                        const iframe = $preview.get(0);
                        iframe.contentWindow.document.open();
                        iframe.contentWindow.document.write(previewResp.data.html);
                        iframe.contentWindow.document.close();
                    }
                });
                
                // Close modal
                $('#base47-he-restore-modal').fadeOut(200);
                
                // Show success message
                alert('Backup restored! Remember to save if you want to keep these changes.');
            } else {
                alert('Failed to restore backup.');
            }
            btn.prop('disabled', false).text(originalText);
        });
    });

    // Download selected backup
    $('#base47-he-download-selected').on('click', function() {
        if (!selectedBackup) return;
        
        const url = BASE47_HE.ajax_url + 
            '?action=base47_he_download_backup' +
            '&file=' + encodeURIComponent($file.val()) +
            '&set=' + encodeURIComponent(getActiveSet()) +
            '&backup_filename=' + encodeURIComponent(selectedBackup) +
            '&nonce=' + BASE47_HE.nonce;
        
        window.location.href = url;
    });

    /* ==========================
       PREVIEW SIZE SWITCHER
    ========================== */
    $('.preview-size-btn').on('click', function () {
        $('.preview-size-btn').removeClass('active');
        $(this).addClass('active');

        const size = $(this).data('size');
        $preview.css({
            width: size === '100%' ? '100%' : size + 'px'
        });
    });

    /* ==========================
       OPEN PREVIEW IN NEW TAB
    ========================== */
    $('#base47-he-open-preview').on('click', function (e) {
        e.preventDefault();
        const src = $preview.attr('src');
        if (src) {
            window.open(src, '_blank');
        }
    });

    /* ==========================
       DRAG RESIZER
    ========================== */
    const $resizer = $('#base47-he-resizer');
    const $left    = $('#base47-he-editor-left');
    const $shell   = $('#base47-he-editor-shell');

    if ($resizer.length && $left.length && $shell.length) {
        let dragging = false;

        $resizer.on('mousedown', function (e) {
            e.preventDefault();
            dragging = true;
            $('body').addClass('base47-he-dragging');
        });

        $(document).on('mousemove', function (e) {
            if (!dragging) return;

            const offset = $shell.offset().left;
            const min    = 200;
            const max    = $shell.width() - 200;

            let w = e.pageX - offset;
            w = Math.max(min, Math.min(max, w));
            $left.css('flex-basis', w + 'px');
        });

        $(document).on('mouseup', function () {
            dragging = false;
            $('body').removeClass('base47-he-dragging');
        });
    }

    /* =======================================================
       LAZY PREVIEW � Shortcodes Page
       (button: .base47-load-preview-btn)
    ======================================================= */
    $(document).on('click', '.base47-load-preview-btn', function (e) {
        e.preventDefault();

        const btn    = $(this);
        const file   = btn.data('file');
        const set    = btn.data('set');
        
        // Support both old and new card structures
        const card   = btn.closest('.base47-he-template-box, .base47-sc-card');
        const iframe = card.find('.base47-he-template-iframe').get(0);

        if (!file || !set || !iframe) {
            return;
        }

        const originalText = btn.html();
        btn.html('<span class="dashicons dashicons-update"></span> Loading...').prop('disabled', true);
        
        // Add loading state to card (Soft UI)
        card.addClass('is-loading');
        
        // Hide empty state (Soft UI)
        card.find('.base47-sc-preview-empty').hide();

        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_lazy_preview',
            nonce:  BASE47_HE.nonce,
            file:   file,
            set:    set
        }, function (res) {

            btn.html(originalText).prop('disabled', false);
            card.removeClass('is-loading');

            if (res && res.success && res.data && res.data.html) {
                iframe.srcdoc = res.data.html;
                $(iframe).show(); // Show iframe (Soft UI)
            } else {
                iframe.srcdoc = '<div style="padding:20px;color:#c00;">Preview error.</div>';
                $(iframe).show();
            }
        }).fail(function () {
            btn.html(originalText).prop('disabled', false);
            card.removeClass('is-loading');
            if (iframe) {
                iframe.srcdoc = '<div style="padding:20px;color:#c00;">Network error.</div>';
                $(iframe).show();
            }
        });
    });

    /* ==========================
       UNINSTALL THEME (Theme Manager)
    ========================== */
    $(document).on('click', '.base47-tm-uninstall-btn', function (e) {
        e.preventDefault();

        const $btn  = $(this);
        const slug  = $btn.data('theme');
        const $card = $btn.closest('.base47-tm-card');

        if (!slug) {
            return;
        }

        if (!confirm('Are you sure you want to uninstall this theme? This will delete its folder from the server.')) {
            return;
        }

        $btn.prop('disabled', true).text('Uninstalling�');

        $.post(
            BASE47_HE.ajax_url,
            {
                action: 'base47_he_uninstall_theme',
                theme:  slug,
                nonce: BASE47_HE.nonce
            }
        )
        .done(function (resp) {
            if (resp && resp.success) {
                $card.slideUp(200, function () {
                    $(this).remove();
                });
            } else {
                alert((resp && resp.data && resp.data.message) ? resp.data.message : 'Error uninstalling theme.');
                $btn.prop('disabled', false).text('Uninstall');
            }
        })
        .fail(function () {
            alert('Ajax error while uninstalling theme.');
            $btn.prop('disabled', false).text('Uninstall');
        });
    });

    /* ==========================
       DEFAULT THEME SELECTOR
    ========================== */
    $('#base47_default_theme').on('change', function () {

        const selected = $(this).val();

        $.ajax({
            url: BASE47_HE.ajax_url,
            method:   'POST',
            data: {
                action:   'base47_set_default_theme',
                theme:    selected,
                nonce: BASE47_HE.nonce
            },
            success: function (response) {
                if (response && response.success) {
                    console.log('Default theme updated:', selected);
                } else {
                    alert('Could not save default theme.');
                }
            }
        });
    });

    /* ==========================
       MONACO EDITOR INTEGRATION
    ========================== */
    
    let monacoEditor = null;
    let currentEditorMode = BASE47_HE.editor_mode || 'advanced';
    let lastSavedContent = '';
    let hasUnsavedChanges = false;
    
    // Initialize Monaco Editor if on editor page
    if ($('#base47-monaco-editor').length > 0) {
        initializeMonacoEditor();
    }
    
    function initializeMonacoEditor() {
        // Set Monaco paths
        require.config({ 
            paths: { 
                'vs': BASE47_HE.plugin_url + 'admin-assets/monaco/vs'
            }
        });
        
        // Load Monaco
        require(['vs/editor/editor.main'], function() {
            // Get initial content from textarea
            const initialContent = $('#base47-he-code').val() || '';
            lastSavedContent = initialContent;
            
            // Get theme from settings
            const theme = (BASE47_HE.editor_theme === 'dark') ? 'vs-dark' : 'vs';
            
            // Create Monaco Editor
            monacoEditor = monaco.editor.create(document.getElementById('base47-monaco-editor'), {
                value: initialContent,
                language: 'html',
                theme: theme,
                automaticLayout: true,
                lineNumbers: 'on',
                wordWrap: 'on',
                minimap: { enabled: true },
                scrollBeyondLastLine: false,
                fontSize: 14,
                tabSize: 2,
                insertSpaces: true,
                renderWhitespace: 'selection',
                bracketPairColorization: { enabled: true },
                suggest: {
                    showKeywords: true,
                    showSnippets: true
                }
            });
            
            // Track changes for unsaved detection
            monacoEditor.onDidChangeModelContent(function() {
                const currentContent = monacoEditor.getValue();
                hasUnsavedChanges = (currentContent !== lastSavedContent);
                updateSaveButtonState();
            });
            
            // Add keyboard shortcuts
            monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, function() {
                $('#base47-he-save').click();
            });
            
            // Set initial mode based on settings
            if (currentEditorMode === 'classic') {
                // Start with classic mode
                $('#base47-monaco-editor').hide();
                $('#base47-he-code').show();
                $('#base47-he-mode-classic').addClass('active');
                $('#base47-he-mode-advanced').removeClass('active');
                
                // Apply dark theme if needed
                if (BASE47_HE.editor_theme === 'dark') {
                    $('#base47-he-code').css({
                        'background': '#1e1e1e',
                        'color': '#d4d4d4',
                        'border-color': '#3e3e3e'
                    });
                }
            } else {
                // Start with advanced mode (default)
                $('#base47-he-mode-advanced').addClass('active');
                $('#base47-he-mode-classic').removeClass('active');
            }
            
            console.log('Monaco Editor initialized successfully');
        });
    }
    
    // Editor Mode Switching
    $('.base47-he-mode-btn').on('click', function() {
        const mode = $(this).attr('id') === 'base47-he-mode-advanced' ? 'advanced' : 'classic';
        switchEditorMode(mode);
    });
    
    function switchEditorMode(mode) {
        if (mode === currentEditorMode) return;
        
        const currentContent = getCurrentEditorContent();
        
        if (mode === 'advanced') {
            // Switch to Monaco
            $('#base47-monaco-editor').show();
            $('#base47-he-code').hide();
            
            if (monacoEditor) {
                monacoEditor.setValue(currentContent);
                monacoEditor.layout(); // Refresh layout
            }
            
            $('#base47-he-mode-advanced').addClass('active');
            $('#base47-he-mode-classic').removeClass('active');
            
        } else {
            // Switch to Classic
            $('#base47-monaco-editor').hide();
            $('#base47-he-code').show().val(currentContent);
            
            // Apply dark theme to classic editor if needed
            if (BASE47_HE.editor_theme === 'dark') {
                $('#base47-he-code').css({
                    'background': '#1e1e1e',
                    'color': '#d4d4d4',
                    'border-color': '#3e3e3e'
                });
            }
            
            $('#base47-he-mode-classic').addClass('active');
            $('#base47-he-mode-advanced').removeClass('active');
        }
        
        currentEditorMode = mode;
        
        // Save mode preference
        localStorage.setItem('base47_editor_mode', mode);
    }
    
    // Get content from current active editor
    function getCurrentEditorContent() {
        if (currentEditorMode === 'advanced' && monacoEditor) {
            return monacoEditor.getValue();
        } else {
            return $('#base47-he-code').val();
        }
    }
    
    // Update save button state based on changes
    function updateSaveButtonState() {
        const saveBtn = $('#base47-he-save');
        if (hasUnsavedChanges) {
            saveBtn.text('Save *').addClass('base47-he-unsaved');
        } else {
            saveBtn.text('Save').removeClass('base47-he-unsaved');
        }
    }
    
    // Load saved editor mode preference (override settings default)
    const savedMode = localStorage.getItem('base47_editor_mode');
    if (savedMode && monacoEditor) {
        switchEditorMode(savedMode);
    }
    
    // Warn about unsaved changes
    $(window).on('beforeunload', function() {
        if (hasUnsavedChanges) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    // Override existing save handler to work with both editors
    const originalSaveHandler = $('#base47-he-save').data('events')?.click;
    $('#base47-he-save').off('click').on('click', function(e) {
        e.preventDefault();
        
        const content = getCurrentEditorContent();
        const btn = $(this);
        const originalText = btn.text();
        
        btn.prop('disabled', true).text('Saving...');
        
        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_save_template',
            nonce: BASE47_HE.nonce,
            file: $('#base47-he-current-file').val(),
            set: $('#base47-he-current-set').val(),
            content: content
        }, function(resp) {
            if (resp.success) {
                lastSavedContent = content;
                hasUnsavedChanges = false;
                updateSaveButtonState();
                
                // Update preview with live preview (not reload)
                $.post(BASE47_HE.ajax_url, {
                    action: 'base47_he_live_preview',
                    nonce: BASE47_HE.nonce,
                    file: $('#base47-he-current-file').val(),
                    set: $('#base47-he-current-set').val(),
                    content: content
                }, function(previewResp) {
                    if (previewResp.success && previewResp.data && previewResp.data.html) {
                        const iframe = $('#base47-he-preview')[0];
                        if (iframe && iframe.contentWindow) {
                            iframe.contentWindow.document.open();
                            iframe.contentWindow.document.write(previewResp.data.html);
                            iframe.contentWindow.document.close();
                        }
                    }
                });
                
                // Show success feedback
                btn.text('Saved!').css('background', '#46b450');
                setTimeout(function() {
                    btn.css('background', '').text('Save');
                }, 1500);
            } else {
                alert('Save failed: ' + (resp.data || 'Unknown error'));
            }
            btn.prop('disabled', false);
        }).fail(function() {
            alert('Save failed: Network error');
            btn.prop('disabled', false).text(originalText);
        });
    });

});
