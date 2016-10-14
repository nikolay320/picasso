<h2>
<?php if (!isset($CONTENT_TITLE)):?>
<?php   if ($_content_bc = array_pop($CONTENT_BREADCRUMBS)):?>
    <?php Sabai::_h($_content_bc['title']);?> 
<?php   endif;?>
<?php else:?>
    <?php Sabai::_h($CONTENT_TITLE);?> 
<?php endif;?>
<?php if ($CONTENT_MENU):?>
<?php   foreach ($CONTENT_MENU as $_CONTENT_MENU): $attr = empty($_CONTENT_MENU['class']) ? array('class' => 'add-new-h2') : array('class' => 'add-new-h2 ' . $_CONTENT_MENU['class']);?>
<?php     if (!empty($_CONTENT_MENU['ajax'])):?>
<?php       echo $this->LinkToRemote($_CONTENT_MENU['title'], $_CONTENT_MENU['ajax'] == 2 ? '#sabai-modal' : '#sabai-content', $_CONTENT_MENU['url'], $_CONTENT_MENU['options'], $attr);?>
<?php     else:?>
<?php       echo $this->LinkTo($_CONTENT_MENU['title'], $_CONTENT_MENU['url'], $_CONTENT_MENU['options'], $attr);?>
<?php     endif;?>
<?php   endforeach;?>
<?php endif;?>
</h2>
<?php if (!empty($TAB_CURRENT)):?>
<div id="sabai-nav" class="sabai-clearfix">
<?php   foreach (array_keys($TAB_CURRENT) as $_TAB_SET): $_TAB_CURRENT = $TAB_CURRENT[$_TAB_SET];?>
  <h2 class="nav-tab-wrapper">
<?php     foreach ($TABS[$_TAB_SET] as $_TAB_NAME => $_TAB): $attr = empty($_TAB['class']) ? array('class' => 'nav-tab') : array('class' => 'nav-tab ' . $_TAB['class']); if ($_TAB_NAME == $_TAB_CURRENT) $attr['class'] .= ' nav-tab-active';?>
      <?php echo $this->LinkTo($_TAB['title'], $_TAB['url'], $_TAB['options'], $attr);?>
<?php     endforeach;?>
  </h2>
<?php   endforeach;?>
<?php   if (!empty($TAB_BREADCRUMBS[$_TAB_SET]) && count($TAB_BREADCRUMBS[$_TAB_SET]) > 1): $_TAB_BREADCRUMB_LAST = array_pop($TAB_BREADCRUMBS[$_TAB_SET]);?>
  <div class="sabai-breadcrumbs sabai-tab-breadcrumbs">
<?php     foreach ($TAB_BREADCRUMBS[$_TAB_SET] as $_TAB_BREADCRUMB):?>
    <span><?php echo $this->LinkTo($_TAB_BREADCRUMB['title'], $_TAB_BREADCRUMB['url']);?></span>
    <span> &raquo; </span>
<?php     endforeach;?>
<?php Sabai::_h($_TAB_BREADCRUMB_LAST['title']);?>
  </div>
<?php   endif;?>
<?php   if (!empty($TAB_MENU[$_TAB_SET])):?>
  <ul class="sabai-tab-menu">
<?php     foreach ($TAB_MENU[$_TAB_SET] as $_TAB_MENU): $attr = array();?>
<?php       if (!empty($_TAB_MENU['ajax'])):?>
    <li><?php echo $this->LinkToRemote($_TAB_MENU['title'], $_TAB_MENU['ajax'] == 2 ? '#sabai-modal' : '#sabai-content', $_TAB_MENU['url'], array(), $attr);?></li>
<?php       else:?>
    <li><?php echo $this->LinkTo($_TAB_MENU['title'], $_TAB_MENU['url'], array(), $attr);?></li>
<?php       endif;?>
<?php     endforeach;?>
  </ul>
<?php   endif;?>
</div>
<?php endif;?>
<div id="sabai-body">
<?php echo $CONTENT;?>
<?php if (!empty($INLINE_TABS)):?>
  <div id="sabai-inline">
    <div id="sabai-inline-nav">
      <h2 class="nav-tab-wrapper">
<?php   foreach ($INLINE_TABS as $_INLINE_TAB_NAME => $_INLINE_TAB): $attr = empty($_INLINE_TAB['class']) ? array() : array('class' => $_INLINE_TAB['class']); if ($_INLINE_TAB_NAME == $INLINE_TAB_CURRENT) $attr['class'] .= ' nav-tab-active';?>
<?php     if (empty($_INLINE_TAB['ajax'])):?>
          <?php echo $this->LinkTo($_INLINE_TAB['title'], $_INLINE_TAB['url'], $_INLINE_TAB['options'], $attr);?>
<?php     else:?>
          <?php echo $this->LinkToRemote($_INLINE_TAB['title'], '#sabai-inline-content', $_INLINE_TAB['url'], array('url' => (string)$_INLINE_TAB['route'], 'content' => 'trigger.closest("ul").find("li.sabai-nav-selected").removeClass("sabai-nav-selected"); trigger.closest("li").addClass("sabai-nav-selected");') + $_INLINE_TAB['options'], $attr);?>
<?php     endif;?>
<?php   endforeach;?>
      </h2>
    </div>
    <div id="sabai-inline-content">
      <?php echo $this->ImportRoute('#sabai-inline-content', $INLINE_TABS[$INLINE_TAB_CURRENT]['route'], $CONTEXT);?>
    </div>
  </div>
<?php endif;?>
</div>
