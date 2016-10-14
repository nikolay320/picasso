<?php 
$has_photos = $entity->directory_photos && ($photos = $this->Entity_RenderField($entity, 'directory_photos', 'summary'));
$distance = isset($entity->data['distance']) ? (array)$entity->data['distance'] : null;
?>
<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix<?php if (!$has_photos):?> sabai-directory-no-image<?php endif;?>">
    <div class="sabai-row">
        <div class="sabai-col-xs-3 sabai-directory-images">
<?php if ($has_photos):?>
            <?php echo $photos;?>
<?php else:?>
            <img src="<?php echo $this->NoImageUrl();?>" alt="" />
<?php endif;?>
        </div>
        <div class="sabai-col-xs-9 sabai-directory-main">
            <div class="sabai-directory-title">
                <?php echo $this->Entity_RenderField($entity, 'content_post_title', 'summary');?><?php if (isset($address_weight) && ($address_count = count((array)$address_weight)) > 1):?> <span class="sabai-directory-location-count"><?php printf(__('(%d)', 'sabai-directory'), $address_count);?></span><?php endif;?>
            </div>
<?php if (!empty($entity->voting_rating['']['count'])):?>
            <div class="sabai-directory-rating">
                <?php echo $this->Entity_RenderField($entity, 'voting_rating', array('link' => $this->Entity_Url($entity, '/reviews'), 'count_formats' => array(sprintf(__('%s review', 'sabai-directory'), '%d'), sprintf(__('%s reviews', 'sabai-directory'), '%d'))));?>
            </div>
<?php endif;?>
<?php if ($entity->directory_category && ($categories = $this->Entity_RenderField($entity, 'directory_category', 'summary'))):?>
            <div class="sabai-directory-category">
                <?php echo $categories;?>
            </div>
<?php endif;?>
            <div class="sabai-directory-info sabai-clearfix">
                <div class="sabai-directory-location">
<?php if ($distance):?>
<?php   foreach ((array)$address_weight as $key):?>
                    <?php echo $this->Entity_RenderField($entity, 'directory_location', 'summary', $key);?> <span class="sabai-directory-distance"><?php printf($is_mile ? __('%s mi', 'sabai-directory') : __('%s km', 'sabai-directory'), round($distance[$key], 2));?></span><br />
<?php   endforeach;?>
<?php else:?>
                    <?php echo $this->Entity_RenderField($entity, 'directory_location', 'summary', isset($address_weight) ? $address_weight : null);?>
<?php endif;?>
                    
                </div>
                <div class="sabai-directory-contact">
                    <?php echo $this->Entity_RenderField($entity, 'directory_contact', 'summary');?>
                </div>
                <div class="sabai-directory-social">
                    <?php echo $this->Entity_RenderField($entity, 'directory_social', 'summary');?>
                </div>
            </div>
<?php if ($listing_body = $this->Entity_RenderField($entity, 'content_body', 'summary')):?>
            <div class="sabai-directory-body">
                <?php echo $listing_body;?>
            </div>
<?php endif;?>
            <div class="sabai-directory-custom-fields">
                <?php $this->displayTemplate('directory_custom_fields', array('entity' => $entity, 'view' => 'summary'));?>
            </div>
        </div>
<?php if (!empty($links) || !empty($buttons)):?>
        <div class="sabai-entity-links">
            <?php echo $this->ButtonLinks($buttons + $links);?>
        </div>
<?php endif;?>
    </div>
</div>
