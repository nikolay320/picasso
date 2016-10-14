<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix">
    <div class="sabai-row">
        <div class="sabai-col-xs-2 sabai-questions-side">
            <div class="sabai-questions-vote-count">
                <?php printf(_n('%s vote', '%s votes', (int)$entity->voting_updown[0]['sum'], 'sabai-discuss'), '<span class="sabai-number">' . (int)$entity->voting_updown[0]['sum'] . '</span>');?>
            </div>
        </div>
        <div class="sabai-col-xs-10 sabai-questions-main">
            <div class="sabai-questions-title">
                <?php echo $this->Entity_RenderTitle($entity, array('alt' => $this->Content_ParentPost($entity)->getTitle(), 'format' => __('In reply to: %s', 'sabai-discuss')));?>
            </div>
            <div class="sabai-questions-body">
                <?php echo $this->Entity_RenderField($entity, 'content_body', 'summary');?>
            </div>
            <div class="sabai-questions-activity sabai-questions-activity-inline">
                <?php echo $this->Entity_RenderActivity($entity, array('action_label' => __('%s answered %s', 'sabai-discuss'), 'permalink' => false));?>
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