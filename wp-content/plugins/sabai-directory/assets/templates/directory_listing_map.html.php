<?php
if (!$entity->directory_location) return;
$markers = array();
foreach ($entity->directory_location as $key => $location) {
    if (!$location['lat'] || !$location['lng']) continue;
    $markers[$key] = array(
        'content' => $this->renderTemplate($entity->getBundleType() . '_single_infobox', array('entity' => $entity, 'address_weight' => $key)),
        'lat' => $location['lat'],
        'lng' => $location['lng'],
        'trigger' => '#sabai-directory-map-directions .sabai-googlemaps-directions-destination',
        'triggerEvent' => 'change'
    );
}
$multi_address = count($markers) > 1;
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
    var googlemaps = function () {
        SABAI.GoogleMaps.map(
            "#sabai-directory-map",
            <?php echo json_encode($markers);?>,
            null,
            <?php echo isset($map_settings['listing_default_zoom']) ? intval($map_settings['listing_default_zoom']) : 15;?>,
            <?php echo json_encode(array('marker_clusters' => false, 'enable_directions' => '#sabai-directory-map-directions', 'icon' => $this->Directory_ListingMapMarkerUrl($entity)) + $map_settings['options']);?>
        );
        SABAI.GoogleMaps.autocomplete(".sabai-googlemaps-directions-input");
    }
    if ($('#sabai-directory-map').is(':visible')) {
        googlemaps();
    } else {
        $('#sabai-inline-content-map-trigger').on('shown.bs.sabaitab', function(e, data){
            googlemaps();
        });
    }
});
</script>
<div id="sabai-directory-map-directions">
    <div id="sabai-directory-map" class="sabai-googlemaps-map" style="height:300px;" data-map-type="<?php echo $map_settings['type'];?>"></div>
    <div class="sabai-googlemaps-directions-search">
        <form class="sabai-search">
            <div class="sabai-row">
                <div class="sabai-col-xs-12<?php if (!$multi_address):?> sabai-col-sm-6<?php endif;?>"><input type="text" class="sabai-googlemaps-directions-input" value="" placeholder="<?php Sabai::_h(__('Enter a location', 'sabai-directory'));?>" /></div>
<?php if ($multi_address):?>
                <div class="sabai-col-xs-12">
                    <select class="sabai-googlemaps-directions-destination">
<?php   foreach (array_keys($markers) as $key):?>
                        <option value="<?php echo $key;?>"><?php Sabai::_h($entity->directory_location[$key]['address']);?></option>
<?php   endforeach;?>
                    </select>
                </div>
<?php else:?>
                <input type="hidden" value="0" class="sabai-googlemaps-directions-destination" />
<?php endif;?>
                <div class="sabai-col-xs-12 sabai-col-sm-3<?php if ($multi_address):?> sabai-col-sm-offset-6<?php endif;?>">
                    <select class="sabai-googlemaps-directions-mode">
                        <option value="DRIVING"><?php echo __('By car', 'sabai-directory');?></option>
                        <option value="TRANSIT"><?php echo __('By public transit', 'sabai-directory');?></option>
                        <option value="WALKING"><?php echo __('Walking', 'sabai-directory');?></option>
                        <option value="BICYCLING"><?php echo __('Bicycling', 'sabai-directory');?></option>
                    </select>
                </div>
                <div class="sabai-col-xs-12 sabai-col-sm-3"><a class="sabai-btn sabai-btn-sm sabai-btn-primary sabai-directory-btn-directions sabai-googlemaps-directions-trigger sabai-btn-block"><?php echo __('Get Directions', 'sabai-directory');?></a></div>
            </div>
        </form>
    </div>
    <div class="sabai-googlemaps-directions-panel" style="height:300px; overflow-y:auto; display:none;"></div>
</div>