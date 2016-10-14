<?php
if (!$IS_EMBED) $this->Action('questions_before_questions', array($bundle->addon, $settings));
if (empty($settings['hide_searchbox'])) {
    $this->displayTemplate('questions_searchbox', array('button' => 'sabai-btn-primary', 'search' => $settings['search'], 'bundle' => @$bundle, 'action_url' => $this->Url($CURRENT_ROUTE, $url_params), 'keywords' => isset($settings['keywords'][2]) ? $settings['keywords'][2] : '', 'current_category' => isset($settings['requested_category']) ? $settings['requested_category'] : $settings['category'], 'category' => $settings['parent_category'], 'category_bundle' => $settings['category_bundle']));
}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
<?php if (empty($settings['hide_searchbox'])):?>
    $("<?php echo $CURRENT_CONTAINER;?> .sabai-questions-search-submit .sabai-btn").click(function(e){
        var $this = $(this),
            form = $this.closest("form");
        form.find("[placeholder]").each(function() {
            var input = $(this);
            if (input.val() == input.attr("placeholder")) {
                input.val("");
            }
        });
        SABAI.ajax({
            trigger: $this,
            type: <?php if (defined('SABAI_FIX_URI_TOO_LONG') && SABAI_FIX_URI_TOO_LONG):?>"post"<?php else:?>"get"<?php endif;?>,
            target: ".sabai-questions-container",
            container: "<?php echo $CURRENT_CONTAINER;?>",
            url: form.attr("action") + "&" + form.serialize(),
            pushState: true
        });
        e.preventDefault();
    });
<?php endif;?>
<?php if (empty($settings['search']['filters_top'])):?>
    $(SABAI).bind('toggle.sabai', function (e, data) {
        if (data.target.hasClass('sabai-questions-filters')) {
            data.target.parent().find('.sabai-questions-questions').removeClass('sabai-col-md-12').addClass('sabai-col-md-8');
        }
    });
    
    $(SABAI).bind('toggled.sabai', function (e, data) {
        if (data.target.hasClass('sabai-questions-filters')) {
            if (!data.target.is(':visible')) {
                data.target.parent().find('.sabai-questions-questions').removeClass('sabai-col-md-8').addClass('sabai-col-md-12');
            }
            $(SABAI).trigger('questions_filters_toggled.sabai', data);
        }
    });
<?php endif;?>
});
</script>
<div class="sabai-questions-container">
<?php if (empty($entities)):?>
    <?php $this->displayTemplate('questions_questions_none', array('category_suggestions' => $category_suggestions, 'sorts' => $sorts, 'url_params' => $url_params, 'settings' => $settings, 'links' => $links, 'show_filters' => $show_filters, 'show_filters_link' => $show_filters_link, 'filter_form' => $filter_form));?>
<?php else:?>
    <?php $this->displayTemplate('questions_questions_list', array('category_suggestions' => $category_suggestions, 'entities' => $entities, 'paginator' => $paginator, 'sorts' => $sorts, 'url_params' => $url_params, 'settings' => $settings, 'links' => $links, 'show_filters' => $show_filters, 'show_filters_link' => $show_filters_link, 'filter_form' => $filter_form));?>
<?php endif;?>
</div>
<?php if (!$IS_EMBED) $this->Action('questions_after_questions', array($bundle->addon, $settings));?>