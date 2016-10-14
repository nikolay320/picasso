<?php if (empty($hide_nav)):?>
<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left sabai-btn-group">
<?php   if (!empty($links[0])):?>
        <?php echo $this->DropdownButtonLinks($links[0]);?>
<?php   endif;?>
<?php   if ($show_filters_link):?>
        <?php echo $show_filters_link;?>
<?php   endif;?>
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-directory'));?>
    </div>
    <div class="sabai-pull-right">
<?php   if (!empty($links[1])):?>
        <?php echo $this->ButtonLinks($links[1], array('label' => true, 'tooltip' => false));?>
<?php   endif;?>
    </div>
</div>
<?php endif;?>
<div class="sabai-directory-filters sabai-directory-review-filters"<?php if (!$filter_form || !$show_filters):?> style="display:none;"<?php endif;?>>
<?php if ($filter_form):?>
    <?php echo $this->Form_Render($filter_form);?>
<?php endif;?>
</div>
<?php if (!empty($entities)):?>
<div class="sabai-directory-listing-reviews" style="clear:both;">
<?php   $listing = isset($entity) ? $entity : null; foreach ($entities as $entity):?>
<?php     $this->displayTemplate('directory_listing_review_single_' . $entity['display_mode'], $entity + array('parent_entity' => $listing));?>
<?php   endforeach;?>
</div>
<?php   if ($paginator && empty($hide_pager)):?>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php     if ($paginator->count() > 1):?>
    <div class="sabai-pull-left">
        <?php printf(__('Showing %d - %d of %s results', 'sabai-directory'), $paginator->getElementOffset() + 1, $paginator->getElementOffset() + $paginator->getElementLimit(), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php     else:?>
    <div class="sabai-pull-left">
        <?php printf(_n('Showing %s result', 'Showing %s results', $paginator->getElementCount(), 'sabai-directory'), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
<?php     endif;?>
</div>
<?php   endif;?>
<?php endif;?>