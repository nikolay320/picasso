<?php if ($count = count($entities)): $categories = $this->SliceArray($entities, $column_count);?>
<div class="sabai-directory-categories" style="clear:both;">
    <div class="sabai-row">
<?php   foreach ($categories as $row => $columns):?>
        <div class="sabai-col-md-<?php echo intval(12 / $column_count);?>">
<?php     foreach ($columns as $entity):?>
            <div class="sabai-questions-category">
                <div class="sabai-questions-category-title">
<?php       if (!empty($entity['entity']->data['content_count']['questions_questions'])):?>
                    <?php printf(__('%s (%d)', 'sabai-discuss'), $this->Entity_Permalink($entity['entity'], array('thumbnail' => 'questions_thumbnail')), $entity['entity']->data['content_count']['questions_questions']);?>
<?php       else:?>
                    <?php echo $this->Entity_Permalink($entity['entity'], array('thumbnail' => 'questions_thumbnail'));?>
<?php       endif;?>
                </div>
<?php       if (empty($hide_children) && !empty($entity['entity']->data['child_terms'])):?>
                <ul class="sabai-questions-category-children">
<?php         foreach ($entity['entity']->data['child_terms'] as $child_term):?>
                    <li>
<?php           if (!empty($child_term->data['content_count']['questions_questions'])):?>
                        <?php printf(__('%s (%d)', 'sabai-discuss'), $this->Entity_Permalink($child_term, array('thumbnail' => 'questions_thumbnail')), $child_term->data['content_count']['questions_questions']);?>
<?php           else:?>
                        <?php echo $this->Entity_Permalink($child_term, array('thumbnail' => 'questions_thumbnail'));?>
<?php           endif;?>               
                    </li>
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