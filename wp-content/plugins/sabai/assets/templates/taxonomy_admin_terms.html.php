<div class="sabai-navigation sabai-clearfix">
<?php if (!empty($links)):?>
  <div class="sabai-pull-right">
      <div class="sabai-btn-group"><?php echo implode(PHP_EOL, $links);?></div>
  </div>
<?php endif;?>
</div>
<?php echo $this->Form_Render($form);?>
<?php if ($pager && $pager->count()):?>
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $pager, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
</div>
<?php endif;?>