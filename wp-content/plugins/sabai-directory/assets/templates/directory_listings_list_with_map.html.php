<?php
$markers = array();
$do_trigger_infobox = $do_content = true;
if (isset($settings['map']['list_infobox'])) {
    if ($settings['map']['list_infobox'] === 'marker') {
        $do_trigger_infobox = false; // do not display infobox on hover
    } elseif ($settings['map']['list_infobox'] === '') {
        $do_trigger_infobox = $do_content = false;
    }
}
foreach ($entities as $entity) {
    if (empty($entity['entity']->data['lat'])) {
        if (($location = $entity['entity']->getSingleFieldValue('directory_location'))
            && $location['lat']
            && $location['lng']
        ) {
            $markers[] = array(
                'lat' => $location['lat'],
                'lng' => $location['lng'],
                'trigger' => '#sabai-entity-content-' . $entity['entity']->getId() . ' .sabai-directory-title > *',
                'trigger_infobox' => $do_trigger_infobox,
                'content' => $do_content ? $this->renderTemplate($entity['entity']->getBundleType() . '_single_infobox', array('is_mile' => $settings['is_mile']) + $entity) : null,
                'icon' => $this->Directory_ListingMapMarkerUrl($entity['entity']),
            );
        }
    } else {
        settype($entity['entity']->data['lng'], 'array');
        $is_first_address = true;
        foreach ((array)$entity['entity']->data['lat'] as $key => $lat) {
            if (!intval($lat) || (null === $address_weight = @$entity['entity']->data['weight'][$key])) continue;

            $trigger = '#sabai-entity-content-' . $entity['entity']->getId();
            if (count($entity['entity']->data['lat']) > 1 && !$is_first_address) {
                $trigger .= ' .sabai-googlemaps-address-' . $key;
            } else {
                $trigger .= ' a.sabai-entity-bundle-type-directory-listing';
                $is_first_address = false;
            }
            $markers[] = array(
                'lat' => $lat,
                'lng' => $entity['entity']->data['lng'][$key],
                'trigger' => $trigger,
                'trigger_infobox' => $do_trigger_infobox,
                'content' => $do_content ? $this->renderTemplate($entity['entity']->getBundleType() . '_single_infobox', array('address_weight' => $address_weight, 'is_mile' => $settings['is_mile']) + $entity) : null,
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
<?php if (empty($settings['map']['list_scroll'])):?>
        $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-map-container').stickyScroll({topSpacing: 40, stopper: '<?php echo $CURRENT_CONTAINER;?> .sabai-navigation-bottom'});
<?php endif;?>
    }
    if ($('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-map').is(':visible')) {
        googlemaps();
    } else {
        $('<?php echo $CURRENT_CONTAINER;?>-trigger').on('shown.bs.sabaitab', function(e, data){
            googlemaps();
        });
    }
    $(SABAI).unbind('entity_filter_form_toggled.sabai.<?php echo $CURRENT_CONTAINER;?>.googlemaps').bind('entity_filter_form_toggled.sabai.<?php echo $CURRENT_CONTAINER;?>.googlemaps', function (e, data) {
        if (data.container !== '<?php echo $CURRENT_CONTAINER;?>') return;
        var map = SABAI.GoogleMaps.maps['<?php echo $CURRENT_CONTAINER;?> .sabai-directory-map'];
        if (map) {
            var center = map.getCenter();
            google.maps.event.trigger(map, 'resize');
            map.setCenter(center);
        }
<?php if (empty($settings['map']['list_scroll'])):?>
        $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-map-container').stickyScroll({topSpacing: 40, stopper: '<?php echo $CURRENT_CONTAINER;?> .sabai-navigation-bottom'});
<?php endif;?>
    });
});
</script>
<div class="sabai-directory-listings-with-map sabai-row">
    <div class="sabai-directory-listings-with-map-listings sabai-col-sm-<?php echo 12 - $settings['map']['span'];?>" style="transition: width 0.5s;">
<?php if (!empty($settings['map']['list_scroll'])):?>
        <div style="overflow-y:auto; overflow-x:hidden; height:<?php echo $settings['map']['list_height'] + 25;?>px;">
<?php endif;?>
<?php if (!empty($entities)):?>
<?php   foreach ($entities as $entity):?>
            <?php $this->displayTemplate($entity['entity']->getBundleType() . '_single_' . $entity['display_mode'], array('address_weight' => @$entity['entity']->data['weight'], 'is_mile' => $settings['is_mile']) + $entity);?>
<?php   endforeach;?>
<?php else:?>
            <p><?php echo __('No entries were found.', 'sabai-directory');?></p>
<?php endif;?>
<?php if (!empty($settings['map']['list_scroll'])):?>
        </div>
<?php endif;?>
    </div>
    <div class="sabai-directory-listings-with-map-map sabai-col-sm-<?php echo intval($settings['map']['span']);?> sabai-hidden-xs" style="transition: width 0.5s;">
        <div class="sabai-directory-map-container" data-spy="sabaiaffix" data-offset-top="60" data-offset-bottom="200">
            <div class="sabai-directory-map-header">
                <input class="sabai-directory-map-update" type="checkbox" /><label><?php echo __('Redo search when map moved', 'sabai-directory');?></label>
            </div>
            <div class="sabai-directory-map sabai-googlemaps-map" style="height:<?php echo intval($settings['map']['list_height']);?>px;" data-map-type="<?php echo $settings['map']['type'];?>"></div>
        </div>
    </div>
</div>
