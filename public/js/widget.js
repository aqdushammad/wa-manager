(function(){
    document.addEventListener('DOMContentLoaded', function(){
        var popup   = document.getElementById('wamPopup');
        var floatBtn= document.getElementById('wamFloatBtn');
        var closeBtn= document.getElementById('wamClose');
        var badge   = document.getElementById('wamBadge');
        var tail    = document.getElementById('wamTail');
        if (!popup || !floatBtn) return;

        floatBtn.addEventListener('click', function(){
            var isOpen = popup.classList.contains('open');
            if (isOpen) {
                popup.classList.remove('open');
                popup.setAttribute('aria-hidden','true');
                tail.style.display = 'none';
                if (badge) badge.style.display = 'flex';
            } else {
                popup.classList.add('open');
                popup.setAttribute('aria-hidden','false');
                tail.style.display = 'block';
                if (badge) badge.style.display = 'none';
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function(){
                popup.classList.remove('open');
                popup.setAttribute('aria-hidden','true');
                tail.style.display = 'none';
                if (badge) badge.style.display = 'flex';
            });
        }
    });
})();
