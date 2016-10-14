<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left">
<?php   if (!empty($links[0])):?>
        <?php echo $this->DropdownButtonLinks($links[0]);?>
<?php   endif;?>
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-discuss'));?>
    </div>
    <div class="sabai-pull-right">
<?php   if (!empty($links[1])):?>
        <?php echo $this->ButtonLinks($links[1], array('label' => true, 'tooltip' => false));?>
<?php   endif;?>
    </div>
</div>
<?php if (!empty($entities)):?>
<div class="sabai-questions-favorites" style="clear:both;">
<?php   foreach ($entities as $entity):?>
    <?php $this->displayTemplate($entity['entity']->getBundleType() . '_single_favorited', $entity);?>
<?php   endforeach;?>
</div>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php   if ($paginator->count() > 1):?>
    <div class="sabai-pull-left">
        <?php printf(__('Showing %d - %d of %s results', 'sabai-discuss'), $paginator->getElementOffset() + 1, $paginator->getElementOffset() + $paginator->getElementLimit(), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php   else:?>
    <div class="sabai-pull-left">
        <?php printf(_n('Showing %s result', 'Showing %s results', $paginator->getElementCount(), 'sabai-discuss'), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
<?php   endif;?>
</div>
<?php endif;?>