<div id="<?php echo $id;?>" class="<?php echo $class;?>">
    <div class="sabai-row">
        <div class="sabai-col-sm-3 sabai-directory-listing">
<?php if ($listing = $this->Content_ParentPost($entity)):?>
<?php   if (!empty($entity->data['directory_listing_photos'])): $photo = $entity->data['directory_listing_photos'][0];?>
            <?php echo $this->File_ThumbnailLink($listing, $photo->file_image[0], array('link_entity' => true));?>
<?php   else:?>
            <img src="<?php echo $this->NoImageUrl(true);?>" alt="" />
<?php   endif;?>
            <div><?php echo $this->Entity_Permalink($listing);?></div>
<?php   if (!empty($listing->voting_rating['']['count'])):?>
            <div class="sabai-directory-rating">
                <?php echo $this->Entity_RenderField($entity, 'voting_rating', array('hide_average' => true));?>
            </div>
<?php   endif;?>
<?php endif;?>
        </div>
        <div class="sabai-col-sm-9 sabai-directory-main">
            <div class="sabai-directory-info">
<?php if (!empty($entity->voting_helpful[0])):?>
                <div class="sabai-directory-review-helpful-count"><?php printf(__('%d of %d people found the following review helpful', 'sabai-directory'), $entity->voting_helpful[0]['sum'], $entity->voting_helpful[0]['count']);?></div>
<?php endif;?>
                <div class="sabai-directory-review-title">
                    <?php echo $this->Entity_RenderField($entity, 'content_post_title', 'summary');?>
                </div>
                <div class="sabai-directory-review-rating">
                    <?php echo $this->Entity_RenderField($entity, 'directory_rating', 'summary');?>
                </div>
                <div class="sabai-directory-activity sabai-directory-activity-inline">
                    <?php echo $this->Entity_RenderActivity($entity, array('action_label' => __('%s reviewed %s', 'sabai-directory'), 'permalink' => false, 'show_last_active' => false, 'show_last_edited' => true));?>
                </div>
            </div>
            <div class="sabai-directory-body">
                <?php echo $this->Entity_RenderField($entity, 'content_body', 'summary');?>
            </div>
        </div>
<?php if (!empty($buttons) || !empty($links)):?>
        <div class="sabai-entity-links">
            <?php echo $this->ButtonLinks($buttons + $links);?>
        </div>
<?php endif;?>
    </div>
</div>