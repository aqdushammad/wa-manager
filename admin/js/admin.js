jQuery(function($){
    var selectedIds = [];

    // Pre-populate from hidden input
    var existing = $('#wamPageIds').val();
    if (existing) {
        selectedIds = existing.split(',').filter(Boolean);
    }

    // Toggle global checkbox
    $('#wamIsGlobal').on('change', function(){
        $('#wamPageField').toggle(!this.checked);
    }).trigger('change');

    // Page search
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
});
