jQuery(document).ready(function() 
{
	if(!jQuery('.sabai-content-btn-edit-questions').length) {
		jQuery('.sabai-entity-field-name-field-question-update-activity').parent().css("display","none");
		jQuery('.sabai-entity-field-name-field-answer-update-activity').parent().css("display","none");
	}
	else {
		jQuery('.sabai-entity-field-name-field-question-update-activity').prependTo(jQuery('.sabai-form-action.sabai-form-type-submit'));
		jQuery('.sabai-entity-field-name-field-answer-update-activity').prependTo(jQuery('.sabai-form-action.sabai-form-type-submit'));
	}
	if(!jQuery('.sabai-content-btn-edit-directory-listing').length) {
		jQuery('.sabai-entity-field-name-field-article-update-activity').parent().parent().css("display","none");
		jQuery('.sabai-entity-field-name-field-review-update-activity').parent().css("display","none");
	}
	else {
		jQuery('.sabai-entity-field-name-field-article-update-activity').prependTo(jQuery('.sabai-form-action.sabai-form-type-submit'));
		jQuery('.sabai-entity-field-name-field-review-update-activity').prependTo(jQuery('.sabai-form-action.sabai-form-type-submit'));
	}
});