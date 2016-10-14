<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix">
    <div class="sabai-row">
        <div class="sabai-col-xs-2 sabai-questions-side">
            <div class="sabai-questions-vote-count">
                <?php printf(_n('%s vote', '%s votes', (int)$entity->voting_updown[0]['sum'], 'sabai-discuss'), '<span class="sabai-number">' . (int)$entity->voting_updown[0]['sum'] . '</span>');?>
            </div>
            <div class="sabai-questions-answer-count">
<?php if ($answer_count = (int)$entity->content_children_count[0]['questions_answers']):?>
                <a href="<?php echo $this->Entity_Url($entity, '', array(), 'sabai-inline-nav');?>"><?php printf(_n('%s answer', '%s answers', $answer_count, 'sabai-discuss'), '<span class="sabai-number">' . $answer_count . '</span>');?></a>
<?php else:?>
                <?php printf(_n('%s answer', '%s answers', $answer_count, 'sabai-discuss'), '<span class="sabai-number">' . $answer_count . '</span>');?>
<?php endif;?>
            </div>
            <div class="sabai-questions-view-count">
                <?php printf(_n('%s view', '%s views', (int)$entity->getViews(), 'sabai-discuss'), '<span class="sabai-number">' . $this->NumberFormat((int)$entity->getViews()) . '</span>');?>
            </div>
        </div>
        <div class="sabai-col-xs-10 sabai-questions-main">
            <div class="sabai-questions-title">
                <?php echo $this->Entity_RenderField($entity, 'content_post_title', 'summary');?>
            </div>
            <div class="sabai-questions-taxonomy">
<?php if ($entity->questions_categories):?>
                <?php echo $this->Entity_RenderField($entity, 'questions_categories', 'summary');?>
<?php endif;?>
<?php if ($entity->questions_tags):?>
                <?php echo $this->Entity_RenderField($entity, 'questions_tags', 'summary');?>
<?php endif;?>
            </div>
            <div class="sabai-questions-body">
                <?php echo $this->Entity_RenderField($entity, 'content_body', 'summary');?>
            </div>
            <div class="sabai-questions-activity sabai-questions-activity-inline">
                <?php echo $this->Entity_RenderActivity($entity, array('action_label' => __('%s asked %s', 'sabai-discuss'), 'permalink' => false));?>
            </div>
            <div class="sabai-questions-flags">
                <strong><?php printf(_n('%d flag (spam score: %d)', '%d flags (spam score: %d)', $entity->voting_flag[0]['count'], 'sabai-discuss'), $entity->voting_flag[0]['count'], $entity->voting_flag[0]['sum']);?></strong>
                <?php echo $this->Voting_RenderFlags($entity);?>
            </div>
        </div>
    </div>
<?php if (!empty($buttons) || !empty($links)):?>
    <div class="sabai-entity-links">
        <?php echo $this->ButtonLinks($buttons + $links);?>
    </div>
<?php endif;?>
</div>