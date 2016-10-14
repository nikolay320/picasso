(function($){
    SABAI.FieldUI = SABAI.FieldUI || {};
    SABAI.FieldUI.adminFields = function (messages) {
        var messages = $.extend({submitConfirm:'', leaveConfirm:'', deleteFieldConfirm:''}, messages),
            form_is_submitting = false,
            form_submit_timeout,
            available_fields_offset,
            _field_create = function(event) {
                var $this = $(this);
                SABAI.ajax({
                    type: 'get',
                    container: '#sabai-modal',
                    url: this.href + (this.href.indexOf('?', 0) === -1 ? '?' : '&') + '&field_id=' + $this.data('field-id'),
                    onError: function(error, target, trigger) {SABAI.flash(error.message, 'danger');},
                    onContent: function(response, target, trigger) {target.focusFirstInput();},
                    trigger: $this,
                    modalWidth: 600
                });
                return false;
            },
            _field_edit = function(event) {
                var $this = $(this), 
                    filter = $(this).closest('.sabai-fieldui-field'),
                    filter_id = filter.find('.sabai-fieldui-field-id').attr('value'),
                    filter_ele_id = filter.attr('id');
                SABAI.ajax({
                    type: 'get',
                    container: '#sabai-modal',
                    url: this.href + (this.href.indexOf('?', 0) === -1 ? '?' : '&') + 'ele_id=' + filter_ele_id + '&filter_id=' + filter_id,
                    onContent: function(response, target, trigger) {target.focusFirstInput();},
                    onError: function(error, target, trigger) {SABAI.flash(error.message, 'danger');},
                    trigger: $this,
                    modalWidth: 600
                });
                _select_field(filter);
                return false;
            },
            _field_delete = function(event) {
                var field = $(this).closest('.sabai-fieldui-field');
                _select_field(field);
                // Confirm deletion
                if (!confirm(messages.deleteFieldConfirm)) return false;

                // Is this field already saved?
                if (field.find('.sabai-fieldui-field-id').attr('value')) {
                    // Set timeout to submit form automatically 
                    form_submit_timeout = setTimeout(function(){
                        $('#sabai-fieldui').submit();
                    }, 2000);
                }
                // Fadeout
                field.fadeTo('fast', 0, function(){
                    $(this).slideUp('medium', function(){
                        $(this).remove();
                    });
                });
                return false;
            },
            _update_field = function (field, result) {
                if (!result.hide_label && result.label) {
                    field.find('> .sabai-fieldui-field-label').html(result.required ? result.label + '<span class="sabai-fieldui-field-required">*</span>' : result.label).show();
                } else {
                    field.find('> .sabai-fieldui-field-label').hide();
                }
                if (result.description) {
                    field.find('> .sabai-fieldui-field-description').html(result.description).show();
                } else {
                    field.find('> .sabai-fieldui-field-description').hide();
                }
                var field_title = result.label + ' - ' + result.name;
                field.find('> .sabai-fieldui-field-preview').html(result.preview).end()
                    .find('.sabai-fieldui-field-title').text(field_title).end()
                    .find('.sabai-fieldui-field-edit').attr('data-modal-title', field_title).end()
                    .effect('highlight', {}, 2000);
            },
            _select_field = function (field) {
                _deselect_selected_fields();
                field.addClass('sabai-fieldui-field-selected');
            },
            _deselect_selected_fields = function (force) {
                $('#sabai-fieldui').find('.sabai-fieldui-field-selected').removeClass('sabai-fieldui-field-selected');
            },
            sortable_conf = {
                items: '.sabai-fieldui-field',
                handle: '.sabai-fieldui-field-info',
                connectWith: '#sabai-fieldui-active .sabai-fieldui-fields',
                helper: 'clone',
                opacity: 0.8,
                cursor: 'move',
                placeholder: 'sabai-fieldui-field-placeholder',
                start: function(event,ui) {
                    _deselect_selected_fields();
                    ui.placeholder.width(ui.helper.outerWidth() - 2).height(ui.helper.outerHeight() - 2);
                    // Clear currently active timeout
                    if (form_submit_timeout) {
                        clearTimeout(form_submit_timeout);
                        form_submit_timeout = null;
                    }
                    $('#sabai-fieldui-active > div.sabai-row-fluid').addClass('sabai-fieldui-placeholder');
                },
                update: function(event, ui) {
                    ui.item.addClass('sabai-fieldui-moved');
                    // Set timeout to submit form automatically 
                    if (!form_submit_timeout) {
                        form_submit_timeout = setTimeout(function(){
                            $('#sabai-fieldui').submit();
                        }, 2000);
                    }
                },
                stop: function(event, ui) {
                    $('#sabai-fieldui-active > div.sabai-row-fluid').removeClass('sabai-fieldui-placeholder');
                }
            },
            _update_layout = function (event) {
                if (form_submit_timeout) {
                    clearTimeout(form_submit_timeout);
                    form_submit_timeout = null;
                }
                var url = SABAI.parseUrl(window.location.toString());
                url.query[this.name] = this.value;
                url.search = $.param(url.query);
                SABAI.ajax({
                    type: 'get',
                    container: '#sabai-content',
                    url: url.toString(),
                    onError: function(error, target, trigger) {SABAI.flash(error.message, 'danger');},
                    onContent: function(response, target, trigger) {SABAI.FieldUI.adminFields(messages);},
                    pushState: true
                });
                form_submit_timeout = setTimeout(function(){
                    $('#sabai-fieldui').submit();
                }, 2000);
                return false;
            };
        $('#sabai-fieldui-active > div.sabai-row-fluid').first().addClass('sabai-row-fluid-first');
        $('#sabai-fieldui-active > div.sabai-row-fluid').last().addClass('sabai-row-fluid-last');
        $('.sabai-fieldui-available.sabai-fieldui-fields').on('click', '.sabai-fieldui-content > a', _field_create);
        $('.sabai-fieldui-available.sabai-fieldui-layout').on('change', '.sabai-fieldui-content > select', _update_layout);
        // Init field controls
        $('.sabai-fieldui-field-control').on('click', '.sabai-fieldui-field-edit', _field_edit)
            .on('click', '.sabai-fieldui-field-delete', _field_delete);
        // Make fields sortable
        $('#sabai-fieldui-active .sabai-fieldui-fields').sortable(sortable_conf);
        // Field expand/collapse
        $('.sabai-fieldui-available > .sabai-fieldui-title').click(function() {
            var $this = $(this), $fields = $this.closest('.sabai-fieldui-available').find('.sabai-fieldui-content');
            if ($fields.is(':hidden')) {
                $fields.slideDown('fast');
                $this.find('a.sabai-fieldui-toggle i').removeClass('fa-caret-down').addClass('fa-caret-up');
            } else {
                $fields.slideUp('fast');
                $this.find('a.sabai-fieldui-toggle i').removeClass('fa-caret-up').addClass('fa-caret-down');
            }
            return false;
        });
        // Form submit callback
        $('#sabai-fieldui').submit(function() {
            var $form = $(this);
            _deselect_selected_fields(true);
            form_is_submitting = true;
            SABAI.ajax({
                type: $form.attr('method'),
                container: $form,
                url: $form.attr('action') + '&' + $form.serialize(),
                onSuccess: function (result, target, trigger) {
                    target.find('.sabai-fieldui-moved').removeClass('sabai-fieldui-moved');
                },
                onError: function(error, target, trigger) {SABAI.flash(error.message, 'danger');},
                loadingImage: false
            });
            return false;
        });
        // Alert user when leaving the page if new form fields or form layout have not been saved yet
        window.onbeforeunload = function() {
            if (form_is_submitting) {
                form_is_submitting = false; // reset
                return;
            }
            if ($('#sabai-fieldui').find('.sabai-fieldui-moved').length) {
                return messages.leaveConfirm;
            }
        };
        
        available_fields_offset = $('#sabai-fieldui-available-wrap').offset();
        $(window).scroll(function () {
            if($(window).scrollTop() > available_fields_offset.top - 40) {
                $('#sabai-fieldui-available-wrap').css({position:'fixed', top:'40px', left:available_fields_offset.left + 'px'});
            } else {
                $('#sabai-fieldui-available-wrap').css('position', 'static');
            }
        });
        $(window).resize(function () {
            $('#sabai-fieldui-available-wrap').css('position', 'static');
            available_fields_offset.left = $('#sabai-fieldui-available-wrap').offset().left;
            if($(window).scrollTop() > available_fields_offset.top - 40) {
                $('#sabai-fieldui-available-wrap').css({position:'fixed', top:'40px', left:available_fields_offset.left + 'px'});
            }
        });
        
        $(SABAI).bind('fieldui_filter_created.sabai', function(e, data){
            data.target.hide();
            var container = $('#sabai-fieldui-active').find('.sabai-fieldui-fields').first();
            if (!container.length) return;
            var field = $('#sabai-fieldui-field')
                .clone(true)
                .attr('id', 'sabai-fieldui-field' + data.result.id)
                .appendTo(container)
                .find('.sabai-fieldui-field-id').attr('value', data.result.id).end()
                .find('.sabai-fieldui-field-help').remove().end()
                .addClass('sabai-fieldui-field-type-' + data.result.type_normalized)
                .show();
            SABAI.scrollTo(field);
            _update_field(field, data.result);
            $('#sabai-fieldui').submit();
            _select_field(field);
        });
        
        $(SABAI).bind('fieldui_filter_updated.sabai', function(e, data){
            data.target.hide();
            var field = $('#' + data.result.ele_id);
            SABAI.scrollTo(field);
            _update_field(field, data.result);
        });
    };    
})(jQuery);