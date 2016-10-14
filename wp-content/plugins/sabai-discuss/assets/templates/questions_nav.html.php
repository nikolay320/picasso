<?php   if (!empty($category_suggestions)):?>
<div class="sabai-questions-category-suggestions">
    <strong><?php echo __('Browse Category:', 'sabai-discuss');?></strong>
    <?php echo implode(', ', $category_suggestions);?>
</div>
<?php   endif; ?>
<?php   if (!empty($settings['keywords']) && (count($settings['keywords'][0]) > 1 || count($settings['keywords'][1]))):?>
<div class="sabai-questions-keywords">
    <strong><?php echo __('Keywords:', 'sabai-discuss');?></strong>
<?php     if (!empty($settings['keywords'][0])):?>
    <span class="sabai-questions-keywords-valid">
<?php       foreach ($settings['keywords'][0] as $keyword):?>
        <?php echo $this->LinkTo($keyword, $this->Url($CURRENT_ROUTE, array('keywords' => strpos($keyword, ' ') ? '"'. $keyword .'"' : $keyword) + $url_params));?>
<?php       endforeach;?>
    </span>
<?php     endif;?>
	<?php     if (!empty($settings['keywords'][1])):?>
	<span class="sabai-questions-keywords-invalid"><span><?php echo implode('</span> <span>', $settings['keywords'][1]);?></span></span>
<?php     endif;?>
</div>
<?php   endif; ?>
<?php if (empty($settings['hide_nav'])):?>
<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left sabai-btn-group">
<?php if (!empty($links[0])):?>
        <?php echo $this->DropdownButtonLinks($links[0]);?>
<?php endif;?>
<?php   if ($show_filters_link):?>
        <?php echo $show_filters_link;?>
<?php   endif;?>
<?php   if (count($sorts) > 1):?>
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-discuss'));?>
<?php   endif;?>
    </div>
    <div class="sabai-pull-right">
<?php   if (!empty($links[1])):?>
        <?php echo $this->ButtonLinks($links[1], array('label' => true, 'tooltip' => false));?>
<?php   endif;?>
    </div>
</div>
<?php endif;?>
<?php if (empty($settings['search']['filters_top'])):?>
<div class="sabai-row">
    <div class="sabai-questions-filters sabai-col-md-4"<?php if (!$filter_form || !$show_filters):?> style="display:none;"<?php endif;?>">
<?php else:?>
    <div class="sabai-questions-filters"<?php if (!$filter_form || !$show_filters):?> style="display:none;"<?php endif;?>>
<?php endif;?>
<?php if ($filter_form):?>
    <?php echo $this->Form_Render($filter_form);?>
<?php endif;?>
</div>