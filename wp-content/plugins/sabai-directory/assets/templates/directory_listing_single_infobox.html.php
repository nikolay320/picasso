<?php 
$has_photos = $entity->directory_photos && ($photos = $this->Entity_RenderField($entity, 'directory_photos', 'map'));
$distance = isset($entity->data['distance']) ? (array)$entity->data['distance'] : null;
?>
<div class="sabai-directory-listing-infobox sabai-clearfix<?php if (!$has_photos):?> sabai-directory-no-image<?php endif;?>">
<?php if ($has_photos):?>
    <div class="sabai-directory-images">
        <?php echo $photos;?>
    </div>
<?php endif;?>
    <div class="sabai-directory-main">
        <div class="sabai-directory-title">
            <?php echo $this->Entity_RenderField($entity, 'content_post_title', 'map');?>
        </div>
<?php if (!empty($entity->voting_rating['']['count'])):?>
        <div class="sabai-directory-rating">
            <?php echo $this->Entity_RenderField($entity, 'voting_rating', array('link' => $this->Entity_Url($entity, '/reviews'), 'count_formats' => array(__('%s review', 'sabai-directory'), __('%s reviews', 'sabai-directory'))));?>
        </div>
<?php endif;?>
<?php if ($entity->directory_category && ($categories = $this->Entity_RenderField($entity, 'directory_category', 'map'))):?>
        <div class="sabai-directory-category">
            <?php echo $categories;?>
        </div>
<?php endif;?>
        <div class="sabai-directory-info sabai-clearfix">
            <div class="sabai-directory-location">
<?php if ($distance && isset($address_weight)):?>
<?php   foreach ((array)$address_weight as $key):?>
                <?php echo $this->Entity_RenderField($entity, 'directory_location', 'map', $key);?> <span class="sabai-directory-distance"><?php printf($is_mile ? __('%s mi', 'sabai-directory') : __('%s km', 'sabai-directory'), round($distance[$key], 2));?></span><br />
<?php   endforeach;?>
<?php else:?>
                <?php echo $this->Entity_RenderField($entity, 'directory_location', 'map', isset($address_weight) ? $address_weight : null);?>
<?php endif;?>
            </div>
            <div class="sabai-directory-contact">
                <?php echo $this->Entity_RenderField($entity, 'directory_contact', 'map');?>
            </div>
            <div class="sabai-directory-social">
                <?php echo $this->Entity_RenderField($entity, 'directory_social', 'map');?>
            </div>
        </div>
<?php if ($listing_body = $this->Entity_RenderField($entity, 'content_body', 'map')):?>
        <div class="sabai-directory-body">
            <?php echo $listing_body;?>
        </div>
<?php endif;?>
        <div class="sabai-directory-custom-fields">
            <?php $this->displayTemplate('directory_custom_fields', array('entity' => $entity, 'view' => 'map'));?>
        </div>
    </div>
</div>
