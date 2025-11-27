// admin-assets/admin.js
jQuery(function($){

  const $code    = $('#base47-he-code');
  const $file    = $('#base47-he-current-file');
  const $set     = $('#base47-he-current-set');
  const $preview = $('#base47-he-preview');

  /* ==========================
     COPY SHORTCODE
  ========================== */
  $(document).on('click', '.base47-he-copy', function(){
    const sc = $(this).data('shortcode');
    if (!sc) return;
    const btn = $(this);
    const original = btn.text();

    navigator.clipboard.writeText(sc).then(() => {
      btn.text('Copied ✓').css('background','#2ecc71');
      setTimeout(()=>{
        btn.text(original).css('background','');
      },1200);
    });
  });

  /* ==========================
     ACTIVE SET HELPER  (EDITOR)
  ========================== */
  function getActiveSet(){
    let setVal = $set.val();
    if (!setVal || setVal === 'undefined') {
      setVal = (BASE47_HE_DATA && BASE47_HE_DATA.default_set) ? BASE47_HE_DATA.default_set : 'base47-templates';
    }
    return setVal;
  }

  /* ==========================
     LIVE PREVIEW (EDITOR)
  ========================== */
  let timer;
  if ($code.length && $preview.length){
    $code.on('input', function(){
      clearTimeout(timer);
      timer = setTimeout(function(){
        $.post(BASE47_HE_DATA.ajax_url, {
          action:  'base47_he_live_preview',
          nonce:   BASE47_HE_DATA.nonce, // editor nonce
          file:    $file.val(),
          set:     getActiveSet(),
          content: $code.val()
        }, function(resp){
          if (resp && resp.success && resp.data && resp.data.html){
            const iframe = $preview.get(0);
            if (iframe && iframe.contentWindow) {
              iframe.contentWindow.document.open();
              iframe.contentWindow.document.write(resp.data.html);
              iframe.contentWindow.document.close();
            }
          }
        });
      }, 600);
    });
  }

  /* ==========================
     SAVE TEMPLATE (EDITOR)
  ========================== */
  $('#base47-he-save').on('click', function(e){
    e.preventDefault();
    if (!$file.length) return;

    $.post(BASE47_HE_DATA.ajax_url, {
      action:  'base47_he_save_template',
      nonce:   BASE47_HE_DATA.nonce, // editor nonce
      file:    $file.val(),
      set:     getActiveSet(),
      content: $code.val()
    }, function(resp){
      if (resp && resp.success){
        $('#base47-he-save').text('Saved ✓');
        setTimeout(()=>$('#base47-he-save').text('Save'), 900);
      } else {
        alert('Save failed.');
      }
    });
  });

  /* ==========================
     RESTORE TEMPLATE (EDITOR)
  ========================== */
  $('#base47-he-restore').on('click', function(e){
    e.preventDefault();
    if (!$file.length) return;

    $.post(BASE47_HE_DATA.ajax_url, {
      action: 'base47_he_get_template',
      nonce:  BASE47_HE_DATA.nonce, // editor nonce
      file:   $file.val(),
      set:    getActiveSet()
    }, function(resp){
      if (resp && resp.success && resp.data){
        if ($code.length) {
          $code.val(resp.data.content);
        }
        if ($preview.length){
          const iframe = $preview.get(0);
          if (iframe && iframe.contentWindow){
            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(resp.data.preview);
            iframe.contentWindow.document.close();
          }
        }
      } else {
        alert('Restore failed.');
      }
    });
  });

  /* ==========================
     PREVIEW SIZE SWITCHER (EDITOR)
  ========================== */
  $('.preview-size-btn').on('click', function(){
    if (!$preview.length) return;
    $('.preview-size-btn').removeClass('active');
    $(this).addClass('active');

    const size = $(this).data('size');
    $preview.css({
      width: size === '100%' ? '100%' : size + 'px'
    });
  });

  /* ==========================
     KEYBOARD SHORTCUTS (EDITOR)
  ========================== */
  $(document).on('keydown', function(e){
    const isMac = navigator.platform && navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    const mod   = isMac ? e.metaKey : e.ctrlKey;
    if (!mod) return;

    const key = (e.key || '').toLowerCase();

    if (key === 's'){
      e.preventDefault();
      $('#base47-he-save').trigger('click');
    }
    if (key === 'p'){
      e.preventDefault();
      $('#base47-he-open-preview').trigger('click');
    }
  });

  /* =======================================================
     LAZY PREVIEW – SHORTCODES PAGE (PER CARD, NO AUTO LOAD)
  ======================================================= */

  $(document).on('click', '.base47-load-preview-btn', function(e){
    e.preventDefault();

    const btn  = $(this);
    const file = btn.data('file');
    const set  = btn.data('set');

    if (!file){
      alert('Preview error: missing file.');
      return;
    }

    const card   = btn.closest('.base47-he-template-box');
    const iframe = card.find('.base47-he-template-iframe').get(0);

    if (!iframe){
      alert('Preview frame not found.');
      return;
    }

    btn.prop('disabled', true).text('Loading...');

    $.post(BASE47_HE_DATA.ajax_url, {
      action: 'base47_he_lazy_preview',
      nonce:  BASE47_HE_DATA.preview_nonce, // ⚠ using PREVIEW nonce
      file:   file,
      set:    set
    }, function(res){
      btn.prop('disabled', false).text('Load preview');

      if (res && res.success && res.data && res.data.html){
        const doc = iframe.contentWindow.document;
        doc.open();
        doc.write(res.data.html);
        doc.close();
      } else {
        alert('Preview failed.');
      }
    }).fail(function(){
      btn.prop('disabled', false).text('Load preview');
      alert('AJAX error.');
    });
  });

});