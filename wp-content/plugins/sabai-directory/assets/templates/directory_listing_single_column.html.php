<?php 
$has_photos = $entity->directory_photos && ($photos = $this->Entity_RenderField($entity, 'directory_photos', 'grid'));
$distance = isset($entity->data['distance']) ? (array)$entity->data['distance'] : null;
?>
<?php if ($span):?>
<div class="sabai-col-md-<?php echo $span;?> sabai-col-xs-6">
<?php endif;?>
<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-directory-listing-column<?php if (!$span):?> sabai-box-shadow<?php endif;?> sabai-clearfix<?php if (!$has_photos):?> sabai-directory-no-image<?php endif;?>">
    <div class="sabai-directory-images">
<?php if ($has_photos):?>
        <?php echo $photos;?>
<?php else:?>
        <img src="<?php echo $this->NoImageUrl();?>" alt="" />
<?php endif;?>
    </div>
    <div class="sabai-directory-main">
        <div class="sabai-directory-title">
            <?php echo $this->Entity_RenderField($entity, 'content_post_title', 'grid');?>
        </div>
<?php if (!empty($entity->voting_rating['']['count'])):?>
        <div class="sabai-directory-rating">
            <?php echo $this->Entity_RenderField($entity, 'voting_rating', array('link' => $this->Entity_Url($entity, '/reviews'), 'count_formats' => array(__('%s review', 'sabai-directory'), __('%s reviews', 'sabai-directory'))));?>
        </div>
<?php endif;?>
<?php if ($entity->directory_category && ($categories = $this->Entity_RenderField($entity, 'directory_category', 'grid'))):?>
        <div class="sabai-directory-category">
            <?php echo $categories;?>
        </div>
<?php endif;?>
        <div class="sabai-directory-info sabai-clearfix">
            <div class="sabai-directory-location">
<?php if ($distance):?>
<?php   foreach ((array)$address_weight as $key):?>
                <?php echo $this->Entity_RenderField($entity, 'directory_location', 'grid', $key);?> <span class="sabai-directory-distance"><?php printf($is_mile ? __('%s mi', 'sabai-directory') : __('%s km', 'sabai-directory'), round($distance[$key], 2));?></span><br />
<?php   endforeach;?>
<?php else:?>
                <?php echo $this->Entity_RenderField($entity, 'directory_location', 'grid', isset($address_weight) ? $address_weight : null);?>
<?php endif;?>
            </div>
            <div class="sabai-directory-contact">
                <?php echo $this->Entity_RenderField($entity, 'directory_contact', 'grid');?>
            </div>
            <div class="sabai-directory-social">
                <?php echo $this->Entity_RenderField($entity, 'directory_social', 'grid');?>
            </div>
        </div>
<?php if ($listing_body = $this->Entity_RenderField($entity, 'content_body', 'grid')):?>
        <div class="sabai-directory-body">
            <?php echo $listing_body;?>
        </div>
<?php endif;?>
        <div class="sabai-directory-custom-fields">
            <?php $this->displayTemplate('directory_custom_fields', array('entity' => $entity, 'view' => 'grid'));?>
        </div>
    </div>
<?php if (!empty($buttons) || !empty($links)):?>
    <div class="sabai-entity-links">
        <?php echo $this->ButtonLinks($buttons + $links);?>
    </div>
<?php endif;?>
</div>
<?php if ($span):?>
</div>
<?php endif; ?>
