<?php if (!$IS_EMBED) $this->Action('directory_before_single_listing', array($bundle->addon, $entity));?>
<?php $has_photos = $entity->directory_photos && ($photos = $this->Entity_RenderField($entity, 'directory_photos'));?>
<div id="<?php echo $id;?>" class="<?php echo $class;?> sabai-clearfix<?php if (!$has_photos):?> sabai-directory-no-image<?php endif;?>" itemscope itemtype="http://schema.org/LocalBusiness">
    <meta itemprop="name" content="<?php Sabai::_h($entity->getTitle());?>" />
    <link itemprop="url" href="<?php echo $this->Entity_Url($entity);?>" />
<?php if ($labels = $this->Entity_RenderLabels($entity)):?>
    <div class="sabai-directory-labels"><?php echo $labels;?></div>
<?php endif;?>
    <div class="sabai-row">
<?php if ($has_photos):?>
        <div class="sabai-col-sm-4 sabai-directory-images">
            <?php echo $photos;?>
        </div>
<?php endif;?>
        <div class="<?php if ($has_photos):?>sabai-col-sm-8<?php else:?>sabai-col-sm-12<?php endif;?> sabai-directory-main">
<?php if (!empty($entity->voting_rating['']['count'])):?>
            <div class="sabai-directory-rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                <?php echo $this->Entity_RenderField($entity, 'voting_rating', array('link' => $this->Entity_Url($entity, '/reviews'), 'count_formats' => array(sprintf(__('%s review', 'sabai-directory'), '<span itemprop="reviewCount">%d</span>'), sprintf(__('%s reviews', 'sabai-directory'), '<span itemprop="reviewCount">%d</span>')), 'summary_url' => $this->Entity_Url($entity, '/ratings')));?>
            </div>
<?php endif;?>
<?php if ($entity->directory_category && ($categories = $this->Entity_RenderField($entity, 'directory_category'))):?>
            <div class="sabai-directory-category">
                <?php echo $categories;?>
            </div>
<?php endif;?>
            <div class="sabai-directory-info sabai-clearfix">
<?php if ($entity->directory_location):?>
                <div class="sabai-directory-location">
                    <?php echo $this->Entity_RenderField($entity, 'directory_location');?>
                </div>
<?php   if ($entity->directory_location[0]['street']):?>
                <div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress" class="sabai-directory-address sabai-hidden">
                    <span itemprop="streetAddress"><?php Sabai::_h($entity->directory_location[0]['street']);?></span>
<?php     if ($entity->directory_location[0]['city']):?>
                    <span itemprop="addressLocality"><?php Sabai::_h($entity->directory_location[0]['city']);?></span>
<?php     endif;?>
<?php     if ($entity->directory_location[0]['state']):?>
                    <span itemprop="addressRegion"><?php Sabai::_h($entity->directory_location[0]['state']);?></span>
<?php     endif;?>
<?php     if ($entity->directory_location[0]['zip']):?>
                    <span itemprop="postalCode"><?php Sabai::_h($entity->directory_location[0]['zip']);?></span>
<?php     endif;?>
<?php     if ($entity->directory_location[0]['country']):?>
                    <span itemprop="addressCountry"><?php Sabai::_h($entity->directory_location[0]['country']);?></span>
<?php     endif;?>
                </div>
<?php   endif;?>
<?php endif;?>
                <div class="sabai-directory-contact">
                    <?php echo $this->Entity_RenderField($entity, 'directory_contact');?>
                </div>
                <div class="sabai-directory-social">
                    <?php echo $this->Entity_RenderField($entity, 'directory_social');?>
                </div>
            </div>
<?php if ($listing_body = $this->Entity_RenderField($entity, 'content_body')):?>
            <div class="sabai-directory-body" itemprop="description">
                <?php echo $listing_body;?>
            </div>
<?php endif;?>
            <div class="sabai-directory-custom-fields">
                <?php $this->displayTemplate('directory_custom_fields', array('entity' => $entity));?>
            </div>
        </div>
    </div>
<?php if (!empty($buttons)):?>
    <div class="sabai-navigation sabai-navigation-bottom sabai-entity-buttons">
        <?php echo $this->ButtonToolbar($buttons);?>
    </div>
<?php endif;?>
<?php if (!empty($links)):?>
    <div class="sabai-entity-links">
        <?php echo $this->ButtonLinks($links);?>
    </div>
<?php endif;?>
</div>
<?php if (!$IS_EMBED) $this->Action('directory_after_single_listing', array($bundle->addon, $entity));?>