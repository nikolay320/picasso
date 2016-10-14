<ul class="sabai-wordpress-widget-entries">
<?php   foreach ($content as $entry):?>
<?php     if (is_array($entry)):?>
<?php       if (isset($entry['image'])):?>
  <li class="sabai-wordpress-widget-entry-with-image"><div class="sabai-wordpress-widget-image"><?php echo $entry['image'];?></div>
<?php       else:?>
  <li>
<?php       endif;?>
    <div class="sabai-wordpress-widget-main">
<?php       if (isset($entry['title_html'])):?>
      <?php echo $entry['title_html'];?>
<?php       else:?>
      <a title="<?php Sabai::_h($entry['title']);?>" href="<?php echo $this->Url($entry['url']);?>"><?php Sabai::_h($entry['title']);?></a>
<?php       endif;?>
<?php       if (isset($entry['summary'])):?>
      <p><?php Sabai::_h($entry['summary']);?></p>
<?php       endif;?>
<?php       if (!empty($entry['meta'])):?>
      <ul><li><?php echo implode('</li><li>', $entry['meta']);?></li></ul>
<?php       endif;?>
    </div>
<?php     else:?>
    <?php echo $entry;?>
<?php     endif;?>
  </li>
<?php   endforeach;?>
</ul>
<?php if (isset($link)):?>
<a class="sabai-wordpress-widget-link" href="<?php echo $link['url'];?>"><?php Sabai::_h($link['title']);?></a>
<?php endif; ?>
