
jQuery(document).ready(function() {
	/*jQuery('#buddypress div[id^="wp-ulike-activity"]').each(function() {
		if($(this).parent().find('div.activity-meta').length) {
			$(this).parent().find('div.activity-meta').prepend($(this));
		}
	});*/


	/* ----- http://[URL]/members/ ----- */
	kleo_child_set_member_type_select();

	kleo_child_set_member_type();
	/* ----- http://[URL]/members/ ----- */

	/* ----- Tooltip ----- */
	kleo_child_activity_comment_tooltip();
	/* ----- Tooltip ----- */

	kleo_child_activity_comment_state();

	kleo_child_removing_space_question_section();

	about_page_modal();

	//kleo_child_BSALA_right_space();

	kleo_child_hide_textarea_on_activity_stream();


	rename_comment_button_in_single_buddypress_doc();

	setTimeout(function() {
		kleo_child_calendar_translation ();
	}, 30);

	if(jQuery('.page-id-59')) {
		if ( !jQuery('.cpm-top-bar #cpm-create-project').length){
			jQuery('.page-id-59 section.main-title').css('display','none');
		}
	}

	setTimeout(function() {
		remove_em_tag();
	}, 3000);
	// HELPER items script
		// yes no button
			jQuery('body').on('click','.ajde_yn_btn ', function(){
				var obj = jQuery(this);
				var afterstatementx = obj.attr('data-afterstatement');
				// yes
				if(obj.hasClass('NO')){
					// afterstatment
					if(afterstatementx!=''){
						var type = (obj.attr('as_type')=='class')? '.':'#';
						jQuery(type+ obj.attr('data-afterstatement')).slideDown('fast');
					}

				}else{//no
					if(afterstatementx!=''){
						var type = (obj.attr('as_type')=='class')? '.':'#';
						jQuery(type+obj.attr('data-afterstatement')).slideUp('fast');
					}
				}
			});
});

function rename_comment_button_in_single_buddypress_doc() {
	jQuery('body.single-bp_doc #commentform .form-submit #submit').val('Publier');
}

function remove_em_tag() {
	jQuery('body.single-bp_doc #doc-settings .bp-docs-access-row label').each(function() {
		var label_value = jQuery(this).text();

		label_value = label_value.replace(/<em>/g, '');
		label_value = label_value.replace(/<\/em>/g, '');

		jQuery(this).text(label_value);

	});
}

function kleo_child_set_member_type_select() {
	jQuery('div.item-list-tabs .kleo_bp_member_select select').change(function(event) {
		if ( jQuery(this).hasClass('no-ajax')  || jQuery( event.target ).hasClass('no-ajax') )  {
			return;
		}

		var targetElem = ( event.target.nodeName === 'SPAN' ) ? event.target.parentNode : event.target,
			target       = jQuery( targetElem ).parent(),
			css_id, object, scope, filter, search_terms;

		if ( 'DIV' === target[0].nodeName && !target.hasClass( 'last' ) ) {
			css_id = jQuery(this).val().split( '-' );
			object = css_id[0];

			if ( 'activity' === object ) {
				return false;
			}

			scope = css_id[1];
			filter = jQuery('#' + object + '-order-select select').val();
			search_terms = jQuery('#' + object + '_search').val();

			jQuery(this).removeClass().addClass('bp-member-type-filter').addClass('member-type-select-bg-' + scope);

			bp_filter_request( object, filter, scope, 'div.' + object, search_terms, 1, jQuery.cookie('bp-' + object + '-extras') );

			return false;
		}
	});
}

function kleo_child_set_member_type() {
	jq('div.item-list-tabs .kleo_bp_member_select select option[value="members-' + jq.cookie('bp-members-scope') + '"]').prop( 'selected', true );
	jQuery('div.item-list-tabs .kleo_bp_member_select select').removeClass().addClass('bp-member-type-filter').addClass('member-type-select-bg-' + jQuery.cookie('bp-members-scope'));
}

function kleo_child_activity_comment_tooltip() {
	jQuery("a.acomment-reply").attr ({
		title: "S'il vous plait, cliquez ici pour ajouter un commentaire."
	}).addClass("user-tooltip");
}

function kleo_child_activity_comment_state() {
	jQuery('input[name=ac_form_submit]').each(function() {
		jQuery(this).click(function() {
			kleo_child_activity_comment_checking();
		});
	});

	jQuery('#buddypress a.acomment-delete').each(function() {
		jQuery(this).click(function() {
			kleo_child_activity_comment_checking();
		});
	});

	jQuery('#buddypress .activity-list li.load-more a').each(function() {
		jQuery(this).click(function() {
			kleo_child_activity_comment_checking();
		});
	});
}

function kleo_child_activity_comment_checking() {
	setTimeout(function() {
		jQuery('#buddypress a.acomment-reply span').each(function() {
			var comments_count = parseInt(jQuery(this).text());

			if(comments_count > 0) {
				if(jQuery(this).parent().hasClass('bp-has-acomment') == false) {
					jQuery(this).parent().addClass('bp-has-acomment');
				}
			} else {
				jQuery(this).parent().removeClass('bp-has-acomment');
			}
		});

		kleo_child_activity_comment_state();
	}, 4000);
}

function about_page_modal() {
	jQuery('.logo a').parent().click(function(e) {
		e.preventDefault();

		if(!jQuery('#kleo_child_about_page_container').hasClass('opened')) {
			jQuery('#kleo_child_about_page_container').fadeIn(500);
			jQuery('#kleo_child_about_page_container').addClass('opened');
		} else {
			jQuery('#kleo_child_about_page_container').fadeOut(500);
			jQuery('#kleo_child_about_page_container').removeClass('opened');
		}
	});

	jQuery(document).keyup(function(e) {
		if (e.keyCode == 27) {
			jQuery('#kleo_child_about_page_container').fadeOut(500);
			jQuery('#kleo_child_about_page_container').removeClass('opened');
		}
	});
}

function kleo_child_BSALA_right_space() {
	jQuery('#buddypress div.activity-meta').each(function() {
		var bsala_avatar_count = jQuery(this).find('.fav_box ul.fav-ul-list > li').length;

		var bsala_avatar_width = bsala_avatar_count*7;

		if(bsala_avatar_count > 0) {
			jQuery(this).find('.fav_box').css('cssText', 'width: ' + bsala_avatar_width + '% !important');
		}

		jQuery(this).parent().find('.activity-header > p').css('cssText', 'width: ' + parseInt(70 + 23 - bsala_avatar_width) + '% !important');
	});
}

function kleo_child_removing_space_question_section() {
	jQuery('#sabai-body > form.sabai-form > div.sabai-form-fields > fieldset.sabai-form-field').each(function() {
		if(jQuery(this).children().size() == 1) {
			console.log(jQuery(this).html());
		}
	});
}

function kleo_child_hide_textarea_on_activity_stream() {
	setTimeout(function() {
		jQuery("input#aw-whats-new-submit").on('click', function() {
			var hide_textarea_timer_id = setInterval(function() {
				if(!jQuery('#whats-new-options').is(":visible")) {
					jQuery('#whats-new-content').hide();
					jQuery('#whats-new-content').after('<a href="" id="refresh" class="show_more_users"><div class="user-block-in-hiddenblock">rafraichir le fil d\'actualites </div> </a>');
					clearInterval(hide_textarea_timer_id);
					jQuery('.show_more_users').hover(function() {
						jQuery('.user-block-in-hiddenblock').toggle();
					});
				}
			}, 500);
		});
	}, 5000);
}

function kleo_child_calendar_translation () {
	if (!jQuery('html[lang="fr-FR"]').length) return;
	jQuery('#calendar .fc-header-title h2').unbind("DOMSubtreeModified");
	kleo_child_date_translation ('#calendar .fc-header-title h2');
	kleo_child_date_translation ('#calendar .fc-day-header');
	kleo_child_date_translation ('#calendar .fc-button-today');
	kleo_child_date_translation ('#calendar .fc-button-month');
	kleo_child_date_translation ('#calendar .fc-button-agendaWeek');
	kleo_child_date_translation ('#calendar .fc-button-agendaDay');
	kleo_child_date_translation ('#calendar .fc-agenda-axis');
	kleo_child_date_translation ('#calendar .fc-widget-header');

	kleo_child_date_translation ('#cpm-mytask-page-content h3.cpm-box-title');
	kleo_child_date_translation ('#mytask-change-range option');
	jQuery('#calendar .fc-header-title h2').bind("DOMSubtreeModified",function(){
		kleo_child_calendar_translation ();
	});

}

function kleo_child_date_translation ( selector ) {

	jQuery(selector).each(function() {
			var text = jQuery(this).text();
			text = text.replace('January','Janvier ');
			text = text.replace('February','Février ');
			text = text.replace('March','Mars');
			text = text.replace('April','Avril');
			text = text.replace('May','Mai');
			text = text.replace('June','Juin');
			text = text.replace('July','Juillet');
			text = text.replace('August','Août');
			text = text.replace('September','Septembre');
			text = text.replace('October','Octobre');
			text = text.replace('November','Novembre');
			text = text.replace('December','Décembre ');

			text = text.replace('Jan','Jan');
			text = text.replace('Feb','Fev');
			text = text.replace('Mar','Mar');
			text = text.replace('Apr','Avr');
			text = text.replace('May','Mai');
			text = text.replace('Jun','Juin');
			text = text.replace('Jul','Juil');
			text = text.replace('Aug','Aou');
			text = text.replace('Sep','Sep');
			text = text.replace('Oct','Oct');
			text = text.replace('Nov','Nov');
			text = text.replace('Dec','Dec');

			text = text.replace('Monday','Lundi');
			text = text.replace('Tuesday','Mardi');
			text = text.replace('Wednesday','Mercredi');
			text = text.replace('Thursday','Jeudi');
			text = text.replace('Friday','Vendredi');
			text = text.replace('Saturday','Samedi');
			text = text.replace('Sunday','Dimanche');

			text = text.replace('Mon','Lun');
			text = text.replace('Tue','Mar');
			text = text.replace('Wed','Mer');
			text = text.replace('Thu','Jeu');
			text = text.replace('Fri','Ven');
			text = text.replace('Sat','Sam');
			text = text.replace('Sun','Dim');

			text = text.replace('all-day','journée');
			text = text.replace('today','aujourd\'hui');
			text = text.replace('month','mois');
			text = text.replace('week','semaine');
			text = text.replace('day','journée');

			text = text.replace('At a glance','Vue d\'ensemble');
			jQuery(this).text(text);
	});

}