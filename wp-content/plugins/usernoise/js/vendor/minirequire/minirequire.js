(function() {
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

}).call(this);
