(function () {
	if( typeof crazyPills === 'undefined' )
		return;

	var labels = crazyPills.labels;
	tinymce.PluginManager.add( 'callouts', function( editor, url ) {
		var add_callout = function(e){
			var text = editor.selection.getContent( { format: 'text' } ) || labels.callout_default;
			editor.insertContent( '<div class="cp_' + this.value() + '">' + text + '</div>' );
		}
		editor.addButton( 'callouts', {
			text: labels.callouts,
			type: 'menubutton',
			menu: [
				{ text : labels.alert, value : 'alert', onclick : add_callout },
				{ text : labels.error, value : 'error', onclick : add_callout },
				{ text : labels.info, value : 'info', onclick : add_callout },
				{ text : labels.success, value : 'success', onclick : add_callout }
			]
		} );
	} );

	tinymce.PluginManager.add( 'highlights', function( editor, url ) {
		var add_highlight = function(e){
			var text = editor.selection.getContent( { format: 'text' } ) || labels.highlight_default;
			editor.insertContent( '<span class="cp_highlight ' + this.value() + '">' + text + '</span>&nbsp;' );
		}
		editor.addButton( 'highlights', {
			text: labels.highlight,
			type: 'menubutton',
			menu: [
				{ text : labels.yellow, value : 'yellow', onclick : add_highlight },
				{ text : labels.brown, value : 'brown', onclick : add_highlight },
				{ text : labels.black, value : 'black', onclick : add_highlight },
				{ text : labels.blue, value : 'blue', onclick : add_highlight },
				{ text : labels.green, value : 'green', onclick : add_highlight },
				{ text : labels.silver, value : 'silver', onclick : add_highlight },
				{ text : labels.magenta, value : 'magenta', onclick : add_highlight },
				{ text : labels.natural, value : 'natural', onclick : add_highlight },
				{ text : labels.orange, value : 'orange', onclick : add_highlight },
				{ text : labels.purple, value : 'purple', onclick : add_highlight },
				{ text : labels.red, value : 'red', onclick : add_highlight },
				{ text : labels.teal, value : 'teal', onclick : add_highlight }
			]
		} );
	} );

	tinymce.PluginManager.add( 'buttons', function( editor, url ) {
		var add_button = function(e){
			var text = editor.selection.getContent( { format: 'text' } ) || labels.button_default;
			editor.insertContent( '<a href="#" class="cp_button ' + this.value() + '">' + text + '</a>' );
		}
		editor.addButton( 'buttons', {
			text: labels.buttons,
			type: 'menubutton',
			menu: [
				{ text : labels.lightblue, value : 'light-blue', onclick : add_button },
				{ text : labels.blue, value : 'blue', onclick : add_button },
				{ text : labels.green, value : 'green', onclick : add_button },
				{ text : labels.red, value : 'red', onclick : add_button },
				{ text : labels.orange, value : 'orange', onclick : add_button },
				{ text : labels.purple, value : 'purple', onclick : add_button },
				{ text : labels.grey, value : 'grey', onclick : add_button },
				{ text : labels.black, value : 'black', onclick : add_button }
			]
		} );
	} );

	tinymce.PluginManager.add( 'checks', function( editor, url ) {
		var add_checklist = function(e){
			var text = editor.selection.getContent( { format: 'text' } ) || labels.listitem;
			editor.insertContent( '<ul class="cp_check ' + this.value() + '"><li>' + text + '</li></ul>' );
		}
		editor.addButton( 'checks', {
			text: labels.checks,
			type: 'menubutton',
			menu: [
				{ text : labels.green, value : 'green', onclick : add_checklist },
				{ text : labels.blue, value : 'blue', onclick : add_checklist },
				{ text : labels.darkblue, value : 'darkblue', onclick : add_checklist },
				{ text : labels.gray, value : 'gray', onclick : add_checklist },
				{ text : labels.orange, value : 'orange', onclick : add_checklist },
				{ text : labels.pink, value : 'pink', onclick : add_checklist },
				{ text : labels.purple, value : 'purple', onclick : add_checklist },
				{ text : labels.red, value : 'red', onclick : add_checklist },
				{ text : labels.black, value : 'black', onclick : add_checklist }
			]
		} );
	} );

	tinymce.PluginManager.add( 'bullets', function( editor, url ) {
		var add_bulletlist = function(e){
			var text = editor.selection.getContent( { format: 'text' } ) || labels.listitem;
			editor.insertContent( '<ul class="cp_bullet ' + this.value() + '"><li>' + text + '</li></ul>' );
		}
		editor.addButton( 'bullets', {
			text: labels.bullets,
			type: 'menubutton',
			menu: [
				{ text : labels.green, value : 'green', onclick : add_bulletlist },
				{ text : labels.blue, value : 'blue', onclick : add_bulletlist },
				{ text : labels.orange, value : 'orange', onclick : add_bulletlist },
				{ text : labels.pink, value : 'pink', onclick : add_bulletlist },
				{ text : labels.purple, value : 'purple', onclick : add_bulletlist },
				{ text : labels.yellow, value : 'yellow', onclick : add_bulletlist },
				{ text : labels.red, value : 'red', onclick : add_bulletlist },
				{ text : labels.black, value : 'black', onclick : add_bulletlist }
			]
		} );
	} );
})();