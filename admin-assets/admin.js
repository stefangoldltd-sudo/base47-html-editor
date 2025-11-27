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
     ACTIVE SET HELPER
  ========================== */
  function getActiveSet(){
    let setVal = $set.val();
    if (!setVal || setVal === 'undefined') {
      setVal = (BASE47_HE_DATA && BASE47_HE_DATA.default_set)
        ? BASE47_HE_DATA.default_set
        : 'base47-templates';
    }
    return setVal;
  }

  /* ==========================
     LIVE PREVIEW (EDITOR)
  ========================== */
  let timer;
  if ($code.length){
    $code.on('input', function(){
      clearTimeout(timer);
      timer = setTimeout(function(){
        $.post(BASE47_HE_DATA.ajax_url,{
          action:'base47_he_live_preview',
          nonce: BASE47_HE_DATA.nonce,
          file:  $file.val(),
          set:   getActiveSet(),
          content: $code.val()
        },function(resp){
          if(resp.success && resp.data && resp.data.html){
            const iframe = $preview.get(0);
            iframe.contentWindow.document.open();
            iframe.contentWindow.document.write(resp.data.html);
            iframe.contentWindow.document.close();
          }
        });
      },700);
    });
  }

  /* ==========================
     SAVE TEMPLATE (EDITOR)
  ========================== */
  $('#base47-he-save').on('click',function(e){
    e.preventDefault();

    $.post(BASE47_HE_DATA.ajax_url,{
      action:'base47_he_save_template',
      nonce: BASE47_HE_DATA.nonce,
      file:  $file.val(),
      set:   getActiveSet(),
      content:$code.val()
    },function(resp){
      if(resp.success){

        // 💥 RESTORED OLD BEHAVIOUR — RELOAD IFRAME
        const src = $preview.attr('src').split('&_rand=')[0];
        $preview.attr('src', src + '&_rand=' + Date.now());

        $('#base47-he-save').text('Saved ✓');
        setTimeout(()=>$('#base47-he-save').text('Save'),900);
      }
    });
  });

  /* ==========================
     RESTORE TEMPLATE (EDITOR)
  ========================== */
  $('#base47-he-restore').on('click',function(e){
    e.preventDefault();
    $.post(BASE47_HE_DATA.ajax_url,{
      action:'base47_he_get_template',
      nonce: BASE47_HE_DATA.nonce,
      file:  $file.val(),
      set:   getActiveSet()
    },function(resp){
      if(resp.success){
        $code.val(resp.data.content);
        const iframe = $preview.get(0);
        iframe.contentWindow.document.open();
        iframe.contentWindow.document.write(resp.data.preview);
        iframe.contentWindow.document.close();
      }
    });
  });

  /* ==========================
     PREVIEW SIZE SWITCHER
  ========================== */
  $('.preview-size-btn').on('click',function(){
    $('.preview-size-btn').removeClass('active');
    $(this).addClass('active');
    const size = $(this).data('size');
    $preview.css({ width: size === '100%' ? '100%' : size + 'px' });
  });

  /* ==========================
     OPEN PREVIEW (OLD FUNCTION)
  ========================== */
  $('#base47-he-open-preview').on('click', function(e){
    e.preventDefault();
    const src = $preview.attr('src');
    if (src) window.open(src, '_blank');
  });

  /* ==========================
     DRAG RESIZER (RESTORED)
  ========================== */
  const $resizer = $('#base47-he-resizer');
  const $left    = $('#base47-he-editor-left');
  const $shell   = $('#base47-he-editor-shell');

  if ($resizer.length && $left.length && $shell.length){
    let dragging = false;

    $resizer.on('mousedown',function(e){
      e.preventDefault();
      dragging = true;
      $('body').addClass('base47-he-dragging');
    });

    $(document).on('mousemove',function(e){
      if(!dragging) return;
      const offset = $shell.offset().left;
      const min = 200;
      const max = $shell.width() - 200;
      let w = e.pageX - offset;
      w = Math.max(min, Math.min(max, w));
      $left.css('flex-basis', w + 'px');
    });

    $(document).on('mouseup',function(){
      dragging = false;
      $('body').removeClass('base47-he-dragging');
    });
  }

  /* =======================================================
     LAZY PREVIEW – Shortcode Page
  ======================================================= */
  $(document).on('click','.base47-load-preview-btn',function(e){
    e.preventDefault();

    const btn  = $(this);
    const file = btn.data('file');
    const set  = btn.data('set');
    const card = btn.closest('.base47-he-template-box');
    const iframe = card.find('.base47-he-template-iframe').get(0);

    btn.text('Loading...').prop('disabled',true);

    $.post(BASE47_HE_DATA.ajax_url,{
      action:'base47_he_lazy_preview',
      nonce: BASE47_HE_DATA.preview_nonce,
      file:file,
      set:set
    },function(res){
      btn.text('Load preview').prop('disabled',false);
      if(res.success && res.data.html){
        const doc = iframe.contentWindow.document;
        doc.open(); doc.write(res.data.html); doc.close();
      }
    });
  });

});