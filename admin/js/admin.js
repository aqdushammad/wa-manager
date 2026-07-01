jQuery(function($){

    /* ── Page selector ─────────────────────────────────────────────────────── */
    var selectedIds = [];

    var existing = $('#wamPageIds').val();
    if (existing) {
        selectedIds = existing.split(',').filter(Boolean);
    }

    $('#wamIsGlobal').on('change', function(){
        $('#wamPageField').toggle(!this.checked);
    }).trigger('change');

    var searchTimer;
    $('#wamPageSearch').on('input', function(){
        clearTimeout(searchTimer);
        var q = $(this).val().trim();
        if (q.length < 1) { $('#wamSuggestions').removeClass('open').empty(); return; }
        searchTimer = setTimeout(function(){
            $.get(WAM.ajax_url, { action:'wam_search_pages', q:q, nonce:WAM.nonce }, function(data){
                var $s = $('#wamSuggestions').empty();
                if (!data.length) { $s.append('<div class="wam-suggestion-item" style="color:#999">No pages found</div>'); }
                data.forEach(function(p){
                    if (selectedIds.indexOf(String(p.id)) !== -1) return;
                    $('<div class="wam-suggestion-item">').text(p.text).attr('data-id', p.id).appendTo($s);
                });
                $s.addClass('open');
            });
        }, 280);
    });

    $(document).on('click', '.wam-suggestion-item', function(){
        var id   = $(this).data('id');
        var text = $(this).text();
        if (!id) return;
        selectedIds.push(String(id));
        updateHidden();
        addTag(id, text);
        $('#wamPageSearch').val('');
        $('#wamSuggestions').removeClass('open').empty();
    });

    $(document).on('click', '.wam-page-tag button', function(){
        var id = $(this).closest('.wam-page-tag').data('id');
        selectedIds = selectedIds.filter(function(x){ return x !== String(id); });
        updateHidden();
        $(this).closest('.wam-page-tag').remove();
    });

    $(document).on('click', function(e){
        if (!$(e.target).closest('.wam-page-search-wrap').length) {
            $('#wamSuggestions').removeClass('open');
        }
    });

    function addTag(id, text) {
        $('<span class="wam-page-tag">').attr('data-id', id).append(
            document.createTextNode(text + ' ')
        ).append($('<button type="button">').text('×')).appendTo('#wamSelectedPages');
    }

    function updateHidden() {
        $('#wamPageIds').val(selectedIds.join(','));
    }

    /* ── Color pickers — sync text ↔ native picker ─────────────────────────── */
    var colorPairs = [
        { picker: '#wamBtnColor', text: '#wamBtnColorText' },
        { picker: '#wamHdrColor', text: '#wamHdrColorText' },
        { picker: '#wamAgtColor', text: '#wamAgtColorText' },
    ];

    colorPairs.forEach(function(pair){
        // Picker → text box
        $(pair.picker).on('input change', function(){
            $(pair.text).val(this.value);
            updatePreview();
        });
        // Text box → picker (only on valid 7-char hex)
        $(pair.text).on('input', function(){
            var v = this.value.trim();
            if (/^#[0-9a-fA-F]{6}$/.test(v)) {
                $(pair.picker).val(v);
                updatePreview();
            }
        });
    });

    /* ── Position toggle active class ──────────────────────────────────────── */
    $('input[name="widget_position"]').on('change', function(){
        $('.wam-pos-opt').removeClass('active');
        $(this).closest('.wam-pos-opt').addClass('active');
        updatePreview();
    });

    /* ── Icon picker active class ───────────────────────────────────────────── */
    $('input[name="icon_style"]').on('change', function(){
        $('.wam-icon-opt').removeClass('active');
        $(this).closest('.wam-icon-opt').addClass('active');
    });

    /* ── Live preview updater ───────────────────────────────────────────────── */
    function updatePreview() {
        var btn    = $('#wamBtnColor').val()  || '#25D366';
        var header = $('#wamHdrColor').val()  || '#1a7c3e';
        var avatar = $('#wamAgtColor').val()  || '#25D366';

        $('#pvHeader').css('background', header);
        $('#pvFloat').css('background', btn);
        $('#pvBtn').css('background', btn);
        $('#pvAvatar').css('background', avatar);
    }

    // Initial preview render
    updatePreview();
});
