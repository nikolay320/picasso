<?php if ($search['form_type'] === 0 || ($search['form_type'] === 1 && (!$category_select = $this->Taxonomy_SelectList($category_bundle, array('name' => 'category', 'class' => 'sabai-pull-right', 'parent' => $category, 'current' => $current_category, 'content_bundle' => 'directory_listing', 'depth' => $search['cat_depth'], 'hide_empty' => $search['cat_hide_empty'], 'hide_count' => $search['cat_hide_count'], 'default_text' => __('Select category', 'sabai-directory')))))) return;?>
<div class="sabai-directory-search sabai-clearfix">
    <form method="get" action="<?php echo $action_url;?>" class="sabai-search<?php if (!empty($mini)):?> sabai-search-mini<?php endif;?>">
        <div class="sabai-row">
<?php switch ($search['form_type']):?>
<?php   case 6:?>
            <div class="sabai-col-sm-5 sabai-directory-search-keyword">
                <input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search...', 'sabai-directory'));?>" />
            </div>
            <div class="sabai-col-sm-5 sabai-directory-search-location">
                <input name="address" type="text" value="<?php Sabai::_h($address);?>" placeholder="<?php Sabai::_h(__('Enter a location', 'sabai-directory'));?>" />
                <span class="sabai-directory-search-radius-trigger"><i class="fa fa-gear"></i></span>
                <input type="hidden" name="directory_radius" value="<?php echo $distance;?>" />
                <input type="hidden" name="center" />
                <input type="hidden" name="address_type" value="<?php echo $address_type;?>" />
            </div>
<?php     break;?>
<?php   case 5: case 3: $category_select = $this->Taxonomy_SelectList($category_bundle, array('name' => 'category', 'class' => 'sabai-pull-right', 'parent' => $category, 'current' => $current_category, 'content_bundle' => 'directory_listing', 'depth' => $search['cat_depth'], 'hide_empty' => $search['cat_hide_empty'], 'hide_count' => $search['cat_hide_count'], 'default_text' => __('Select category', 'sabai-directory')));?>
<?php     if (empty($search['no_key'])):?>
            <div class="<?php if ($category_select):?>sabai-col-sm-6<?php else:?>sabai-col-sm-10<?php endif;?> sabai-directory-search-keyword">
                <input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search...', 'sabai-directory'));?>" />
            </div>
<?php     else:?>
            <div class="<?php if ($category_select):?>sabai-col-sm-6<?php else:?>sabai-col-sm-10<?php endif;?> sabai-directory-search-location">
                <input name="address" type="text" value="<?php Sabai::_h($address);?>" placeholder="<?php Sabai::_h(__('Enter a location', 'sabai-directory'));?>" />
                <span class="sabai-directory-search-radius-trigger"><i class="fa fa-gear"></i></span>
                <input type="hidden" name="directory_radius" value="<?php echo $distance;?>" />
                <input type="hidden" name="center" />
                <input type="hidden" name="address_type" value="<?php echo $address_type;?>" />
            </div>
<?php     endif;?>
<?php     if ($category_select):?>
            <div class="sabai-col-sm-4 sabai-directory-search-category">
                <?php echo $category_select;?>
            </div>
<?php     endif;?>
<?php     break;?>
<?php   case 4: case 2:?>
<?php     if (empty($search['no_key'])):?>
            <div class="sabai-col-sm-10 sabai-directory-search-keyword">
                <input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search...', 'sabai-directory'));?>" />
            </div>
<?php     else:?>
            <div class="sabai-col-sm-10 sabai-directory-search-location">
                <input name="address" type="text" value="<?php Sabai::_h($address);?>" placeholder="<?php Sabai::_h(__('Enter a location', 'sabai-directory'));?>" />
                <span class="sabai-directory-search-radius-trigger"><i class="fa fa-gear"></i></span>
                <input type="hidden" name="directory_radius" value="<?php echo $distance;?>" />
                <input type="hidden" name="center" />
                <input type="hidden" name="address_type" value="<?php echo $address_type;?>" />
            </div>
<?php     endif;?>
<?php     break;?>
<?php   case 1:?>
            <div class="sabai-col-sm-10 sabai-directory-search-category">
                <?php echo $category_select;?>
            </div>
<?php     break;?>
<?php   default: $category_select = $this->Taxonomy_SelectList($category_bundle, array('name' => 'category', 'class' => 'sabai-pull-right', 'parent' => $category, 'current' => $current_category, 'content_bundle' => 'directory_listing', 'depth' => $search['cat_depth'], 'hide_empty' => $search['cat_hide_empty'], 'hide_count' => $search['cat_hide_count'], 'default_text' => __('Select category', 'sabai-directory'))); $button_width = $category_select ? 1 : 2;?>
            <div class="<?php if ($category_select):?>sabai-col-sm-4<?php else:?>sabai-col-sm-5<?php endif;?> sabai-directory-search-keyword">
                <input name="keywords" type="text" value="<?php Sabai::_h($keywords);?>" placeholder="<?php Sabai::_h(__('Search...', 'sabai-directory'));?>" />
            </div>
            <div class="<?php if ($category_select):?>sabai-col-sm-4<?php else:?>sabai-col-sm-5<?php endif;?> sabai-directory-search-location">
                <input name="address" type="text" value="<?php Sabai::_h($address);?>" placeholder="<?php Sabai::_h(__('Enter a location', 'sabai-directory'));?>" style="padding-right:20px;" />
                <span class="sabai-directory-search-radius-trigger"><i class="fa fa-gear"></i></span>
                <input type="hidden" name="directory_radius" value="<?php echo $distance;?>" />
                <input type="hidden" name="center" />
                <input type="hidden" name="address_type" value="<?php echo $address_type;?>" />
            </div>
<?php     if ($category_select):?>
            <div class="sabai-col-sm-3 sabai-directory-search-category">
                <?php echo $category_select;?>
            </div>
<?php     endif;?>
<?php endswitch;?>
            <div class="<?php if (@$button_width === 1):?>sabai-col-sm-1<?php else:?>sabai-col-sm-2<?php endif;?> sabai-directory-search-submit">
                <button type="submit" class="sabai-btn sabai-btn-sm sabai-directory-btn-search sabai-btn-block <?php echo Sabai::_h($button);?>">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {    
    $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search input').keydown(function(e){
        if (e.keyCode == 13) { 
             $("<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-submit .sabai-btn").click();
        }
    });
<?php if (empty($search['no_key']) && !empty($search['auto_suggest'])):?>
<?php   if (!empty($search['suggest_cat'])):?>
    var category_templates = {}, categories = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: '<?php echo $this->Url('/sabai/taxonomy/termlist', is_array($category_bundle) ? array('bundle' => implode(',', array_keys($category_bundle))) : array('bundle' => $category_bundle, 'parent' => $category, Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');?>'
        },
        limit: <?php echo isset($search['suggest_cat_num']) ? $search['suggest_cat_num'] : 5;?>
    });
    categories.initialize();
<?php     if (!empty($search['suggest_cat_header'])):?>
    category_templates.header = '<h4><?php Sabai::_h($search['suggest_cat_header']);?></h4>';
<?php     endif;?>
<?php     if (!empty($search['suggest_cat_icon'])):?>
    category_templates.suggestion = function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_cat_icon']);?>"></i> ' + item.title};
<?php     endif;?>
<?php   endif;?>
<?php   if (!empty($search['suggest_listing'])): $num = isset($search['suggest_listing_num']) ? $search['suggest_listing_num'] : 5;?>
    var listing_templates = {}, listings = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            wildcard: 'QUERY',
            url: '<?php echo $this->Url('/sabai/directory/listinglist', array('query' => 'QUERY', Sabai_Request::PARAM_CONTENT_TYPE => 'json') + (isset($bundle) ? array('bundle' => $bundle->name, 'category' => $category, 'num' => $num) : (isset($bundles) ? array('bundle' => implode(',', $bundles), 'num' => $num) : array('bundle_type' => 'directory_listing', 'num' => $num))), '', '&');?>'
        },
        limit: <?php echo $num;?>
    });
    listings.initialize();
<?php     if (!empty($search['suggest_listing_header'])):?>
    listing_templates.header = '<h4><?php Sabai::_h($search['suggest_listing_header']);?></h4>';
<?php     endif;?>
<?php     if (!empty($search['suggest_listing_icon'])):?>
    listing_templates.suggestion = function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_listing_icon']);?>"></i> ' + item.title};
<?php     endif;?>
<?php   endif;?>
<?php   if (!empty($search['suggest_listing']) || !empty($search['suggest_cat'])):?>
    $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-keyword input').typeahead(
        {highlight: true, minLength: <?php echo empty($search['key_suggest_min_length']) ? 1 : $search['key_suggest_min_length'];?>}
<?php     if (!empty($search['suggest_listing'])):?>
        , {name: 'listings', displayKey: 'title', source: listings.ttAdapter(), templates: listing_templates}
<?php     endif;?>
<?php     if (!empty($search['suggest_cat'])):?>
        , {name: 'categories', displayKey: 'title', source: categories.ttAdapter(), templates: category_templates}
<?php     endif;?>
    ).bind('typeahead:selected', function(obj, datum, name) {
<?php     if (!empty($search['suggest_listing_jump'])):?>
        if (name === 'listings') window.location.href = datum.url;
<?php     endif;?>
<?php     if (!empty($search['suggest_cat_jump'])):?>
<?php       if (!empty($search['no_loc'])):?>
        if (name === 'categories') window.location.href = datum.url;
<?php       else:?>
        if (name === 'categories') {
            window.location.href = datum.url + '?address=' + encodeURIComponent($('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-location input[name="address"]').val());
        }
<?php       endif;?>
<?php     endif;?>
    });
<?php   endif;?>
<?php endif;?>
<?php if (empty($search['no_loc'])): $distances = $this->Filter('directory_distances', array(0, 1, 2, 3, 5, 10, 20, 50, 100));?>
    var geocoder, location = $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-location input[type=text]');
<?php   if (!empty($search['auto_suggest_loc'])):?>
<?php     if (!empty($search['suggest_location'])):?>
    var autocomplete,
        location_templates = {},
        findLocation = function(q, cb) {
            if (!google.maps.places) return;
            if (!autocomplete) autocomplete = new google.maps.places.AutocompleteService();
            autocomplete.getPlacePredictions({input: q, types: ['(regions)']<?php if ($search['country']):?>, componentRestrictions: {country: '<?php Sabai::_h($search['country']);?>'}<?php endif;?>}, function(predictions, status){
                if (status == google.maps.places.PlacesServiceStatus.OK) {
                    cb(predictions);
                }
            });
        };
<?php       if (!empty($search['suggest_location_header'])):?>
    location_templates.header = '<h4><?php Sabai::_h($search['suggest_location_header']);?></h4>';
<?php       endif;?>
<?php       if (!empty($search['suggest_location_icon'])):?>
    location_templates.suggestion = function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_location_icon']);?>"></i> ' + item.description};
<?php       endif;?>
<?php     endif;?>
<?php     if (!empty($search['suggest_state'])):?>
    var state_templates = {}, states = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: '<?php echo $this->Url('/sabai/directory/locationlist', array('column' => 'state', 'addon' => isset($bundle) ? $bundle->addon : '', Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');?>'
        },
        limit: <?php echo !empty($search['suggest_state_num']) ? $search['suggest_state_num'] : 5;?>
    });
    states.initialize();
<?php       if (!empty($search['suggest_state_header'])):?>
    state_templates.header = '<h4><?php Sabai::_h($search['suggest_state_header']);?></h4>';
<?php       endif;?>
<?php       if (!empty($search['suggest_state_icon'])):?>
    state_templates.suggestion = function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_state_icon']);?>"></i> ' + item.value};
<?php       endif;?>
<?php     endif;?>
<?php     if (!empty($search['suggest_city'])):?>
    var city_templates = {}, cities = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: '<?php echo $this->Url('/sabai/directory/locationlist', array('column' => 'city', 'addon' => isset($bundle) ? $bundle->addon : '', Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');?>'
        },
        limit: <?php echo !empty($search['suggest_city_num']) ? $search['suggest_city_num'] : 5;?>
    });
    cities.initialize();
<?php       if (!empty($search['suggest_city_header'])):?>
    city_templates.header = '<h4><?php Sabai::_h($search['suggest_city_header']);?></h4>';
<?php       endif;?>
<?php       if (!empty($search['suggest_city_icon'])):?>
    city_templates.suggestion = function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_city_icon']);?>"></i> ' + item.value};
<?php       endif;?>
<?php     endif;?>
<?php     if (!empty($search['suggest_zip'])):?>
    var zip_templates = {}, zips = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: '<?php echo $this->Url('/sabai/directory/locationlist', array('column' => 'zip', 'addon' => isset($bundle) ? $bundle->addon : '', Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');?>'
        },
        limit: <?php echo !empty($search['suggest_zip_num']) ? $search['suggest_zip_num'] : 5;?>
    });
    zips.initialize();
<?php       if (!empty($search['suggest_zip_header'])):?>
    zip_templates.header = '<h4><?php Sabai::_h($search['suggest_zip_header']);?></h4>';
<?php       endif;?>
<?php       if (!empty($search['suggest_zip_icon'])):?>
    zip_templates.suggestion = function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_zip_icon']);?>"></i> ' + item.value};
<?php       endif;?>
<?php     endif;?>
<?php     if (!empty($search['suggest_country'])):?>
    var country_templates = {}, countries = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: {
            url: '<?php echo $this->Url('/sabai/directory/locationlist', array('column' => 'country', 'addon' => isset($bundle) ? $bundle->addon : '', Sabai_Request::PARAM_CONTENT_TYPE => 'json'), '', '&');?>'
        },
        limit: <?php echo !empty($search['suggest_country_num']) ? $search['suggest_country_num'] : 5;?>
    });
    countries.initialize();
<?php       if (!empty($search['suggest_country_header'])):?>
    country_templates.header = '<h4><?php Sabai::_h($search['suggest_country_header']);?></h4>';
<?php       endif;?>
<?php       if (!empty($search['suggest_country_icon'])):?>
    country_templates.suggestion = function(item){return '<i class="fa fa-<?php Sabai::_h($search['suggest_country_icon']);?>"></i> ' + item.value};
<?php       endif;?>
<?php     endif;?>
    location.typeahead(
        {highlight: true, minLength: <?php echo empty($search['loc_suggest_min_length']) ? 1 : $search['loc_suggest_min_length'];?>}
<?php     if (!empty($search['suggest_location'])):?>
        , {name: 'location', displayKey: 'description', source: findLocation, templates: location_templates}
<?php     endif;?>
<?php     if (!empty($search['suggest_city'])):?>
        , {name: 'city', source: cities.ttAdapter(), templates: city_templates}
<?php     endif;?>
<?php     if (!empty($search['suggest_state'])):?>
        , {name: 'state', source: states.ttAdapter(), templates: state_templates}
<?php     endif;?>
<?php     if (!empty($search['suggest_zip'])):?>
        , {name: 'zip', source: zips.ttAdapter(), templates: zip_templates}
<?php     endif;?>
<?php     if (!empty($search['suggest_country'])):?>
        , {name: 'country', source: countries.ttAdapter(), templates: country_templates}
<?php     endif;?>
    ).bind('typeahead:selected', function(obj, datum, name) {
        if (name !== 'location') {
            $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-location input[name="address_type"]').val(name);
        }
    }).bind('keyup', function(e) {
        if (e.keyCode !== 13 && e.keyCode !== 27 && e.keyCode !== 32) {
            $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-location input[name="address_type"]').val('');
        }
    });
<?php   endif;?>
    $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-radius-trigger').click(function(e){
        var $this = $(this), container = $this.parent(), radius = container.find('input[name="directory_radius"]'), slid;
        SABAI.popover(
            $this,
            {
                html: true,
                container: container,
                placement: function (pop, ele) { return window.innerWidth - $(ele).offset().left > 300 ? 'bottom' : (SABAI.isRTL ? 'right' : 'left');},
                title: '<?php echo __('Location Options', 'sabai-directory');?>',
                content: '<div class="sabai-directory-search-radius">'
                    + '<div class="sabai-directory-search-radius-label"><?php printf($is_mile ? __('Search Radius: %s mi', 'sabai-directory') : __('Search Radius: %s km', 'sabai-directory'), '<strong></strong>');?></div>'
                    + '<div class="sabai-directory-search-radius-slider" style="margin-top:5px;"></div>'
                    + '</div>'
                    + '<button style="display:none; margin-top:20px !important; width:auto;" class="sabai-btn sabai-btn-xs sabai-btn-default sabai-directory-search-geolocate"><i class="fa fa-map-marker"></i> <?php echo __('Get My Location', 'sabai-directory');?></button>'
            }
        );
        container.on('shown.bs.sabaipopover', function(){
            if (slid) return;
            var label = container.find('.sabai-directory-search-radius-label strong').text(radius.val());
            container.find('.sabai-directory-search-radius-slider').slider({animate: true, min: <?php echo min($distances);?>, max: <?php echo max($distances);?>, value: radius.val(), step: 1, slide: function(e, ui){
                radius.val(ui.value);
                label.text(ui.value);
            }});
            if (navigator.geolocation) {
                var geocode = function (trigger) {
                    if (trigger) SABAI.ajaxLoader(trigger);
                    if (!geocoder) geocoder = new google.maps.Geocoder();
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            geocoder.geocode({'latLng': new google.maps.LatLng(pos.coords.latitude,pos.coords.longitude)}, function(results, status) {
                                if (trigger) SABAI.ajaxLoader(trigger, true);
                                if (status == google.maps.GeocoderStatus.OK) {
                                    location.val(results[0].formatted_address).typeahead('val', results[0].formatted_address).effect('highlight', {}, 2000);
                                    $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-location')
                                        .find('input[name="center"]').val(results[0].geometry.location.lat() + ',' + results[0].geometry.location.lng()).end()
                                        .find('input[name="address_type"]').val('');
                                }
                            });
                        },
                        function (error) {
                            if (trigger) {
                                SABAI.ajaxLoader(trigger, true);
                                alert('<?php Sabai::_h(__('The position of the device could not be determined.', 'sabai-directory'));?> (' + error.code + ')');
                            }
                            SABAI.console.log(error.message + ' (' + error.code + ')');
                        },
                        {enableHighAccuracy:true, timeout:5000}
                    );
                };
                container.find('.sabai-directory-search-geolocate').show().click(function(e){
                    e.preventDefault();
                    geocode($(this));
                });
            }
            slid = true;
        });
    });
<?php endif;?>
    $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-submit .sabai-btn').click(function(e){
        var $this = $(this),
            form = $this.closest('form');
        e.preventDefault();
        form.find('[placeholder]').each(function() {
            var input = $(this);
            if (input.val() == input.attr('placeholder')) {
                input.val('');
            }
        });
<?php if (empty($no_ajax_submit)):?>
        var submit = function(){
            SABAI.ajax({
                type: <?php if (defined('SABAI_FIX_URI_TOO_LONG') && SABAI_FIX_URI_TOO_LONG):?>'post'<?php else:?>'get'<?php endif;?>,
                container: '<?php echo $CURRENT_CONTAINER;?>', 
                target: '.sabai-directory-listings-container',
                url: form.attr('action') + '&' + form.serialize(),
                pushState: true
            });
        };
<?php else:?>
        var submit = function(){form.submit()};
<?php endif;?>
<?php if (empty($search['no_loc'])):?>
        var center = $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-location input[name="center"]');
        if (location.val()
            && $('<?php echo $CURRENT_CONTAINER;?> .sabai-directory-search-location input[name="address_type"]').val() === ''
        ) {
            if (!geocoder) geocoder = new google.maps.Geocoder();
            geocoder.geocode({address: location.val()<?php if ($search['country']):?>, region: '<?php echo $search['country'];?>'<?php endif;?>}, function(results, status) {
                switch (status) {
                    case google.maps.GeocoderStatus.OK:
                        center.val(results[0].geometry.location.lat() + ',' + results[0].geometry.location.lng());
                        submit();
                        break;
                    case google.maps.GeocoderStatus.ZERO_RESULTS:
                        alert('<?php echo __('Invalid location', 'sabai-directory');?>');
                        break;
                    default:
                        alert(status);
                }
            });
        } else {
            center.val('');
            submit();
        }
<?php else:?>
        submit();
<?php endif;?>
    }); 
});
</script>