<?php
class Sabai_Addon_WordPress_Helper_PostboxJs extends Sabai_Helper
{    
    public function help(Sabai $application, $page, $entityType = 'content')
    {
        return sprintf(
            'jQuery(document).ready(function(){
	if (postboxes) postboxes.add_postbox_toggles(\'%1$s\');
	var $ = jQuery;
	if ( $(\'#edit-slug-box\').length ) {
		editPermalink = function(post_id) {
			var i, c = 0, e = $(\'#editable-post-name\'), revert_e = e.html(), real_slug = $(\'#post_name\'), revert_slug = real_slug.val(), b = $(\'#edit-slug-buttons\'), revert_b = b.html(), full = $(\'#editable-post-name-full\').html();

			$(\'#view-post-btn\').hide();
			b.html(\'<a href="#" class="save button">%2$s</a> <a class="cancel" href="#">%3$s</a>\');
			b.children(\'.save\').click(function() {
				var new_slug = e.children(\'input\').val();
				if ( new_slug == $(\'#editable-post-name-full\').text() ) {
					return $(\'.cancel\', \'#edit-slug-buttons\').click();
				}
				$.post(\'%4$s\', {
					post_id: post_id,
					new_slug: new_slug,
					new_title: $(\'#title\').val(),
					samplepermalinknonce: $(\'#samplepermalinknonce\').val(),
                    entity_type: \'%5$s\',
                    __ajax: \'#editable-post-name\'
				}, function(data) {
					$(\'#edit-slug-box\').html(data);
					b.html(revert_b);
					real_slug.val(new_slug);
					makeSlugeditClickable();
					$(\'#view-post-btn\').show();
				});
				return false;
			});

			$(\'.cancel\', \'#edit-slug-buttons\').click(function() {
				$(\'#view-post-btn\').show();
				e.html(revert_e);
				b.html(revert_b);
				real_slug.val(revert_slug);
				return false;
			});

			for ( i = 0; i < full.length; ++i ) {
				if ( \'%%\' == full.charAt(i) )
					c++;
			}

			slug_value = ( c > full.length / 4 ) ? \'\' : full;
			e.html(\'<input type="text" id="new-post-slug" value="\'+slug_value+\'" />\').children(\'input\').keypress(function(e){
				var key = e.keyCode || 0;
				// on enter, just save the new slug, don\'t save the post
				if ( 13 == key ) {
					b.children(\'.save\').click();
					return false;
				}
				if ( 27 == key ) {
					b.children(\'.cancel\').click();
					return false;
				}
				real_slug.val(this.value);
			}).focus();
		}

		makeSlugeditClickable = function() {
			$(\'#editable-post-name\').click(function() {
				$(\'#edit-slug-buttons\').children(\'.edit-slug\').click();
			});
		}
		makeSlugeditClickable();
	}
});',
            $page,
            Sabai::h(__('OK', 'sabai')),
            Sabai::h(__('Cancel', 'sabai')),
            $application->Url('/wordpress/permalink'),
            $entityType
        );
    }
}