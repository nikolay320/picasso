<?php 
if (!$IS_EMBED) $this->Action('directory_before_listings', array($bundle->addon, $settings));
unset($url_params['center'], $url_params['is_geolocate']);
if (empty($settings['hide_searchbox'])) {
    if (!isset($action_url)) {
        $url_params2 = $url_params;
        unset($url_params2['keywords'], $url_params2['address'], $url_params2['category'], $url_params2['view']);
        if (isset($settings['user_id'])) {
            $url_params2['user_id'] = $settings['user_id'];
        }
        $action_url = $this->Url($CURRENT_ROUTE, $url_params2);
    }
    $this->displayTemplate('directory_searchbox', array('button' => 'sabai-btn-primary', 'is_mile' => $settings['is_mile'], 'distance' => empty($settings['distance']) ? $settings['search']['radius'] : $settings['distance'], 'bundle' => @$bundle, 'action_url' => $action_url, 'search' => $settings['search'], 'address' => $settings['address'], 'address_type' => isset($settings['address_type']) ? $settings['address_type'] : '', 'keywords' => isset($settings['keywords'][2]) ? $settings['keywords'][2] : '', 'current_category' => isset($settings['requested_category']) ? $settings['requested_category'] : $settings['category'], 'category' => $settings['parent_category'], 'category_bundle' => $settings['category_bundle'], 'no_ajax_submit' => !empty($no_ajax_submit)));
}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    $(SABAI).bind('sabaipopstate', function (e, state) {
        if (state.container !== '<?php echo $CURRENT_CONTAINER;?>' || state.target !== '.sabai-directory-listings-container') return;

        var url = SABAI.parseUrl(state.url);
        $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search')
            .find('input[name="keywords"]').val(url.query.keywords || '').end()
            .find('input[name="address"]').val(url.query.address || '').end()
            .find('select[name="category"]').val(url.query.category || 0);
    });
<?php if (empty($settings['search']['filters_top'])):?>
    $(SABAI).bind('toggle.sabai', function (e, data) {
        if (data.target.hasClass('sabai-directory-filters')) {
            data.target.parent().find('.sabai-directory-listings').removeClass('sabai-col-md-12').addClass('sabai-col-md-8');
        }
    });
    
    $(SABAI).bind('entity_filter_form_toggled.sabai.<?php echo $CURRENT_CONTAINER;?>', function (e, data) {
        if (data.container === '<?php echo $CURRENT_CONTAINER;?>' && !data.target.is(':visible')) {
            data.target.parent().find('.sabai-directory-listings').removeClass('sabai-col-md-8').addClass('sabai-col-md-12');
        }
    });
<?php endif;?>
});
</script>
<div class="sabai-directory-listings-container">
<?php if (empty($settings['hide_listings'])):?>  
<?php   $this->displayTemplate('directory_listings_' . $settings['view'], array('category_suggestions' => $category_suggestions, 'entities' => $entities, 'paginator' => $paginator, 'url_params' => $url_params, 'sorts' => $sorts, 'views' => $views, 'settings' => $settings, 'center' => $center, 'is_drag' => $is_drag, 'is_geolocate' => $is_geolocate, 'geocode_error' => $geocode_error, 'show_filters' => $show_filters, 'show_filters_link' => $show_filters_link, 'filter_form' => $filter_form, 'links' => $links));?>
<?php endif;?>
</div>
<?php if (!$IS_EMBED) $this->Action('directory_after_listings', array($bundle->addon, $settings));?>