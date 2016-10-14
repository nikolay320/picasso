var SABAI = SABAI || {};

(function($) {    
    SABAI.console = window.console || {'log': function(msg) {
        alert(msg);
    }};
    SABAI.isRTL = false;
    SABAI.init = (function() {
        var _initFadeout = function(context) {
                SABAI.fadeout($('.sabai-fadeout', context));   
            },
            _initCheckall = function(context) {
                // Highlight related table rows when a checkall checkbox is checked
                $('input.sabai-form-check-trigger', context).show().click(function() {
                    var $this = $(this);
                    $this.closest('table').find('input.sabai-form-check-target, input.sabai-form-check-trigger').not(':disabled')
                        .prop('checked', $this.prop('checked'));
                });
            },
            _initFormCollapsible = function(context) {
                // Collapse collapsible form elements
                $('fieldset.sabai-form-collapsible', context).not('.sabai-form-collapsible-processed').each(function() {
                    var $this = $(this);
                    $this.find('> legend span:first').prepend('<i class="fa fa-caret-down"></i> ').wrap('<a class="sabai-form-collapsible" href="#"></a>').end()
                        .find('a.sabai-form-collapsible:first').click(function() {
                            $(this).toggleClass('sabai-form-collapsed')
                                .find('i').toggleClass('fa-caret-down').toggleClass(SABAI.isRTL ? 'fa-caret-left' : 'fa-caret-right').end()
                                .closest('.sabai-form-collapsible-processed').toggleClass('sabai-form-collapsed')
                                .find('.sabai-form-fields:first').slideToggle('fast', function() {
                                    $(this).find('textarea:visible').autosize();
                                });
                            return false;
                        }).end()
                        .addClass('sabai-form-collapsible-processed');
                    if ($this.hasClass('sabai-form-collapsed')) {
                        if ($this.hasClass('sabai-form-field-error')) {
                            // Do not collapse elements with error
                            $this.removeClass('sabai-form-collapsed');
                        } else {
                            $this.find('.sabai-form-fields:first').css({display:'none'}).end()
                                .find('a.sabai-form-collapsible:first').addClass('sabai-form-collapsed')
                                .find('i').removeClass('fa-caret-down').addClass(SABAI.isRTL ? 'fa-caret-left' : 'fa-caret-right');
                        }
                    }
                });
            },
            _initElasticTextarea = function(context) {
                $('textarea:visible', context).autosize();
            },
            _initTooltip = function(context) {
                var hasTouch = 'ontouchstart' in document.documentElement;
                if (!hasTouch) {                
                    $('[rel="sabaitooltip"]', context).each(function(){
                        var $this = $(this),
                            container = context.attr('id') === 'sabai-content' && $this.closest('#sabai-inline-content').length
                                ? $this.closest('#sabai-inline-content')
                                : $this.data('container') || context;
                        $this.sabaitooltip({container: container});
                    });
                }
                $('a[data-popover-url]', context).bind('click', function(e) {
                    var $this = $(this).unbind('hover'), content = $this.attr('data-content'), container;
                    e.preventDefault();
                    container = context.attr('id') === 'sabai-content' && $this.closest('#sabai-inline-content').length
                        ? $this.closest('#sabai-inline-content')
                        : $this.data('container') || context;
                    if (typeof content === 'undefined' || content === false) {
                        var url = $this.data('popover-url'), cache = SABAI.cache(url), placement;
                        if (SABAI.isRTL) {
                            placement= function (pop, ele) { return $(ele).offset().left > 300 ? 'left' : 'right'};
                        } else {
                            placement= function (pop, ele) { return window.innerWidth - $(ele).offset().left > 300 ? 'right' : 'left';};
                        }
                        if (!cache) {
                            $.get(url, {'__ajax': 1}, function(data) {
                                SABAI.cache(url, data);
                                SABAI.popover($this.attr('data-content', data), {html: true, container: container, placement: placement});
                            });
                        } else {
                            SABAI.popover($this.attr('data-content', cache), {html: true, container: container, placement: placement});
                        }
                    }
                });
            },
            _initToggle = function (context) {
                $('a.sabai-toggle', context).not('.sabai-toggle-processed').unbind('click').click(function (e) {
                    var $this = $(this).addClass('sabai-toggle-processed'), target = $($this.data('toggle-target'));
                    if (!$.trim(target.html())) return;
            
                    $(SABAI).trigger('toggle.sabai', {trigger: $this, target: target});
                    var cookie_name = $this.data('toggle-cookie'), toggle_method = $this.hasClass('sabai-toggle-slide') ? 'slideToggle' : 'toggle';
                    target[toggle_method]('fast', function () {
                        var is_visible = target.is(':visible');
                        $this.toggleClass('sabai-active', is_visible);
                        if (cookie_name) {
                            $.cookie(cookie_name, is_visible ? 1 : 0, {path: SABAI.path, domain: SABAI.domain});
                        }
                        $(SABAI).trigger('toggled.sabai', {trigger: $this, target: target});
                    });
                    e.preventDefault();
                });
            },
            _initCarousels = function (context) {
                $('.sabai-carousel', context).each(function () {
                    var $this = $(this);
                    $this.data('carousel', $this.bxSlider($this.data('carousel-options') || {}));
                    $(SABAI).bind('entity_filter_form_toggled.sabai', function (e, data) {
                        if ($this.parents(data.container).length) {
                            $this.data('carousel').redrawSlider();
                        }
                    });
                });
            },
            _initFormFieldButtons = function (context) {
                $('a.sabai-form-field-add', context).each(function () {
                    var $this = $(this), $container = $this.closest('.sabai-form-fields'), maxNum = parseInt($this.data('field-max-num'));
                    $this.click(function (e) {
                        var nextIndex = $this.data('field-next-index');
                        SABAI.cloneField($container, $this.data('field-name'), maxNum, nextIndex, $this);
                        if (nextIndex) $this.data('field-next-index', ++nextIndex);
                        e.preventDefault();
                    });
                    $container.find('> .sabai-form-field:not(.sabai-form-field-add)').each(function(i){
                        if (i === 0) return;
                        $(this).append('<a class="sabai-btn sabai-btn-danger sabai-btn-xs sabai-form-field-remove" href="#"><i class="fa fa-times" title="Remove this field"></i></a>')
                            .find('a.sabai-form-field-remove')
                            .click(function(e){
                                $(this).closest('.sabai-form-field').fadeTo('fast', 0, function(){
                                    $(this).slideUp('fast', function(){
                                        $(this).remove();
                                        if (maxNum && $container.find('> .sabai-form-field:not(.sabai-form-field-add)').length < maxNum) {
                                            $this.show();
                                        }
                                    });
                                });
                                e.preventDefault();
                            })
                            .parent().css('position', 'relative');
                    });
                    if (maxNum && $container.find('> .sabai-form-field:not(.sabai-form-field-add)').length >= maxNum) {
                        $this.hide();
                    }
                });
            };
            
        return function(context, callback) {
            _initFadeout(context);
            _initCheckall(context);
            _initFormCollapsible(context);
            _initElasticTextarea(context);
            _initTooltip(context);
            _initToggle(context);
            _initCarousels(context);
            _initFormFieldButtons(context);
            // Init prettyPrint
            if (typeof prettyPrint === 'function') {
                prettyPrint();
            }
            // Init prettyPhoto
            if (typeof $.fn.prettyPhoto === 'function') {
                $('a[rel^="prettyPhoto"]', context).prettyPhoto();
            }
            // Init bootstrap dropdown
            $('.sabai-dropdown-toggle', context).sabaidropdown();
            // Click 
            $('.sabai-click', context).click();
            
            if (callback) callback.call(null, context);
            
            $(SABAI).trigger('sabai_init.sabai', {context: context});
        };
    }());
    
    SABAI.fadeout = function(selector, timer) {
        timer = timer || 6000;
        // Apply fadeout effect but cancel the effect when hovered
        $(selector).animate({opacity: '+=0'}, timer, function() {
            $(this).fadeOut('fast', function() {
                $(this).remove();
            });
        });
    };
    
    SABAI.cache = (function() {
        var _cache = {};
        return function(id, data, lifetime) {
            if (arguments.length == 1) {
                if (!_cache[id]) {
                    return false;
                }
                if (_cache[id]['expires'] < new Date().getTime()) {
                    return false;
                }
                return _cache[id]['data'];
            }
            lifetime = lifetime || 600;
            _cache[id] = {
                data: data,
                expires: new Date().getTime() + lifetime * 1000
            };
        };
    }());
    
    SABAI.flash = function(message, type) {
        if (typeof message === 'undefined' || message === null) {
            return;
        }
        if (typeof(message) == 'string') {
            $.growl(message, {type: type, delay: type === 'danger' ? 0 : 5000, z_index: 999999});
        } else {
            for (var i = 0; i < message.length; i++) {
                SABAI.flash(message[i].msg, message[i].level);
            }
        }
    };
    
    SABAI.load = function(selector, url, complete) {
        var $target = $(selector);
        $target.load(url, {'__ajax': selector}, function(response, status, xhr) {
            SABAI.init($target);
            if (complete) {
                complete.call($target, response, status, xhr);
            }
        });
        return $target;
    };
    
    SABAI.replace = function(selector, url, complete, inside) {
        var $target;
        $.get(url, {'__ajax': selector}, function(response, status, xhr) {
            if (inside) {
                $target = $(selector).html(response);
            } else {
                $(selector).replaceWith(response);
                // Reload with selector since replaceWith returns the removed DOM
                $target = $(selector);
            }
            SABAI.init($target);
            if (complete) {
                complete.call(null, $target, response, status, xhr);
            }
        });
        return $target;
    };
    
    SABAI.popover = function(target, options, force) {
        target = target instanceof jQuery ? target : $(target);
        if (!force && target.hasClass('sabai-popover-processed')) return;
        options = options || {};
        options.template = '<div class="sabai-popover"><div class="sabai-arrow"></div><div class="sabai-popover-inner"><div class="sabai-close"><i class="fa fa-times"></i></div><div class="sabai-popover-title"></div><div class="sabai-popover-content"></div></div></div>';
        target.sabaipopover(options)
            .sabaipopover('show')
            .addClass('sabai-popover-processed')
            .data('bs.sabaipopover')
            .tip()
            .css(options.width ? {width: options.width} : {})
            .find('.sabai-close')
            .on('click', function(){target.data('bs.sabaipopover').hide();});
            
        if (!SABAI.popoverInit) {
            $('body').on('click', function (e) {
                $('.sabai-popover-processed').each(function () {
                    //the 'is' for buttons that trigger popups
                    //the 'has' for icons within a button that triggers a popup
                    if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.sabai-popover').has(e.target).length === 0) {
                        $(this).sabaipopover('hide');
                    }
                });
            });
        }
    }
    
    SABAI.modal = (function() {
        var _modal,
            _createModal = function() {
                var modal = $('<div class="sabai" id="sabai-modal" style="display:none;"><div class="sabai-modal-container">'
                    + '<div class="sabai-modal-title"><span></span><span class="sabai-close"><i class="fa fa-times"></i></span></div>'
                    + '<div class="sabai-modal-content"></div>'
                    + '<div class="sabai-modal-footer"></div>'
                    + '</div></div>').prependTo('body')
                    .find('.sabai-close').mousedown(function() {
                        $('#sabai-modal').fadeOut('fast', function() {
                            $(this).remove();
                        });
                    }).end();
                // Close modal if ESC is pressed
                $(document).keyup(function(e) {
                    if (e.keyCode == 27) {
                        $('#sabai-modal').find('.sabai-close').mousedown();
                    }
                });
                return modal;
            },
            _resizeModal = function(modal, width) {
                var modal_max_height,
                    modal_footer_height,
                    modalPercentage = document.documentElement.clientWidth <= 768 ? 0.95 : 0.8;
                if (width === null || width > document.documentElement.clientWidth * modalPercentage) {
                    width = document.documentElement.clientWidth * modalPercentage;
                }
                modal.find('.sabai-modal-container').css({
                    width: width + 'px',
                    left: SABAI.isRTL ? 'auto' : document.documentElement.clientWidth/2 - width/2, // position at the center
                    right: SABAI.isRTL ? document.documentElement.clientWidth/2 - width/2 : 'auto', // position at the center
                    top: document.documentElement.clientHeight * 0.07
                });
                if (modal.find('.sabai-form-buttons').length) {
                    modal_footer_height = modal.find('.sabai-form-buttons').outerHeight() + 10;
                } else {
                    modal_footer_height = 20;
                }
                modal.find('.sabai-modal-footer').css('height', modal_footer_height + 'px');
                // Set the maximum height of modal content
                modal_max_height = document.documentElement.clientHeight * 0.8
                    - modal.find('.sabai-modal-title').outerHeight()
                    - modal_footer_height;
                modal.find('.sabai-modal-content').css('max-height', modal_max_height + 'px');
                // Set the height to maximum height if larger than the client height
                if (modal.get(0).scrollHeight > document.documentElement.clientHeight) {
                    modal.find('.sabai-modal-content').css('height', modal_max_height + 'px');
                }
            }; 
        
        return function(content, title, width, modal) {
            if (modal) {
                _modal = modal;
            } else if ($('body').has('#sabai-modal').length) {
                _modal = $('#sabai-modal');
            } else {
                _modal = _createModal();
            }
            if (title || content) {
                // Show modal
                if (title) {
                    _modal.find('.sabai-modal-title > span:first').text(title);
                }
                if (content) {
                    _modal.find('.sabai-modal-content').html(content);
                }
                _modal.show();
            }
            if (typeof width !== 'undefined') {
                if (width === 0) {
                    width = _modal.find('.sabai-modal-container').width();
                }
                _resizeModal(_modal, width);
            }
            return _modal;
        };
    }());
    
    SABAI.ajaxLoader = function(trigger, remove, target) {
        var $trigger = $(trigger);
        if (target) {
            var $target = $(target);
            if (!$target.length) return;
            if (!remove) {
                var ajaxloader = $('<div class="sabai-ajax-loader"></div>')
                    .css('top', parseInt($target.position().top, 10) + parseInt($target.css('margin-top'), 10) + 'px')
                    .width($target.outerWidth())
                    .height($target.outerHeight());
                $target.after(ajaxloader);
            } else {
                $target.next('.sabai-ajax-loader').remove();
            }
            if ($trigger.length) $trigger.blur().prop('disabled', !remove).css('pointer-events', remove ? 'auto' : 'none');
        } else {
            if (!$trigger.length) return;
            $trigger.blur().prop('disabled', !remove).css('pointer-events', remove ? 'auto' : 'none').toggleClass('sabai-ajax-loading', !remove);
        }
    }
    
    SABAI.ajax = function(options) {
        var o = $.extend({
                trigger: null,
                async: true,
                type: 'get',
                url: '',
                data: '',
                processData: true,
                target: '',
                container: null,
                modalWidth: null,
                cache: false,
                cacheLifetime: 600,
                onSendData: null,
                onSuccess: null,
                onError: null,
                onErrorFlash: true,
                onContent: null,
                onSuccessFlash: false,
                effect: null,
                scroll: false,
                replace: false,
                highlight: false,
                callback: false,
                loadingImage: true,
                position: false,
                toggle: false,
                pushState: false,
                state: {}
            }, options),
            target,
            targetSelector = '',
            overlay,
            _handleSuccess = function(response, target) {
                try {
                    var result = JSON.parse(response.replace(/<!--[\s\S]*?-->/g, ''));
                    if (o.onSuccess) {
                        if (!o.onSuccess(result, target, o.trigger)) {
                            if (o.onSuccessFlash && result.messages) {
                                SABAI.flash(result.messages, 'success');
                            }
                            return; // returning null or false means not to load content from URL or redirect
                        }
                    }
                    if (result.url) {
                        if (o.container === '#sabai-modal') {
                            $('#sabai-modal').hide();
                        }
                        window.location = result.url;
                        return;
                    }
                    if (o.onSuccessFlash && result.messages) {
                        SABAI.flash(result.messages, 'success');
                    }
                } catch (e) {
                    SABAI.console.log('Failed parsing response:<p>' + response.toString().replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>' + '<p>' + e.toString() + '</p>');
                }
            },
            _handleContent = function(response, target) {
                if (o.container === '#sabai-modal') {
                    if (o.trigger) {
                        var modalTitle = o.trigger.attr('data-modal-title');
                        if (typeof modalTitle === 'undefined' || modalTitle === false) {
                            modalTitle = o.trigger.attr('title') || o.trigger.attr('data-original-title') || '';
                        }
                        SABAI.modal(response, modalTitle, o.modalWidth, target);
                    } else {
                        SABAI.modal(response, '', o.modalWidth, target);
                    }
                    if (!o.onContent) {
                        o.onContent = function(response, target, trigger){target.focusFirstInput();};
                    }
                    o.onContent(response, target, o.trigger);
                } else {            
                    if (o.trigger && o.trigger.hasClass('sabai-toggle')) {
                        $(SABAI).trigger('toggle.sabai', {trigger: o.trigger, target: target});
                    }
                    if (o.replace) {
                        // Scroll to the updated content? We need to scroll before replace otherwise scroll target will not exist. 
                        if (o.scroll && targetSelector) {
                            SABAI.scrollTo(targetSelector);
                        }
                        // For now, no effect when replacing
                        target = target.hide().after(response).remove().next();
                        if (o.onContent) {
                            o.onContent(response, target, o.trigger);
                        }
                    } else {
                        if (!o.callback && target.attr('id') != 'sabai-content' && target.attr('id') != 'sabai-inline-content') {
                            target.addClass('sabai-ajax');
                        }
                
                        // Effect
                        switch (o.effect) {
                            case 'slide':
                                target.hide().html(response).slideDown("fast", function () {
                                    if (o.onContent) {
                                        o.onContent(response, target, o.trigger);
                                    }
                                });
                                break;
                            default:
                                target.html(response).show();
                                if (o.onContent) {
                                    o.onContent(response, target, o.trigger);
                                }
                        }
                        // Scroll to the updated content?
                        if (o.scroll && targetSelector) {
                            SABAI.scrollTo(targetSelector);
                        }
                    }
                    if (o.highlight) {
                        target.effect('highlight', {}, 1500);
                    } 
                    
                    if (o.trigger && o.trigger.hasClass('sabai-toggle')) {   
                        var is_visible = target.is(':visible');
                        o.trigger.removeAttr('onclick').removeClass('sabai-click').toggleClass('sabai-active', is_visible);
                        var cookie_name = o.trigger.data('toggle-cookie'); 
                        if (cookie_name) {
                            $.cookie(cookie_name, is_visible ? 1 : 0, {path: SABAI.path, domain: SABAI.domain});
                        }
                        $(SABAI).trigger('toggled.sabai', {trigger: o.trigger, target: target});                                
                    }
                }

                if (o.pushState && window.history && window.history.pushState) {
                    var push_url = SABAI.parseUrl(o.url);
                    if (push_url.pathname !== location.pathname) {
                        push_url.pathname = location.pathname;
                    }
                    o.state.data = o.data;
                    o.state.url = o.url;
                    o.state.container = o.container;
                    o.state.target = o.target;
                    window.history.pushState(o.state, null, push_url.toString());
                }
                
                SABAI.init(target);
                
                $(SABAI).trigger("loaded.sabai", {target: target, selector: targetSelector});
            },
            _handleError = function(response, target) {
                try {
                    var error = JSON.parse(response.replace(/<!--[\s\S]*?-->/g, ''));
                    if (o.onError) { 
                        if (!o.onError(error, target, o.trigger)) {
                            if (o.onErrorFlash && error.messages) {
                                SABAI.flash(error.messages, 'danger');
                            }
                            return; // returning null or false means not to load content from URL or redirect
                        }
                    } else if (error.url) {
                        window.location = error.url;
                        return;
                    }
                    if (o.onErrorFlash && error.messages) {
                        if (o.trigger) {
                            SABAI.popover(o.trigger, {
                                content: error.messages[0],
                                html: true,
                                container: o.trigger.closest('.sabai'),
                                title: o.trigger.attr('data-sabaipopover-title') || ''
                            });
                            o.trigger.attr('onclick', 'return false;');
                        } else {
                            SABAI.flash(error.messages, 'danger');
                        }
                    }
                } catch (e) {
                    SABAI.console.log('Failed parsing response:<p>' + response.toString().replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</p>' + '<p>' + e.toString() + '</p>');
                }
            };
        if (o.trigger) {
            if (o.trigger.hasClass('sabai-disabled')) {
                return;
            }
            // set cookie if any
            if (o.trigger.data('cookie-name') && o.trigger.data('cookie-value')) {
                $.cookie(o.trigger.data('cookie-name'), o.trigger.data('cookie-value'), {path: SABAI.path, domain: SABAI.domain});
            }
            if (!o.url) o.url = o.trigger.data('sabai-remote-url');
            if (o.trigger.hasClass('sabai-dropdown-link')) {
                o.trigger = o.trigger.closest('.sabai-btn-group').find('.sabai-dropdown-toggle');
            }
        }
        if (!o.url) return;
        if (o.container) {
            targetSelector = o.container;
            if (o.container === '#sabai-modal') {
                target = SABAI.modal();
            } else {
                if (o.target) {
                    target = $(o.container).find(o.target);
                    targetSelector = o.container + ' ' + o.target;
                } else {
                    target = $(o.container);
                }
                if (!target.length) {SABAI.console.log(targetSelector); return;}
            }
        }
        if (o.cache && o.type === 'get') {
            var cached = SABAI.cache(o.container + o.url);
            if (cached) {
                _handleContent(cached, target);
                return;
            }
        }
        if (o.onSendData) {
            if (typeof o.data !== 'object') {
                o.data = {};
            }
            o.onSendData(o.data, o.trigger);
        }
        if (typeof o.data === 'object') {
            if (!o.data.hasOwnProperty('__ajax')) {
                o.data['__ajax'] = targetSelector || 1;
            }
            o.data = $.param(o.data);
        } else if (typeof o.data === 'string' && o.data !== '') {
            o.data += '&__ajax=' + (targetSelector ? encodeURIComponent(targetSelector) : 1);
        } else {
            o.data = '__ajax=' + (targetSelector ? encodeURIComponent(targetSelector) : 1);
        }
        $.ajax({
            global: true,
            async: o.async,
            type: o.type,
            dataType: 'html',
            url: o.url,
            data: o.data,
            processData: o.processData,
            cache: false,
            beforeSend: function(xhr) {
                if (!o.loadingImage) return;
                // displya ajax loading image
                if (target && target.attr('id') !== 'sabai-content' && target.is(':visible')) {
                    overlay = target.attr('id') === 'sabai-modal' ? target.find('.sabai-modal-content') : target;
                    SABAI.ajaxLoader(o.trigger, false, overlay);
                } else {
                    SABAI.ajaxLoader(o.trigger);
                }
            },
            complete: function(xhr, textStatus) {
                switch (textStatus) {
                    case 'success':
                        if (xhr.status == 278 || xhr.getResponseHeader('content-type').indexOf('json') > -1) {
                            // Sabai response was success
                            _handleSuccess(xhr.responseText, target);
                        } else {
                            // Sabai response was HTML content
                            _handleContent(xhr.responseText, target);
                            if (o.type == 'get') {
                                if (o.cache) {
                                    SABAI.cache(o.container + o.url, xhr.responseText, o.cacheLifetime);
                                }
                            }
                        }
                        break;
                    case 'error':
                        _handleError(xhr.responseText, target);
                        break;
                }
                if (o.loadingImage) {
                    SABAI.ajaxLoader(o.trigger, true, overlay);
                }
            }
        });
    };
    
    SABAI.scrollTo = function(target, duration, offset) {
        target = target instanceof jQuery ? target : $(target);
        duration = typeof duration !== 'undefined' && duration !== null ? duration : 1000;
        offset = typeof offset !== 'undefined' && offset !== null ? offset : 0;
        $.sabaiScrollTo(target, duration, {offset: {top: offset}});
    };
    
    SABAI.states = function(states, context, isClonedField) {
        var initial_triggers = {},
            inverted_actions = {
                'enabled': 'disabled',
                'optional': 'required',
                'visible': 'invisible',
                'unchecked': 'checked',
                'expanded': 'collapsed',
                'unload_options': 'load_options',
                'show_options': 'hide_options'
            },
            _addRule = function(selector, action, conditions, context) {
                var $dependent = $(selector, context);
                if (!$dependent.length) {
                    return;
                }
                
                $dependent.each(function(){
                    var dependee, $dependee, condition, events, event_data;
                    for (dependee in conditions) {
                        condition = conditions[dependee];
                        $dependee = $(this).closest(condition['container'] || 'form')
                            .find(dependee);
                        if (!$dependee.length) {
                            SABAI.console.log('Invalid or non existent dependee selector: ' + dependee);
                            continue;
                        }
                        events = [];
                        switch (condition['type']) {
                            case 'checked':
                            case 'unchecked':
                            case 'selected':
                            case 'unselected':
                                events.push('change', 'cloneremoved.sabai');
                                break;
                            case 'filled':
                            case 'empty':
                                events.push('keyup', 'cloneremoved.sabai');
                                break;
                            case 'request':
                            case 'requested':
                                events.push('requested.sabai');
                                break;
                            case 'values':
                            default: // default type is "value"
                                events.push('keyup', 'change', 'cloneremoved.sabai');
                        }
                        initial_triggers[dependee] = {};
                        for (var i = 0; i < events.length; i++) {
                            event_data = {selector: selector, action: action};
                            event_data['conditions'] = conditions;
                            event_data['container'] = condition['container'] || 'form';
                            $dependee.bind(events[i], event_data, function(e, isInit) {
                                _applyRule($(this), e.data.selector, e.data.action, e.data.conditions, e.data.container, e.type, isInit);				
                                e.stopPropagation();
                            });
                            initial_triggers[dependee][events[i]] = true;
                        }
                    }
                });
            },
            _applyRule = function($dependee, dependent, action, conditions, container, event, isInit) {  
                var flag = true, _dependee, $_dependee, condition, $container = $dependee.closest(container);
                for (_dependee in conditions) {
                    $_dependee = $container.find(_dependee);
                    if (!$_dependee.length) {
                        // Invalid dependee selector
                        flag = false;
                        break;
                    }
            
                    condition = conditions[_dependee];
                    if (!_isConditionMet($_dependee, condition['type'] || 'value', condition['value'])) {
                        flag = false;
                        break;
                    }
                }
                if (action in inverted_actions) {
                    action = inverted_actions[action];
                    flag = !flag;
                }
                _doAction($container.find(dependent), action, flag, $dependee, event, isInit, dependent);
            },
            _isConditionMet = function($dependee, type, value) {
                switch (type) {
                    case 'value':
                    case 'values':
                        if (typeof value !== 'object') {
                            // convert to an array
                            value = [value];
                        }
                        var dependee_val = [];
                        if ($dependee.length > 1) {
                            $dependee.each(function(){
                                if (this.type === 'checkbox' || this.type === 'radio') {
                                    if (this.checked) {
                                        dependee_val.push(this.value);
                                    }
                                } else {
                                    dependee_val.push($(this).val());
                                }
                            });
                        } else {
                            dependee_val.push($dependee.val());
                        }
                        var value_length = value.length, dependee_val_length = dependee_val.length;                        
                        loop1:
                        for (var i = 0; i < value_length; i++) {
                            loop2:
                            for (var j = 0; j < dependee_val_length; j++) {
                                if (typeof value[i] !== 'object') {
                                    if (value[i] == dependee_val[j]) {
                                        if (type === 'value') return true;
                                        continue loop1;
                                    }
                                } else {
                                    if (_compare(value[i][0], dependee_val[j], value[i][1])) {
                                        if (type === 'value') return true;
                                        continue loop1;
                                    }
                                }
                            }
                            if (type === 'values') return false;
                        }
                        return type === 'values' ? true : false;
                    case 'checked':
                    case 'unchecked':
                        var result = false;
                        $dependee.each(function(){
                            if ($(this).prop('checked') === Boolean(value)) {
                                result = true;
                                return false; // breaks each()
                            }
                        });
                        return type === 'checked' ? result : !result;
                    case 'empty':
                    case 'filled':
                    case 'selected':
                        var result = false;
                        $dependee.each(function(){
                            if (($.trim($dependee.val()) === '') === Boolean(value)) {
                                result = true;
                                return false; // breaks each()
                            }
                        });
                        return type === 'empty' ? result : !result;
                    case 'collapsed':
                    case 'expanded':
                        var result = false;
                        $dependee.each(function(){
                            if (($dependee.hasClass('sabai-form-collapsible') && $dependee.hasClass('sabai-form-collapsed')) === Boolean(value)) {
                                result = true;
                                return false; // breaks each()
                            }
                        });
                        return type === 'collapsed' ? result : !result;
                    case 'request':
                        return $dependee.data('request-url') && $dependee.data('request-result') === value;
                    case 'requested':
                        if ($dependee.data('request-url')) {
                            var result = $dependee.data('request-result');
                            return (result === 'success' || result === 'error') === Boolean(value);
                        }
                        return false;
                    default:
                        alert('Invalid condition type: ' + type);
                        return false;
                }
            },
            _compare = function(a, b, opr) {
                switch (opr) {
                    case '==':
                        return a == b;
                    case '!=':
                        return a != b;
                    case '<':
                        return a < b;
                    case '>':
                        return a > b;
                }
            },
            _doAction = function($dependent, action, flag, $dependee, event, isInit, selector) {
                switch (action) {
                    case 'disabled':
                        $dependent.find(':input').prop('disabled', flag);
                        break;
                    case 'required':
                        $dependent.toggleClass('sabai-form-field-required', flag);
                        break;
                    case 'invisible':
                        $dependent.toggleClass('sabai-form-states-invisible', flag);
                        if (flag) {
                            $dependent.hide();
                            if (isInit) {
                                //$dependent.prev('.sabai-form-field').addClass('sabai-form-field-no-margin');
                            } else {
                                $dependent.prev('.sabai-form-field')
                                    .parent()
                                    .find('.sabai-form-field:visible:last')
                                    .addClass('sabai-form-field-no-margin');
                            }
                        } else if ($dependent.is(':hidden')) {
                            if (isInit) {
                                $dependent.show();
                            } else {
                                $dependent.css('opacity', 0)
                                    .slideDown('fast')
                                    .animate(
                                        {opacity: 1},
                                        {queue: false, duration: 'slow'}
                                    )
                                    .prev('.sabai-form-field').removeClass('sabai-form-field-no-margin');
                            }
                        }
                        break;
                    case 'checked':
                        $dependent.find(':checkbox').prop('checked', flag).change();
                        break;
                    case 'load_options':
                        if (event !== 'change') return;
                        
                        var dropdown = $dependent.find('select');
                        dropdown.find('option[value!=""]').remove();
                        var do_trigger = initial_triggers[selector + ' select'] && initial_triggers[selector + ' select'].change;
                        if (flag) {
                            var url = dropdown.data('load-url'),
                                cacheId = url + $dependee.val(),
                                data = SABAI.cache(cacheId),
                                success = function (data) {
                                    SABAI.cache(cacheId, data);
                                    if (typeof data !== 'undefined' && data.length === 0) {
                                        $dependent.hide();
                                        // clear default value and trigger change event
                                        dropdown.data('default-value', '').val('');
                                        if (do_trigger) dropdown.trigger('change', [isInit]);
                                        return;
                                    }
                                    $.each(data, function (index, val) {
                                        dropdown.append($('<option></option>').text(val[1]).val(val[0]));
                                    });
                                    if (!isClonedField) {
                                        var default_value = dropdown.data('default-value');
                                        if (typeof default_value != 'undefined') {
                                            dropdown.val(default_value);
                                            if (do_trigger) dropdown.trigger('change', [isInit]);
                                        }
                                    }
                                    if (!$dependent.hasClass('sabai-form-states-invisible')
                                        && $dependent.is(':hidden')
                                        && dropdown.find('option[value!=""]').length
                                    ) {
                                        $dependent.removeClass('sabai-hidden').addClass('sabai-was-hidden');
                                        var display = $dependent.find('.sabai-form-field-prefix').length ? 'inline-block' : 'block';
                                        if (isInit) {
                                            $dependent.css('display', display);
                                        } else {
                                            $dependent.hide().css('display', display).fadeIn('fast');
                                        }
                                    }
                                };
                            if (data !== false) {
                                success(data);
                            } else {
                                $dependee.addClass('sabai-ajax-loading');
                                $.getJSON(url, {value: $dependee.val()}, success).always(function() {
                                    $dependee.removeClass('sabai-ajax-loading');
                                });;
                            }
                        } else {
                            if (!$dependent.is(':hidden') && $dependent.hasClass('sabai-was-hidden')) {
                                $dependent.hide();
                                // clear default value and trigger change event
                                dropdown.data('default-value', '').val('');
                                if (do_trigger) dropdown.trigger('change', [isInit]);
                            }
                        }
                        break;
                    case 'request_url':
                        if (flag) {
                            SABAI.ajax({
                                target: $dependent,
                                url: $dependent.data('request-url'),
                                onSuccess: function(result, target, trigger){$dependent.data('request-result', 'success').trigger('requested.sabai');},
                                onError: function(result, target, trigger){$dependent.data('request-result', 'error').trigger('requested.sabai');},
                                onContent: function(result, target, trigger){$dependent.data('request-result', 'error').trigger('requested.sabai');}
                            });
                        } else {
                            $dependent.data('request-result', '');
                        }
                        break;
                    case 'collapsed':
                        if (!$dependent.hasClass('sabai-form-collapsible')) {
                            return; // not collapsible
                        }
                        if ((flag && !$dependent.hasClass('sabai-form-collapsed'))
                            || (!flag && $dependent.hasClass('sabai-form-collapsed'))
                        ) {
                            $dependent.find('a.sabai-form-collapsible:first').click();
                        }
                        break;
                    case 'hide_options':
                        if (!flag || !$dependee.prop('checked')) return;
                        $dependent.find('input').each(function(){
                            var $this = $(this), values = $this.data('values'), field = $this.closest('.sabai-form-field');
                            field[values && (-1 !== $.inArray($dependee.val(), values)) ? "slideDown" : "hide"]();
                        });
                        break;
                    default:
                        alert('Invalid action: ' + action);
                }
            },
            selector,
            state,
            dependee,
            event;

        for (selector in states) {
            state = states[selector];
            for (action in state) {
                _addRule(selector, action, state[action]['conditions'], context);
            }
        }
        for (dependee in initial_triggers) {
            for (event in initial_triggers[dependee]) {
                $(dependee, context).trigger(event, [true]);
            }
        }
    };
    
    SABAI.cloneField = function(container, fieldName, maxNum, nextIndex, trigger) {
        var $container = $(container), fields = $container.find('> .sabai-form-field:not(.sabai-form-field-add)'),
            index = nextIndex || fields.length;
        if (maxNum && index >= maxNum) return;
        var field = fields.first(),
            clone = field.clone().attr('id', 'sabai-' + SABAI.guid()).find(':input')
                .each(function () {
                    var $this = jQuery(this);
                    if ($this.attr('name')) {
                        field_name = trigger.data('field-form-wrap') ? trigger.data('field-form-wrap') + '[' + fieldName + ']' : fieldName;
                        $this.attr('name', $this.attr('name').replace(field_name + '[0]', field_name + '[' + index + ']'));
                    }
                    if ($this.attr('id')) {
                        $this.attr('id', $this.attr('id') + '-' + index);
                    }
                    // Make sure default value is empty
                    $this.removeData('default-value').removeAttr('data-default-value')
                    // Fix for jquery.uniform
                    if ($.fn.uniform && $this.parent().is('.selector')) {
                        $this.prev('span').remove().end().unwrap().uniform().parent('.selector').show();
                    }
                }).end()
                .clearInput()
                .removeClass('sabai-form-field-error')
                .find('span.sabai-form-field-error').remove().end()
                .find('.sabai-was-hidden').hide().end()
                .hide()
                .insertAfter(fields.last());
            clone.append('<a class="sabai-btn sabai-btn-danger sabai-btn-xs sabai-form-field-remove" href="#"><i class="fa fa-times" title="Remove this field"></i></a>')
                .slideDown('fast')
                .focusFirstInput()
                .find('a.sabai-form-field-remove')
                .click(function(e){
                    $(this).closest('.sabai-form-field').fadeTo('fast', 0, function(){
                        $(this).slideUp('fast', function(){
                            $(this).remove();
                            var bros = $container.find('> .sabai-form-field:not(.sabai-form-field-add)');
                            if (maxNum && bros.length < maxNum) {
                                trigger.show();
                            }
                            bros.find(':input').trigger('cloneremoved.sabai');
                        });
                    });
                    e.preventDefault();
                })
                .parent().css('position', 'relative');
            $(SABAI).trigger('clonefield.sabai', {container:container, field:field, clone:clone, index:index});
        if (maxNum && trigger && index + 1 >= maxNum) {
            trigger.hide();
        }
    };
    
    SABAI.addOption = function(container, fieldName, trigger, isCheckbox, callback) {
        var $container = $(container),
            $original = $(trigger).closest('.sabai-form-field-option'),
            options = $container.find("> .sabai-form-field-option"),
            choiceName = isCheckbox ? fieldName + "[default][]" : fieldName + "[default]",
            i = $original.find("input[name='" + choiceName + "']").val(),
            option = $original.clone().find(':text').each(function(){
                var $this = jQuery(this);
                if (!$this.attr('name')) return;
                $this.attr('name', $this.attr('name').replace(fieldName + '[options][' + i + ']', fieldName + '[options][' + options.length + ']'));
            }).end()
                .clearInput()
                .find("input[name='" + choiceName + "']").val(options.length).end()
                .hide()
                .insertAfter($original);
            if (callback) {
                callback.call(null, option);
            }
            option.slideDown('fast').focusFirstInput();
        return false;
    };
    
    SABAI.removeOption = function(container, trigger, confirmMsg) {
        var options_non_disabled = $(container).find("> .sabai-form-field-option:not(.sabai-form-field-option-disabled)");
        if (options_non_disabled.length === 1) {
            // There must be at least one non-disabled optoin, so just clear it instead of removing
            options_non_disabled.clearInput();
            return;
        }
        // Confirm deletion
        if (!confirm(confirmMsg)) return false;
        $(trigger).closest('.sabai-form-field-option').slideUp('fast', function() {$(this).remove();});
    };
    
    SABAI.guid = function () {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
            return v.toString(16);
        });
    }
    
    if (window.history && window.history.pushState) {
        var popped = false, initial_url = window.location.href.replace(/%2F/g, '/');
        $(window).on('popstate', function(e) {
            // Ignore inital popstate that some browsers fire on page load
            if (!popped) {
                popped = true;
                if (location.href.replace(/%2F/g, '/') == initial_url) {
                    return;
                }
            }
            var state = e.originalEvent.state;
            if (state) {
                state.data.__ajax = state.target ? state.container + ' ' + state.target : state.container;
                SABAI.ajax(state);
                $(SABAI).trigger('sabaipopstate.sabai', state);
            } else {
                window.location.href = window.location.href;
            }
        });
    }
    
    SABAI.parseUrl = function(url) {
        var loc = url ? $('<a/>').prop('href', url)[0] : window.location;
        loc.query = {};
        if (loc.search && typeof loc.search == 'string') {
            var params = loc.search.substr(1).replace(/\+/g, '%20').split('&');
            $.each(params, function(i, val) {
                var param = val.split('=');
                try {
                    param[1] = decodeURIComponent(param[1]);
                } catch (e) {
                    
                }
                loc.query[param[0]] = param[1];
            });
        }
        return loc;
    }
    
    SABAI.getScript = function(url, options, success) { 
        options = $.extend(options || {}, {
            dataType: "script",
            cache: true,
            url: url
        });
        return jQuery.ajax(options, success);
    }
    
    $.fn.sabai = function() {
        SABAI.init(this);
    }
    
    $.fn.focusTextRange = function(start, end) {
        if (this.is('input[type="text"]') || this.is('textarea')) {
            var domEl = this.get(0);
            if (domEl.setSelectionRange) {
                domEl.focus();
                domEl.setSelectionRange(start, end);
            } else if (domEl.createTextRange) {
                var range = domEl.createTextRange();
                range.collapse(true);
                range.moveEnd('character', end);
                range.moveStart('character', start);
                range.select();
            }
        }
        return this;
    };
    $.fn.focusFirstInput = function() {
        var target = this.find('input[type="text"],input[type="password"],textarea').not('.sabai-focus-off').filter(':visible:first');
        if (!target.length) {
            return this;
        }
        var len = target.val().length;
        target.focusTextRange(len, len);
        return this;
    };
    $.fn.clearInput = function() {
        return this.each(function() {
            var $this = $(this), tag = $this.get(0).tagName.toLowerCase();
            if (typeof $this.data('default-value') !== 'undefined') {
                $this.val($this.data('default-value'));
                return;
            }
            if (tag === 'input') {
                var type = $this.attr('type')
                if (type === 'checkbox' || type === 'radio') {
                    $this.prop('checked', false);
                } else {
                    $this.val('');
                }
            } else if (tag === 'textarea') {
                $this.val('');
            } else if (tag === 'select') {
                 $this.prop('selectedIndex', 0);
            } else {
                return $this.find(':input').clearInput();
            }
        });
    };
})(jQuery);