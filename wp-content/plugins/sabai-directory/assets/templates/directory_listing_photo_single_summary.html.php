<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-box-shadow sabai-clearfix">
    <div class="sabai-directory-photo">
        <a href="<?php echo $this->Directory_PhotoUrl($entity);?>" rel="prettyPhoto[<?php echo $entity->getBundleType();?>]" title="<?php Sabai::_h($entity->getTitle());?>">
            <img src="<?php echo $this->Directory_PhotoUrl($entity, 'medium');?>" alt="" />
        </a>
    </div>
    <div class="sabai-directory-photo-title">
        <strong><?php echo $this->Entity_RenderTitle($entity, array('no_link' => true));?></strong>
        <span><?php echo $this->Directory_RenderPhotoMeta($entity, !empty($link_to_listing));?></span>
    </div>
    <div class="sabai-directory-photo-stats">
<?php if (!empty($entity->voting_helpful[0]['sum'])):?>
        <span><i class="fa fa-thumbs-up"></i> <?php echo $entity->voting_helpful[0]['sum'];?></span>
<?php endif;?>
<?php if (!empty($entity->data['comment_count'])):?>
        <span><i class="fa fa-comment"></i> <?php echo $entity->data['comment_count'];?></span>
<?php endif;?>
    </div>
<?php if (!empty($buttons) || !empty($links)):?>
    <div class="sabai-entity-links">
        <?php echo $this->ButtonLinks($buttons + $links);?>
    </div>
<?php endif;?>
</div>