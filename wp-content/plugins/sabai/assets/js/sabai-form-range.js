(function($){    
    SABAI.Form = SABAI.Form || {};
    SABAI.Form.range = SABAI.Form.range || function (selector) {
        var $slider = $(selector);
        if (!$slider.length) return;
        
        var $slider_slider = $slider.find('.sabai-form-slider');
        if (!$slider_slider.length) return;
        
        var $slider_min = $slider.find('.sabai-form-slider-min');
        if (!$slider_min.length) return;
        
        var $slider_max = $slider.find('.sabai-form-slider-max');
        if (!$slider_max.length) return;
        
        var min_value = parseFloat($slider_slider.data('slider-min')) || 0;
        var max_value = parseFloat($slider_slider.data('slider-max')) || 100;
        var orig_min_value = parseFloat($slider_slider.data('slider-value-min')) || min_value;
        var orig_max_value = parseFloat($slider_slider.data('slider-value-max')) || max_value;
        $slider_slider.empty().show().slider({
            animate: true,
            range: true,
            min: min_value,
            max: max_value,
            values: [orig_min_value, orig_max_value],
            step: $slider_slider.data('slider-step') || 1,
            slide: function(e, ui) {
                $slider_slider.removeClass('sabai-form-inactive');
                $slider_min.val(ui.values[0]);
                $slider_max.val(ui.values[1]);
            },
            stop: function(e, ui) {
                var min = parseFloat($slider_min.val()), max = parseFloat($slider_max.val());
                if (!isNaN(min)) {
                    if (min !== orig_min_value) {
                        $slider_min.trigger('change', [min, max, true]);
                    }
                } else {
                    $slider_slider.toggleClass('sabai-form-inactive', isNaN(max)).slider('values', 0, orig_min_value);
                }
                if (!isNaN(max)) {
                    if (max !== orig_max_value) {
                        $slider_max.trigger('change', [max, min, true]);
                    }
                } else {
                    $slider_slider.toggleClass('sabai-form-inactive', isNaN(min)).slider('values', 0, orig_max_value);
                }
            }
        });
        $slider_min.change(function(e, min, max, slid){
            if (!slid) {
                if (typeof min === 'undefined') {
                    min = parseFloat($slider_min.val());
                    if (isNaN(min)) {
                        $slider_min.val(orig_min_value);
                    }
                }
                if (min < min_value) {
                    $slider_min.val(min_value);
                } else {
                    if (typeof max === 'undefined') {
                        max = parseFloat($slider_max.val());
                        if (isNaN(max)) {
                            max = orig_max_value;
                        }
                    }
                    if (min > max) {
                        $slider_min.val(max);
                    } else if (min > max_value) {
                        $slider_min.val(max_value);
                    }
                }
                $slider_slider.removeClass('sabai-form-inactive').slider('values', 0, $slider_min.val());
            }
            orig_min_value = $slider_min.val();
        });
        $slider_max.change(function(e, max, min, slid){
            if (!slid) {     
                if (typeof max === 'undefined') {
                    max = parseFloat($slider_max.val());
                    if (isNaN(max)) {
                        $slider_max.val(orig_max_value);
                    }
                }
                if (max > max_value) {
                    $slider_max.val(max_value);
                } else {
                    if (typeof min === 'undefined') {
                        min = parseFloat($slider_min.val());
                        if (isNaN(min)) {
                            min = orig_min_value;
                        }
                    }
                    if (max < min) {
                        $slider_max.val(min);
                    } else if (max < min_value) {
                        $slider_max.val(min_value);
                    }
                }                
                $slider_slider.removeClass('sabai-form-inactive').slider('values', 1, $slider_max.val()); 
            }
            orig_max_value = $slider_max.val();
        });
    };
    
    $(SABAI).bind('clonefield.sabai', function (e, data) {
        if (data.clone.hasClass('sabai-form-type-range')) {
            SABAI.Form.range(data.clone.removeAttr('id'));
        }
    });
})(jQuery);