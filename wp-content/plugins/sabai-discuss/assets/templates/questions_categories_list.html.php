<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left">
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-discuss'));?>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->ButtonLinks($links, array('tooltip' => false, 'label' => true));?>
    </div>
</div>
<?php if ($count = count($entities)): $categories = $this->SliceArray($entities, 2);?>
<div class="sabai-questions-categories" style="clear:both;">
    <div class="sabai-row">
<?php   foreach ($categories as $row => $columns):?>
        <div class="sabai-col-md-6">
<?php     foreach ($columns as $entity):?>
            <div class="sabai-questions-category">
                <div class="sabai-questions-category-title">
<?php       if (!empty($entity['entity']->data['content_count']['questions'])):?>
                    <?php printf(__('%s (%d)', 'sabai-discuss'), $this->Entity_Permalink($entity['entity']), $entity['entity']->data['content_count']['questions']);?>
<?php       else:?>
                    <?php echo $this->Entity_Permalink($entity['entity']);?>
<?php       endif;?>
                </div>
<?php       if (!empty($entity['entity']->data['child_terms'])):?>
                <ul class="sabai-questions-category-children">
<?php         foreach ($entity['entity']->data['child_terms'] as $child_term):?>
                    <li>
<?php           if (!empty($child_term->data['content_count']['questions'])):?>
                        <?php printf(__('%s (%d)', 'sabai-discuss'), $this->Entity_Permalink($child_term), $child_term->data['content_count']['questions']);?>
<?php           else:?>
                        <?php echo $this->Entity_Permalink($child_term);?>
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
<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php   if ($paginator->count() > 1):?>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php   endif;?>
</div>
<?php endif;?>