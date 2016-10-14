<div class="sabai-alert sabai-alert-info">Open the file <strong><?php echo substr(__FILE__, strpos(__FILE__, '/wp-content/plugins/sabai-directory'));?></strong> with a text editor to see how the content on this tab is displayed.</div>

<h3>ID</h3>
<p><?php echo $entity->getId();?></p>

<h3>Title</h3>
<p><?php echo $entity->getTitle();?></p>

<h3>Slug</h3>
<p><?php echo $entity->getSlug();?></p>

<h3>Permalink</h3>
<p><?php echo $this->Entity_Permalink($entity);?></p>

<h3>Publish Date</h3>
<p><?php echo $this->Date($entity->getTimestamp());?></p>

<h3>Publish Date/Time</h3>
<p><?php echo $this->DateTime($entity->getTimestamp());?></p>

<h3>Publish Timestamp</h3>
<p><?php echo $entity->getTimestamp();?></p>

<h3>Views</h3>
<p><?php echo $entity->getViews();?></p>

<h3>Location</h3>
<dl>
    <dt>Address</dt>
    <dd><?php echo $entity->directory_location[0]['address'];?></dd>
    <dt>Latitude</dt>
    <dd><?php echo $entity->directory_location[0]['lat'];?></dd>
    <dt>Longitude</dt>
    <dd><?php echo $entity->directory_location[0]['lng'];?></dd>
    <dt>GoogleMaps Zoom Level</dt>
    <dd><?php echo $entity->directory_location[0]['zoom'];?></dd>
</dl>

<h3>Contact</h3>
<dl>
    <dt>Phone Number</dt>
    <dd><?php echo $entity->directory_contact[0]['phone'];?></dd>
    <dt>Mobile Number</dt>
    <dd><?php echo $entity->directory_contact[0]['mobile'];?></dd>
    <dt>Fax Number</dt>
    <dd><?php echo $entity->directory_contact[0]['fax'];?></dd>
    <dt>E-mail Address</dt>
    <dd><?php echo $entity->directory_contact[0]['email'];?></dd>
    <dt>Website URL</dt>
    <dd><?php echo $entity->directory_contact[0]['website'];?></dd>
</dl>

<h3>Social Accounts</h3>
<dl>
    <dt>Twitter</dt>
    <dd><?php echo $entity->directory_social[0]['twitter'];?></dd>
    <dt>Facebook URL</dt>
    <dd><?php echo $entity->directory_social[0]['facebook'];?></dd>
    <dt>Google+ URL</dt>
    <dd><?php echo $entity->directory_social[0]['googleplus'];?></dd>
</dl>

<h3>Description</h3>
<?php echo $this->Entity_RenderField($entity, 'content_body');?>

<h3>Description (summarized)</h3>
<?php echo $this->Entity_RenderField($entity, 'content_body', 'summary');?>

<h3>Author</h3>
<dl>
    <dt>Name</dt>
    <dd><?php echo $entity->getAuthor()->name;?></dd>
    <dt>Username</dt>
    <dd><?php echo $entity->getAuthor()->username;?></dd>
    <dt>Email</dt>
    <dd><?php echo $entity->getAuthor()->email;?></dd>
    <dt>Website URL</dt>
    <dd><?php echo $entity->getAuthor()->url;?></dd>
    <dt>Registration Date</dt>
    <dd><?php echo $this->Date($entity->getAuthor()->created);?></dd>
    <dt>Thumbnail (small)</dt>
    <dd><?php echo $this->UserIdentityThumbnailSmall($entity->getAuthor());?></dd>
    <dt>Thumbnail (medium)</dt>
    <dd><?php echo $this->UserIdentityThumbnailMedium($entity->getAuthor());?></dd>
    <dt>Thumbnail (large)</dt>
    <dd><?php echo $this->UserIdentityThumbnailLarge($entity->getAuthor());?></dd>
    <dt>Link</dt>
    <dd><?php echo $this->UserIdentityLink($entity->getAuthor());?></dd>
    <dt>Link (with small thumbnail)</dt>
    <dd><?php echo $this->UserIdentityLinkWithThumbnailSmall($entity->getAuthor());?></dd>
    <dt>Link (with medium thumbnail)</dt>
    <dd><?php echo $this->UserIdentityLinkWithThumbnailMedium($entity->getAuthor());?></dd>
</dl>

<?php if ($entity->directory_category): $category_count = 0;?>
<?php   foreach ($entity->directory_category as $category):?>
<h3>Category <?php echo ++$category_count;?></h3>
<dl>
    <dt>ID</dt>
    <dd><?php echo $category->getId();?></dd>
    <dt>Title</dt>
    <dd><?php echo $category->getTitle();?></dd>
    <dt>Slug</dt>
    <dd><?php echo $category->getSlug();?></dd>
    <dt>Permalink</dt>
    <dd><?php echo $this->Entity_Permalink($category);?></dd>
</dl>
<?php   endforeach;?>
<?php else:?>
<h3>Categories</h3>
<p>No categories</p>
<?php endif;?>

<?php if ($entity->directory_claim): $claim_count = 0;?>
<?php   foreach ($entity->directory_claim as $claim):?>
<h3>Claim <?php echo ++$claim_count;?></h3>
<dl>
    <dt>Claimed By</dt>
    <dd><?php echo $this->UserIdentityLinkWithThumbnailSmall($this->UserIdentity($claim['claimed_by']));?></dd>
    <dt>Claimed At</dt>
    <dd><?php echo $this->DateTime($claim['claimed_at']);?></dd>
    <dt>Claim Expires At</dt>
    <dd><?php echo $this->DateTime($claim['expires_at']);?></dd>
</dl>
<?php   endforeach;?>
<?php else:?>
<h3>Claims</h3>
<p>No claims</p>
<?php endif;?>

<h3>Ratings</h3>
<?php if ($entity->voting_rating):?>
<dl>
    <dt>Count</dt>
    <dd><?php echo $entity->voting_rating['']['count'];?></dd>
    <dt>Sum</dt>
    <dd><?php echo $entity->voting_rating['']['sum'];?></dd>
    <dt>Average</dt>
    <dd><?php echo $entity->voting_rating['']['average'];?></dd>
    <dt>Stars</dt>
    <dd><?php echo $this->Voting_RenderRating($entity);?></dd>
    <dt>Last Rating Date</dt>
    <dd><?php echo $this->Date($entity->voting_rating['']['last_voted_at']);?></dd>
    <dt>Last Rating Date/Time</dt>
    <dd><?php echo $this->DateTime($entity->voting_rating['']['last_voted_at']);?></dd>
    <dt>Last Rating Timestamp</dt>
    <dd><?php echo $entity->voting_rating['']['last_voted_at'];?></dd>
</dl>
<?php else:?>
<p>No ratings</p>
<?php endif;?>

<h3>Bookmarks</h3>
<?php if ($entity->voting_favorite):?>
<dl>
    <dt>Count</dt>
    <dd><?php echo $entity->voting_favorite[0]['count'];?></dd>
    <dt>Last Bookmark Date</dt>
    <dd><?php echo $this->Date($entity->voting_favorite[0]['last_voted_at']);?></dd>
    <dt>Last Bookmark Date/Time</dt>
    <dd><?php echo $this->DateTime($entity->voting_favorite[0]['last_voted_at']);?></dd>
    <dt>Last Bookmark Timestamp</dt>
    <dd><?php echo $entity->voting_favorite[0]['last_voted_at'];?></dd>
</dl>
<?php else:?>
<p>No bookmarks</p>
<?php endif;?>

<h3>Flags</h3>
<?php if ($entity->voting_flag):?>
<dl>
    <dt>Count</dt>
    <dd><?php echo $entity->voting_flag[0]['count'];?></dd>
    <dt>Last Flag Date</dt>
    <dd><?php echo $this->Date($entity->voting_flag[0]['last_voted_at']);?></dd>
    <dt>Last Flag Date/Time</dt>
    <dd><?php echo $this->DateTime($entity->voting_flag[0]['last_voted_at']);?></dd>
    <dt>Last Flag Timestamp</dt>
    <dd><?php echo $entity->voting_flag[0]['last_voted_at'];?></dd>
</dl>
<?php else:?>
<p>No flags</p>
<?php endif;?>

<h3>Featured</h3>
<?php if ($entity->content_featured):?>
<dl>
    <dt>Featured Date</dt>
    <dd><?php echo $this->Date($entity->content_featured[0]['featured_at']);?></dd>
    <dt>Featured Date/Time</dt>
    <dd><?php echo $this->DateTime($entity->content_featured[0]['featured_at']);?></dd>
    <dt>Feadured Timestamp</dt>
    <dd><?php echo $entity->content_featured[0]['featured_at'];?></dd>
    <dt>Expiration Date</dt>
    <dd><?php echo $this->Date($entity->content_featured[0]['expires_at']);?></dd>
    <dt>Expiration Date/Time</dt>
    <dd><?php echo $this->DateTime($entity->content_featured[0]['expires_at']);?></dd>
    <dt>Expiration Timestamp</dt>
    <dd><?php echo $entity->content_featured[0]['expires_at'];?></dd>
</dl>
<?php else:?>
<p>Not featured</p>
<?php endif;?>

<h3>Review Count</h3>
<p><?php echo intval(@$entity->content_children_count[0]['directory_listing_review']);?></p>

<h3>Photo Count</h3>
<p><?php echo intval(@$entity->content_children_count[0]['directory_listing_photo']);?></p>

<h3>Custom Fields</h3>
<?php if ($custom_fields = $this->Entity_CustomFields($entity)):?>

<dl>
<?php foreach ($custom_fields as $field):?>
    <dt><?php echo $field->getFieldTitle();?> (<?php echo $field->getFieldName();?>)</dt>
    <dd><?php echo $this->Entity_RenderField($entity, $field);?></dd>
    <dd><pre><?php print_r($entity->getFieldValue($field->getFieldName()));?></pre></dd>    
<?php endforeach;?>
</dl>
<?php else:?>
<p>No custom fields</p>
<?php endif;?>