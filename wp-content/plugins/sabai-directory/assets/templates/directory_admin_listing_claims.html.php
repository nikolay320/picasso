<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left">
        <div class="sabai-btn-group"><?php echo implode(PHP_EOL, $filters);?></div>
    </div>
    <div class="sabai-pull-right">
        <div class="sabai-btn-group"><?php echo implode(PHP_EOL, $links);?></div>
    </div>
</div>
<?php echo $this->Form_Render($form);?>
<?php if ($paginator && $paginator->count()):?>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
</div>
<?php endif;?>