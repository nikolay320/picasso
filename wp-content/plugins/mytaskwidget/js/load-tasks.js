var current_active_task_type = "";
jQuery('body').on('click', '.sl_link_box a', function(){
	boxval = jQuery(this).data('task');
	current_active_task_type = boxval;
	jQuery.ajax({
		type: "POST",
		url: js_site_url + "wp-content/plugins/mytaskwidget/lib/get-projects.php",
		data: { ptype:boxval },
		context: document.body
	}).done(function(itemlst) {
		jQuery('#SL_PRJ_LIST').html(itemlst);
	});
});

function refreshTaskWidget() {
	boxval = current_active_task_type;
	jQuery.ajax({
		type: "POST",
		url: js_site_url + "wp-content/plugins/mytaskwidget/lib/refresh-widget.php",
		context: document.body
	}).done(function(wgdata) {
		jQuery('.sl_link_box').html(wgdata);
		jQuery.ajax({
			type: "POST",
			url: js_site_url + "wp-content/plugins/mytaskwidget/lib/get-projects.php",
			data: { ptype:boxval },
			context: document.body
		}).done(function(itemlst) {
			jQuery('#SL_PRJ_LIST').html(itemlst);
		});
	});
}

jQuery('.sl-cpm-todo-openlist input[type="checkbox"].cpm-uncomplete').live('change', function(){
	var self = jQuery(this);
	data = {
            task_id: self.val(),
            project_id: self.data('project'),
            list_id: self.data('list'),
            single: self.data('single'),
            action: self.data('action'),
            '_wpnonce': CPM_Vars.nonce,
            is_admin : self.data('is_admin')
        };
	jQuery.post(CPM_Vars.ajaxurl, data, function (res) {
        //res = JSON.parse(res);
		//console.log(res);
		refreshTaskWidget()
    });
});

jQuery('.sl-cpm-todo-openlist input[type="checkbox"].cpm-completed').live('change', function(){
	var self = jQuery(this);
	data = {
            task_id: self.val(),
            project_id: self.data('project'),
            list_id: self.data('list'),
            single: self.data('single'),
            action: self.data('action'),
            '_wpnonce': CPM_Vars.nonce,
            is_admin : self.data('is_admin')
        };
	
	jQuery.post(CPM_Vars.ajaxurl, data, function (res) {
        //res = JSON.parse(res);
		//console.log(res);
		refreshTaskWidget()
    });
});

jQuery('span.close-wg-box').live('click', function(){
	jQuery('#SL_PRJ_LIST').html('');
});

