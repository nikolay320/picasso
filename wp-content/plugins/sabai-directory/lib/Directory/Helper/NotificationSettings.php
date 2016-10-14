<?php
class Sabai_Addon_Directory_Helper_NotificationSettings extends Sabai_Helper
{
    protected $_listingTags, $_reviewTags, $_photoTags, $_leadTags;
    
    public function help(Sabai $application, $addonName)
    {
        return array(
            'listing_submitted_admin' => array(
                'type' => 'admin',
                'title' => __('Listing Submitted Admin Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to administrators whenever a listing that requires approval is submitted.', 'sabai-directory'),
                'tags' => $this->_getListingTags($application, $addonName),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new listing (ID: {listing_id}) has been submitted', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A new listing "{listing_title}" has been submitted on {listing_date}.

Submitted by {listing_author_name} ({listing_author_email}):

------------------------------------
{listing_summary}
------------------------------------

You can view the listing at {listing_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'review_submitted_admin' => array(
                'type' => 'admin',
                'title' => __('Review Submitted Admin Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to administrators whenever a review that requires approval is submitted.', 'sabai-directory'),
                'tags' => array_merge($this->_getReviewTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new review (ID: {review_id}) has been submitted', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A review of {listing_title} titled "{review_title}" has been submitted on {review_date}.

Submitted by {review_author_name} ({review_author_email}):

------------------------------------
{review_summary}
------------------------------------

You can view the review at {review_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'photo_submitted_admin' => array(
                'type' => 'admin',
                'title' => __('Photo Submitted Admin Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to administrators whenever a photo that requires approval is submitted.', 'sabai-directory'),
                'tags' => array_merge($this->_getPhotoTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new photo (ID: {photo_id}) has been submitted', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},

A new photo has been added to {listing_title} on {photo_date}.

Submitted by {photo_author_name} ({photo_author_email}):

You can view the photo at {photo_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'lead_submitted_admin' => array(
                'type' => 'admin',
                'title' => __('Lead Submitted Admin Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to administrators whenever a lead that requires approval is submitted.', 'sabai-directory'),
                'tags' => array_merge($this->_getLeadTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new lead (ID: {lead_id}) has been submitted', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},

A new lead has been added to {listing_title} on {lead_date}.

Submitted by {lead_author_name} ({lead_author_email}):

You can view the lead at {lead_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'listing_approved' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Listing Approved Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the user when a listing the user has submitted is approved.', 'sabai-directory'),
                'tags' => $this->_getListingTags($application, $addonName),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your listing has been published', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The listing you have submitted has been approved and is now published.

------------------------------------
{listing_title}
------------------------------------

You can view the listing at {listing_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'review_approved' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Review Approved Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the user when a review the user has submitted is approved.', 'sabai-directory'),
                'tags' => array_merge($this->_getReviewTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your review has been published', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The review of {listing_title} you have submitted has been approved and is now published.

------------------------------------
{review_title}
------------------------------------

You can view the review at {review_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'photo_approved' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Photo Approved Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the user when a photo the user has submitted is approved.', 'sabai-directory'),
                'tags' => array_merge($this->_getPhotoTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your photo has been published', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The photo of {listing_title} you have submitted has been approved and is now published.

You can view the photo at {photo_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'review_commented' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Comment Posted Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the review author when a comment is submitted.', 'sabai-directory'),
                'tags' => array_merge($this->_getReviewTags($application, $addonName), $this->_getCommentTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] You have a new comment', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A comment has been added to your review "{review_title}".

Comment by {comment_author_name}:

------------------------------------
{comment_summary}
------------------------------------

You can view the comment at {review_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory')
                ),
            ),
            'photo_commented' => array(
                'type' => 'user',
                'has_guest_author' => true,
                'title' => __('Comment Posted Notification Email (Photo)', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the photo author when a comment is submitted.', 'sabai-directory'),
                'tags' => array_merge($this->_getPhotoTags($application, $addonName), $this->_getCommentTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] You have a new comment', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A comment has been added to your photo.

Comment by {comment_author_name}:

------------------------------------
{comment_summary}
------------------------------------

You can view the comment at {photo_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory')
                ),
            ),
            
            'review_added' => array(
                'type' => 'user',
                'title' => __('Review Added Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the listing owner when a review is posted.', 'sabai-directory'),
                'tags' => array_merge($this->_getReviewTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new review has been posted', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A review of {listing_title} titled "{review_title}" has been posted on {review_date}.

Posted by {review_author_name} ({review_author_email}):

------------------------------------
{review_summary}
------------------------------------

You can read the full review at {review_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'photo_added' => array(
                'type' => 'user',
                'title' => __('Photo Added Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the listing owner when photos are added to the listing.', 'sabai-directory'),
                'tags' => array_merge($this->_getPhotoTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new photo has been added', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A new photo has been added to your listing {listing_title} on {photo_date} by {photo_author_name} ({photo_author_email}).

You can view the photo at {photo_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'lead_added' => array(
                'type' => 'user',
                'title' => __('Lead Added Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the listing owner when leads are added to the listing.', 'sabai-directory'),
                'tags' => array_merge($this->_getLeadTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new lead has been added', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A new lead has been added to your listing {listing_title} on {lead_date} by {lead_author_name} ({lead_author_email}).

You can view the lead at {lead_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),     
            'listing_expires' => array(
                'type' => 'user',
                'title' => __('Listing Claim Expiration Notification Email', 'sabai-directory'),
                'description' => sprintf(__('If enabled, a notification email is sent to the listing owner from %d days before expiration of a claim until the claim expires.', 'sabai-directory'), ($days = $application->System_EmailSettings($addonName, 'listing_expires', 'days')) ? $days : 7),
                'tags' => array_merge(array('{expiration_date}', '{expiration_date_diff}', '{listing_renew_url}'), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your listing claim will expire in {expiration_date_diff}', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
Your claim on listing {listing_title} will expire on {expiration_date}.

You can renew the claim at {listing_renew_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'listing_expired' => array(
                'type' => 'user',
                'title' => __('Listing Claim Expired Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the listing owner after a claim has expired.', 'sabai-directory'),
                'tags' => array_merge(array('{expiration_date}', '{expiration_date_diff}', '{listing_renew_url}'), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your listing claim has expired on {expiration_date}', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
Your claim on listing {listing_title} has expird {expiration_date_diff}.

You can renew the claim at {listing_renew_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            
            'listing_published' => array(
                'type' => 'roles',
                'title' => __('Listing Published Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a listing is published.', 'sabai-directory'),
                'tags' => array_merge($this->_getListingTags($application, $addonName), array('{listing_claim_url}')) ,
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] A new listing (ID: {listing_id}) has been published', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A new listing "{listing_title}" has been published on {listing_date}.

Submitted by {listing_author_name} ({listing_author_email}):

------------------------------------
{listing_summary}
------------------------------------

You can view the listing at {listing_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory')
                ),
            ),
            'review_published' => array(
                'type' => 'roles',
                'title' => __('Review Published Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a review is published.', 'sabai-directory'),
                'tags' => array_merge($this->_getReviewTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] A new review (ID: {review_id}) has been published', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A review of {listing_title} titled "{review_title}" has been published on {review_date}.

Submitted by {review_author_name} ({review_author_email}):

------------------------------------
{review_summary}
------------------------------------

You can read the full review at {review_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory')
                ),
            ),
            'photo_published' => array(
                'type' => 'roles',
                'title' => __('Photo Published Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a photo is published.', 'sabai-directory'),
                'tags' => array_merge($this->_getPhotoTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] A new photo (ID: {photo_id}) has been published', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
A photo for {listing_title} titled "{photo_title}" has been published on {photo_date}.

Submitted by {photo_author_name} ({photo_author_email}):

------------------------------------
{photo_summary}
------------------------------------

You can view the photo at {photo_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory')
                ),
            ),
            'listing_flagged' => array(
                'type' => 'roles',
                'title' => __('Listing Flagged Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a listing is flagged.', 'sabai-directory'),
                'tags' => array_merge($this->_getFlagTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] Listing (ID: {listing_id}) has been flagged', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The following listing has been flagged on {flag_date} by {flag_user_name} ({flag_user_email}):

------------------------------------
{listing_title}
------------------------------------

Reason: {flag_reason}
Score: {flag_score} (total: {flag_score_total})

You can view the listing at {listing_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'review_flagged' => array(
                'type' => 'roles',
                'title' => __('Review Flagged Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a review is flagged.', 'sabai-directory'),
                'tags' => array_merge($this->_getFlagTags($application, $addonName), $this->_getReviewTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] Review (ID: {review_id}) has been flagged', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The following review has been flagged on {flag_date} by {flag_user_name} ({flag_user_email}):

------------------------------------
{review_title} (listing: {listing_title})
------------------------------------

Reason: {flag_reason}
Score: {flag_score} (total: {flag_score_total})

You can view the review at {review_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'photo_flagged' => array(
                'type' => 'roles',
                'title' => __('Photo Flagged Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to users of selected roles whenever a photo is flagged.', 'sabai-directory'),
                'tags' => array_merge($this->_getFlagTags($application, $addonName), $this->_getPhotoTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => false,
                'email' => array(
                    'subject' => __('[{site_name}] Listing (ID: {listing_id}) has been flagged', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The following photo has been flagged on {flag_date} by {flag_user_name} ({flag_user_email}):

------------------------------------
{photo_title} (listing: {listing_title})
------------------------------------

Reason: {flag_reason}
Score: {flag_score} (total: {flag_score_total})

You can view the photo at {photo_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'claim_approved' => array(
                'type' => 'user',
                'title' => __('Listing Claim Approved Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the user when a listing claim the user has submitted is approved.', 'sabai-directory'),
                'tags' => array_merge($this->_getClaimTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your claim has been approved', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The ownership claim for {listing_title} you submitted on {claim_date} has been approved. You can view the listing at {listing_url}.

{claim_admin_note}

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'claim_rejected' => array(
                'type' => 'user',
                'title' => __('Listing Claim Rejected Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to the user when a listing claim the user has submitted is rejected.', 'sabai-directory'),
                'tags' => array_merge($this->_getClaimTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] Your claim was rejected', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},
                
The ownership claim for {listing_title} you submitted on {claim_date} was rejected. You can view the listing at {listing_url}.

{claim_admin_note}

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
            'claim_pending' => array(
                'type' => 'admin',
                'title' => __('Listing Claim Pending Admin Notification Email', 'sabai-directory'),
                'description' => __('If enabled, a notification email is sent to administrators whenever a listing claim that requires approval is submitted.', 'sabai-directory'),
                'tags' => array_merge($this->_getPhotoTags($application, $addonName), $this->_getListingTags($application, $addonName)),
                'enable' => true,
                'email' => array(
                    'subject' => __('[{site_name}] A new listing claim (ID: {claim_id}) has been submitted', 'sabai-directory'),
                    'body' => __('Hi {recipient_name},

A new listing claim has been submitted for {listing_title} on {claim_date}.

Submitted by {claim_user_name} ({claim_user_email}):

{claim_comment}

To approve or reject the claim, go to {claim_admin_url}.

Regards,
{site_name}
{site_url}', 'sabai-directory'),
                ),
            ),
        );
    }
    
    private function _getListingTags(Sabai $application, $addonName)
    {
        if (!isset($this->_listingTags)) {
            $this->_listingTags = $application->Entity_BundleTemplateTags($application->getAddon($addonName)->getListingBundleName(), 'listing_');
        }
        return $this->_listingTags;
    }
    
    private function _getReviewTags(Sabai $application, $addonName)
    {
        if (!isset($this->_reviewTags)) {
            $this->_reviewTags = $application->Entity_BundleTemplateTags($application->getAddon($addonName)->getReviewBundleName(), 'review_');
        }
        return $this->_reviewTags;
    }
    
    private function _getPhotoTags(Sabai $application, $addonName)
    {
        if (!isset($this->_photoTags)) {
            $this->_photoTags = $application->Entity_BundleTemplateTags($application->getAddon($addonName)->getPhotoBundleName(), 'photo_', array('summary', 'body'));
        }
        return $this->_photoTags;
    }
            
    private function _getLeadTags(Sabai $application, $addonName)
    {
        if (!isset($this->_leadTags)) {
            $this->_leadTags = $application->Entity_BundleTemplateTags($application->getAddon($addonName)->getPhotoBundleName(), 'lead_', array('title'));
        }
        return $this->_leadTags;
    }
        
    private function _getClaimTags()
    {
        return array('{claim_id}', '{claim_user_name}', '{claim_user_email}', '{claim_date}', '{claim_comment}', '{claim_admin_url}', '{claim_admin_note}');
    }
            
    protected function _getCommentTags()
    {
        return array('{comment_id}', '{comment_author_name}', '{comment_author_email}', '{comment_date}', '{comment_summary}');
    }
    
    protected function _getFlagTags()
    {
        return array('{flag_id}', '{flag_user_name}', '{flag_user_email}', '{flag_date}', '{flag_reason}', '{flag_score}', '{flag_score_total}');
    }
}