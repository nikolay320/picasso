(function($){    
    SABAI.Time = SABAI.Time || {};
    SABAI.Time.timepicker = SABAI.Time.timepicker || function (selector) {
        var $time = $(selector);
        if (!$time.length) return;
        
        var $time_start = $time.find('.sabai-time-timepicker-start');    
        if (!$time_start.length) return;
        $time_start.removeClass('hasTimepicker').timepicker();
        
        var $time_end = $time.find('.sabai-time-timepicker-end');    
        if ($time_end.length) {
            $time_end.removeClass('hasTimepicker').timepicker();
        }
    };
    
    $(SABAI).bind('clonefield.sabai', function (e, data) {
        if (data.clone.hasClass('sabai-form-type-time-timepicker')) {
            SABAI.Time.timepicker(data.clone.removeAttr('id'));
        }
    });
})(jQuery);