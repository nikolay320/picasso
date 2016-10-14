<div class="sabai-navigation sabai-clearfix">
    <div class="sabai-pull-left sabai-btn-group">
<?php   if (!empty($links[0])):?>
        <?php echo $this->DropdownButtonLinks($links[0]);?>
<?php   endif;?>
<?php   if ($show_filters_link):?>
        <?php echo $show_filters_link;?>
<?php   endif;?>
        <?php echo $this->DropdownButtonLinks($sorts, 'sm', __('Sort by: <strong>%s</strong>', 'sabai-discuss'));?>
    </div>
    <div class="sabai-pull-right">
        <div class="sabai-btn-group">
<?php if (!$entity->questions_closed[0]):?>
<?php   if ($this->HasPermission($child_bundle->name . '_add')):?>
<?php     if (!$IS_AJAX):?>
            <a href="#sabai-questions-<?php echo $entity->getId();?>-add-answer" class="sabai-btn sabai-btn-sm sabai-btn-primary sabai-questions-btn-answer" onclick="SABAI.scrollTo('#sabai-questions-<?php echo $entity->getId();?>-add-answer', 500); jQuery('#sabai-questions-<?php echo $entity->getId();?>-add-answer form').focusFirstInput(); return false;"><i class="fa fa-pencil"></i> <?php Sabai::_h(__('Post Answer', 'sabai-discuss'));?></a>
<?php     else:?>
<?php       echo $this->LinkTo(__('Post Answer', 'sabai-discuss'), $this->Entity_Url($entity, '/answers/add'), array('icon' => 'pencil'), array('class' => 'sabai-btn sabai-btn-sm sabai-btn-primary sabai-questions-btn-answer'));?>
<?php     endif;?>
<?php   elseif ($CURRENT_USER->isAnonymous()):?>
            <a href="<?php echo $this->LoginUrl($this->Entity_Url($entity, '/answers', array('__fragment' => 'sabai-questions-' . $entity->getId() . '-add-answer')));?>" class="sabai-btn sabai-btn-sm sabai-btn-primary sabai-login popup-login"><i class="fa fa-pencil"></i> <?php echo Sabai::_h(__('Post Answer', 'sabai-discuss'));?></a>
<?php   endif;?>
<?php endif;?>
        </div>
    </div>
</div>
<div class="sabai-questions-filters sabai-questions-answers-filters"<?php if (!$filter_form || !$show_filters):?> style="display:none;"<?php endif;?>>
<?php if ($filter_form):?>
    <?php echo $this->Form_Render($filter_form);?>
<?php endif;?>
</div>

<div class="sabai-questions-answers">
<?php foreach ($entities as $answer):?>
<?php   $this->displayTemplate('questions_answers_single_full', $answer);?>
<?php endforeach;?>
</div>

<div class="sabai-navigation sabai-navigation-bottom sabai-clearfix">
<?php   if ($paginator->count() > 1):?>
    <div class="sabai-pull-left">
        <?php printf(__('Showing %d - %d of %s results', 'sabai-discuss'), $paginator->getElementOffset() + 1, $paginator->getElementOffset() + $paginator->getElementLimit(), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
    <div class="sabai-pull-right">
        <?php echo $this->PageNav($CURRENT_CONTAINER, $paginator, $this->Url($CURRENT_ROUTE, $url_params));?>
    </div>
<?php   else:?>
    <div class="sabai-pull-left">
        <?php printf(_n('Showing %s result', 'Showing %s results', $paginator->getElementCount(), 'sabai-discuss'), $this->NumberFormat($paginator->getElementCount()));?>
    </div>
<?php   endif;?>
</div>

<?php if (!$entity->questions_closed[0] && !$IS_AJAX):?>
<div class="sabai-questions-add-answer-form" id="sabai-questions-<?php echo $entity->getId();?>-add-answer">
<?php   if ($answer_form):?>
    <strong><?php echo __('Your Answer', 'sabai-discuss');?></strong>
    <?php echo $this->Form_Render($answer_form);?>
<?php   elseif ($CURRENT_USER->isAnonymous()):?>
    <strong><?php echo __('Your Answer', 'sabai-discuss');?></strong>
    <p><?php printf(__('Please <a href="%s" class="sabai-login popup-login">login</a> first to submit.', 'sabai-discuss'), $this->LoginUrl($this->Entity_Url($entity, '/answers', array('__fragment' => 'sabai-questions-' . $entity->getId() . '-add-answer'))));?></p>
<?php   endif;?>
</div>
<?php endif;?>