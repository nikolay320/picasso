<?php if ($count = count($entities)): $categories = $this->SliceArray($entities, $column_count);?>
<div class="sabai-directory-categories" style="clear:both;">
    <div class="sabai-row">
<?php   foreach ($categories as $row => $columns):?>
        <div class="sabai-col-md-<?php echo intval(12 / $column_count);?>">
<?php     foreach ($columns as $entity): $count = @$entity['entity']->data['content_count']['directory_listing'];?>
<?php       if (!$count && !empty($hide_empty)) continue;?>
            <div class="sabai-directory-category">
                <div class="sabai-directory-category-title">
<?php       if (empty($hide_count) && $count):?>
                    <?php printf(__('%s (%d)', 'sabai-directory'), $this->Entity_Permalink($entity['entity'], array('thumbnail' => 'directory_thumbnail')), $count);?>
<?php       else:?>
                    <?php echo $this->Entity_Permalink($entity['entity'], array('thumbnail' => 'directory_thumbnail'));?>
<?php       endif;?>
                </div>
<?php       if (empty($hide_children) && !empty($entity['entity']->data['child_terms'])):?>
<?php         $child_terms = empty($child_count) ? $entity['entity']->data['child_terms'] : array_slice($entity['entity']->data['child_terms'], 0, $child_count, true);?>
                <ul class="sabai-directory-category-children">
<?php         foreach ($child_terms as $child_term): $count = $child_term->data['content_count']['directory_listing'];?>                 
<?php           if (empty($hide_count) && $count):?>
                    <li><?php printf(__('%s (%d)', 'sabai-directory'), $this->Entity_Permalink($child_term, array('thumbnail' => 'directory_thumbnail')), $count);?></li>
<?php           else:?>
<?php             if (empty($hide_empty)):?>
                    <li><?php echo $this->Entity_Permalink($child_term, array('thumbnail' => 'directory_thumbnail'));?></li>
<?php             endif;?>
<?php           endif;?>               
<?php         endforeach;?>
                </ul>
<?php       endif;?>
            </div>
<?php     endforeach;?>
        </div>
<?php   endforeach;?>
    </div>
</div>
<?php endif;?>
