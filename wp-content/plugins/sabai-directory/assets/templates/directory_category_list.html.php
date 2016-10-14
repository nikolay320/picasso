<?php if (!$IS_EMBED) $this->Action('directory_before_categories', array($bundle->addon));?>
<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left">
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-directory'));?>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->ButtonLinks($links, 'sm', true, true);?>
    </div>
</div>
<?php if (!empty($entities)): $config = $this->Config($bundle->addon, 'display');?>
<?php   $this->displayTemplate(
            'directory_categories',
            array(
                'entities' => $entities,
                'column_count' => (int)@$config['category_columns'],
                'hide_empty' => !empty($config['category_hide_empty']),
                'hide_children' => !empty($config['category_hide_children']),
                'hide_count' => !empty($config['category_hide_count']),
                'child_count' => (int)@$config['category_child_count'],
            ));?>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php   if ($paginator->count() > 1):?>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php   endif;?>
</div>
<?php endif;?>
<?php if (!$IS_EMBED) $this->Action('directory_after_categories', array($bundle->addon));?>
