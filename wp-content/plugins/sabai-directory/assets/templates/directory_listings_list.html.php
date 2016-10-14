<?php $this->displayTemplate('directory_listings_nav', array('category_suggestions' => $category_suggestions, 'sorts' => $sorts, 'views' => $views, 'url_params' => $url_params, 'settings' => $settings, 'geocode_error' => $geocode_error, 'show_filters' => $show_filters, 'show_filters_link' => $show_filters_link, 'filter_form' => $filter_form, 'links' => $links));?>
<div class="sabai-directory-listings sabai-directory-listings-list<?php if (empty($settings['search']['filters_top'])):?><?php if (!$filter_form || !$show_filters):?> sabai-col-md-12<?php else:?> sabai-col-md-8<?php endif;?><?php endif;?>">
<?php if ($settings['map']['list_show']):?>
    <?php $this->displayTemplate('directory_listings_list_with_map', array('entities' => $entities, 'url_params' => $url_params, 'center' => $center, 'settings' => $settings, 'is_drag' => $is_drag, 'is_geolocate' => $is_geolocate, 'show_filters' => $show_filters, 'filter_form' => $filter_form));?>
<?php   else:?>
    <?php $this->displayTemplate('directory_listings_list_no_map', array('entities' => $entities, 'url_params' => $url_params, 'settings' => $settings));?>
<?php endif;?>
</div>
<?php $this->displayTemplate('directory_listings_pager', array('paginator' => $paginator, 'url_params' => $url_params, 'settings' => $settings));?>