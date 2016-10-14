/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports, __webpack_require__) {

	var MiniRequire;

	MiniRequire = __webpack_require__(1);

	jQuery.extend(usernoise, {
	  isMobileDevice: function() {
	    return jQuery(window).width() < 768;
	  },
	  miniRequire: new MiniRequire({
	    baseUrl: usernoise.config.urls.usernoise + "js",
	    shim: {
	      'jQuery': jQuery
	    }
	  }),
	  discussion: {
	    init: function() {
	      var element, i, id, len, ref;
	      ref = jQuery('.un-discussion');
	      for (i = 0, len = ref.length; i < len; i++) {
	        element = ref[i];
	        usernoise.miniRequire.require(['discussion/dist/discussion'], function(discussion) {
	          return discussion.discussion(element);
	        });
	      }
	      if (window.location.hash.match(/feedback\-\d+/)) {
	        id = window.location.hash.replace('feedback-', '');
	        return usernoise.miniRequire.require(['discussion/dist/discussion'], function(discussion) {
	          return discussion.discussionPopup(id);
	        });
	      }
	    }
	  },
	  window: {
	    show: function(bindTo) {
	      var _this;
	      _this = this;
	      return usernoise.miniRequire.require(['popup/dist/popup'], function(popup) {
	        var props;
	        props = {};
	        if (bindTo) {
	          bindTo = jQuery(bindTo);
	        }
	        if (!bindTo && _this !== usernoise.window) {
	          bindTo = jQuery(_this);
	        }
	        if (bindTo) {
	          props.element = bindTo[0];
	        }
	        props.config = usernoise.config;
	        props.i18n = usernoise.i18n;
	        return usernoise.window.current = popup(props);
	      });
	    },
	    hide: function() {
	      var elements;
	      elements = jQuery("#un-overlay, #un-iframe, #un-loading}");
	      elements.removeClass('un-visible');
	      return setTimeout((function() {
	        return elements.remove();
	      }), 500);
	    }
	  }
	});

	jQuery(function($) {
	  if (navigator && navigator.appVersion && (navigator.appVersion.indexOf("MSIE 6.0") !== -1 || navigator.appVersion.indexOf("MSIE 7.0") !== -1)) {
	    return;
	  }
	  usernoise.discussion.init();
	  return $.post(usernoise.config.urls.config.get, function(config) {
	    var button, buttonEnabled, handleButtonClick, selector;
	    buttonEnabled = usernoise.config.button.enabled;
	    usernoise.config = config.config;
	    usernoise.config.button.enabled = buttonEnabled;
	    usernoise.miniRequire.require(['popup/dist/popup'], function() {});
	    if (usernoise.config.button.enabled && !(window.usernoise.isMobileDevice() && usernoise.config.button.disableOnMobiles) && (usernoise.config.loggedIn || !usernoise.config.onlyLoggedIn)) {
	      button = $('<button id="un-button" rel="usernoise"/>');
	      button.html(usernoise.config.button.text);
	      button.css(usernoise.config.button.style);
	      $('body').append(button);
	      button.addClass(usernoise.config.button['class']);
	      setTimeout((function() {
	        return button.addClass('un-visible');
	      }), 1);
	    }
	    handleButtonClick = function(e) {
	      e.preventDefault();
	      e.stopPropagation();
	      return usernoise.window.show(this);
	    };
	    selector = 'a[rel=usernoise], button[rel=usernoise], a[href="#usernoise"]';
	    if ($.on) {
	      return $.on('click', selector, handleButtonClick);
	    } else {
	      return $(selector).click(handleButtonClick);
	    }
	  });
	});


/***/ },
/* 1 */
/***/ function(module, exports) {

	var MiniRequire;

	MiniRequire = (function() {
	  function MiniRequire(options) {
	    var module;
	    this.options = options != null ? options : {};
	    if (!this.options.baseUrl) {
	      this.options.baseUrl = "/";
	    }
	    this.moduleStore = {};
	    this.watched = {};
	    if (this.options.shim) {
	      for (module in this.options.shim) {
	        this.moduleStore[module] = (function() {
	          return this.options.shim[module];
	        });
	      }
	    }
	    this.define.amd = {};
	  }

	  MiniRequire.prototype.define = function(moduleName, dependencyNames, moduleDefinition) {
	    if (this.moduleStore[moduleName]) {
	      return;
	    }
	    return this.require(dependencyNames, (function(_this) {
	      return function(deps) {
	        _this.moduleStore[moduleName] = moduleDefinition.apply(_this, arguments);
	        return _this.onLoad(moduleName);
	      };
	    })(this));
	  };

	  MiniRequire.prototype.waitFor = function(moduleName, callback) {
	    if (!this.watched[moduleName]) {
	      this.watched[moduleName] = [];
	    }
	    return this.watched[moduleName].push(callback);
	  };

	  MiniRequire.prototype.onLoad = function(moduleName) {
	    var callback, i, len, ref;
	    if (!this.watched[moduleName]) {
	      return;
	    }
	    ref = this.watched[moduleName];
	    for (i = 0, len = ref.length; i < len; i++) {
	      callback = ref[i];
	      callback.call(this, this.moduleStore[moduleName]);
	    }
	    return delete this.watched[moduleName];
	  };

	  MiniRequire.prototype.require = function(moduleNames, callback) {
	    var availableModuleNames, i, len, moduleLoaded, moduleName;
	    availableModuleNames = [];
	    if (typeof moduleNames === 'string') {
	      moduleNames = [moduleNames];
	    }
	    moduleLoaded = (function(_this) {
	      return function() {
	        if (availableModuleNames.length === moduleNames.length) {
	          return callback.apply(_this, moduleNames.map(function(dependency) {
	            return _this.moduleStore[dependency];
	          }));
	        }
	      };
	    })(this);
	    for (i = 0, len = moduleNames.length; i < len; i++) {
	      moduleName = moduleNames[i];
	      if (this.moduleStore[moduleName]) {
	        availableModuleNames.push(moduleName);
	      } else {
	        this.waitFor(moduleName, (function(_this) {
	          return function() {
	            availableModuleNames.push(moduleName);
	            return moduleLoaded();
	          };
	        })(this));
	        if (!this.hasScriptForModule(moduleName)) {
	          this.buildScriptForModule(moduleName);
	        }
	      }
	    }
	    return moduleLoaded();
	  };

	  MiniRequire.prototype.hasScriptForModule = function(module) {
	    return document.querySelectorAll('[data-module-name="' + module + '"]').length > 0;
	  };

	  MiniRequire.prototype.buildScriptForModule = function(module, callback) {
	    var moduleScript;
	    moduleScript = document.createElement('script');
	    moduleScript.src = this.options.baseUrl + "/" + module + ".js";
	    moduleScript.setAttribute('data-module-name', module);
	    return document.body.appendChild(moduleScript);
	  };

	  return MiniRequire;

	})();

	if (module.exports) {
	  module.exports = MiniRequire;
	}

	if (typeof window !== 'undefined') {
	  window.MiniRequire = MiniRequire;
	}


/***/ }
/******/ ]);