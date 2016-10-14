<?php
$filters_style = $settings['view'] === 'list' && !empty($settings['map']['list_show']) && !empty($settings['map']['list_scroll']) ? sprintf('overflow-y:auto; overflow-x:hidden; height:%dpx;', $settings['map']['list_height'] + 25) : '';
if (!$filter_form || !$show_filters) $filters_style .= ' display:none;';
?>
<?php if ($geocode_error):?>
<div class="sabai-alert sabai-alert-danger" style="margin-bottom:15px;"><?php Sabai::_h($geocode_error);?></div>
<?php endif;?>
<?php if (!empty($category_suggestions)):?>
<div class="sabai-directory-category-suggestions">
    <strong><?php echo __('Browse Category:', 'sabai-directory');?></strong>
    <?php echo implode(', ', $category_suggestions);?>
</div>
<?php endif; ?>
<?php if (!empty($settings['keywords']) && (count($settings['keywords'][0]) > 1 || count($settings['keywords'][1]))):?>
<div class="sabai-directory-keywords">
    <strong><?php echo __('Keywords:', 'sabai-directory');?></strong>
<?php   if (!empty($settings['keywords'][0])):?>
    <span class="sabai-directory-keywords-valid">
<?php     foreach ($settings['keywords'][0] as $keyword):?>
        <?php echo Sabai::h($keyword);?>
<?php     endforeach;?>
    </span>
<?php   endif;?>
<?php   if (!empty($settings['keywords'][1])):?>
    <span class="sabai-directory-keywords-invalid"><span><?php echo implode('</span> <span>', array_map(array('Sabai', 'h'), $settings['keywords'][1]));?></span></span>
<?php   endif;?>
</div>
<?php endif; ?>
<?php if (empty($settings['hide_nav'])):?>
<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left sabai-btn-group">
<?php   if (!empty($links[0])):?>
        <?php echo $this->DropdownButtonLinks($links[0]);?>
<?php   endif;?>
<?php   if ($show_filters_link):?>
        <?php echo $show_filters_link;?>
<?php   endif;?>
<?php   if (empty($settings['hide_nav_sorts']) && count($sorts) > 1):?>
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-directory'));?>
<?php   endif;?>
    </div>
    <div class="sabai-pull-right">
<?php if (!empty($links[1])):?>
        <?php echo $this->ButtonLinks($links[1], array('label' => true, 'tooltip' => false));?>
<?php endif;?>
    </div>
<?php   if (empty($settings['hide_nav_views'])):?>
    <div class="sabai-pull-right">
        <?php echo $this->ButtonLinks($views, 'sm', false, true);?>
    </div>
<?php   endif;?>
</div>
<?php endif;?>
<?php if (empty($settings['search']['filters_top'])):?>
<div class="sabai-row">
    <div class="sabai-directory-filters sabai-col-md-4" style="<?php echo $filters_style;?>">
<?php else:?>
    <div class="sabai-directory-filters" style="<?php echo $filters_style;?>">
<?php endif;?>
<?php if ($filter_form):?>
        <?php echo $this->Form_Render($filter_form);?>
<?php endif;?>
    </div>