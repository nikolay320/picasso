<?php $this->Action('before_' . $CONTEXT_NAME, array($CONTEXT));?>
<?php if ($CONTENT_MENU):?>
<ul class="sabai-page-menu">
<?php   foreach ($CONTENT_MENU as $_CONTENT_MENU):?>
<?php     $attr = empty($_CONTENT_MENU['class']) ? array() : array('class' => $_CONTENT_MENU['class']);?>
<?php     if (!empty($_CONTENT_MENU['ajax'])):?>
  <li><?php echo $this->LinkToRemote($_CONTENT_MENU['title'], $_CONTENT_MENU['ajax'] == 2 ? '#sabai-modal' : '#sabai-content', $_CONTENT_MENU['url'], $_CONTENT_MENU['options'], $attr);?></li>
<?php     else:?>
  <li><?php echo $this->LinkTo($_CONTENT_MENU['title'], $_CONTENT_MENU['url'], $_CONTENT_MENU['options'], $attr);?></li>
<?php     endif;?>
<?php   endforeach;?>
</ul>
<?php endif;?>
<?php if (!empty($TAB_CURRENT)):?>
<div id="sabai-nav" class="sabai-clearfix">
<?php   foreach (array_keys($TAB_CURRENT) as $_TAB_SET): $_TAB_CURRENT = $TAB_CURRENT[$_TAB_SET];?>
  <ul class="sabai-nav sabai-nav-tabs sabai-nav-justified">
<?php     foreach ($TABS[$_TAB_SET] as $_TAB_NAME => $_TAB): $attr = empty($_TAB['class']) ? array() : array('class' => $_TAB['class']);?>
	<li class="<?php if (!empty($_TAB['featured'])):?>sabai-pull-right<?php endif;?><?php if (!empty($_TAB['disabled'])):?> sabai-disabled<?php endif;?><?php if ($_TAB_NAME == $_TAB_CURRENT):?> sabai-active<?php endif;?>">
<?php       if (empty($_TAB['ajax'])):?>
      <?php echo $this->LinkTo($_TAB['title'], $_TAB['url'], $_TAB['options'], $attr);?>
<?php       else:?>
      <?php echo $this->LinkToRemote($_TAB['title'], '#sabai-content', $_TAB['url'], $_TAB['options'], $attr);?>
<?php       endif;?>
    </li>
<?php     endforeach;?>
  </ul>
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
<?php     foreach ($TAB_MENU[$_TAB_SET] as $_TAB_MENU): $attr = empty($_TAB_MENU['class']) ? array() : array('class' => $_TAB_MENU['class']);?>
<?php       if (!empty($_TAB_MENU['ajax'])):?>
    <li><?php echo $this->LinkToRemote($_TAB_MENU['title'], $_TAB_MENU['ajax'] == 2 ? '#sabai-modal' : '#sabai-content', $_TAB_MENU['url'], $_TAB_MENU['options'], $attr);?></li>
<?php       else:?>
    <li><?php echo $this->LinkTo($_TAB_MENU['title'], $_TAB_MENU['url'], $_TAB_MENU['options'], $attr);?></li>
<?php       endif;?>
<?php     endforeach;?>
  </ul>
<?php   endif;?>
</div>
<?php endif;?>
<div id="sabai-body">
<?php echo $CONTENT;?>
</div>
<?php if (!empty($INLINE_TABS)):?>
<div id="sabai-inline">
  <div id="sabai-inline-nav">
    <ul class="sabai-nav sabai-nav-tabs<?php if (count($INLINE_TABS) > 1):?> sabai-nav-justified<?php endif;?>">
<?php   foreach ($INLINE_TABS as $_INLINE_TAB_NAME => $_INLINE_TAB):?>
      <li class="<?php if (!empty($_INLINE_TAB['featured'])):?>sabai-pull-right<?php endif;?><?php if (!empty($_INLINE_TAB['disabled'])):?> sabai-disabled<?php endif;?><?php if ($_INLINE_TAB_NAME == $INLINE_TAB_CURRENT):?> sabai-active<?php endif;?>">
<?php     if ($_INLINE_TAB['hide_empty'] && !isset($_INLINE_TAB['content'])):?>
        <?php echo $this->LinkTo($_INLINE_TAB['title'], $_INLINE_TAB['url'], $_INLINE_TAB['options'], isset($_INLINE_TAB['class']) ? array('class' => $_INLINE_TAB['class']) : array());?>
<?php     else:?>
        <a href="#" id="sabai-inline-content-<?php echo $_INLINE_TAB_NAME;?>-trigger" data-toggle="tab" data-target="#sabai-inline-content-<?php echo $_INLINE_TAB_NAME;?>"<?php if (isset($_INLINE_TAB['class'])):?> class="<?php echo $_INLINE_TAB['class'];?>"<?php endif;?>><?php Sabai::_h($_INLINE_TAB['title']);?></a>
<?php     endif;?>
      </li>
<?php   endforeach;?>
    </ul>
  </div>
  <div class="sabai-tab-content" id="sabai-inline-content">
<?php foreach ($INLINE_TABS as $_INLINE_TAB_NAME => $_INLINE_TAB):?>
    <div class="sabai-tab-pane<?php if ($_INLINE_TAB_NAME == $INLINE_TAB_CURRENT):?> sabai-active<?php endif;?>" id="sabai-inline-content-<?php echo $_INLINE_TAB_NAME;?>">
      <?php echo isset($_INLINE_TAB['content']) ? $_INLINE_TAB['content'] : $this->ImportRoute('#sabai-inline-content-' . $_INLINE_TAB_NAME, $_INLINE_TAB['route'], $CONTEXT);?>
    </div>
<?php endforeach;?>
  </div>
</div>
<?php endif;?>
<?php $this->Action('after_' . $CONTEXT_NAME, array($CONTEXT));?>