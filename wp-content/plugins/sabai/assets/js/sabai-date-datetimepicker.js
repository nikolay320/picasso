(function($){
    $.datepicker.setDefaults({
        altFormat: "yy/mm/dd",
        autoSize: true,
        changeYear: true,
        changeMonth: true,
        showButtonPanel: false
    });
    
    SABAI.Date = SABAI.Date || {};
    SABAI.Date.datetimepicker = SABAI.Date.datetimepicker || function (selector) {
        var $date,
            $date_date,
            $date_alt;

        $date = $(selector);
        if (!$date.length) return;
        
        $date_date = $date.find('.sabai-date-datepicker-date');    
        if (!$date_date.length) return;
        
        $date_alt = $date.find('.sabai-date-datepicker-alt');    
        if (!$date_alt.length) return;
        
        $date_date.removeClass('hasDatepicker')
            .datepicker({
                altField: $date_alt,
                minDate: $date_date.data('date-min') || null,
                maxDate: $date_date.data('date-max') || null,
                numberOfMonths: $date_date.data('date-num-months')
            });
        if ($date_alt.attr('value')) {
            $date_date.attr('value', $.datepicker.formatDate($date_date.datepicker('option', 'dateFormat'), new Date($date_alt.attr('value') + ' 00:00:00')));
        }
        
        var $time = $date.find('.sabai-date-datepicker-time');    
        if (!$time.length) return;
        
        $time.removeClass('hasTimepicker').timepicker();
    };
    
    $(SABAI).bind('clonefield.sabai', function (e, data) {
        if (data.clone.hasClass('sabai-form-type-date-datepicker')) {
            SABAI.Date.datetimepicker(data.clone.removeAttr('id'));
        }
    });
})(jQuery);