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
        const original = btn.text();

        navigator.clipboard.writeText(sc).then(() => {
            btn.text('Copied').css('background', '#2ecc71');
            setTimeout(() => {
                btn.text(original).css('background', '');
            }, 1200);
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

                // Reload iframe (old behaviour)
                const src = $preview.attr('src').split('&_rand=')[0];
                $preview.attr('src', src + '&_rand=' + Date.now());

                $('#base47-he-save').text('Saved ?');
                setTimeout(() => $('#base47-he-save').text('Save'), 900);
            }
        });
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
        const card   = btn.closest('.base47-he-template-box');
        const iframe = card.find('.base47-he-template-iframe').get(0);

        if (!file || !set || !iframe) {
            return;
        }

        btn.text('Loading�').prop('disabled', true);

        $.post(BASE47_HE.ajax_url, {
            action: 'base47_he_lazy_preview',
            nonce:  BASE47_HE.nonce,
            file:   file,
            set:    set
        }, function (res) {

            btn.text('Load preview').prop('disabled', false);

            if (res && res.success && res.data && res.data.html) {
                iframe.srcdoc = res.data.html; // no extra URL, instant render
            } else {
                iframe.srcdoc = '<div style="padding:20px;color:#c00;">Preview error.</div>';
            }
        }).fail(function () {
            btn.text('Load preview').prop('disabled', false);
            if (iframe) {
                iframe.srcdoc = '<div style="padding:20px;color:#c00;">Network error.</div>';
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

});