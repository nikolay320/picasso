<?php
foreach ($filters as $key => $_filter) {
    if (!is_array($_filter)) {
        $_filter = array('label' => $_filter);
        $attr = array();
    } else {
        $attr = isset($_filter['title']) ? array('title' => $_filter['title']) : array();
    }
    if ($key === $filter) {
        $attr['class'] = 'sabai-btn sabai-btn-default sabai-btn-xs sabai-active';
    } else {
        $attr['class'] = 'sabai-btn sabai-btn-default sabai-btn-xs';
    }     
    $filters[$key] = $this->LinkToRemote($_filter['label'], $CURRENT_CONTAINER, $this->Url($CURRENT_ROUTE, array('filter' => $key) + $url_params), array(), $attr);
}
?>
<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left">
        <div class="sabai-btn-group"><?php echo implode(PHP_EOL, $filters);?></div>
    </div>
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