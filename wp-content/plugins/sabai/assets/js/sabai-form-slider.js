(function($){    
    SABAI.Form = SABAI.Form || {};
    SABAI.Form.slider = SABAI.Form.slider || function (selector) {
        var $slider = $(selector);
        if (!$slider.length) return;
        
        var $slider_slider = $slider.find('.sabai-form-slider');
        if (!$slider_slider.length) return;
        
        var $slider_value = $slider.find('.sabai-form-slider-value');
        if (!$slider_value.length) return;
        
        var min_value = parseFloat($slider_slider.data('slider-min')) || 0;
        var max_value = parseFloat($slider_slider.data('slider-max')) || 100;
        var orig_value = parseFloat($slider_slider.data('slider-value')) || min_value;
        $slider_slider.empty().show().slider({
            animate: true,
            min: min_value,
            max: max_value,
            value: orig_value,
            step: $slider_slider.data('slider-step') || 1,
            slide: function(e, ui) {
                $slider_slider.removeClass('sabai-form-inactive');
                $slider_value.val(ui.value);
            },
            stop: function(e, ui) {
                var val = parseFloat($slider_value.val());
                if (!isNaN(val)) {
                    if (val !== orig_value) {
                        $slider_value.trigger('change', [val, true]);
                    }
                } else {
                    $slider_slider.slider('value', orig_value);
                }
            }
        });
        $slider_value.change(function(e, val, slid){
            if (!slid) {
                if (typeof val === 'undefined') {
                    val = parseFloat($slider_value.val());
                    if (isNaN(val)) {
                        $slider_value.val(orig_value);
                    }
                }
                if (val < min_value) {
                    $slider_value.val(min_value);
                } else if (val > max_value) {
                    $slider_value.val(max_value);
                }
                $slider_slider.removeClass('sabai-form-inactive').slider('value', $slider_value.val()); 
            }
            orig_value = $slider_value.val();
        });
    };
    
    $(SABAI).bind('clonefield.sabai', function (e, data) {
        if (data.clone.hasClass('sabai-form-type-slider')) {
            SABAI.Form.slider(data.clone.removeAttr('id'));
        }
    });
})(jQuery);