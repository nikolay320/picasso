jQuery(SABAI).bind('sabai_init.sabai', function(e, data) {
    if ('ontouchstart' in document.documentElement) return;
    
    var timer;
    jQuery('.sabai-directory-thumbnails a').hover(function () {
        var $this = jQuery(this), timeout = $this.hasClass('sabai-directory-photo-loaded') ? 0 : 500;
        timer = setTimeout(function () {
            $this.addClass('sabai-directory-photo-loaded').closest('.sabai-directory-photos')
                .find('> a').attr('href', $this.attr('href'))
                .find('img').attr('src', $this.find('img').data('full-image'));
        }, timeout);
    }, function () {
        clearTimeout(timer);
    });
});