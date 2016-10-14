<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix">
    <div class="sabai-questions-status">
        <?php echo $this->Entity_RenderLabels($entity);?>
    </div>
    <div class="sabai-row">
        <div class="sabai-col-xs-1 sabai-questions-side">
            <div class="sabai-questions-voting-updown">
                <?php echo $this->Voting_RenderUpdown($entity);?>
            </div>
            <div class="sabai-questions-voting-favorite">
                <?php echo $this->Voting_RenderFavorite($entity);?>
            </div>
        </div>
        <div class="sabai-col-xs-11 sabai-questions-main">
            <div class="sabai-questions-body">
                <?php echo $this->Entity_RenderField($entity, 'content_body');?>
            </div>
            <div class="sabai-questions-custom-fields">
                <?php $this->displayTemplate('questions_custom_fields', array('entity' => $entity));?>
            </div>
            <div class="sabai-row">
                <div class="sabai-col-sm-5 sabai-col-sm-offset-7 sabai-questions-activity">
                    <?php echo $this->Entity_RenderActivity($entity, array('action_label' => __('%s answered %s', 'sabai-discuss'), 'show_last_edited' => true, 'show_last_active' => false));?>
                </div>
            </div>
            <div class="sabai-questions-comments" id="<?php echo $id;?>-comments">
                <?php echo $this->Comment_RenderComments($entity, $id . '-comments');?>
            </div>
        </div>
    </div>
<?php if (!empty($buttons) || !empty($links)):?>
    <div class="sabai-entity-links">
        <?php echo $this->ButtonLinks($buttons + $links);?>
    </div>
<?php endif;?>
</div>