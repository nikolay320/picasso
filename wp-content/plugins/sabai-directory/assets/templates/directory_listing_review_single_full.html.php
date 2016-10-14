<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix" itemprop="review" itemscope itemtype="http://schema.org/Review">
    <span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">
        <meta itemprop="name" content="<?php Sabai::_h($parent_entity->getTitle());?>" />
        <link itemprop="url" href="<?php echo $this->Entity_Url($parent_entity);?>" />
    </span>
    <meta itemprop="datePublished" content="<?php echo date('Y-m-d', $entity->getTimestamp());?>" />
    <meta itemprop="author" content="<?php Sabai::_h($entity->getAuthor()->name);?>" />
    <div class="sabai-directory-info">
<?php if (!empty($entity->voting_helpful[0])):?>
        <div class="sabai-directory-review-helpful-count"><?php printf(__('%d of %d people found the following review helpful', 'sabai-directory'), $entity->voting_helpful[0]['sum'], $entity->voting_helpful[0]['count']);?></div>
<?php endif;?>
        <div class="sabai-directory-review-title" itemprop="name">
            <?php echo $this->Entity_RenderField($entity, 'content_post_title');?>
        </div>
        <div class="sabai-directory-review-rating" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
            <meta itemprop="worstRating" content="0" />
            <meta itemprop="bestRating" content="5" />
            <?php echo $this->Entity_RenderField($entity, 'directory_rating');?>
        </div>
        <div class="sabai-directory-activity sabai-directory-activity-inline">
            <?php echo $this->Entity_RenderActivity($entity, array('action_label' => __('%s reviewed %s', 'sabai-directory'), 'permalink' => true, 'show_last_active' => false, 'show_last_edited' => true));?>
        </div>
    </div>
<?php if ($entity->directory_photos):?>
    <div class="sabai-directory-review-photos">
        <?php echo $this->Entity_RenderField($entity, 'directory_photos');?>
    </div>
<?php endif;?>
    <div class="sabai-directory-body" itemprop="reviewBody">
        <?php echo $this->Entity_RenderField($entity, 'content_body');?>
    </div>
    <div class="sabai-directory-custom-fields">
        <?php $this->displayTemplate(array('directory_listing_review_custom_fields', 'directory_custom_fields'), array('entity' => $entity));?>
    </div>
    <div class="sabai-directory-review-helpful-yesno">
        <?php echo $this->Voting_RenderYesno($entity, '.sabai-directory-review-helpful-yesno', array('format' => __('<span>Was this review helpful to you?</span> %s %s', 'sabai-directory')));?>
    </div>
<?php if (!empty($buttons) || !empty($links)):?>
    <div class="sabai-entity-links">
        <?php echo $this->ButtonLinks($buttons + $links);?>
    </div>
<?php endif;?>
    <div class="sabai-directory-comments" id="<?php echo $id;?>-comments">
        <?php echo $this->Comment_RenderComments($entity, $id . '-comments');?>
    </div>
</div>