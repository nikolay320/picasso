jQuery(SABAI).bind('sabai_init.sabai', function(e, data){
    jQuery('.sabai-entity-field-type-taxonomy-terms.sabai-taxonomy-select2 select, .sabai-entity-field-type-taxonomy-term-parent select', data.context).each(function(){
        var $this = jQuery(this);
        $this.select2({
            allowClear: true,
            width: "100%"
        });
    });
});