<?php if (empty($settings['search']['filters_top'])):?>
</div>
<?php endif;?>
<?php if ($paginator && empty($settings['hide_pager'])):?>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php   if ($paginator->count() > 1):?>
    <div class="sabai-pull-left">
        <span><?php printf(__('Showing %d - %d of %s results', 'sabai-directory'), $paginator->getElementOffset() + 1, $paginator->getElementOffset() + $paginator->getElementLimit(), $this->NumberFormat($paginator->getElementCount()));?></span>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params), array('target' => '.sabai-directory-listings-container', 'scroll' => true));?>
    </div>
<?php   else:?>
    <div class="sabai-pull-left">
        <span><?php printf(_n('Showing %s result', 'Showing %s results', $paginator->getElementCount(), 'sabai-directory'), $this->NumberFormat($paginator->getElementCount()));?></span>
    </div>
<?php   endif;?>
</div>
<?php endif;?>