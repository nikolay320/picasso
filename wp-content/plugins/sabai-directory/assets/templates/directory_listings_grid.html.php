<?php $this->displayTemplate('directory_listings_nav', array('category_suggestions' => $category_suggestions, 'sorts' => $sorts, 'views' => $views, 'url_params' => $url_params, 'settings' => $settings, 'geocode_error' => $geocode_error, 'show_filters' => $show_filters, 'show_filters_link' => $show_filters_link, 'filter_form' => $filter_form, 'links' => $links));?>
<div class="sabai-directory-listings sabai-directory-listings-grid<?php if (!empty($settings['no_masonry'])):?>  sabai-directory-listings-grid-no-masonry<?php endif;?><?php if (empty($settings['search']['filters_top'])):?><?php if (!$filter_form || !$show_filters):?> sabai-col-md-12<?php else:?> sabai-col-md-8<?php endif;?><?php endif;?>">
<?php if (!empty($entities)):?>
<?php   if (empty($settings['no_masonry'])):?>
<script type="text/javascript">
jQuery('document').ready(function($){
    var $container = $('<?php echo $CURRENT_CONTAINER;?>').find('.sabai-directory-listings-grid'),
        masonry = function () {
            $container.imagesLoaded(function() {
                var parent = $container.closest('.sabai-row'), parent_margin = (parseInt(parent.css('margin-left'), 10) || 0) + (parseInt(parent.css('margin-right'), 10) || 0),
                    border = (parseInt($container.css('border-left'), 10) || 0) + (parseInt($container.css('border-right'), 10) || 0),
                    containerWidth = $container.outerWidth() + parent_margin - border - 1, columnWidth;
                if (containerWidth > 768) {
                    columnWidth = Math.floor((containerWidth - <?php echo $settings['grid_columns'] - 1;?> * 20) / <?php echo $settings['grid_columns'];?>);
                } else if (containerWidth > 480) {
                    columnWidth = Math.floor((containerWidth - 20) / 2);
                } else {
                    columnWidth = containerWidth;
                }
                $container.find('> div').width(columnWidth).end().masonry({columnWidth:columnWidth, itemSelector:'.sabai-entity', gutter:20, isRTL:SABAI.isRTL});
            });
        }
    if ($container.is(':visible')) {
        masonry();
    } else {
        $('<?php echo $CURRENT_CONTAINER;?>-trigger').on('shown.bs.sabaitab', function(e, data){
            masonry();
        });
    }
    $(SABAI).unbind('entity_filter_form_toggled.sabai.<?php echo $CURRENT_CONTAINER;?>.masonry').bind('entity_filter_form_toggled.sabai.<?php echo $CURRENT_CONTAINER;?>.masonry', function (e, data) {
        if (data.container === '<?php echo $CURRENT_CONTAINER;?>') {
            masonry();
        }
    });
});
</script>
<?php     foreach ($entities as $entity):?>
<?php $this->displayTemplate($entity['entity']->getBundleType() . '_single_column', array('span' => null, 'address_weight' => @$entity['entity']->data['weight'], 'is_mile' => $settings['is_mile']) + $entity);?>
<?php     endforeach;?>
<?php   else:?>
<?php     $_entities = $this->SliceArray($entities, $settings['grid_columns'], false);?>
<?php     foreach ($_entities as $row => $columns):?>
    <div class="sabai-row">
<?php       foreach ($columns as $entity):?>
        <?php $this->displayTemplate($entity['entity']->getBundleType() . '_single_column', array('span' => intval(12 / $settings['grid_columns']), 'address_weight' => @$entity['entity']->data['weight'], 'is_mile' => $settings['is_mile']) + $entity);?>
<?php       endforeach;?>
    </div>
<?php     endforeach;?>
<?php   endif;?>
<?php else:?>
    <p><?php echo __('No entries were found.', 'sabai-directory');?></p>
<?php endif;?>
</div>
<?php $this->displayTemplate('directory_listings_pager', array('paginator' => $paginator, 'url_params' => $url_params, 'settings' => $settings));?>
