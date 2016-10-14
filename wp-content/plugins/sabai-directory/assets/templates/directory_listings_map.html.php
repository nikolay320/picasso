<?php
$markers = array();
foreach ($entities as $entity) {
    if (empty($entity['entity']->data['lat'])) {
        if (!empty($settings['map']['map_show_all'])) {
            if (!$locations = $entity['entity']->getFieldValue('directory_location')) continue;
            foreach ($locations as $location) {
                if ($location['lat'] && $location['lng']) {
                    $markers[] = array(
                        'lat' => $location['lat'],
                        'lng' => $location['lng'],
                        'content' => $this->renderTemplate($entity['entity']->getBundleType() . '_single_infobox', array('is_mile' => $settings['is_mile']) + $entity),
                        'icon' => $this->Directory_ListingMapMarkerUrl($entity['entity']),
                    );
                }
            }
        } else {
            if (($location = $entity['entity']->getSingleFieldValue('directory_location'))
                && $location['lat']
                && $location['lng']
            ) {
                $markers[] = array(
                    'lat' => $location['lat'],
                    'lng' => $location['lng'],
                    'content' => $this->renderTemplate($entity['entity']->getBundleType() . '_single_infobox', array('is_mile' => $settings['is_mile']) + $entity),
                    'icon' => $this->Directory_ListingMapMarkerUrl($entity['entity']),
                );
            }
        }
    } else {
        settype($entity['entity']->data['lng'], 'array');
        foreach ((array)$entity['entity']->data['lat'] as $key => $lat) {
            if (null === $address_weight = @$entity['entity']->data['weight'][$key]) continue;

            $markers[] = array(
                'lat' => $lat,
                'lng' => $entity['entity']->data['lng'][$key],
                'content' => $this->renderTemplate($entity['entity']->getBundleType() . '_single_infobox', array('address_weight' => $address_weight, 'is_mile' => $settings['is_mile']) + $entity),
                'icon' => $this->Directory_ListingMapMarkerUrl($entity['entity']),
            );
        }
    }
}
$url_params2 = $url_params;
unset($url_params2['center'], $url_params2['sw'], $url_params2['ne'], $url_params2['zoom']);
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    var googlemaps = function () {
        SABAI.GoogleMaps.map(
            '<?php echo $CURRENT_CONTAINER;?> .sabai-directory-map',
            <?php echo json_encode($markers);?>,
            <?php if (!empty($center) && ($is_drag || $is_geolocate || empty($markers))):?><?php echo json_encode($center);?><?php else:?>null<?php endif;?>,
            <?php echo isset($settings['map']['listing_default_zoom']) ? intval($settings['map']['listing_default_zoom']) : 15;?>,
            <?php echo json_encode($settings['map']['options']);?>,
            function (center, bounds, zoom) {
                SABAI.ajax({
                    type: <?php if (defined('SABAI_FIX_URI_TOO_LONG') && SABAI_FIX_URI_TOO_LONG):?>'post'<?php else:?>'get'<?php endif;?>,
                    container: '<?php echo $CURRENT_CONTAINER;?>',
                    target: '.sabai-directory-listings-container',
                    url: '<?php echo $this->Url($CURRENT_ROUTE);?>?<?php echo http_build_query($url_params2);?>&is_drag=1&center=' + center.lat() + ',' + center.lng() + '&sw=' + bounds.getSouthWest().lat() + ',' + bounds.getSouthWest().lng() + '&ne=' + bounds.getNorthEast().lat() + ',' + bounds.getNorthEast().lng() + '&zoom=' + zoom,
                    onError: function(error) {SABAI.flash(error.message, 'danger');},
                    pushState: true
                });
            }
        );
    }
    if ($('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-map').is(':visible')) {
        googlemaps();
    } else {
        $('<?php echo $CURRENT_CONTAINER;?>-trigger').on('shown.bs.sabaitab', function(e, data){
            googlemaps();
        });
    }
    $(SABAI).unbind('entity_filter_form_toggled.sabai.<?php echo $CURRENT_CONTAINER;?>.googlemaps').bind('entity_filter_form_toggled.sabai.<?php echo $CURRENT_CONTAINER;?>.googlemaps', function (e, data) {
        if (data.container !== '<?php echo $CURRENT_CONTAINER;?>') { 
            var map = SABAI.GoogleMaps.maps['<?php echo $CURRENT_CONTAINER;?> .sabai-directory-map'];
            if (map) {
                var center = map.getCenter();
                google.maps.event.trigger(map, 'resize');
                map.setCenter(center);
            }
        }
    });
});
</script>
<?php $this->displayTemplate('directory_listings_nav', array('category_suggestions' => $category_suggestions, 'sorts' => $sorts, 'views' => $views, 'url_params' => $url_params, 'settings' => $settings, 'geocode_error' => $geocode_error, 'show_filters' => $show_filters, 'show_filters_link' => $show_filters_link, 'filter_form' => $filter_form, 'links' => $links));?>
<div class="sabai-directory-listings sabai-directory-listings-map<?php if (empty($settings['search']['filters_top'])):?><?php if (!$filter_form || !$show_filters):?> sabai-col-md-12<?php else:?> sabai-col-md-8<?php endif;?><?php endif;?>">
<?php if (empty($settings['map']['no_header'])):?>
    <div class="sabai-directory-map-header"><input class="sabai-directory-map-update" type="checkbox" /><label><?php echo __('Redo search when map moved', 'sabai-directory');?></label></div>
<?php endif;?>
    <div class="sabai-directory-map sabai-googlemaps-map" style="height:<?php echo intval($settings['map']['height']);?>px;" data-map-type="<?php echo $settings['map']['type'];?>"></div>
</div>
<?php $this->displayTemplate('directory_listings_pager', array('paginator' => $paginator, 'url_params' => $url_params, 'settings' => $settings));?>