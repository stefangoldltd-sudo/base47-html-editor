jQuery(function($){

  const $code    = $('#base47-he-code');
  const $file    = $('#base47-he-current-file');
  const $set     = $('#base47-he-current-set');
  const $preview = $('#base47-he-preview');

  // ==========================
  // COPY SHORTCODE BUTTON
  // ==========================
  $('.base47-he-copy').on('click', function(){
    const sc = $(this).data('shortcode');
    if (!sc) return;
    const $btn = $(this);
    const original = $btn.text();

    navigator.clipboard.writeText(sc).then(() => {
      $btn.text('Copied ✓').css('background', '#2ecc71');
      setTimeout(() => {
        $btn.text(original).css('background', '');
      }, 1200);
    });
  });

  // ==========================
  // Helper → Get Safe Theme Set
  // ==========================
  function getActiveSet() {
    let setVal = $set.val();
    if (!setVal || setVal === '' || setVal === 'undefined') {
      if (typeof BASE47_HE_DATA.default_set !== 'undefined' && BASE47_HE_DATA.default_set) {
        setVal = BASE47_HE_DATA.default_set;
      } else {
        setVal = 'base47-templates';
      }
    }
    return setVal;
  }

  // ==========================
  // LIVE PREVIEW (debounced)
  // ==========================
  let liveTimer;
  if ($code.length && $preview.length) {
    $code.on('input', function(){
      clearTimeout(liveTimer);
      liveTimer = setTimeout(function(){
        $.post(BASE47_HE_DATA.ajax_url, {
          action:  'base47_he_live_preview',
          nonce:   BASE47_HE_DATA.nonce,
          file:    $file.val(),
          set:     getActiveSet(),
          content: $code.val()
        }, function(resp){
          if (resp && resp.success && resp.data && resp.data.html) {
            const iframe = $preview.get(0);
            if (iframe && iframe.contentWindow) {
              iframe.contentWindow.document.open();
              iframe.contentWindow.document.write(resp.data.html);
              iframe.contentWindow.document.close();
            }
          }
        });
      }, 700);
    });
  }

  // ==========================
  // SAVE TEMPLATE
  // ==========================
  $('#base47-he-save').on('click', function(e){
    e.preventDefault();
    $.post(BASE47_HE_DATA.ajax_url, {
      action:  'base47_he_save_template',
      nonce:   BASE47_HE_DATA.nonce,
      file:    $file.val(),
      set:     getActiveSet(),
      content: $code.val()
    }, function(resp){
      if (resp && resp.success) {
        const src = $preview.attr('src').split('&_rand=')[0];
        $preview.attr('src', src + '&_rand=' + Date.now());
        $('#base47-he-save').text('Saved ✓');
        setTimeout(()=>$('#base47-he-save').text('Save'),1000);
      } else {
        alert('Save failed: ' + (resp && resp.data ? resp.data : 'unknown'));
      }
    });
  });

  // ==========================
  // RESTORE TEMPLATE
  // ==========================
  $('#base47-he-restore').on('click', function(e){
    e.preventDefault();
    $.post(BASE47_HE_DATA.ajax_url, {
      action: 'base47_he_get_template',
      nonce:  BASE47_HE_DATA.nonce,
      file:   $file.val(),
      set:    getActiveSet()
    }, function(resp){
      if (resp && resp.success) {
        if ($code.length) $code.val(resp.data.content);
        if ($preview.length) {
          const iframe = $preview.get(0);
          if (iframe && iframe.contentWindow) {
            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(resp.data.preview);
            iframe.contentWindow.document.close();
          }
        }
      } else {
        alert('Restore failed: ' + (resp && resp.data ? resp.data : 'unknown'));
      }
    });
  });

  // ==========================
  // PREVIEW SIZE SWITCHER
  // ==========================
  $('.preview-size-btn').on('click', function(){
    const size = $(this).data('size');
    $('.preview-size-btn').removeClass('active');
    $(this).addClass('active');
    $preview.css({width: size === '100%' ? '100%' : size + 'px'});
  });

  // ==========================
  // OPEN PREVIEW IN NEW TAB
  // ==========================
  $('#base47-he-open-preview').on('click', function(e){
    e.preventDefault();
    if (!$preview.length) return;
    const src = $preview.attr('src');
    if (!src) return;
    window.open(src, '_blank');
  });

  // ==========================
  // DRAG RESIZER
  // ==========================
  const $resizer = $('#base47-he-resizer');
  const $left    = $('#base47-he-editor-left');
  const $shell   = $('#base47-he-editor-shell');
  if ($resizer.length && $left.length && $shell.length) {
    let dragging = false;

    $resizer.on('mousedown', function(e){
      e.preventDefault();
      dragging = true;
      $('body').addClass('mivon-he-dragging');
    });

    $(document).on('mousemove', function(e){
      if (!dragging) return;
      const shellOffset = $shell.offset().left;
      const minW = 200;
      const maxW = $shell.width() - 200;
      let newW = e.pageX - shellOffset;
      newW = Math.max(minW, Math.min(maxW, newW));
      $left.css('flex-basis', newW + 'px');
    });

    $(document).on('mouseup', function(){
      if (dragging) {
        dragging = false;
        $('body').removeClass('mivon-he-dragging');
      }
    });
  }

  // ==========================
  // KEYBOARD SHORTCUTS
  // ==========================
  $(document).on('keydown', function(e){
    const isMac = navigator.platform && navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    const ctrlOrMeta = isMac ? e.metaKey : e.ctrlKey;
    if (!ctrlOrMeta) return;

    const key = (e.key || '').toLowerCase();

    if (key === 's') { e.preventDefault(); $('#base47-he-save').trigger('click'); }
    if (key === 'p') { e.preventDefault(); $('#base47-he-open-preview').trigger('click'); }
  });

  // ==========================
  // THEME MANAGER TOGGLES  (FIXED VERSION)
  // ==========================
  $(document).on('change', '.base47-switch input', function(){
    const $el = $(this);
    const theme = $el.data('theme');   // <─ MUST EXIST NOW
    const enabled = $el.is(':checked') ? 1 : 0;

    if (!theme) {
      alert("Error: Missing theme slug.");
      return;
    }

    $el.closest('label').css('opacity', '0.7');

    $.post(BASE47_HE_DATA.ajax_url, {
      action: 'base47_he_toggle_theme',
      nonce: BASE47_HE_DATA.nonce,
      theme: theme,
      enabled: enabled
    }, function(resp){
      $el.closest('label').css('opacity', '1');
      if (!resp || !resp.success) {
        alert('Error updating theme toggle.');
      }
    });
  });

});