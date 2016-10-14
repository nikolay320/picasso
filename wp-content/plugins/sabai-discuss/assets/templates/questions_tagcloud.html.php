<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left">
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-discuss'));?>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->ButtonLinks($links, array('tooltip' => false, 'label' => true));?>
    </div>
</div>
<?php if (!empty($entities)):?>
<div style="clear:both;"></div>
<ul class="sabai-questions-taglist sabai-clearfix">
<?php   foreach ($entities as $entity):?>
  <li><?php echo $this->Questions_TagLink($entity);?></li>
<?php   endforeach;?>
</ul>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php   if ($paginator->count() > 1):?>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, array('sort' => $current_sort)));?>
    </div>
<?php   endif;?>
</div>
<?php endif;?>