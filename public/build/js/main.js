/**!
 * AngularJS file upload shim for HTML5 FormData
 * @author  Danial  <danial.farid@gmail.com>
 * @version 1.5.0
 */
(function() {

    var hasFlash = function() {
        try {
            var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
            if (fo) return true;
        } catch(e) {
            if (navigator.mimeTypes["application/x-shockwave-flash"] != undefined) return true;
        }
        return false;
    }

    var patchXHR = function(fnName, newFn) {
        window.XMLHttpRequest.prototype[fnName] = newFn(window.XMLHttpRequest.prototype[fnName]);
    };

    if (window.XMLHttpRequest) {
        if (window.FormData && (!window.FileAPI || !FileAPI.forceLoad)) {
            // allow access to Angular XHR private field: https://github.com/angular/angular.js/issues/1934
            patchXHR("setRequestHeader", function(orig) {
                return function(header, value) {
                    if (header === '__setXHR_') {
                        var val = value(this);
                        // fix for angular < 1.2.0
                        if (val instanceof Function) {
                            val(this);
                        }
                    } else {
                        orig.apply(this, arguments);
                    }
                }
            });
        } else {
            function initializeUploadListener(xhr) {
                if (!xhr.__listeners) {
                    if (!xhr.upload) xhr.upload = {};
                    xhr.__listeners = [];
                    var origAddEventListener = xhr.upload.addEventListener;
                    xhr.upload.addEventListener = function(t, fn, b) {
                        xhr.__listeners[t] = fn;
                        origAddEventListener && origAddEventListener.apply(this, arguments);
                    };
                }
            }

            patchXHR("open", function(orig) {
                return function(m, url, b) {
                    initializeUploadListener(this);
                    this.__url = url;
                    orig.apply(this, [m, url, b]);
                }
            });

            patchXHR("getResponseHeader", function(orig) {
                return function(h) {
                    return this.__fileApiXHR ? this.__fileApiXHR.getResponseHeader(h) : orig.apply(this, [h]);
                };
            });

            patchXHR("getAllResponseHeaders", function(orig) {
                return function() {
                    return this.__fileApiXHR ? this.__fileApiXHR.abort() : (orig == null ? null : orig.apply(this));
                }
            });

            patchXHR("abort", function(orig) {
                return function() {
                    return this.__fileApiXHR ? this.__fileApiXHR.abort() : (orig == null ? null : orig.apply(this));
                }
            });

            patchXHR("setRequestHeader", function(orig) {
                return function(header, value) {
                    if (header === '__setXHR_') {
                        initializeUploadListener(this);
                        var val = value(this);
                        // fix for angular < 1.2.0
                        if (val instanceof Function) {
                            val(this);
                        }
                    } else {
                        this.__requestHeaders = this.__requestHeaders || {};
                        this.__requestHeaders[header] = value;
                        orig.apply(this, arguments);
                    }
                }
            });

            patchXHR("send", function(orig) {
                return function() {
                    var xhr = this;
                    if (arguments[0] && arguments[0].__isShim) {
                        var formData = arguments[0];
                        var config = {
                            url: xhr.__url,
                            complete: function(err, fileApiXHR) {
                                if (!err && xhr.__listeners['load'])
                                    xhr.__listeners['load']({type: 'load', loaded: xhr.__loaded, total: xhr.__total, target: xhr, lengthComputable: true});
                                if (!err && xhr.__listeners['loadend'])
                                    xhr.__listeners['loadend']({type: 'loadend', loaded: xhr.__loaded, total: xhr.__total, target: xhr, lengthComputable: true});
                                if (err === 'abort' && xhr.__listeners['abort'])
                                    xhr.__listeners['abort']({type: 'abort', loaded: xhr.__loaded, total: xhr.__total, target: xhr, lengthComputable: true});
                                if (fileApiXHR.status !== undefined) Object.defineProperty(xhr, 'status', {get: function() {return fileApiXHR.status}});
                                if (fileApiXHR.statusText !== undefined) Object.defineProperty(xhr, 'statusText', {get: function() {return fileApiXHR.statusText}});
                                Object.defineProperty(xhr, 'readyState', {get: function() {return 4}});
                                if (fileApiXHR.response !== undefined) Object.defineProperty(xhr, 'response', {get: function() {return fileApiXHR.response}});
                                Object.defineProperty(xhr, 'responseText', {get: function() {return fileApiXHR.responseText}});
                                xhr.__fileApiXHR = fileApiXHR;
                                xhr.onreadystatechange();
                            },
                            fileprogress: function(e) {
                                e.target = xhr;
                                xhr.__listeners['progress'] && xhr.__listeners['progress'](e);
                                xhr.__total = e.total;
                                xhr.__loaded = e.loaded;
                            },
                            headers: xhr.__requestHeaders
                        }
                        config.data = {};
                        config.files = {}
                        for (var i = 0; i < formData.data.length; i++) {
                            var item = formData.data[i];
                            if (item.val != null && item.val.name != null && item.val.size != null && item.val.type != null) {
                                config.files[item.key] = item.val;
                            } else {
                                config.data[item.key] = item.val;
                            }
                        }

                        setTimeout(function() {
                            if (!hasFlash()) {
                                throw 'Adode Flash Player need to be installed. To check ahead use "FileAPI.hasFlash"';
                            }
                            xhr.__fileApiXHR = FileAPI.upload(config);
                        }, 1);
                    } else {
                        orig.apply(xhr, arguments);
                    }
                }
            });
        }
        window.XMLHttpRequest.__isShim = true;
    }

    if (!window.FormData || (window.FileAPI && FileAPI.forceLoad)) {
        var wrapFileApi = function(elem) {
            if (!hasFlash()) {
                throw 'Adode Flash Player need to be installed. To check ahead use "FileAPI.hasFlash"';
            }
            if (!elem.__isWrapped && (elem.getAttribute('ng-file-select') != null || elem.getAttribute('data-ng-file-select') != null)) {
                var wrap = document.createElement('div');
                wrap.innerHTML = '<div class="js-fileapi-wrapper" style="position:relative; overflow:hidden"></div>';
                wrap = wrap.firstChild;
                var parent = elem.parentNode;
                parent.insertBefore(wrap, elem);
                parent.removeChild(elem);
                wrap.appendChild(elem);
                elem.__isWrapped = true;
            }
        };
        var changeFnWrapper = function(fn) {
            return function(evt) {
                var files = FileAPI.getFiles(evt);
                //just a double check for #233
                for (var i = 0; i < files.length; i++) {
                    if (files[i].size === undefined) files[i].size = 0;
                    if (files[i].name === undefined) files[i].name = 'file';
                    if (files[i].type === undefined) files[i].type = 'undefined';
                }
                if (!evt.target) {
                    evt.target = {};
                }
                evt.target.files = files;
                evt.target.files.item = function(i) {
                    return evt.target.files[i] || null;
                }
                fn(evt);
            };
        };
        var isFileChange = function(elem, e) {
            return (e.toLowerCase() === 'change' || e.toLowerCase() === 'onchange') && elem.getAttribute('type') == 'file';
        }
        if (HTMLInputElement.prototype.addEventListener) {
            HTMLInputElement.prototype.addEventListener = (function(origAddEventListener) {
                return function(e, fn, b, d) {
                    if (isFileChange(this, e)) {
                        wrapFileApi(this);
                        origAddEventListener.apply(this, [e, changeFnWrapper(fn), b, d]);
                    } else {
                        origAddEventListener.apply(this, [e, fn, b, d]);
                    }
                }
            })(HTMLInputElement.prototype.addEventListener);
        }
        if (HTMLInputElement.prototype.attachEvent) {
            HTMLInputElement.prototype.attachEvent = (function(origAttachEvent) {
                return function(e, fn) {
                    if (isFileChange(this, e)) {
                        wrapFileApi(this);
                        origAttachEvent.apply(this, [e, changeFnWrapper(fn)]);
                    } else {
                        origAttachEvent.apply(this, [e, fn]);
                    }
                }
            })(HTMLInputElement.prototype.attachEvent);
        }

        window.FormData = FormData = function() {
            return {
                append: function(key, val, name) {
                    this.data.push({
                        key: key,
                        val: val,
                        name: name
                    });
                },
                data: [],
                __isShim: true
            };
        };

        (function () {
            //load FileAPI
            if (!window.FileAPI) {
                window.FileAPI = {};
            }
            if (!FileAPI.upload) {
                var jsUrl, basePath, script = document.createElement('script'), allScripts = document.getElementsByTagName('script'), i, index, src;
                if (window.FileAPI.jsUrl) {
                    jsUrl = window.FileAPI.jsUrl;
                } else if (window.FileAPI.jsPath) {
                    basePath = window.FileAPI.jsPath;
                } else {
                    for (i = 0; i < allScripts.length; i++) {
                        src = allScripts[i].src;
                        index = src.indexOf('angular-file-upload-shim.js')
                        if (index == -1) {
                            index = src.indexOf('angular-file-upload-shim.min.js');
                        }
                        if (index > -1) {
                            basePath = src.substring(0, index);
                            break;
                        }
                    }
                }

                if (FileAPI.staticPath == null) FileAPI.staticPath = basePath;
                script.setAttribute('src', jsUrl || basePath + "FileAPI.min.js");
                document.getElementsByTagName('head')[0].appendChild(script);
                FileAPI.hasFlash = hasFlash();
            }
        })();
    }


    if (!window.FileReader) {
        window.FileReader = function() {
            var _this = this, loadStarted = false;
            this.listeners = {};
            this.addEventListener = function(type, fn) {
                _this.listeners[type] = _this.listeners[type] || [];
                _this.listeners[type].push(fn);
            };
            this.removeEventListener = function(type, fn) {
                _this.listeners[type] && _this.listeners[type].splice(_this.listeners[type].indexOf(fn), 1);
            };
            this.dispatchEvent = function(evt) {
                var list = _this.listeners[evt.type];
                if (list) {
                    for (var i = 0; i < list.length; i++) {
                        list[i].call(_this, evt);
                    }
                }
            };
            this.onabort = this.onerror = this.onload = this.onloadstart = this.onloadend = this.onprogress = null;

            function constructEvent(type, evt) {
                var e = {type: type, target: _this, loaded: evt.loaded, total: evt.total, error: evt.error};
                if (evt.result != null) e.target.result = evt.result;
                return e;
            };
            var listener = function(evt) {
                if (!loadStarted) {
                    loadStarted = true;
                    _this.onloadstart && this.onloadstart(constructEvent('loadstart', evt));
                }
                if (evt.type === 'load') {
                    _this.onloadend && _this.onloadend(constructEvent('loadend', evt));
                    var e = constructEvent('load', evt);
                    _this.onload && _this.onload(e);
                    _this.dispatchEvent(e);
                } else if (evt.type === 'progress') {
                    var e = constructEvent('progress', evt);
                    _this.onprogress && _this.onprogress(e);
                    _this.dispatchEvent(e);
                } else {
                    var e = constructEvent('error', evt);
                    _this.onerror && _this.onerror(e);
                    _this.dispatchEvent(e);
                }
            };
            this.readAsArrayBuffer = function(file) {
                FileAPI.readAsBinaryString(file, listener);
            }
            this.readAsBinaryString = function(file) {
                FileAPI.readAsBinaryString(file, listener);
            }
            this.readAsDataURL = function(file) {
                FileAPI.readAsDataURL(file, listener);
            }
            this.readAsText = function(file) {
                FileAPI.readAsText(file, listener);
            }
        }
    }

})();
/**!
 * AngularJS file upload/drop directive with http post and progress
 * @author  Danial  <danial.farid@gmail.com>
 * @version 1.5.0
 */
(function() {

    var angularFileUpload = angular.module('angularFileUpload', []);

    angularFileUpload.service('$upload', ['$http', '$q', '$timeout', function($http, $q, $timeout) {
        function sendHttp(config) {
            config.method = config.method || 'POST';
            config.headers = config.headers || {};
            config.transformRequest = config.transformRequest || function(data, headersGetter) {
                if (window.ArrayBuffer && data instanceof window.ArrayBuffer) {
                    return data;
                }
                return $http.defaults.transformRequest[0](data, headersGetter);
            };
            var deferred = $q.defer();

            if (window.XMLHttpRequest.__isShim) {
                config.headers['__setXHR_'] = function() {
                    return function(xhr) {
                        if (!xhr) return;
                        config.__XHR = xhr;
                        config.xhrFn && config.xhrFn(xhr);
                        xhr.upload.addEventListener('progress', function(e) {
                            deferred.notify(e);
                        }, false);
                        //fix for firefox not firing upload progress end, also IE8-9
                        xhr.upload.addEventListener('load', function(e) {
                            if (e.lengthComputable) {
                                deferred.notify(e);
                            }
                        }, false);
                    };
                };
            }

            $http(config).then(function(r){deferred.resolve(r)}, function(e){deferred.reject(e)}, function(n){deferred.notify(n)});

            var promise = deferred.promise;
            promise.success = function(fn) {
                promise.then(function(response) {
                    fn(response.data, response.status, response.headers, config);
                });
                return promise;
            };

            promise.error = function(fn) {
                promise.then(null, function(response) {
                    fn(response.data, response.status, response.headers, config);
                });
                return promise;
            };

            promise.progress = function(fn) {
                promise.then(null, null, function(update) {
                    fn(update);
                });
                return promise;
            };
            promise.abort = function() {
                if (config.__XHR) {
                    $timeout(function() {
                        config.__XHR.abort();
                    });
                }
                return promise;
            };
            promise.xhr = function(fn) {
                config.xhrFn = (function(origXhrFn) {
                    return function() {
                        origXhrFn && origXhrFn.apply(promise, arguments);
                        fn.apply(promise, arguments);
                    }
                })(config.xhrFn);
                return promise;
            };

            return promise;
        }

        this.upload = function(config) {
            config.headers = config.headers || {};
            config.headers['Content-Type'] = undefined;
            config.transformRequest = config.transformRequest || $http.defaults.transformRequest;
            var formData = new FormData();
            var origTransformRequest = config.transformRequest;
            var origData = config.data;
            config.transformRequest = function(formData, headerGetter) {
                if (origData) {
                    if (config.formDataAppender) {
                        for (var key in origData) {
                            var val = origData[key];
                            config.formDataAppender(formData, key, val);
                        }
                    } else {
                        for (var key in origData) {
                            var val = origData[key];
                            if (typeof origTransformRequest == 'function') {
                                val = origTransformRequest(val, headerGetter);
                            } else {
                                for (var i = 0; i < origTransformRequest.length; i++) {
                                    var transformFn = origTransformRequest[i];
                                    if (typeof transformFn == 'function') {
                                        val = transformFn(val, headerGetter);
                                    }
                                }
                            }
                            formData.append(key, val);
                        }
                    }
                }

                if (config.file != null) {
                    var fileFormName = config.fileFormDataName || 'file';

                    if (Object.prototype.toString.call(config.file) === '[object Array]') {
                        var isFileFormNameString = Object.prototype.toString.call(fileFormName) === '[object String]';
                        for (var i = 0; i < config.file.length; i++) {
                            formData.append(isFileFormNameString ? fileFormName : fileFormName[i], config.file[i],
                                (config.fileName && config.fileName[i]) || config.file[i].name);
                        }
                    } else {
                        formData.append(fileFormName, config.file, config.fileName || config.file.name);
                    }
                }
                return formData;
            };

            config.data = formData;

            return sendHttp(config);
        };

        this.http = function(config) {
            return sendHttp(config);
        }
    }]);

    angularFileUpload.directive('ngFileSelect', [ '$parse', '$timeout', function($parse, $timeout) {
        return function(scope, elem, attr) {
            var fn = $parse(attr['ngFileSelect']);
            elem.bind('change', function(evt) {
                var files = [], fileList, i;
                fileList = evt.target.files;
                if (fileList != null) {
                    for (i = 0; i < fileList.length; i++) {
                        files.push(fileList.item(i));
                    }
                }
                $timeout(function() {
                    fn(scope, {
                        $files : files,
                        $event : evt
                    });
                });
            });
            // removed this since it was confusing if the user click on browse and then cancel #181
//		elem.bind('click', function(){
//			this.value = null;
//		});

            // touch screens
            if (('ontouchstart' in window) ||
                (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0)) {
                elem.bind('touchend', function(e) {
                    e.preventDefault();
                    e.target.click();
                });
            }
        };
    } ]);

    angularFileUpload.directive('ngFileDropAvailable', [ '$parse', '$timeout', function($parse, $timeout) {
        return function(scope, elem, attr) {
            if ('draggable' in document.createElement('span')) {
                var fn = $parse(attr['ngFileDropAvailable']);
                $timeout(function() {
                    fn(scope);
                });
            }
        };
    } ]);

    angularFileUpload.directive('ngFileDrop', [ '$parse', '$timeout', '$location', function($parse, $timeout, $location) {
        return function(scope, elem, attr) {
            if ('draggable' in document.createElement('span')) {
                var leaveTimeout = null;
                elem[0].addEventListener("dragover", function(evt) {
                    evt.stopPropagation();
                    evt.preventDefault();
                    elem.addClass(elem[0].__drag_over_class_);
                    $timeout.cancel(leaveTimeout);
                    if (!elem[0].__drag_entered_) {
                        elem[0].__drag_entered_ = true;
                        var dragOverClassFn = $parse(attr['ngFileDragOverClass']);
                        if (dragOverClassFn instanceof Function) {
                            var dragOverClass = dragOverClassFn(scope, {
                                $event : evt
                            });
                            elem[0].__drag_over_class_ = dragOverClass;
                            elem.addClass(elem[0].__drag_over_class_);
                        } else {
                            elem[0].__drag_over_class_ = attr['ngFileDragOverClass'] || "dragover";
                            elem.addClass(elem[0].__drag_over_class_);
                        }
                    }
                }, false);
                elem[0].addEventListener("dragenter", function(evt) {
                    evt.stopPropagation();
                    evt.preventDefault();
                }, false);
                elem[0].addEventListener("dragleave", function(evt) {
                    leaveTimeout = $timeout(function() {
                        elem[0].__drag_entered_ = false;
                        elem.removeClass(elem[0].__drag_over_class_);
                    });
                }, false);
                var fn = $parse(attr['ngFileDrop']);
                elem[0].addEventListener("drop", function(evt) {
                    evt.stopPropagation();
                    evt.preventDefault();
                    elem[0].__drag_entered_ = false;
                    elem.removeClass(elem[0].__drag_over_class_);
                    extractFiles(evt, function(files) {
                        fn(scope, {
                            $files : files,
                            $event : evt
                        });
                    });
                }, false);

                function isASCII(str) {
                    return /^[\000-\177]*$/.test(str);
                }

                function extractFiles(evt, callback) {
                    var files = [], items = evt.dataTransfer.items;
                    if (items && items.length > 0 && items[0].webkitGetAsEntry && $location.protocol() != 'file') {
                        for (var i = 0; i < items.length; i++) {
                            var entry = items[i].webkitGetAsEntry();
                            if (entry != null) {
                                //fix for chrome bug https://code.google.com/p/chromium/issues/detail?id=149735
                                if (isASCII(entry.name)) {
                                    traverseFileTree(files, entry);
                                } else {
                                    files.push(items[i].getAsFile());
                                }
                            }
                        }
                    } else {
                        var fileList = evt.dataTransfer.files;
                        if (fileList != null) {
                            for (var i = 0; i < fileList.length; i++) {
                                files.push(fileList.item(i));
                            }
                        }
                    }
                    (function waitForProcess(delay) {
                        $timeout(function() {
                            if (!processing) {
                                callback(files);
                            } else {
                                waitForProcess(10);
                            }
                        }, delay || 0)
                    })();
                }

                var processing = 0;
                function traverseFileTree(files, entry) {
                    if (entry != null) {
                        if (entry.isDirectory) {
                            var dirReader = entry.createReader();
                            processing++;
                            dirReader.readEntries(function(entries) {
                                for (var i = 0; i < entries.length; i++) {
                                    traverseFileTree(files, entries[i]);
                                }
                                processing--;
                            });
                        } else {
                            processing++;
                            entry.file(function(file) {
                                processing--;
                                files.push(file);
                            });
                        }
                    }
                }
            }
        };
    } ]);

})();
/*!
 * twitter-text-js 1.9.1
 *
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this work except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 */
var sailrUrls = {
    hashtagBase: 'http://sailr.co/s/%23',
    cashtagBase: 'http://sailr.co/s/%24',
    usernameBase: 'http://sailr.co/'

};

var sailrClasses = {
    cashtagClass: 'sailr cashtag-link',
    hashtagClass: 'sailr hashtag-link',
    usernameClass: 'sailr username-link'
};

(function() {
    if (typeof twttr === "undefined" || twttr === null) {
        var twttr = {};
    }

    twttr.txt = {};
    twttr.txt.regexen = {};

    var HTML_ENTITIES = {
        '&': '&amp;',
        '>': '&gt;',
        '<': '&lt;',
        '"': '&quot;',
        "'": '&#39;'
    };

    // HTML escaping
    twttr.txt.htmlEscape = function(text) {
        return text && text.replace(/[&"'><]/g, function(character) {
            return HTML_ENTITIES[character];
        });
    };

    // Builds a RegExp
    function regexSupplant(regex, flags) {
        flags = flags || "";
        if (typeof regex !== "string") {
            if (regex.global && flags.indexOf("g") < 0) {
                flags += "g";
            }
            if (regex.ignoreCase && flags.indexOf("i") < 0) {
                flags += "i";
            }
            if (regex.multiline && flags.indexOf("m") < 0) {
                flags += "m";
            }

            regex = regex.source;
        }

        return new RegExp(regex.replace(/#\{(\w+)\}/g, function(match, name) {
            var newRegex = twttr.txt.regexen[name] || "";
            if (typeof newRegex !== "string") {
                newRegex = newRegex.source;
            }
            return newRegex;
        }), flags);
    }

    twttr.txt.regexSupplant = regexSupplant;

    // simple string interpolation
    function stringSupplant(str, values) {
        return str.replace(/#\{(\w+)\}/g, function(match, name) {
            return values[name] || "";
        });
    }

    twttr.txt.stringSupplant = stringSupplant;

    function addCharsToCharClass(charClass, start, end) {
        var s = String.fromCharCode(start);
        if (end !== start) {
            s += "-" + String.fromCharCode(end);
        }
        charClass.push(s);
        return charClass;
    }

    twttr.txt.addCharsToCharClass = addCharsToCharClass;

    // Space is more than %20, U+3000 for example is the full-width space used with Kanji. Provide a short-hand
    // to access both the list of characters and a pattern suitible for use with String#split
    // Taken from: ActiveSupport::Multibyte::Handlers::UTF8Handler::UNICODE_WHITESPACE
    var fromCode = String.fromCharCode;
    var UNICODE_SPACES = [
        fromCode(0x0020), // White_Space # Zs       SPACE
        fromCode(0x0085), // White_Space # Cc       <control-0085>
        fromCode(0x00A0), // White_Space # Zs       NO-BREAK SPACE
        fromCode(0x1680), // White_Space # Zs       OGHAM SPACE MARK
        fromCode(0x180E), // White_Space # Zs       MONGOLIAN VOWEL SEPARATOR
        fromCode(0x2028), // White_Space # Zl       LINE SEPARATOR
        fromCode(0x2029), // White_Space # Zp       PARAGRAPH SEPARATOR
        fromCode(0x202F), // White_Space # Zs       NARROW NO-BREAK SPACE
        fromCode(0x205F), // White_Space # Zs       MEDIUM MATHEMATICAL SPACE
        fromCode(0x3000)  // White_Space # Zs       IDEOGRAPHIC SPACE
    ];
    addCharsToCharClass(UNICODE_SPACES, 0x009, 0x00D); // White_Space # Cc   [5] <control-0009>..<control-000D>
    addCharsToCharClass(UNICODE_SPACES, 0x2000, 0x200A); // White_Space # Zs  [11] EN QUAD..HAIR SPACE

    var INVALID_CHARS = [
        fromCode(0xFFFE),
        fromCode(0xFEFF), // BOM
        fromCode(0xFFFF) // Special
    ];
    addCharsToCharClass(INVALID_CHARS, 0x202A, 0x202E); // Directional change

    twttr.txt.regexen.spaces_group = regexSupplant(UNICODE_SPACES.join(""));
    twttr.txt.regexen.spaces = regexSupplant("[" + UNICODE_SPACES.join("") + "]");
    twttr.txt.regexen.invalid_chars_group = regexSupplant(INVALID_CHARS.join(""));
    twttr.txt.regexen.punct = /\!'#%&'\(\)*\+,\\\-\.\/:;<=>\?@\[\]\^_{|}~\$/;
    twttr.txt.regexen.rtl_chars = /[\u0600-\u06FF]|[\u0750-\u077F]|[\u0590-\u05FF]|[\uFE70-\uFEFF]/mg;
    twttr.txt.regexen.non_bmp_code_pairs = /[\uD800-\uDBFF][\uDC00-\uDFFF]/mg;

    var nonLatinHashtagChars = [];
    // Cyrillic
    addCharsToCharClass(nonLatinHashtagChars, 0x0400, 0x04ff); // Cyrillic
    addCharsToCharClass(nonLatinHashtagChars, 0x0500, 0x0527); // Cyrillic Supplement
    addCharsToCharClass(nonLatinHashtagChars, 0x2de0, 0x2dff); // Cyrillic Extended A
    addCharsToCharClass(nonLatinHashtagChars, 0xa640, 0xa69f); // Cyrillic Extended B
    // Hebrew
    addCharsToCharClass(nonLatinHashtagChars, 0x0591, 0x05bf); // Hebrew
    addCharsToCharClass(nonLatinHashtagChars, 0x05c1, 0x05c2);
    addCharsToCharClass(nonLatinHashtagChars, 0x05c4, 0x05c5);
    addCharsToCharClass(nonLatinHashtagChars, 0x05c7, 0x05c7);
    addCharsToCharClass(nonLatinHashtagChars, 0x05d0, 0x05ea);
    addCharsToCharClass(nonLatinHashtagChars, 0x05f0, 0x05f4);
    addCharsToCharClass(nonLatinHashtagChars, 0xfb12, 0xfb28); // Hebrew Presentation Forms
    addCharsToCharClass(nonLatinHashtagChars, 0xfb2a, 0xfb36);
    addCharsToCharClass(nonLatinHashtagChars, 0xfb38, 0xfb3c);
    addCharsToCharClass(nonLatinHashtagChars, 0xfb3e, 0xfb3e);
    addCharsToCharClass(nonLatinHashtagChars, 0xfb40, 0xfb41);
    addCharsToCharClass(nonLatinHashtagChars, 0xfb43, 0xfb44);
    addCharsToCharClass(nonLatinHashtagChars, 0xfb46, 0xfb4f);
    // Arabic
    addCharsToCharClass(nonLatinHashtagChars, 0x0610, 0x061a); // Arabic
    addCharsToCharClass(nonLatinHashtagChars, 0x0620, 0x065f);
    addCharsToCharClass(nonLatinHashtagChars, 0x066e, 0x06d3);
    addCharsToCharClass(nonLatinHashtagChars, 0x06d5, 0x06dc);
    addCharsToCharClass(nonLatinHashtagChars, 0x06de, 0x06e8);
    addCharsToCharClass(nonLatinHashtagChars, 0x06ea, 0x06ef);
    addCharsToCharClass(nonLatinHashtagChars, 0x06fa, 0x06fc);
    addCharsToCharClass(nonLatinHashtagChars, 0x06ff, 0x06ff);
    addCharsToCharClass(nonLatinHashtagChars, 0x0750, 0x077f); // Arabic Supplement
    addCharsToCharClass(nonLatinHashtagChars, 0x08a0, 0x08a0); // Arabic Extended A
    addCharsToCharClass(nonLatinHashtagChars, 0x08a2, 0x08ac);
    addCharsToCharClass(nonLatinHashtagChars, 0x08e4, 0x08fe);
    addCharsToCharClass(nonLatinHashtagChars, 0xfb50, 0xfbb1); // Arabic Pres. Forms A
    addCharsToCharClass(nonLatinHashtagChars, 0xfbd3, 0xfd3d);
    addCharsToCharClass(nonLatinHashtagChars, 0xfd50, 0xfd8f);
    addCharsToCharClass(nonLatinHashtagChars, 0xfd92, 0xfdc7);
    addCharsToCharClass(nonLatinHashtagChars, 0xfdf0, 0xfdfb);
    addCharsToCharClass(nonLatinHashtagChars, 0xfe70, 0xfe74); // Arabic Pres. Forms B
    addCharsToCharClass(nonLatinHashtagChars, 0xfe76, 0xfefc);
    addCharsToCharClass(nonLatinHashtagChars, 0x200c, 0x200c); // Zero-Width Non-Joiner
    // Thai
    addCharsToCharClass(nonLatinHashtagChars, 0x0e01, 0x0e3a);
    addCharsToCharClass(nonLatinHashtagChars, 0x0e40, 0x0e4e);
    // Hangul (Korean)
    addCharsToCharClass(nonLatinHashtagChars, 0x1100, 0x11ff); // Hangul Jamo
    addCharsToCharClass(nonLatinHashtagChars, 0x3130, 0x3185); // Hangul Compatibility Jamo
    addCharsToCharClass(nonLatinHashtagChars, 0xA960, 0xA97F); // Hangul Jamo Extended-A
    addCharsToCharClass(nonLatinHashtagChars, 0xAC00, 0xD7AF); // Hangul Syllables
    addCharsToCharClass(nonLatinHashtagChars, 0xD7B0, 0xD7FF); // Hangul Jamo Extended-B
    addCharsToCharClass(nonLatinHashtagChars, 0xFFA1, 0xFFDC); // half-width Hangul
    // Japanese and Chinese
    addCharsToCharClass(nonLatinHashtagChars, 0x30A1, 0x30FA); // Katakana (full-width)
    addCharsToCharClass(nonLatinHashtagChars, 0x30FC, 0x30FE); // Katakana Chouon and iteration marks (full-width)
    addCharsToCharClass(nonLatinHashtagChars, 0xFF66, 0xFF9F); // Katakana (half-width)
    addCharsToCharClass(nonLatinHashtagChars, 0xFF70, 0xFF70); // Katakana Chouon (half-width)
    addCharsToCharClass(nonLatinHashtagChars, 0xFF10, 0xFF19); // \
    addCharsToCharClass(nonLatinHashtagChars, 0xFF21, 0xFF3A); //  - Latin (full-width)
    addCharsToCharClass(nonLatinHashtagChars, 0xFF41, 0xFF5A); // /
    addCharsToCharClass(nonLatinHashtagChars, 0x3041, 0x3096); // Hiragana
    addCharsToCharClass(nonLatinHashtagChars, 0x3099, 0x309E); // Hiragana voicing and iteration mark
    addCharsToCharClass(nonLatinHashtagChars, 0x3400, 0x4DBF); // Kanji (CJK Extension A)
    addCharsToCharClass(nonLatinHashtagChars, 0x4E00, 0x9FFF); // Kanji (Unified)
    // -- Disabled as it breaks the Regex.
    //addCharsToCharClass(nonLatinHashtagChars, 0x20000, 0x2A6DF); // Kanji (CJK Extension B)
    addCharsToCharClass(nonLatinHashtagChars, 0x2A700, 0x2B73F); // Kanji (CJK Extension C)
    addCharsToCharClass(nonLatinHashtagChars, 0x2B740, 0x2B81F); // Kanji (CJK Extension D)
    addCharsToCharClass(nonLatinHashtagChars, 0x2F800, 0x2FA1F); // Kanji (CJK supplement)
    addCharsToCharClass(nonLatinHashtagChars, 0x3003, 0x3003); // Kanji iteration mark
    addCharsToCharClass(nonLatinHashtagChars, 0x3005, 0x3005); // Kanji iteration mark
    addCharsToCharClass(nonLatinHashtagChars, 0x303B, 0x303B); // Han iteration mark

    twttr.txt.regexen.nonLatinHashtagChars = regexSupplant(nonLatinHashtagChars.join(""));

    var latinAccentChars = [];
    // Latin accented characters (subtracted 0xD7 from the range, it's a confusable multiplication sign. Looks like "x")
    addCharsToCharClass(latinAccentChars, 0x00c0, 0x00d6);
    addCharsToCharClass(latinAccentChars, 0x00d8, 0x00f6);
    addCharsToCharClass(latinAccentChars, 0x00f8, 0x00ff);
    // Latin Extended A and B
    addCharsToCharClass(latinAccentChars, 0x0100, 0x024f);
    // assorted IPA Extensions
    addCharsToCharClass(latinAccentChars, 0x0253, 0x0254);
    addCharsToCharClass(latinAccentChars, 0x0256, 0x0257);
    addCharsToCharClass(latinAccentChars, 0x0259, 0x0259);
    addCharsToCharClass(latinAccentChars, 0x025b, 0x025b);
    addCharsToCharClass(latinAccentChars, 0x0263, 0x0263);
    addCharsToCharClass(latinAccentChars, 0x0268, 0x0268);
    addCharsToCharClass(latinAccentChars, 0x026f, 0x026f);
    addCharsToCharClass(latinAccentChars, 0x0272, 0x0272);
    addCharsToCharClass(latinAccentChars, 0x0289, 0x0289);
    addCharsToCharClass(latinAccentChars, 0x028b, 0x028b);
    // Okina for Hawaiian (it *is* a letter character)
    addCharsToCharClass(latinAccentChars, 0x02bb, 0x02bb);
    // Combining diacritics
    addCharsToCharClass(latinAccentChars, 0x0300, 0x036f);
    // Latin Extended Additional
    addCharsToCharClass(latinAccentChars, 0x1e00, 0x1eff);
    twttr.txt.regexen.latinAccentChars = regexSupplant(latinAccentChars.join(""));

    // A hashtag must contain characters, numbers and underscores, but not all numbers.
    twttr.txt.regexen.hashSigns = /[#＃]/;
    twttr.txt.regexen.hashtagAlpha = regexSupplant(/[a-z_#{latinAccentChars}#{nonLatinHashtagChars}]/i);
    twttr.txt.regexen.hashtagAlphaNumeric = regexSupplant(/[a-z0-9_#{latinAccentChars}#{nonLatinHashtagChars}]/i);
    twttr.txt.regexen.endHashtagMatch = regexSupplant(/^(?:#{hashSigns}|:\/\/)/);
    twttr.txt.regexen.hashtagBoundary = regexSupplant(/(?:^|$|[^&a-z0-9_#{latinAccentChars}#{nonLatinHashtagChars}])/);
    twttr.txt.regexen.validHashtag = regexSupplant(/(#{hashtagBoundary})(#{hashSigns})(#{hashtagAlphaNumeric}*#{hashtagAlpha}#{hashtagAlphaNumeric}*)/gi);

    // Mention related regex collection
    twttr.txt.regexen.validMentionPrecedingChars = /(?:^|[^a-zA-Z0-9_!#$%&*@＠]|(?:rt|RT|rT|Rt):?)/;
    twttr.txt.regexen.atSigns = /[@＠]/;
    twttr.txt.regexen.validMentionOrList = regexSupplant(
        '(#{validMentionPrecedingChars})' +  // $1: Preceding character
        '(#{atSigns})' +                     // $2: At mark
        '([a-zA-Z0-9_]{1,20})' +             // $3: Screen name
        '(\/[a-zA-Z][a-zA-Z0-9_\-]{0,24})?'  // $4: List (optional)
        , 'g');
    twttr.txt.regexen.validReply = regexSupplant(/^(?:#{spaces})*#{atSigns}([a-zA-Z0-9_]{1,20})/);
    twttr.txt.regexen.endMentionMatch = regexSupplant(/^(?:#{atSigns}|[#{latinAccentChars}]|:\/\/)/);

    // URL related regex collection
    twttr.txt.regexen.validUrlPrecedingChars = regexSupplant(/(?:[^A-Za-z0-9@＠$#＃#{invalid_chars_group}]|^)/);
    twttr.txt.regexen.invalidUrlWithoutProtocolPrecedingChars = /[-_.\/]$/;
    twttr.txt.regexen.invalidDomainChars = stringSupplant("#{punct}#{spaces_group}#{invalid_chars_group}", twttr.txt.regexen);
    twttr.txt.regexen.validDomainChars = regexSupplant(/[^#{invalidDomainChars}]/);
    twttr.txt.regexen.validSubdomain = regexSupplant(/(?:(?:#{validDomainChars}(?:[_-]|#{validDomainChars})*)?#{validDomainChars}\.)/);
    twttr.txt.regexen.validDomainName = regexSupplant(/(?:(?:#{validDomainChars}(?:-|#{validDomainChars})*)?#{validDomainChars}\.)/);
    twttr.txt.regexen.validGTLD = regexSupplant(RegExp(
        '(?:(?:academy|actor|aero|agency|arpa|asia|bar|bargains|berlin|best|bid|bike|biz|blue|boutique|build|builders|' +
        'buzz|cab|camera|camp|cards|careers|cat|catering|center|ceo|cheap|christmas|cleaning|clothing|club|codes|' +
        'coffee|com|community|company|computer|construction|contractors|cool|coop|cruises|dance|dating|democrat|' +
        'diamonds|directory|domains|edu|education|email|enterprises|equipment|estate|events|expert|exposed|farm|fish|' +
        'flights|florist|foundation|futbol|gallery|gift|glass|gov|graphics|guitars|guru|holdings|holiday|house|' +
        'immobilien|industries|info|institute|int|international|jobs|kaufen|kim|kitchen|kiwi|koeln|kred|land|lighting|' +
        'limo|link|luxury|management|mango|marketing|menu|mil|mobi|moda|monash|museum|nagoya|name|net|neustar|ninja|' +
        'okinawa|onl|org|partners|parts|photo|photography|photos|pics|pink|plumbing|post|pro|productions|properties|' +
        'pub|qpon|recipes|red|rentals|repair|report|reviews|rich|ruhr|sexy|shiksha|shoes|singles|social|solar|' +
        'solutions|supplies|supply|support|systems|tattoo|technology|tel|tienda|tips|today|tokyo|tools|training|' +
        'travel|uno|vacations|ventures|viajes|villas|vision|vote|voting|voto|voyage|wang|watch|wed|wien|wiki|works|' +
        'xxx|xyz|zone|дети|онлайн|орг|сайт|بازار|شبكة|みんな|中信|中文网|公司|公益|在线|我爱你|政务|游戏|移动|网络|集团|삼성)' +
        '(?=[^0-9a-zA-Z@]|$))'));
    twttr.txt.regexen.validCCTLD = regexSupplant(RegExp(
        '(?:(?:ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bl|bm|bn|bo|bq|br|bs|' +
        'bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cw|cx|cy|cz|de|dj|dk|dm|do|dz|ec|ee|eg|eh|er|es|' +
        'et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|' +
        'im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|' +
        'me|mf|mg|mh|mk|ml|mm|mn|mo|mp|mq|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|om|pa|pe|pf|' +
        'pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|ss|st|su|sv|' +
        'sx|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|' +
        'ye|yt|za|zm|zw|мон|рф|срб|укр|қаз|الاردن|الجزائر|السعودية|المغرب|امارات|ایران|بھارت|تونس|سودان|سورية|عمان|فلسطين|قطر|مصر|مليسيا|پاکستان|' +
        'भारत|বাংলা|ভারত|ਭਾਰਤ|ભારત|இந்தியா|இலங்கை|சிங்கப்பூர்|భారత్|ලංකා|ไทย|გე|中国|中國|台湾|台灣|新加坡|' +
        '香港|한국)(?=[^0-9a-zA-Z@]|$))'));
    twttr.txt.regexen.validPunycode = regexSupplant(/(?:xn--[0-9a-z]+)/);
    twttr.txt.regexen.validDomain = regexSupplant(/(?:#{validSubdomain}*#{validDomainName}(?:#{validGTLD}|#{validCCTLD}|#{validPunycode}))/);
    twttr.txt.regexen.validAsciiDomain = regexSupplant(/(?:(?:[\-a-z0-9#{latinAccentChars}]+)\.)+(?:#{validGTLD}|#{validCCTLD}|#{validPunycode})/gi);
    twttr.txt.regexen.invalidShortDomain = regexSupplant(/^#{validDomainName}#{validCCTLD}$/i);

    twttr.txt.regexen.validPortNumber = regexSupplant(/[0-9]+/);

    twttr.txt.regexen.validGeneralUrlPathChars = regexSupplant(/[a-z0-9!\*';:=\+,\.\$\/%#\[\]\-_~@|&#{latinAccentChars}]/i);
    // Allow URL paths to contain up to two nested levels of balanced parens
    //  1. Used in Wikipedia URLs like /Primer_(film)
    //  2. Used in IIS sessions like /S(dfd346)/
    //  3. Used in Rdio URLs like /track/We_Up_(Album_Version_(Edited))/
    twttr.txt.regexen.validUrlBalancedParens = regexSupplant(
        '\\('                                   +
        '(?:'                                 +
        '#{validGeneralUrlPathChars}+'      +
        '|'                                 +
            // allow one nested level of balanced parentheses
        '(?:'                               +
        '#{validGeneralUrlPathChars}*'    +
        '\\('                             +
        '#{validGeneralUrlPathChars}+'  +
        '\\)'                             +
        '#{validGeneralUrlPathChars}*'    +
        ')'                                 +
        ')'                                   +
        '\\)'
        , 'i');
    // Valid end-of-path chracters (so /foo. does not gobble the period).
    // 1. Allow =&# for empty URL parameters and other URL-join artifacts
    twttr.txt.regexen.validUrlPathEndingChars = regexSupplant(/[\+\-a-z0-9=_#\/#{latinAccentChars}]|(?:#{validUrlBalancedParens})/i);
    // Allow @ in a url, but only in the middle. Catch things like http://example.com/@user/
    twttr.txt.regexen.validUrlPath = regexSupplant('(?:' +
    '(?:' +
    '#{validGeneralUrlPathChars}*' +
    '(?:#{validUrlBalancedParens}#{validGeneralUrlPathChars}*)*' +
    '#{validUrlPathEndingChars}'+
    ')|(?:@#{validGeneralUrlPathChars}+\/)'+
    ')', 'i');

    twttr.txt.regexen.validUrlQueryChars = /[a-z0-9!?\*'@\(\);:&=\+\$\/%#\[\]\-_\.,~|]/i;
    twttr.txt.regexen.validUrlQueryEndingChars = /[a-z0-9_&=#\/]/i;
    twttr.txt.regexen.extractUrl = regexSupplant(
        '('                                                            + // $1 total match
        '(#{validUrlPrecedingChars})'                                + // $2 Preceeding chracter
        '('                                                          + // $3 URL
        '(https?:\\/\\/)?'                                         + // $4 Protocol (optional)
        '(#{validDomain})'                                         + // $5 Domain(s)
        '(?::(#{validPortNumber}))?'                               + // $6 Port number (optional)
        '(\\/#{validUrlPath}*)?'                                   + // $7 URL Path
        '(\\?#{validUrlQueryChars}*#{validUrlQueryEndingChars})?'  + // $8 Query String
        ')'                                                          +
        ')'
        , 'gi');

    twttr.txt.regexen.validTcoUrl = /^https?:\/\/t\.co\/[a-z0-9]+/i;
    twttr.txt.regexen.urlHasProtocol = /^https?:\/\//i;
    twttr.txt.regexen.urlHasHttps = /^https:\/\//i;

    // cashtag related regex
    twttr.txt.regexen.cashtag = /[a-z]{1,6}(?:[._][a-z]{1,2})?/i;
    twttr.txt.regexen.validCashtag = regexSupplant('(^|#{spaces})(\\$)(#{cashtag})(?=$|\\s|[#{punct}])', 'gi');

    // These URL validation pattern strings are based on the ABNF from RFC 3986
    twttr.txt.regexen.validateUrlUnreserved = /[a-z0-9\-._~]/i;
    twttr.txt.regexen.validateUrlPctEncoded = /(?:%[0-9a-f]{2})/i;
    twttr.txt.regexen.validateUrlSubDelims = /[!$&'()*+,;=]/i;
    twttr.txt.regexen.validateUrlPchar = regexSupplant('(?:' +
    '#{validateUrlUnreserved}|' +
    '#{validateUrlPctEncoded}|' +
    '#{validateUrlSubDelims}|' +
    '[:|@]' +
    ')', 'i');

    twttr.txt.regexen.validateUrlScheme = /(?:[a-z][a-z0-9+\-.]*)/i;
    twttr.txt.regexen.validateUrlUserinfo = regexSupplant('(?:' +
    '#{validateUrlUnreserved}|' +
    '#{validateUrlPctEncoded}|' +
    '#{validateUrlSubDelims}|' +
    ':' +
    ')*', 'i');

    twttr.txt.regexen.validateUrlDecOctet = /(?:[0-9]|(?:[1-9][0-9])|(?:1[0-9]{2})|(?:2[0-4][0-9])|(?:25[0-5]))/i;
    twttr.txt.regexen.validateUrlIpv4 = regexSupplant(/(?:#{validateUrlDecOctet}(?:\.#{validateUrlDecOctet}){3})/i);

    // Punting on real IPv6 validation for now
    twttr.txt.regexen.validateUrlIpv6 = /(?:\[[a-f0-9:\.]+\])/i;

    // Also punting on IPvFuture for now
    twttr.txt.regexen.validateUrlIp = regexSupplant('(?:' +
    '#{validateUrlIpv4}|' +
    '#{validateUrlIpv6}' +
    ')', 'i');

    // This is more strict than the rfc specifies
    twttr.txt.regexen.validateUrlSubDomainSegment = /(?:[a-z0-9](?:[a-z0-9_\-]*[a-z0-9])?)/i;
    twttr.txt.regexen.validateUrlDomainSegment = /(?:[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?)/i;
    twttr.txt.regexen.validateUrlDomainTld = /(?:[a-z](?:[a-z0-9\-]*[a-z0-9])?)/i;
    twttr.txt.regexen.validateUrlDomain = regexSupplant(/(?:(?:#{validateUrlSubDomainSegment]}\.)*(?:#{validateUrlDomainSegment]}\.)#{validateUrlDomainTld})/i);

    twttr.txt.regexen.validateUrlHost = regexSupplant('(?:' +
    '#{validateUrlIp}|' +
    '#{validateUrlDomain}' +
    ')', 'i');

    // Unencoded internationalized domains - this doesn't check for invalid UTF-8 sequences
    twttr.txt.regexen.validateUrlUnicodeSubDomainSegment = /(?:(?:[a-z0-9]|[^\u0000-\u007f])(?:(?:[a-z0-9_\-]|[^\u0000-\u007f])*(?:[a-z0-9]|[^\u0000-\u007f]))?)/i;
    twttr.txt.regexen.validateUrlUnicodeDomainSegment = /(?:(?:[a-z0-9]|[^\u0000-\u007f])(?:(?:[a-z0-9\-]|[^\u0000-\u007f])*(?:[a-z0-9]|[^\u0000-\u007f]))?)/i;
    twttr.txt.regexen.validateUrlUnicodeDomainTld = /(?:(?:[a-z]|[^\u0000-\u007f])(?:(?:[a-z0-9\-]|[^\u0000-\u007f])*(?:[a-z0-9]|[^\u0000-\u007f]))?)/i;
    twttr.txt.regexen.validateUrlUnicodeDomain = regexSupplant(/(?:(?:#{validateUrlUnicodeSubDomainSegment}\.)*(?:#{validateUrlUnicodeDomainSegment}\.)#{validateUrlUnicodeDomainTld})/i);

    twttr.txt.regexen.validateUrlUnicodeHost = regexSupplant('(?:' +
    '#{validateUrlIp}|' +
    '#{validateUrlUnicodeDomain}' +
    ')', 'i');

    twttr.txt.regexen.validateUrlPort = /[0-9]{1,5}/;

    twttr.txt.regexen.validateUrlUnicodeAuthority = regexSupplant(
        '(?:(#{validateUrlUserinfo})@)?'  + // $1 userinfo
        '(#{validateUrlUnicodeHost})'     + // $2 host
        '(?::(#{validateUrlPort}))?'        //$3 port
        , "i");

    twttr.txt.regexen.validateUrlAuthority = regexSupplant(
        '(?:(#{validateUrlUserinfo})@)?' + // $1 userinfo
        '(#{validateUrlHost})'           + // $2 host
        '(?::(#{validateUrlPort}))?'       // $3 port
        , "i");

    twttr.txt.regexen.validateUrlPath = regexSupplant(/(\/#{validateUrlPchar}*)*/i);
    twttr.txt.regexen.validateUrlQuery = regexSupplant(/(#{validateUrlPchar}|\/|\?)*/i);
    twttr.txt.regexen.validateUrlFragment = regexSupplant(/(#{validateUrlPchar}|\/|\?)*/i);

    // Modified version of RFC 3986 Appendix B
    twttr.txt.regexen.validateUrlUnencoded = regexSupplant(
        '^'                               + // Full URL
        '(?:'                             +
        '([^:/?#]+):\\/\\/'             + // $1 Scheme
        ')?'                              +
        '([^/?#]*)'                       + // $2 Authority
        '([^?#]*)'                        + // $3 Path
        '(?:'                             +
        '\\?([^#]*)'                    + // $4 Query
        ')?'                              +
        '(?:'                             +
        '#(.*)'                         + // $5 Fragment
        ')?$'
        , "i");


    // Default CSS class for auto-linked lists (along with the url class)
    var DEFAULT_LIST_CLASS = "tweet-url list-slug";
    // Default CSS class for auto-linked usernames (along with the url class)
    var DEFAULT_USERNAME_CLASS = "tweet-url username";
    // Default CSS class for auto-linked hashtags (along with the url class)
    var DEFAULT_HASHTAG_CLASS = "tweet-url hashtag";
    // Default CSS class for auto-linked cashtags (along with the url class)
    var DEFAULT_CASHTAG_CLASS = "tweet-url cashtag";
    // Options which should not be passed as HTML attributes
    var OPTIONS_NOT_ATTRIBUTES = {'urlClass':true, 'listClass':true, 'usernameClass':true, 'hashtagClass':true, 'cashtagClass':true,
        'usernameUrlBase':true, 'listUrlBase':true, 'hashtagUrlBase':true, 'cashtagUrlBase':true,
        'usernameUrlBlock':true, 'listUrlBlock':true, 'hashtagUrlBlock':true, 'linkUrlBlock':true,
        'usernameIncludeSymbol':true, 'suppressLists':true, 'suppressNoFollow':true, 'targetBlank':true,
        'suppressDataScreenName':true, 'urlEntities':true, 'symbolTag':true, 'textWithSymbolTag':true, 'urlTarget':true,
        'invisibleTagAttrs':true, 'linkAttributeBlock':true, 'linkTextBlock': true, 'htmlEscapeNonEntities': true
    };

    var BOOLEAN_ATTRIBUTES = {'disabled':true, 'readonly':true, 'multiple':true, 'checked':true};

    // Simple object cloning function for simple objects
    function clone(o) {
        var r = {};
        for (var k in o) {
            if (o.hasOwnProperty(k)) {
                r[k] = o[k];
            }
        }

        return r;
    }

    twttr.txt.tagAttrs = function(attributes) {
        var htmlAttrs = "";
        for (var k in attributes) {
            var v = attributes[k];
            if (BOOLEAN_ATTRIBUTES[k]) {
                v = v ? k : null;
            }
            if (v == null) continue;
            htmlAttrs += " " + twttr.txt.htmlEscape(k) + "=\"" + twttr.txt.htmlEscape(v.toString()) + "\"";
        }
        return htmlAttrs;
    };

    twttr.txt.linkToText = function(entity, text, attributes, options) {
        if (!options.suppressNoFollow) {
            attributes.rel = "nofollow";
        }
        // if linkAttributeBlock is specified, call it to modify the attributes
        if (options.linkAttributeBlock) {
            options.linkAttributeBlock(entity, attributes);
        }
        // if linkTextBlock is specified, call it to get a new/modified link text
        if (options.linkTextBlock) {
            text = options.linkTextBlock(entity, text);
        }
        var d = {
            text: text,
            attr: twttr.txt.tagAttrs(attributes)
        };
        return stringSupplant("<a#{attr}>#{text}</a>", d);
    };

    twttr.txt.linkToTextWithSymbol = function(entity, symbol, text, attributes, options) {
        var taggedSymbol = options.symbolTag ? "<" + options.symbolTag + ">" + symbol + "</"+ options.symbolTag + ">" : symbol;
        text = twttr.txt.htmlEscape(text);
        var taggedText = options.textWithSymbolTag ? "<" + options.textWithSymbolTag + ">" + text + "</"+ options.textWithSymbolTag + ">" : text;

        if (options.usernameIncludeSymbol || !symbol.match(twttr.txt.regexen.atSigns)) {
            return twttr.txt.linkToText(entity, taggedSymbol + taggedText, attributes, options);
        } else {
            return taggedSymbol + twttr.txt.linkToText(entity, taggedText, attributes, options);
        }
    };

    twttr.txt.linkToHashtag = function(entity, text, options) {
        var hash = text.substring(entity.indices[0], entity.indices[0] + 1);
        var hashtag = twttr.txt.htmlEscape(entity.hashtag);
        var attrs = clone(options.htmlAttrs || {});
        attrs.href = options.hashtagUrlBase + hashtag;
        attrs.title = "#" + hashtag;
        attrs["class"] = options.hashtagClass;
        if (hashtag.charAt(0).match(twttr.txt.regexen.rtl_chars)){
            attrs["class"] += " rtl";
        }
        if (options.targetBlank) {
            attrs.target = '_blank';
        }

        return twttr.txt.linkToTextWithSymbol(entity, hash, hashtag, attrs, options);
    };

    twttr.txt.linkToCashtag = function(entity, text, options) {
        var cashtag = twttr.txt.htmlEscape(entity.cashtag);
        var attrs = clone(options.htmlAttrs || {});
        attrs.href = options.cashtagUrlBase + cashtag;
        attrs.title = "$" + cashtag;
        attrs["class"] =  options.cashtagClass;
        if (options.targetBlank) {
            attrs.target = '_blank';
        }

        return twttr.txt.linkToTextWithSymbol(entity, "$", cashtag, attrs, options);
    };

    twttr.txt.linkToMentionAndList = function(entity, text, options) {
        var at = text.substring(entity.indices[0], entity.indices[0] + 1);
        var user = twttr.txt.htmlEscape(entity.screenName);
        var slashListname = twttr.txt.htmlEscape(entity.listSlug);
        var isList = entity.listSlug && !options.suppressLists;
        var attrs = clone(options.htmlAttrs || {});
        attrs["class"] = (isList ? options.listClass : options.usernameClass);
        attrs.href = isList ? options.listUrlBase + user + slashListname : options.usernameUrlBase + user;
        if (!isList && !options.suppressDataScreenName) {
            attrs['data-screen-name'] = user;
        }
        if (options.targetBlank) {
            attrs.target = '_blank';
        }

        return twttr.txt.linkToTextWithSymbol(entity, at, isList ? user + slashListname : user, attrs, options);
    };

    twttr.txt.linkToUrl = function(entity, text, options) {
        var url = entity.url;
        var displayUrl = url;
        var linkText = twttr.txt.htmlEscape(displayUrl);

        // If the caller passed a urlEntities object (provided by a Twitter API
        // response with include_entities=true), we use that to render the display_url
        // for each URL instead of it's underlying t.co URL.
        var urlEntity = (options.urlEntities && options.urlEntities[url]) || entity;
        if (urlEntity.display_url) {
            linkText = twttr.txt.linkTextWithEntity(urlEntity, options);
        }

        var attrs = clone(options.htmlAttrs || {});

        if (!url.match(twttr.txt.regexen.urlHasProtocol)) {
            url = "http://" + url;
        }
        attrs.href = url;

        if (options.targetBlank) {
            attrs.target = '_blank';
        }

        // set class only if urlClass is specified.
        if (options.urlClass) {
            attrs["class"] = options.urlClass;
        }

        // set target only if urlTarget is specified.
        if (options.urlTarget) {
            attrs.target = options.urlTarget;
        }

        if (!options.title && urlEntity.display_url) {
            attrs.title = urlEntity.expanded_url;
        }

        return twttr.txt.linkToText(entity, linkText, attrs, options);
    };

    twttr.txt.linkTextWithEntity = function (entity, options) {
        var displayUrl = entity.display_url;
        var expandedUrl = entity.expanded_url;

        // Goal: If a user copies and pastes a tweet containing t.co'ed link, the resulting paste
        // should contain the full original URL (expanded_url), not the display URL.
        //
        // Method: Whenever possible, we actually emit HTML that contains expanded_url, and use
        // font-size:0 to hide those parts that should not be displayed (because they are not part of display_url).
        // Elements with font-size:0 get copied even though they are not visible.
        // Note that display:none doesn't work here. Elements with display:none don't get copied.
        //
        // Additionally, we want to *display* ellipses, but we don't want them copied.  To make this happen we
        // wrap the ellipses in a tco-ellipsis class and provide an onCopy handler that sets display:none on
        // everything with the tco-ellipsis class.
        //
        // Exception: pic.twitter.com images, for which expandedUrl = "https://twitter.com/#!/username/status/1234/photo/1
        // For those URLs, display_url is not a substring of expanded_url, so we don't do anything special to render the elided parts.
        // For a pic.twitter.com URL, the only elided part will be the "https://", so this is fine.

        var displayUrlSansEllipses = displayUrl.replace(/…/g, ""); // We have to disregard ellipses for matching
        // Note: we currently only support eliding parts of the URL at the beginning or the end.
        // Eventually we may want to elide parts of the URL in the *middle*.  If so, this code will
        // become more complicated.  We will probably want to create a regexp out of display URL,
        // replacing every ellipsis with a ".*".
        if (expandedUrl.indexOf(displayUrlSansEllipses) != -1) {
            var displayUrlIndex = expandedUrl.indexOf(displayUrlSansEllipses);
            var v = {
                displayUrlSansEllipses: displayUrlSansEllipses,
                // Portion of expandedUrl that precedes the displayUrl substring
                beforeDisplayUrl: expandedUrl.substr(0, displayUrlIndex),
                // Portion of expandedUrl that comes after displayUrl
                afterDisplayUrl: expandedUrl.substr(displayUrlIndex + displayUrlSansEllipses.length),
                precedingEllipsis: displayUrl.match(/^…/) ? "…" : "",
                followingEllipsis: displayUrl.match(/…$/) ? "…" : ""
            };
            for (var k in v) {
                if (v.hasOwnProperty(k)) {
                    v[k] = twttr.txt.htmlEscape(v[k]);
                }
            }
            // As an example: The user tweets "hi http://longdomainname.com/foo"
            // This gets shortened to "hi http://t.co/xyzabc", with display_url = "…nname.com/foo"
            // This will get rendered as:
            // <span class='tco-ellipsis'> <!-- This stuff should get displayed but not copied -->
            //   …
            //   <!-- There's a chance the onCopy event handler might not fire. In case that happens,
            //        we include an &nbsp; here so that the … doesn't bump up against the URL and ruin it.
            //        The &nbsp; is inside the tco-ellipsis span so that when the onCopy handler *does*
            //        fire, it doesn't get copied.  Otherwise the copied text would have two spaces in a row,
            //        e.g. "hi  http://longdomainname.com/foo".
            //   <span style='font-size:0'>&nbsp;</span>
            // </span>
            // <span style='font-size:0'>  <!-- This stuff should get copied but not displayed -->
            //   http://longdomai
            // </span>
            // <span class='js-display-url'> <!-- This stuff should get displayed *and* copied -->
            //   nname.com/foo
            // </span>
            // <span class='tco-ellipsis'> <!-- This stuff should get displayed but not copied -->
            //   <span style='font-size:0'>&nbsp;</span>
            //   …
            // </span>
            v['invisible'] = options.invisibleTagAttrs;
            return stringSupplant("<span class='tco-ellipsis'>#{precedingEllipsis}<span #{invisible}>&nbsp;</span></span><span #{invisible}>#{beforeDisplayUrl}</span><span class='js-display-url'>#{displayUrlSansEllipses}</span><span #{invisible}>#{afterDisplayUrl}</span><span class='tco-ellipsis'><span #{invisible}>&nbsp;</span>#{followingEllipsis}</span>", v);
        }
        return displayUrl;
    };

    twttr.txt.autoLinkEntities = function(text, entities, options) {


        options = clone(options || {});

        options.hashtagClass = options.hashtagClass || sailrClasses.hashtagClass;
        options.hashtagUrlBase = options.hashtagUrlBase || sailrUrls.hashtagBase;
        options.cashtagClass = options.cashtagClass || sailrClasses.cashtagClass;
        options.cashtagUrlBase = options.cashtagUrlBase || sailrUrls.cashtagBase;
        options.listClass = options.listClass || DEFAULT_LIST_CLASS;
        options.usernameClass = options.usernameClass || sailrClasses.usernameClass
        options.usernameUrlBase = options.usernameUrlBase || sailrUrls.usernameBase;
        options.listUrlBase = options.listUrlBase || "https://sailr.co/"; //TODO: get rid of lists
        options.htmlAttrs = twttr.txt.extractHtmlAttrsFromOptions(options);
        options.invisibleTagAttrs = options.invisibleTagAttrs || "style='position:absolute;left:-9999px;'";

        // remap url entities to hash
        var urlEntities, i, len;
        if(options.urlEntities) {
            urlEntities = {};
            for(i = 0, len = options.urlEntities.length; i < len; i++) {
                urlEntities[options.urlEntities[i].url] = options.urlEntities[i];
            }
            options.urlEntities = urlEntities;
        }

        var result = "";
        var beginIndex = 0;

        // sort entities by start index
        entities.sort(function(a,b){ return a.indices[0] - b.indices[0]; });

        var nonEntity = options.htmlEscapeNonEntities ? twttr.txt.htmlEscape : function(text) {
            return text;
        };

        for (var i = 0; i < entities.length; i++) {
            var entity = entities[i];
            result += nonEntity(text.substring(beginIndex, entity.indices[0]));

            if (entity.url) {
                result += twttr.txt.linkToUrl(entity, text, options);
            } else if (entity.hashtag) {
                result += twttr.txt.linkToHashtag(entity, text, options);
            } else if (entity.screenName) {
                result += twttr.txt.linkToMentionAndList(entity, text, options);
            } else if (entity.cashtag) {
                result += twttr.txt.linkToCashtag(entity, text, options);
            }
            beginIndex = entity.indices[1];
        }
        result += nonEntity(text.substring(beginIndex, text.length));
        return result;
    };

    twttr.txt.autoLinkWithJSON = function(text, json, options) {
        // map JSON entity to twitter-text entity
        if (json.user_mentions) {
            for (var i = 0; i < json.user_mentions.length; i++) {
                // this is a @mention
                json.user_mentions[i].screenName = json.user_mentions[i].screen_name;
            }
        }

        if (json.hashtags) {
            for (var i = 0; i < json.hashtags.length; i++) {
                // this is a #hashtag
                json.hashtags[i].hashtag = json.hashtags[i].text;
            }
        }

        if (json.symbols) {
            for (var i = 0; i < json.symbols.length; i++) {
                // this is a $CASH tag
                json.symbols[i].cashtag = json.symbols[i].text;
            }
        }

        // concatenate all entities
        var entities = [];
        for (var key in json) {
            entities = entities.concat(json[key]);
        }

        // modify indices to UTF-16
        twttr.txt.modifyIndicesFromUnicodeToUTF16(text, entities);

        return twttr.txt.autoLinkEntities(text, entities, options);
    };

    twttr.txt.extractHtmlAttrsFromOptions = function(options) {
        var htmlAttrs = {};
        for (var k in options) {
            var v = options[k];
            if (OPTIONS_NOT_ATTRIBUTES[k]) continue;
            if (BOOLEAN_ATTRIBUTES[k]) {
                v = v ? k : null;
            }
            if (v == null) continue;
            htmlAttrs[k] = v;
        }
        return htmlAttrs;
    };

    twttr.txt.autoLink = function(text, options) {
        var entities = twttr.txt.extractEntitiesWithIndices(text, {extractUrlsWithoutProtocol: false});
        return twttr.txt.autoLinkEntities(text, entities, options);
    };

    twttr.txt.autoLinkUsernamesOrLists = function(text, options) {
        var entities = twttr.txt.extractMentionsOrListsWithIndices(text);
        return twttr.txt.autoLinkEntities(text, entities, options);
    };

    twttr.txt.autoLinkHashtags = function(text, options) {
        var entities = twttr.txt.extractHashtagsWithIndices(text);
        return twttr.txt.autoLinkEntities(text, entities, options);
    };

    twttr.txt.autoLinkCashtags = function(text, options) {
        var entities = twttr.txt.extractCashtagsWithIndices(text);
        return twttr.txt.autoLinkEntities(text, entities, options);
    };

    twttr.txt.autoLinkUrlsCustom = function(text, options) {
        var entities = twttr.txt.extractUrlsWithIndices(text, {extractUrlsWithoutProtocol: false});
        return twttr.txt.autoLinkEntities(text, entities, options);
    };

    twttr.txt.removeOverlappingEntities = function(entities) {
        entities.sort(function(a,b){ return a.indices[0] - b.indices[0]; });

        var prev = entities[0];
        for (var i = 1; i < entities.length; i++) {
            if (prev.indices[1] > entities[i].indices[0]) {
                entities.splice(i, 1);
                i--;
            } else {
                prev = entities[i];
            }
        }
    };

    twttr.txt.extractEntitiesWithIndices = function(text, options) {
        var entities = twttr.txt.extractUrlsWithIndices(text, options)
            .concat(twttr.txt.extractMentionsOrListsWithIndices(text))
            .concat(twttr.txt.extractHashtagsWithIndices(text, {checkUrlOverlap: false}))
            .concat(twttr.txt.extractCashtagsWithIndices(text));

        if (entities.length == 0) {
            return [];
        }

        twttr.txt.removeOverlappingEntities(entities);
        return entities;
    };

    twttr.txt.extractMentions = function(text) {
        var screenNamesOnly = [],
            screenNamesWithIndices = twttr.txt.extractMentionsWithIndices(text);

        for (var i = 0; i < screenNamesWithIndices.length; i++) {
            var screenName = screenNamesWithIndices[i].screenName;
            screenNamesOnly.push(screenName);
        }

        return screenNamesOnly;
    };

    twttr.txt.extractMentionsWithIndices = function(text) {
        var mentions = [],
            mentionOrList,
            mentionsOrLists = twttr.txt.extractMentionsOrListsWithIndices(text);

        for (var i = 0 ; i < mentionsOrLists.length; i++) {
            mentionOrList = mentionsOrLists[i];
            if (mentionOrList.listSlug == '') {
                mentions.push({
                    screenName: mentionOrList.screenName,
                    indices: mentionOrList.indices
                });
            }
        }

        return mentions;
    };

    /**
     * Extract list or user mentions.
     * (Presence of listSlug indicates a list)
     */
    twttr.txt.extractMentionsOrListsWithIndices = function(text) {
        if (!text || !text.match(twttr.txt.regexen.atSigns)) {
            return [];
        }

        var possibleNames = [],
            slashListname;

        text.replace(twttr.txt.regexen.validMentionOrList, function(match, before, atSign, screenName, slashListname, offset, chunk) {
            var after = chunk.slice(offset + match.length);
            if (!after.match(twttr.txt.regexen.endMentionMatch)) {
                slashListname = slashListname || '';
                var startPosition = offset + before.length;
                var endPosition = startPosition + screenName.length + slashListname.length + 1;
                possibleNames.push({
                    screenName: screenName,
                    listSlug: slashListname,
                    indices: [startPosition, endPosition]
                });
            }
        });

        return possibleNames;
    };


    twttr.txt.extractReplies = function(text) {
        if (!text) {
            return null;
        }

        var possibleScreenName = text.match(twttr.txt.regexen.validReply);
        if (!possibleScreenName ||
            RegExp.rightContext.match(twttr.txt.regexen.endMentionMatch)) {
            return null;
        }

        return possibleScreenName[1];
    };

    twttr.txt.extractUrls = function(text, options) {
        var urlsOnly = [],
            urlsWithIndices = twttr.txt.extractUrlsWithIndices(text, options);

        for (var i = 0; i < urlsWithIndices.length; i++) {
            urlsOnly.push(urlsWithIndices[i].url);
        }

        return urlsOnly;
    };

    twttr.txt.extractUrlsWithIndices = function(text, options) {
        if (!options) {
            options = {extractUrlsWithoutProtocol: true};
        }

        if (!text || (options.extractUrlsWithoutProtocol ? !text.match(/\./) : !text.match(/:/))) {
            return [];
        }

        var urls = [];

        while (twttr.txt.regexen.extractUrl.exec(text)) {
            var before = RegExp.$2, url = RegExp.$3, protocol = RegExp.$4, domain = RegExp.$5, path = RegExp.$7;
            var endPosition = twttr.txt.regexen.extractUrl.lastIndex,
                startPosition = endPosition - url.length;

            // if protocol is missing and domain contains non-ASCII characters,
            // extract ASCII-only domains.
            if (!protocol) {
                if (!options.extractUrlsWithoutProtocol
                    || before.match(twttr.txt.regexen.invalidUrlWithoutProtocolPrecedingChars)) {
                    continue;
                }
                var lastUrl = null,
                    lastUrlInvalidMatch = false,
                    asciiEndPosition = 0;
                domain.replace(twttr.txt.regexen.validAsciiDomain, function(asciiDomain) {
                    var asciiStartPosition = domain.indexOf(asciiDomain, asciiEndPosition);
                    asciiEndPosition = asciiStartPosition + asciiDomain.length;
                    lastUrl = {
                        url: asciiDomain,
                        indices: [startPosition + asciiStartPosition, startPosition + asciiEndPosition]
                    };
                    lastUrlInvalidMatch = asciiDomain.match(twttr.txt.regexen.invalidShortDomain);
                    if (!lastUrlInvalidMatch) {
                        urls.push(lastUrl);
                    }
                });

                // no ASCII-only domain found. Skip the entire URL.
                if (lastUrl == null) {
                    continue;
                }

                // lastUrl only contains domain. Need to add path and query if they exist.
                if (path) {
                    if (lastUrlInvalidMatch) {
                        urls.push(lastUrl);
                    }
                    lastUrl.url = url.replace(domain, lastUrl.url);
                    lastUrl.indices[1] = endPosition;
                }
            } else {
                // In the case of t.co URLs, don't allow additional path characters.
                if (url.match(twttr.txt.regexen.validTcoUrl)) {
                    url = RegExp.lastMatch;
                    endPosition = startPosition + url.length;
                }
                urls.push({
                    url: url,
                    indices: [startPosition, endPosition]
                });
            }
        }

        return urls;
    };

    twttr.txt.extractHashtags = function(text) {
        var hashtagsOnly = [],
            hashtagsWithIndices = twttr.txt.extractHashtagsWithIndices(text);

        for (var i = 0; i < hashtagsWithIndices.length; i++) {
            hashtagsOnly.push(hashtagsWithIndices[i].hashtag);
        }

        return hashtagsOnly;
    };

    twttr.txt.extractHashtagsWithIndices = function(text, options) {
        if (!options) {
            options = {checkUrlOverlap: true};
        }

        if (!text || !text.match(twttr.txt.regexen.hashSigns)) {
            return [];
        }

        var tags = [];

        text.replace(twttr.txt.regexen.validHashtag, function(match, before, hash, hashText, offset, chunk) {
            var after = chunk.slice(offset + match.length);
            if (after.match(twttr.txt.regexen.endHashtagMatch))
                return;
            var startPosition = offset + before.length;
            var endPosition = startPosition + hashText.length + 1;
            tags.push({
                hashtag: hashText,
                indices: [startPosition, endPosition]
            });
        });

        if (options.checkUrlOverlap) {
            // also extract URL entities
            var urls = twttr.txt.extractUrlsWithIndices(text);
            if (urls.length > 0) {
                var entities = tags.concat(urls);
                // remove overlap
                twttr.txt.removeOverlappingEntities(entities);
                // only push back hashtags
                tags = [];
                for (var i = 0; i < entities.length; i++) {
                    if (entities[i].hashtag) {
                        tags.push(entities[i]);
                    }
                }
            }
        }

        return tags;
    };

    twttr.txt.extractCashtags = function(text) {
        var cashtagsOnly = [],
            cashtagsWithIndices = twttr.txt.extractCashtagsWithIndices(text);

        for (var i = 0; i < cashtagsWithIndices.length; i++) {
            cashtagsOnly.push(cashtagsWithIndices[i].cashtag);
        }

        return cashtagsOnly;
    };

    twttr.txt.extractCashtagsWithIndices = function(text) {
        if (!text || text.indexOf("$") == -1) {
            return [];
        }

        var tags = [];

        text.replace(twttr.txt.regexen.validCashtag, function(match, before, dollar, cashtag, offset, chunk) {
            var startPosition = offset + before.length;
            var endPosition = startPosition + cashtag.length + 1;
            tags.push({
                cashtag: cashtag,
                indices: [startPosition, endPosition]
            });
        });

        return tags;
    };

    twttr.txt.modifyIndicesFromUnicodeToUTF16 = function(text, entities) {
        twttr.txt.convertUnicodeIndices(text, entities, false);
    };

    twttr.txt.modifyIndicesFromUTF16ToUnicode = function(text, entities) {
        twttr.txt.convertUnicodeIndices(text, entities, true);
    };

    twttr.txt.getUnicodeTextLength = function(text) {
        return text.replace(twttr.txt.regexen.non_bmp_code_pairs, ' ').length;
    };

    twttr.txt.convertUnicodeIndices = function(text, entities, indicesInUTF16) {
        if (entities.length == 0) {
            return;
        }

        var charIndex = 0;
        var codePointIndex = 0;

        // sort entities by start index
        entities.sort(function(a,b){ return a.indices[0] - b.indices[0]; });
        var entityIndex = 0;
        var entity = entities[0];

        while (charIndex < text.length) {
            if (entity.indices[0] == (indicesInUTF16 ? charIndex : codePointIndex)) {
                var len = entity.indices[1] - entity.indices[0];
                entity.indices[0] = indicesInUTF16 ? codePointIndex : charIndex;
                entity.indices[1] = entity.indices[0] + len;

                entityIndex++;
                if (entityIndex == entities.length) {
                    // no more entity
                    break;
                }
                entity = entities[entityIndex];
            }

            var c = text.charCodeAt(charIndex);
            if (0xD800 <= c && c <= 0xDBFF && charIndex < text.length - 1) {
                // Found high surrogate char
                c = text.charCodeAt(charIndex + 1);
                if (0xDC00 <= c && c <= 0xDFFF) {
                    // Found surrogate pair
                    charIndex++;
                }
            }
            codePointIndex++;
            charIndex++;
        }
    };

    // this essentially does text.split(/<|>/)
    // except that won't work in IE, where empty strings are ommitted
    // so "<>".split(/<|>/) => [] in IE, but is ["", "", ""] in all others
    // but "<<".split("<") => ["", "", ""]
    twttr.txt.splitTags = function(text) {
        var firstSplits = text.split("<"),
            secondSplits,
            allSplits = [],
            split;

        for (var i = 0; i < firstSplits.length; i += 1) {
            split = firstSplits[i];
            if (!split) {
                allSplits.push("");
            } else {
                secondSplits = split.split(">");
                for (var j = 0; j < secondSplits.length; j += 1) {
                    allSplits.push(secondSplits[j]);
                }
            }
        }

        return allSplits;
    };

    twttr.txt.hitHighlight = function(text, hits, options) {
        var defaultHighlightTag = "em";

        hits = hits || [];
        options = options || {};

        if (hits.length === 0) {
            return text;
        }

        var tagName = options.tag || defaultHighlightTag,
            tags = ["<" + tagName + ">", "</" + tagName + ">"],
            chunks = twttr.txt.splitTags(text),
            i,
            j,
            result = "",
            chunkIndex = 0,
            chunk = chunks[0],
            prevChunksLen = 0,
            chunkCursor = 0,
            startInChunk = false,
            chunkChars = chunk,
            flatHits = [],
            index,
            hit,
            tag,
            placed,
            hitSpot;

        for (i = 0; i < hits.length; i += 1) {
            for (j = 0; j < hits[i].length; j += 1) {
                flatHits.push(hits[i][j]);
            }
        }

        for (index = 0; index < flatHits.length; index += 1) {
            hit = flatHits[index];
            tag = tags[index % 2];
            placed = false;

            while (chunk != null && hit >= prevChunksLen + chunk.length) {
                result += chunkChars.slice(chunkCursor);
                if (startInChunk && hit === prevChunksLen + chunkChars.length) {
                    result += tag;
                    placed = true;
                }

                if (chunks[chunkIndex + 1]) {
                    result += "<" + chunks[chunkIndex + 1] + ">";
                }

                prevChunksLen += chunkChars.length;
                chunkCursor = 0;
                chunkIndex += 2;
                chunk = chunks[chunkIndex];
                chunkChars = chunk;
                startInChunk = false;
            }

            if (!placed && chunk != null) {
                hitSpot = hit - prevChunksLen;
                result += chunkChars.slice(chunkCursor, hitSpot) + tag;
                chunkCursor = hitSpot;
                if (index % 2 === 0) {
                    startInChunk = true;
                } else {
                    startInChunk = false;
                }
            } else if(!placed) {
                placed = true;
                result += tag;
            }
        }

        if (chunk != null) {
            if (chunkCursor < chunkChars.length) {
                result += chunkChars.slice(chunkCursor);
            }
            for (index = chunkIndex + 1; index < chunks.length; index += 1) {
                result += (index % 2 === 0 ? chunks[index] : "<" + chunks[index] + ">");
            }
        }

        return result;
    };

    var MAX_LENGTH = 140;

    // Characters not allowed in Tweets
    var INVALID_CHARACTERS = [
        // BOM
        fromCode(0xFFFE),
        fromCode(0xFEFF),

        // Special
        fromCode(0xFFFF),

        // Directional Change
        fromCode(0x202A),
        fromCode(0x202B),
        fromCode(0x202C),
        fromCode(0x202D),
        fromCode(0x202E)
    ];

    // Returns the length of Tweet text with consideration to t.co URL replacement
    // and chars outside the basic multilingual plane that use 2 UTF16 code points
    twttr.txt.getTweetLength = function(text, options) {
        if (!options) {
            options = {
                // These come from https://api.twitter.com/1/help/configuration.json
                // described by https://dev.twitter.com/docs/api/1/get/help/configuration
                short_url_length: 22,
                short_url_length_https: 23
            };
        }
        var textLength = twttr.txt.getUnicodeTextLength(text),
            urlsWithIndices = twttr.txt.extractUrlsWithIndices(text);
        twttr.txt.modifyIndicesFromUTF16ToUnicode(text, urlsWithIndices);

        for (var i = 0; i < urlsWithIndices.length; i++) {
            // Subtract the length of the original URL
            textLength += urlsWithIndices[i].indices[0] - urlsWithIndices[i].indices[1];

            // Add 23 characters for URL starting with https://
            // Otherwise add 22 characters
            if (urlsWithIndices[i].url.toLowerCase().match(twttr.txt.regexen.urlHasHttps)) {
                textLength += options.short_url_length_https;
            } else {
                textLength += options.short_url_length;
            }
        }

        return textLength;
    };

    // Check the text for any reason that it may not be valid as a Tweet. This is meant as a pre-validation
    // before posting to api.twitter.com. There are several server-side reasons for Tweets to fail but this pre-validation
    // will allow quicker feedback.
    //
    // Returns false if this text is valid. Otherwise one of the following strings will be returned:
    //
    //   "too_long": if the text is too long
    //   "empty": if the text is nil or empty
    //   "invalid_characters": if the text contains non-Unicode or any of the disallowed Unicode characters
    twttr.txt.isInvalidTweet = function(text) {
        if (!text) {
            return "empty";
        }

        // Determine max length independent of URL length
        if (twttr.txt.getTweetLength(text) > MAX_LENGTH) {
            return "too_long";
        }

        for (var i = 0; i < INVALID_CHARACTERS.length; i++) {
            if (text.indexOf(INVALID_CHARACTERS[i]) >= 0) {
                return "invalid_characters";
            }
        }

        return false;
    };

    twttr.txt.isValidTweetText = function(text) {
        return !twttr.txt.isInvalidTweet(text);
    };

    twttr.txt.isValidUsername = function(username) {
        if (!username) {
            return false;
        }

        var extracted = twttr.txt.extractMentions(username);

        // Should extract the username minus the @ sign, hence the .slice(1)
        return extracted.length === 1 && extracted[0] === username.slice(1);
    };

    var VALID_LIST_RE = regexSupplant(/^#{validMentionOrList}$/);

    twttr.txt.isValidList = function(usernameList) {
        var match = usernameList.match(VALID_LIST_RE);

        // Must have matched and had nothing before or after
        return !!(match && match[1] == "" && match[4]);
    };

    twttr.txt.isValidHashtag = function(hashtag) {
        if (!hashtag) {
            return false;
        }

        var extracted = twttr.txt.extractHashtags(hashtag);

        // Should extract the hashtag minus the # sign, hence the .slice(1)
        return extracted.length === 1 && extracted[0] === hashtag.slice(1);
    };

    twttr.txt.isValidUrl = function(url, unicodeDomains, requireProtocol) {
        if (unicodeDomains == null) {
            unicodeDomains = true;
        }

        if (requireProtocol == null) {
            requireProtocol = true;
        }

        if (!url) {
            return false;
        }

        var urlParts = url.match(twttr.txt.regexen.validateUrlUnencoded);

        if (!urlParts || urlParts[0] !== url) {
            return false;
        }

        var scheme = urlParts[1],
            authority = urlParts[2],
            path = urlParts[3],
            query = urlParts[4],
            fragment = urlParts[5];

        if (!(
            (!requireProtocol || (isValidMatch(scheme, twttr.txt.regexen.validateUrlScheme) && scheme.match(/^https?$/i))) &&
            isValidMatch(path, twttr.txt.regexen.validateUrlPath) &&
            isValidMatch(query, twttr.txt.regexen.validateUrlQuery, true) &&
            isValidMatch(fragment, twttr.txt.regexen.validateUrlFragment, true)
            )) {
            return false;
        }

        return (unicodeDomains && isValidMatch(authority, twttr.txt.regexen.validateUrlUnicodeAuthority)) ||
        (!unicodeDomains && isValidMatch(authority, twttr.txt.regexen.validateUrlAuthority));
    };

    function isValidMatch(string, regex, optional) {
        if (!optional) {
            // RegExp["$&"] is the text of the last match
            // blank strings are ok, but are falsy, so we check stringiness instead of truthiness
            return ((typeof string === "string") && string.match(regex) && RegExp["$&"] === string);
        }

        // RegExp["$&"] is the text of the last match
        return (!string || (string.match(regex) && RegExp["$&"] === string));
    }

    if (typeof module != 'undefined' && module.exports) {
        module.exports = twttr.txt;
    }

    if (typeof window != 'undefined') {
        if (window.twttr) {
            for (var prop in twttr) {
                window.twttr[prop] = twttr[prop];
            }
        } else {
            window.twttr = twttr;
        }
    }
})();
var app = angular.module('app', ['angularFileUpload', 'ngSanitize']);

app.run(['$rootScope', function ($rootScope) {
    if (baseURL) {
        $rootScope.baseURL = baseURL;
    }
    else {
        $rootScope.baseURL = 'https://sailr.co';
    }

    $rootScope.loggedInUser = loggedInUser;
}]);

var openFileBrowser = function () {
    document.getElementById('addFiles').click();
};
app.directive('contenteditable', function () {
    return {
        restrict: 'A', // only activate on element attribute
        require: '?ngModel', // get a hold of NgModelController
        link: function (scope, element, attrs, ngModel) {
            if (!ngModel) return; // do nothing if no ng-model

            // Specify how UI should be updated
            ngModel.$render = function () {
                element.html(ngModel.$viewValue);
            };

            // Listen for change events to enable binding
            element.on('blur keyup change input', function () {
                scope.$apply(read);
            });
            read(); // initialize

            // Write data to the model
            function read() {
                var html = element.html();
                // When we clear the content editable the browser leaves a <br> behind
                // If strip-br attribute is provided then we strip this out
                if (attrs.stripBr && html == '<br>') {
                    html = '';
                }
                ngModel.$setViewValue(html);
            }
        }
    };
});

app.directive('num-binding', function () {
    return {
        restrict: 'A',
        require: 'ngModel',
        scope: {
            model: '=ngModel'
        },
        link: function (scope, element, attrs, ngModelCtrl) {
            if (scope.model && typeof scope.model == 'string') {
                scope.model = parseInt(scope.model);
            }
        }
    };
});


app.directive('sailrFooter', ['$document', '$window', function ($document, $window) {
    return {
        scope: false,
        link: function(scope, element, attrs) {

            var positionFooter = function() {
                var hasVScroll = document.body.scrollHeight | document.body.clientHeight > $window.innerHeight;
                //console.log(element.style.height);

                var newTop = $window.document.body.scrollHeight;// + element.style.height;
                var stringNewHeight = newTop + 'px';
                if (hasVScroll) {
                    element.css({
                        position: 'relative',
                        top: '100%'
                    });
                }

                else {
                    element.css({
                        position: 'absolute',
                        top: stringNewHeight
                    });
                }
            };

            positionFooter();

            scope.$watch($window.innerHeight, function(newValue, oldValue) {
                positionFooter();

            });


        }
    }


}]);

app.directive('sailrOpactiy', function () {

    function link(scope, iElement, iAttrs) {

        scope.sailrOpacity = iAttrs.sailrOpacity;
        iElement.css({
            opacity: scope.sailrOpacity
        });
    }

    return {
        restrict: 'A',
        scope: {
            sailrOpacity: '@'
        },
        link: link
    }
});


app.directive('sailrComments', function () {
    return {
        restrict: 'AE',
        require: '^sailrProductId',
        scope: {
            sailrProductId: '@'
        },
        controller: ['$scope', '$http', 'CommentsFactory', function ($scope, $http, CommentsFactory) {

            $scope.comments = [];
            $scope.commentOpacity = 1.00;
            $scope.webError = false;

            $scope.newComment = {};
            $scope.loggedInUser = loggedInUser;
            $scope.item_id = 0;

            $scope.getComments = function (product_id) {
                //console.log('PRODUCT ID:: ' + product_id);
                $scope.item_id = product_id;


                var getCommentsPromise = CommentsFactory.getComments($scope.item_id);

                getCommentsPromise.then(function (successResponse) {
                        $scope.comments = successResponse.data;
                    },
                    function (failResponse) {
                        console.log(failResponse);
                        $scope.webError = true;
                    });

            };

            $scope.postNewComment = function () {
                var newCommentIndex = $scope.comments.unshift({
                    item_id: $scope.item_id,
                    comment: $scope.newComment.comment,
                    created_at: new Date(),
                    user: loggedInUser
                });

                var newCommentPromise = CommentsFactory.postNewComment($scope.newComment.comment, $scope.item_id);
                console.log(CommentsFactory);
                console.log(newCommentPromise);

                newCommentPromise.then(function (successResponse) {
                        console.log(successResponse);
                        $scope.newComment.comment = '';
                        //Set opacity to 1.00
                    },
                    function (failResponse) {
                        console.log(failResponse);
                        $scope.comments = $scope.comments.splice(1, newCommentIndex);
                    });

            }
        }],

        templateUrl: baseURL + '/js/templates/comments/master.html',
        link: function (scope, iElement, iAttrs) {
            scope.getComments(iAttrs.sailrProductId);
            scope.item_id = iAttrs.sailrProductId;
        }
    }
});

app.directive('sailrProductId', function () {
    return {
        controller: ['$scope', function ($scope) {}]
    }
});

app.directive('sailrComment', function () {
    return {
        restrict: 'AE',
        scope: {
            profileImageUrl: '@',
            username: '@',
            name: '@',
            commentText: '@'
        },
        controller: ['$scope', function ($scope) {

        }],

        link: function (scope, iElement, iAttrs) {
            scope.username = iAttrs.username;
            scope.name = iAttrs.name;
            scope.profileImageUrl = iAttrs.profileImageUrl;
            scope.commentText = iAttrs.commentText;
            scope.baseURL = baseURL;

        },

        templateUrl: baseURL + '/js/templates/comments/comment-item.html'

    }

});

app.directive('sailrEntityLink', function() {
    return {
        priority: -1,
        restrict: 'AE',
        scope: false,
        link: function(scope, iElement, iAttrs) {
            scope.$watch(iAttrs.sailrEntityLink, function(newValue, oldValue) {
                var tempHTML = iElement.html();
                iElement.html(twttr.txt.autoLink(tempHTML));
            });

        }
    }
});

app.directive('sailrFeedOnboardBox', function() {
    return {
        restrict: 'E',
        scope: false,
        templateUrl: baseURL + '/js/templates/onboard/feed/onboard-box.html'
    }
});

app.directive('sailrNumberOfProducts', function() {
    return {
        restrict: 'A',
        controller: ['$scope', function($scope){}]
    }
});

app.directive('sailrOffsetBy', function() {
    return {
        restrict: 'A',
        controller: ['$scope', function($scope){}]
    }
});

app.directive('sailrProductPreview', function() {
    return {
        restrict: 'AE',
        scope: {
            productTitle: '@',
            productPreviewImageUrl: '@',
            productLinkUrl: '@',
            productSellerUsername: '@',
            productSellerName: '@',
            productSellerUrl: '@'
        },

        templateUrl: baseURL + '/js/templates/onboard/recent/products/product-preview.html'
    }
});

app.directive('sailrRecentProducts', function() {
    return {
        restrict: 'AE',
        require: ['sailrNumberOfProducts', 'sailrOffsetBy'],
        scope: {
            sailrNumberOfProducts: '@',
            sailrOffsetBy: '@'

        },

        controller: ['$scope', '$http', 'OnboardFactory', function ($scope, $http, OnboardFactory) {

            $scope.baseURL = baseURL;
            $scope.products = [];
            $scope.initialValue = 00;

            $scope.getProducts = function (offset, limit) {

                OnboardFactory.getRecentProducts(offset, limit)
                    .success(function(data, status, headers) {
                        /*append all the things */
                        angular.forEach(data, function (value) {
                            $scope.products.push(value);
                        });

                        //console.log('NUMBER OF ELEMENTS IN ARRAY:: ' + $scope.products.length);

                    })
                    .error(function(data, status, headers) {
                        $scope.webError = true;
                        //console.log(data);
                    });

            };

        }],

        templateUrl: baseURL + '/js/templates/onboard/recent/products/master.html',
        link: function(scope, elem, attrs) {

            var loadProducts = function(numberToLoad, offset) {
                scope.getProducts(offset, numberToLoad);
                return true;
            };

            scope.$watch(function() {
                return [attrs.sailrNumberOfProducts, attrs.sailrOffsetBy];
            }, function() {
                loadProducts(scope.sailrNumberOfProducts, scope.sailrOffsetBy);
            }, true);

        }

    }
});

app.directive('focusMe',['$timeout', '$parse', function ($timeout, $parse) {
    return {
        //scope: true,   // optionally create a child scope
        link: function (scope, element, attrs) {
            var model = $parse(attrs.focusMe);
            scope.$watch(model, function (value) {
                console.log('value=', value);
                if (value === true) {
                    $timeout(function () {
                        element[0].focus();
                    });
                }
            });
        }
    };
}]);

app.factory('OnboardFactory',['$http',  function($http){
    var service = {};

    service.getRecentProducts = function (offset, limit) {
        console.log('OFFSET IS ::' + offset);
        console.log('LIMIT IS:: ' + limit);
        var url = baseURL + '/onboard/recent/products/' + offset + '/' + limit;
        return $http.get(url);
    };

    return service;
}]);

app.factory('StripeFactory',['$q', function ($q) {
    var service = {};

    service.sayHello = function () {
        return 'HELLO FROM STRIPE FACTORY';
    };

    service.setPublishableKey = function (key) {
        Stripe.setPublishableKey(key);
    };


    service.createToken = function (cardData) {
        var defered = $q.defer();

        Stripe.card.createToken(cardData, function (status, response) {

            if (response.error) {
                service.errors = response.error;
                defered.reject(response);

            }

            else {
                service.token = response;
                defered.resolve(response);

            }
        });

        return defered.promise;
    };

    service.getToken = function () {
        return service.token;
    };

    service.getErrors = function () {
        return service.errors;
    };

    return service;
}]);

app.factory('HelperFactory', function () {
    var service = {};

    service.stripWhiteSpace = function (string) {
        string = string.replace(/\s/g, "");
        return string;
    };

    service.createStripeCardObjectFromFormattedInput = function (inputObject) {

        var returnCard = {};
        var expiryArray = service.stripWhiteSpace(inputObject.expiry).split('/');

        returnCard = {
            number: service.stripWhiteSpace(inputObject.number),
            cvc: service.stripWhiteSpace(inputObject.cvc),
            exp_month: expiryArray[0],
            exp_year: expiryArray[1]
        };

        /* If there is a cardholder name, add it to the card object */
        if (typeof inputObject.name !== 'undefined') {
            if (inputObject.name.length > 0) {
                returnCard.name = inputObject.name;
            }
        }

        return returnCard;

    };

    return service;
});


app.factory('SubscriptionFactory', ['$q', '$rootScope', '$http', function ($q, $rootScope, $http) {

    var service = {};
    service.subscriptionURL = baseURL + '/settings/subscription';
    console.log('SUBSCRIPTION URL:: ' + service.subscriptionURL);

    service.sayHello = function () {
        return 'HELLO FROM STRIPE FACTORY';
    };

    service.createSubscription = function (planID, stripeToken, couponCode) {

        var data = {
            _token: csrfToken,
            stripeToken: stripeToken,
            plan: 'awesome'
        };

        if(typeof couponCode !== 'undefined') {
            if(couponCode.length > 0) {
                data.coupon = couponCode;
            }
        }

        var defered = $q.defer();

        $http.post(service.subscriptionURL, data)
            .success(function (data, status) {
                defered.resolve(data);
            })

            .error(function (data, status, headers, config) {
                var rejectObject = {
                    data: data,
                    status: status,
                    headers: headers,
                    config: config
                };

                defered.reject(rejectObject);

            });

        return defered.promise;
    };

    service.cancelSubscription = function () {

        var configObject = {
            _token: csrfToken
            //_method: 'delete'
        };

        var defered = $q.defer();

        $http.post(service.subscriptionURL + '/delete', configObject)
            .success(function (data, status) {
                defered.resolve(data);
            })
            .error(function (data, status) {
                defered.reject(data);

            });

        return defered.promise;

    };

    return service;

}]);

app.factory('CommentsFactory',['$q', '$http', function ($q, $http) {

    var service = {};

    service.sayHello = function () {
        console.log('Hello from CommentsFactory');
        return 'Hello from CommentFactory';
    };

    service.postNewComment = function (commentText, productID) {
        console.log('Add new comment function called');
        var postObject = {
            _token: csrfToken,
            comment: commentText,
            item_id: productID
        };

        var defered = $q.defer();

        console.log('BASE URL:: ' + baseURL);
        console.log(postObject);

        $http.post(baseURL + '/comments', postObject)
            .success(function (data, status) {
                defered.resolve(data);
            })
            .error(function (data, status) {
                defered.reject(data);
            });

        return defered.promise;

    };

    service.getComments = function (productID) {
        var defered = $q.defer();

        $http.get(baseURL + '/username/product/' + productID + '/' + 'comments')
            .success(function (data, status) {
                defered.resolve(data);
            })
            .error(function (data, status) {
                defered.reject(data);
            });

        return defered.promise;

    };


    return service;


}]);

app.controller('homeController', ['$scope', '$interval', function ($scope, $interval) {

    $scope.theWord = 'fashion';

    $scope.numberOfProducts = 3;
    $scope.alreadyLoadedNumber = $scope.numberOfProducts;
    $scope.offsetLoadProducts = 0;
    $scope.loadMoreButtonPressCount = 0;

    $scope.showNowSignupText = false;

    $scope.loadMore = function (numberToLoad) {
        $scope.alreadyLoadedNumber = $scope.numberOfProducts + $scope.offsetLoadProducts;
        $scope.numberOfProducts = numberToLoad;
        $scope.offsetLoadProducts = $scope.alreadyLoadedNumber;

        $scope.loadMoreButtonPressCount++;
        if ($scope.loadMoreButtonPressCount > 2 && !$scope.showNowSignupText) {
            $scope.showNowSignupText = true;
        }
    };

    var i = 0;
    var length = words.length;
    var changeWord = $('#changeWord');

    $scope.doWordChange = function() {
       var intervalPromise = $interval(function() {
           changeWord.removeClass('animate-title-text-in');
            var word = words[i];
           $scope.theWord = word;

            changeWord.animate({'opacity': 0}, 600, function () {
                $(this).addClass('animate-title-text-in');
                $(this).text(word);
            }).animate({'opacity': 1}, 600);

            //console.log(word);
            i++;

            if (i >= length) {
                i = 0;
            }
        }, 2500);

        $scope.$on('$destroy', function () { $interval.cancel(intervalPromise); });
    };

    $scope.doWordChange();


}]);
/*
 var stripWhiteSpace = function (string) {
 string = string.replace(/\s/g, "");
 return string;
 };
 */

app.controller('billingController', ['$scope', '$http', 'HelperFactory', 'StripeFactory', function ($scope, $http, HelperFactory, StripeFactory) {

    $scope.showUpdateCard = false;
    $scope.baseURL = baseURL;
    $scope.card = {};
    $scope.token = {};
    $scope.posting = false;
    $scope.card.name = loggedInUser.name;
    $scope.card.last4 = last4;
    $scope.card.type = cardType;

    $scope.subscription = subscription;

    $scope.updateCard = function () {
        $scope.posting = true;

        var stripeCard = HelperFactory.createStripeCardObjectFromFormattedInput($scope.card);

        var StripePromise = StripeFactory.createToken(stripeCard);
        StripePromise.then(function (response) {
            console.log(response);
            console.log('SUCCESS!');
            //console.log('TOKEN:::: ' + JSON.stringify(StripeFactory.getToken()));
            console.log(StripeFactory.getToken());
            $scope.token = StripeFactory.getToken();
            $scope.token.id = StripeFactory.getToken().id;
            $scope.submitUpdate();


        }, function (response) {
            $scope.posting = false;
            console.log('Stripe card fail::::');
            console.log(response);
            humane.log(StripeFactory.getErrors().message);

        });

    };

    $scope.submitUpdate = function () {
        $scope.posting = true;
        var data = {
            'stripeToken': $scope.token.id,
            '_token': csrfToken
        };


        $http.put($scope.baseURL + '/settings/billing', data)
            .success(function (data, status, headers, config) {
                $scope.posting = false;
                humane.log(data.message);


                $scope.card.last4 = $scope.token.card.last4;
                $scope.card.type = $scope.token.card.type;


                if ($scope.showUpdateCard) {
                    $scope.showUpdateCard = false;
                }
            }).
            error(function (data, status, headers, config) {
                $scope.posting = false;
                if (!data) {
                    humane.log("We're afraid something went wrong and the card didn't update");
                }
                else {
                    humane.log(data.message);
                }
                if ($scope.showUpdateCard) {
                    $scope.showUpdateCard = false;
                }
            });

    };

    $scope.toggleShowingForm = function () {
        $scope.showUpdateCard = !$scope.showUpdateCard;
    }


}]);


app.controller('collectionsIndexController', ['$scope', '$http', function ($scope, $http) {

    var container = document.querySelector('#masonryContainer');
    var msnry = new Masonry(container, {
        columnWidth: 220,
       itemSelector: '.collection-item'
    });
    $scope.collections = {};
    $scope.loading = true;
    $scope.message = '';
    $scope.username = username;

    $scope.getCollections = function() {
      $http.get(baseURL + '/api/collections/' + username + '/all')
          .success(function(data) {
              $scope.loading = false;
             $scope.collections = data.collections;
          })
          .error(function(data) {
              $scope.loading = false;
            $scope.message = data.error;
          });
    };

    $scope.getCollections();
}]);


app.controller('feedContentController', ['$scope', function ($scope) {
    $scope.numberOfProducts = 6;
    $scope.alreadyLoadedNumber = $scope.numberOfProducts;
    $scope.offsetLoadProducts = 0;

    $scope.loadMoreRecentProducts = function (numberToLoad) {
        $scope.alreadyLoadedNumber = $scope.numberOfProducts + $scope.offsetLoadProducts;
        $scope.numberOfProducts = numberToLoad;
        $scope.offsetLoadProducts = $scope.alreadyLoadedNumber;
    }



}]);
app.controller('feedController', ['$scope', '$http', function ($scope, $http) {
    $scope.baseURL = baseURL;
    $scope.submitSearchForm = function() {
        window.location = $scope.baseURL + '/s/' + encodeURIComponent($scope.searchText);
    };

}]);
app.controller('editController', ['$scope', '$http', '$upload', '$timeout', function ($scope, $http, $upload, $timeout) {

    if (typeof itemModel != 'undefined') {
        itemModel.price = parseFloat(itemModel.price);
        itemModel.initial_units = parseFloat(itemModel.initial_units);
        itemModel.ship_price = parseFloat(itemModel.ship_price);
        itemModel.public = parseInt(itemModel.public);
    }

    $scope.countries = countries;
    $scope.currencies = currencies;
    $scope.item = {};

    if (itemModel.ships_to.length < 1) {
        itemModel.ships_to = undefined;
    }
    $scope.item = itemModel;
    $scope.photos = [];
    $scope.dataUrls = [];

    if("photos" in $scope.item) {
        $scope.photos = $scope.item.photos;
    }

    /* Set the inital values for these fields as the data binding is a bit dodgy */
    document.getElementById('title-heading').innerText = $scope.item.title;
    document.getElementById('item-description').innerHTML = $scope.item.description;
    // document.getElementById('item-price').value = $scope.item.price;
    document.scope = $scope;

    $scope.buttonPressed = function () {
        console.log($scope.desc);
        console.log($scope);
    };

    $scope.saveChanges = function () {
        $scope.posting = true;

        var data = $scope.item;
        data._token = sessionToken;
        console.log('the data to be sent is ' + JSON.stringify(data));

        $http.put(updateURL, JSON.stringify(data))
            .success(function (data, status, headers, config) {
                $scope.posting = false;
                $scope.responseData = data;
                console.log('The response data is: ');
                console.log($scope.responseData);
                //window.location = $scope.responseData.redirect_url;
                $scope.item.description = data.description;

            })

            .error(function (data, status, headers, config) {
                $scope.posting = false;
                console.log(data);


            });

    };

    $scope.toggleVisibility = function () {
        $scope.saveChanges();
        $scope.posting = true;

        var data = {
            _token: sessionToken
        };

        console.log('the data to be sent is ' + JSON.stringify(data));

        $http.put(updateURL  + '/toggle-visibility', JSON.stringify(data))
            .success(function (data, status, headers, config) {
                $scope.item.public = data.public;
                $scope.posting = false;

                humane.log(data.message);
                console.log('The response data is: ');
                console.log(data);

            })

            .error(function (data, status, headers, config) {
                $scope.posting = false;
                console.log(data);

                if (data.message) {

                    humane.log(data.message);
                }

                if (data.errors) {
                    console.log('ERORROS ARE');
                    console.log(data.errors);
                    humane.log(data.errors);
                }



            });

    };

    $scope.tempURL = '';
    $scope.uploading = false;
    $scope.onFileSelect = function ($files) {


        if ($scope.uploading) {
            humane.log('One at a time please.');
            return;
        }
        //$files: an array of files selected, each file has name, size, and type.
        for (var i = 0; i < $files.length; i++) {
            if ($files.length > 1) {
                humane.log('Please only one photo at a time.. We are only new here.');
                break;
            }
            var file = $files[i];
            if (window.FileReader && file.type.indexOf('image') > -1) {
                var fileReader = new FileReader();
                fileReader.readAsDataURL($files[i]);

                var tooBig = false;
                if (file.size > 7340032) {
                    tooBig = true;
                    console.log('File too large.');
                    humane.log('File is too large. Please try compressing it first.');
                    return;
                }


                    console.log('not too big');
                    var loadFile = function (fileReader, index) {
                        fileReader.onload = function (e) {
                            $timeout(function () {
                                //$scope.showCropBox(e);
                                $scope.dataUrls[index] = e.target.result;
                                console.log(e.target.result);
                                $scope.photos.push({url: e.target.result});
                                $scope.tempURL = e.target.result;
                            });
                        }
                    }(fileReader, i);


            }


            console.log(file);


            humane.log('Uploading...');
            $scope.uploading = true;

            $scope.upload = $upload.upload({
                url: baseURL + '/photo/upload/' + itemModel.id, //upload.php script, node.js route, or servlet url
                // method: 'POST' or 'PUT',
                // headers: {'header-key': 'header-value'},
                // withCredentials: true,
                data: {_token: csrfToken},
                file: file,
                fileFormDateName: 'photo'
                // or list of files: $files for html5 only
                /* set the file formData name ('Content-Desposition'). Default is 'file' */
                //fileFormDataName: myFile, //or a list of names for multiple files (html5).
                /* customize how data is added to formData. See #40#issuecomment-28612000 for sample code */
                //formDataAppender: function(formData, key, val){}
            }).progress(function (evt) {
                console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
            }).success(function (data, status, headers, config) {
                $scope.photos[$scope.photos.length -1].set_id = data.set_id;
                $scope.photos[$scope.photos.length -1].url = data.url;
                // file is uploaded successfully
                humane.log('Photo successfully uploaded!');
                console.log(data);
                console.log(status);
                console.log(headers);
                $scope.uploading = false;
            })
                .error(function (data, status, headers, config) {
                    $scope.photos = $scope.photos.slice($scope.photos.length -1, 1);
                    humane.log('Upload failed. ' + data);
                    console.log(data);
                    console.log(status);
                    console.log(headers);
                    $scope.uploading = false;
                });

        }

    };

    $scope.showCropBox = function(e) {
        //$('#crop-modal').modal('show');
        var image = new Image();
        image.src = e.target.result;

        image.onload = (function() {
            var canvas = document.createElement('canvas');
            canvas.width = 300;
            canvas.height = image.height * (300 / image.width);
            var ctx = canvas.getContext('2d');
            ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

            $('#image_input').html(['<img src="', canvas.toDataURL(), '"/>'].join(''));

            var img = $('#image_input img')[0];
            var canvas = document.createElement('canvas');

            $('#image_input img').Jcrop({
                bgColor: 'black',
                bgOpacity: .6,
                setSelect: [0, 0, 100, 100],
                aspectRatio: 1,
                //minSize: [150, 150],
                keySupport: false,
                //onSelect: imgSelect,
                onChange: imgSelect
            });


            function imgSelect(selection) {
                canvas.width = canvas.height = 100;

                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, selection.x, selection.y, selection.w, selection.h, 0, 0, canvas.width, canvas.height);

                $('#image_output').attr('src', canvas.toDataURL());
                $('#image_source').text(canvas.toDataURL());
            }
        });

        };

    $scope.deletePhoto = function($index) {
        var photo = $scope.photos[$index];
        var set_id = photo.set_id;
        photo.deleted = true;

        var deleteImageUrl = baseURL + '/photo/' + $scope.item.id;
        var data = {set_id: set_id, _token: csrfToken, _method: 'DELETE'};

        humane.log('Deleting...');
        $http.put(deleteImageUrl, JSON.stringify(data))
            .success(function (data, status, headers, config) {
                $scope.responseData = data;
                console.log('The response data is: ');
                console.log($scope.responseData);
                humane.log('Photo deleted successfully');

                if ($scope.item.public && data.message) {
                    humane.log(data.message);
                }

                if (data.item) {

                    $scope.item.public = data.item.public;
                }

                $scope.photos.splice($index, 1);

            })

            .error(function (data, status, headers, config) {
                console.log(data);
                humane.log('Photo deletion failed.');
                photo.deleted = false;

            });
    };


}]);
app.controller('indexController', ['$scope', '$http', function($scope, $http) {

    $scope.currency = 'USD';
    $scope.codes = currencyCodes;

    $scope.handleCodeChange = function ($index) {
        $scope.currency = $scope.codes[$index];
        console.log($scope.currency);
    };

    $scope.toggleAdd = function () {
        $scope.shouldShowAdd = !$scope.shouldShowAdd;
        $('#addItem').slideToggle(300);
        //console.log('Show pressed');
    };

    $scope.formSubmit = function () {
        $scope.posting = true;
        $scope.formData = {_token: csrfToken, title: $scope.title, currency: $scope.currency, price: $scope.price};
        console.log($scope.formData);

        $http.post('/products', JSON.stringify($scope.formData))
            .success(function (data, status) {
                //console.log('the data to be sent is ' + JSON.stringify(data));
                $scope.responseData = data;
                //console.log($scope.responseData);

                if (data.message) {
                    $scope.posting = false;
                    humane.log(data.message);
                }

                if (data.redirect_url) {
                    window.location = $scope.responseData.redirect_url;
                }

                $scope.posting = false;
            })

            .error(function (data, status, headers, config) {
                //console.log(data);
                $scope.posting = false;

            });
    };

}]);
app.controller('showController', ['$scope', function ($scope) {
    $scope.item = item;
    $scope.user = $scope.item.user;
    $scope.profile_img_url = $scope.user.profile_img.url;
}]);
app.controller('notificationsController', ['$scope', function ($scope) {
    $scope.notifications = sailr.notifications;
    $scope.baseURL = baseURL;

}]);
app.controller('searchController', ['$scope', '$http', function ($scope, $http) {
    $scope.results = sailr.results;
    $scope.baseURL = baseURL;

}]);
app.controller('chooseController', ['$scope', '$http', '$q', 'StripeFactory', 'HelperFactory', 'SubscriptionFactory', function ($scope, $http, $q, StripeFactory, HelperFactory, SubscriptionFactory) {

    $scope.showingCreditForm = false;
    $scope.showCardForm = false;
    $scope.posting = false;
    $scope.showCoupon = false;
    $scope.couponCode = '';

    //$scope.successSubscribe = false;
    $scope.card = {};

    $scope.toggleCouponShow = function() {
      $scope.showCoupon = !$scope.showCoupon;
    };
    $scope.subscribeToPlan = function(planID) {
        console.log('PLAN ID::: ' + planID);

        $scope.posting = true;

        var stripeCard = HelperFactory.createStripeCardObjectFromFormattedInput($scope.card);

        var StripePromise = StripeFactory.createToken(stripeCard);

        StripePromise.then(function(response)
        {
            console.log(response);
            console.log(StripeFactory.getToken());

            if ($scope.couponCode) {

                var createSubscriptionPromise = SubscriptionFactory.createSubscription(planID, StripeFactory.getToken().id, $scope.couponCode);
            }

            else {
                var createSubscriptionPromise = SubscriptionFactory.createSubscription(planID, StripeFactory.getToken().id);
            }


            createSubscriptionPromise.then(function(responseObject) {

                $scope.posting = false;
                $scope.showCardForm = false;
                //Success!
                console.log('SUCCESS on server subscription create');
                console.log(responseObject);
                humane.log(responseObject.message);
                window.location =  responseObject.redirect_url;
            },

            function(responseObject) {
                //Fail :(
                $scope.posting = false;
                $scope.$apply(function() {
                    $scope.posting = false;
                });

                console.log('Subscription fail::   --');
                console.log(responseObject);
                humane.log(responseObject.data.message);

            });


        }, function(response)
       {
           $scope.posting = false;
           console.log('Stripe card fail::::');
           console.log(response);

           humane.log(StripeFactory.getErrors().message);

       });

    };

    $scope.handleSubscribeButtonPressed = function() {
        if (!$scope.showingCreditForm) {
            //cardFormContainer.slideToggle();
            $scope.showCardForm = true;
            $scope.showingCreditForm = true;
        }


    }


}]);
app.controller('manageController', ['$scope', '$http', '$q', 'SubscriptionFactory', '$rootScope', function ($scope, $http, $q, SubscriptionFactory) {
//Code goes here
    $scope.posting = false;
    $scope.subscription = subscription;
    $scope.user = user;

    $scope.cancelSubscription = function () {
        $scope.posting = true;
        var cancelSubscription = SubscriptionFactory.cancelSubscription();

        cancelSubscription.then(function(response) {
            $scope.posting = false;
            $scope.subscription.cancel_at_period_end = true;

            humane.log(response.message);

            //window.location.reload(false);
        },
            function (response) {
                $scope.posting = false;
                $scope.$apply();
                humane.log(response.message)
            }
        );
    }

}]);
app.controller('updateController', ['$scope', function ($scope) {

    $scope.showSubmit = false;
    $('#addFiles').on('change', function() {
        $scope.$apply(function() {
           $scope.showSubmit = true;
        });
    });

    $scope.user = {};
    $scope.fileButtonText = 'Select new profile photo';

    $scope.user = loggedInUser;
    $scope.user.username = $('input#username').val();
    $scope.profileURL = profileImageURL;


}]);
(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
// Generated by CoffeeScript 1.7.1
    (function() {
        var $, cardFromNumber, cardFromType, cards, defaultFormat, formatBackCardNumber, formatBackExpiry, formatCardNumber, formatExpiry, formatForwardExpiry, formatForwardSlash, hasTextSelected, luhnCheck, reFormatCardNumber, restrictCVC, restrictCardNumber, restrictExpiry, restrictNumeric, setCardType,
            __slice = [].slice,
            __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

        $ = jQuery;

        $.payment = {};

        $.payment.fn = {};

        $.fn.payment = function() {
            var args, method;
            method = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
            return $.payment.fn[method].apply(this, args);
        };

        defaultFormat = /(\d{1,4})/g;

        cards = [
            {
                type: 'maestro',
                pattern: /^(5018|5020|5038|6304|6759|676[1-3])/,
                format: defaultFormat,
                length: [12, 13, 14, 15, 16, 17, 18, 19],
                cvcLength: [3],
                luhn: true
            }, {
                type: 'dinersclub',
                pattern: /^(36|38|30[0-5])/,
                format: defaultFormat,
                length: [14],
                cvcLength: [3],
                luhn: true
            }, {
                type: 'laser',
                pattern: /^(6706|6771|6709)/,
                format: defaultFormat,
                length: [16, 17, 18, 19],
                cvcLength: [3],
                luhn: true
            }, {
                type: 'jcb',
                pattern: /^35/,
                format: defaultFormat,
                length: [16],
                cvcLength: [3],
                luhn: true
            }, {
                type: 'unionpay',
                pattern: /^62/,
                format: defaultFormat,
                length: [16, 17, 18, 19],
                cvcLength: [3],
                luhn: false
            }, {
                type: 'discover',
                pattern: /^(6011|65|64[4-9]|622)/,
                format: defaultFormat,
                length: [16],
                cvcLength: [3],
                luhn: true
            }, {
                type: 'mastercard',
                pattern: /^5[1-5]/,
                format: defaultFormat,
                length: [16],
                cvcLength: [3],
                luhn: true
            }, {
                type: 'amex',
                pattern: /^3[47]/,
                format: /(\d{1,4})(\d{1,6})?(\d{1,5})?/,
                length: [15],
                cvcLength: [3, 4],
                luhn: true
            }, {
                type: 'visa',
                pattern: /^4/,
                format: defaultFormat,
                length: [13, 16],
                cvcLength: [3],
                luhn: true
            }
        ];

        cardFromNumber = function(num) {
            var card, _i, _len;
            num = (num + '').replace(/\D/g, '');
            for (_i = 0, _len = cards.length; _i < _len; _i++) {
                card = cards[_i];
                if (card.pattern.test(num)) {
                    return card;
                }
            }
        };

        cardFromType = function(type) {
            var card, _i, _len;
            for (_i = 0, _len = cards.length; _i < _len; _i++) {
                card = cards[_i];
                if (card.type === type) {
                    return card;
                }
            }
        };

        luhnCheck = function(num) {
            var digit, digits, odd, sum, _i, _len;
            odd = true;
            sum = 0;
            digits = (num + '').split('').reverse();
            for (_i = 0, _len = digits.length; _i < _len; _i++) {
                digit = digits[_i];
                digit = parseInt(digit, 10);
                if ((odd = !odd)) {
                    digit *= 2;
                }
                if (digit > 9) {
                    digit -= 9;
                }
                sum += digit;
            }
            return sum % 10 === 0;
        };

        hasTextSelected = function($target) {
            var _ref;
            if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== $target.prop('selectionEnd')) {
                return true;
            }
            if (typeof document !== "undefined" && document !== null ? (_ref = document.selection) != null ? typeof _ref.createRange === "function" ? _ref.createRange().text : void 0 : void 0 : void 0) {
                return true;
            }
            return false;
        };

        reFormatCardNumber = function(e) {
            return setTimeout((function(_this) {
                return function() {
                    var $target, value;
                    $target = $(e.currentTarget);
                    value = $target.val();
                    value = $.payment.formatCardNumber(value);
                    return $target.val(value);
                };
            })(this));
        };

        formatCardNumber = function(e) {
            var $target, card, digit, length, re, upperLength, value;
            digit = String.fromCharCode(e.which);
            if (!/^\d+$/.test(digit)) {
                return;
            }
            $target = $(e.currentTarget);
            value = $target.val();
            card = cardFromNumber(value + digit);
            length = (value.replace(/\D/g, '') + digit).length;
            upperLength = 16;
            if (card) {
                upperLength = card.length[card.length.length - 1];
            }
            if (length >= upperLength) {
                return;
            }
            if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== value.length) {
                return;
            }
            if (card && card.type === 'amex') {
                re = /^(\d{4}|\d{4}\s\d{6})$/;
            } else {
                re = /(?:^|\s)(\d{4})$/;
            }
            if (re.test(value)) {
                e.preventDefault();
                return $target.val(value + ' ' + digit);
            } else if (re.test(value + digit)) {
                e.preventDefault();
                return $target.val(value + digit + ' ');
            }
        };

        formatBackCardNumber = function(e) {
            var $target, value;
            $target = $(e.currentTarget);
            value = $target.val();
            if (e.meta) {
                return;
            }
            if (e.which !== 8) {
                return;
            }
            if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== value.length) {
                return;
            }
            if (/\d\s$/.test(value)) {
                e.preventDefault();
                return $target.val(value.replace(/\d\s$/, ''));
            } else if (/\s\d?$/.test(value)) {
                e.preventDefault();
                return $target.val(value.replace(/\s\d?$/, ''));
            }
        };

        formatExpiry = function(e) {
            var $target, digit, val;
            digit = String.fromCharCode(e.which);
            if (!/^\d+$/.test(digit)) {
                return;
            }
            $target = $(e.currentTarget);
            val = $target.val() + digit;
            if (/^\d$/.test(val) && (val !== '0' && val !== '1')) {
                e.preventDefault();
                return $target.val("0" + val + " / ");
            } else if (/^\d\d$/.test(val)) {
                e.preventDefault();
                return $target.val("" + val + " / ");
            }
        };

        formatForwardExpiry = function(e) {
            var $target, digit, val;
            digit = String.fromCharCode(e.which);
            if (!/^\d+$/.test(digit)) {
                return;
            }
            $target = $(e.currentTarget);
            val = $target.val();
            if (/^\d\d$/.test(val)) {
                return $target.val("" + val + " / ");
            }
        };

        formatForwardSlash = function(e) {
            var $target, slash, val;
            slash = String.fromCharCode(e.which);
            if (slash !== '/') {
                return;
            }
            $target = $(e.currentTarget);
            val = $target.val();
            if (/^\d$/.test(val) && val !== '0') {
                return $target.val("0" + val + " / ");
            }
        };

        formatBackExpiry = function(e) {
            var $target, value;
            if (e.meta) {
                return;
            }
            $target = $(e.currentTarget);
            value = $target.val();
            if (e.which !== 8) {
                return;
            }
            if (($target.prop('selectionStart') != null) && $target.prop('selectionStart') !== value.length) {
                return;
            }
            if (/\d(\s|\/)+$/.test(value)) {
                e.preventDefault();
                return $target.val(value.replace(/\d(\s|\/)*$/, ''));
            } else if (/\s\/\s?\d?$/.test(value)) {
                e.preventDefault();
                return $target.val(value.replace(/\s\/\s?\d?$/, ''));
            }
        };

        restrictNumeric = function(e) {
            var input;
            if (e.metaKey || e.ctrlKey) {
                return true;
            }
            if (e.which === 32) {
                return false;
            }
            if (e.which === 0) {
                return true;
            }
            if (e.which < 33) {
                return true;
            }
            input = String.fromCharCode(e.which);
            return !!/[\d\s]/.test(input);
        };

        restrictCardNumber = function(e) {
            var $target, card, digit, value;
            $target = $(e.currentTarget);
            digit = String.fromCharCode(e.which);
            if (!/^\d+$/.test(digit)) {
                return;
            }
            if (hasTextSelected($target)) {
                return;
            }
            value = ($target.val() + digit).replace(/\D/g, '');
            card = cardFromNumber(value);
            if (card) {
                return value.length <= card.length[card.length.length - 1];
            } else {
                return value.length <= 16;
            }
        };

        restrictExpiry = function(e) {
            var $target, digit, value;
            $target = $(e.currentTarget);
            digit = String.fromCharCode(e.which);
            if (!/^\d+$/.test(digit)) {
                return;
            }
            if (hasTextSelected($target)) {
                return;
            }
            value = $target.val() + digit;
            value = value.replace(/\D/g, '');
            if (value.length > 6) {
                return false;
            }
        };

        restrictCVC = function(e) {
            var $target, digit, val;
            $target = $(e.currentTarget);
            digit = String.fromCharCode(e.which);
            if (!/^\d+$/.test(digit)) {
                return;
            }
            if (hasTextSelected($target)) {
                return;
            }
            val = $target.val() + digit;
            return val.length <= 4;
        };

        setCardType = function(e) {
            var $target, allTypes, card, cardType, val;
            $target = $(e.currentTarget);
            val = $target.val();
            cardType = $.payment.cardType(val) || 'unknown';
            if (!$target.hasClass(cardType)) {
                allTypes = (function() {
                    var _i, _len, _results;
                    _results = [];
                    for (_i = 0, _len = cards.length; _i < _len; _i++) {
                        card = cards[_i];
                        _results.push(card.type);
                    }
                    return _results;
                })();
                $target.removeClass('unknown');
                $target.removeClass(allTypes.join(' '));
                $target.addClass(cardType);
                $target.toggleClass('identified', cardType !== 'unknown');
                return $target.trigger('payment.cardType', cardType);
            }
        };

        $.payment.fn.formatCardCVC = function() {
            this.payment('restrictNumeric');
            this.on('keypress', restrictCVC);
            return this;
        };

        $.payment.fn.formatCardExpiry = function() {
            this.payment('restrictNumeric');
            this.on('keypress', restrictExpiry);
            this.on('keypress', formatExpiry);
            this.on('keypress', formatForwardSlash);
            this.on('keypress', formatForwardExpiry);
            this.on('keydown', formatBackExpiry);
            return this;
        };

        $.payment.fn.formatCardNumber = function() {
            this.payment('restrictNumeric');
            this.on('keypress', restrictCardNumber);
            this.on('keypress', formatCardNumber);
            this.on('keydown', formatBackCardNumber);
            this.on('keyup', setCardType);
            this.on('paste', reFormatCardNumber);
            return this;
        };

        $.payment.fn.restrictNumeric = function() {
            this.on('keypress', restrictNumeric);
            return this;
        };

        $.payment.fn.cardExpiryVal = function() {
            return $.payment.cardExpiryVal($(this).val());
        };

        $.payment.cardExpiryVal = function(value) {
            var month, prefix, year, _ref;
            value = value.replace(/\s/g, '');
            _ref = value.split('/', 2), month = _ref[0], year = _ref[1];
            if ((year != null ? year.length : void 0) === 2 && /^\d+$/.test(year)) {
                prefix = (new Date).getFullYear();
                prefix = prefix.toString().slice(0, 2);
                year = prefix + year;
            }
            month = parseInt(month, 10);
            year = parseInt(year, 10);
            return {
                month: month,
                year: year
            };
        };

        $.payment.validateCardNumber = function(num) {
            var card, _ref;
            num = (num + '').replace(/\s+|-/g, '');
            if (!/^\d+$/.test(num)) {
                return false;
            }
            card = cardFromNumber(num);
            if (!card) {
                return false;
            }
            return (_ref = num.length, __indexOf.call(card.length, _ref) >= 0) && (card.luhn === false || luhnCheck(num));
        };

        $.payment.validateCardExpiry = (function(_this) {
            return function(month, year) {
                var currentTime, expiry, prefix, _ref;
                if (typeof month === 'object' && 'month' in month) {
                    _ref = month, month = _ref.month, year = _ref.year;
                }
                if (!(month && year)) {
                    return false;
                }
                month = $.trim(month);
                year = $.trim(year);
                if (!/^\d+$/.test(month)) {
                    return false;
                }
                if (!/^\d+$/.test(year)) {
                    return false;
                }
                if (!(parseInt(month, 10) <= 12)) {
                    return false;
                }
                if (year.length === 2) {
                    prefix = (new Date).getFullYear();
                    prefix = prefix.toString().slice(0, 2);
                    year = prefix + year;
                }
                expiry = new Date(year, month);
                currentTime = new Date;
                expiry.setMonth(expiry.getMonth() - 1);
                expiry.setMonth(expiry.getMonth() + 1, 1);
                return expiry > currentTime;
            };
        })(this);

        $.payment.validateCardCVC = function(cvc, type) {
            var _ref, _ref1;
            cvc = $.trim(cvc);
            if (!/^\d+$/.test(cvc)) {
                return false;
            }
            if (type) {
                return _ref = cvc.length, __indexOf.call((_ref1 = cardFromType(type)) != null ? _ref1.cvcLength : void 0, _ref) >= 0;
            } else {
                return cvc.length >= 3 && cvc.length <= 4;
            }
        };

        $.payment.cardType = function(num) {
            var _ref;
            if (!num) {
                return null;
            }
            return ((_ref = cardFromNumber(num)) != null ? _ref.type : void 0) || null;
        };

        $.payment.formatCardNumber = function(num) {
            var card, groups, upperLength, _ref;
            card = cardFromNumber(num);
            if (!card) {
                return num;
            }
            upperLength = card.length[card.length.length - 1];
            num = num.replace(/\D/g, '');
            num = num.slice(0, +upperLength + 1 || 9e9);
            if (card.format.global) {
                return (_ref = num.match(card.format)) != null ? _ref.join(' ') : void 0;
            } else {
                groups = card.format.exec(num);
                if (groups != null) {
                    groups.shift();
                }
                return groups != null ? groups.join(' ') : void 0;
            }
        };

    }).call(this);

},{}],2:[function(require,module,exports){
    var $, Card,
        __slice = [].slice;

    require('jquery.payment');

    $ = jQuery;

    $.card = {};

    $.card.fn = {};

    $.fn.card = function(opts) {
        return $.card.fn.construct.apply(this, opts);
    };

    Card = (function() {
        Card.prototype.cardTemplate = "<div class=\"card-container\">\n    <div class=\"card\">\n        <div class=\"front\">\n                <div class=\"card-logo visa\">visa</div>\n                <div class=\"card-logo mastercard\">MasterCard</div>\n                <div class=\"card-logo amex\"></div>\n                <div class=\"card-logo discover\">discover</div>\n            <div class=\"lower\">\n                <div class=\"shiny\"></div>\n                <div class=\"cvc display\">{{cvc}}</div>\n                <div class=\"number display\">{{number}}</div>\n                <div class=\"name display\">{{name}}</div>\n                <div class=\"expiry display\" data-before=\"{{monthYear}}\" data-after=\"{{validDate}}\">{{expiry}}</div>\n            </div>\n        </div>\n        <div class=\"back\">\n            <div class=\"bar\"></div>\n            <div class=\"cvc display\">{{cvc}}</div>\n            <div class=\"shiny\"></div>\n        </div>\n    </div>\n</div>";

        Card.prototype.template = function(tpl, data) {
            return tpl.replace(/\{\{(.*?)\}\}/g, function(match, key, str) {
                return data[key];
            });
        };

        Card.prototype.cardTypes = ['maestro', 'dinersclub', 'laser', 'jcb', 'unionpay', 'discover', 'mastercard', 'amex', 'visa'];

        Card.prototype.defaults = {
            formatting: true,
            formSelectors: {
                numberInput: 'input[name="number"]',
                expiryInput: 'input[name="expiry"]',
                cvcInput: 'input[name="cvc"]',
                nameInput: 'input[name="name"]'
            },
            cardSelectors: {
                cardContainer: '.card-container',
                card: '.card',
                numberDisplay: '.number',
                expiryDisplay: '.expiry',
                cvcDisplay: '.cvc',
                nameDisplay: '.name'
            },
            messages: {
                validDate: 'valid\nthru',
                monthYear: 'month/year'
            },
            values: {
                number: '&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;',
                cvc: '&bull;&bull;&bull;',
                expiry: '&bull;&bull;/&bull;&bull;',
                name: 'Full Name'
            },
            classes: {
                valid: 'card-valid',
                invalid: 'card-invalid'
            }
        };

        function Card(el, opts) {
            this.options = $.extend(true, {}, this.defaults, opts);
            $.extend(this.options.messages, $.card.messages);
            $.extend(this.options.values, $.card.values);
            this.$el = $(el);
            if (!this.options.container) {
                console.log("Please provide a container");
                return;
            }
            this.$container = $(this.options.container);
            this.render();
            this.attachHandlers();
            this.handleInitialValues();
        }

        Card.prototype.render = function() {
            var baseWidth, ua;
            this.$container.append(this.template(this.cardTemplate, $.extend({}, this.options.messages, this.options.values)));
            $.each(this.options.cardSelectors, (function(_this) {
                return function(name, selector) {
                    return _this["$" + name] = _this.$container.find(selector);
                };
            })(this));
            $.each(this.options.formSelectors, (function(_this) {
                return function(name, selector) {
                    var obj;
                    if (_this.options[name]) {
                        obj = $(_this.options[name]);
                    } else {
                        obj = _this.$el.find(selector);
                    }
                    if (!obj.length) {
                        console.error("Card can't find a " + name + " in your form.");
                    }
                    return _this["$" + name] = obj;
                };
            })(this));
            if (this.options.formatting) {
                this.$numberInput.payment('formatCardNumber');
                this.$cvcInput.payment('formatCardCVC');
                if (this.$expiryInput.length === 1) {
                    this.$expiryInput.payment('formatCardExpiry');
                }
            }
            if (this.options.width) {
                baseWidth = parseInt(this.$cardContainer.css('width'));
                this.$cardContainer.css("transform", "scale(" + (this.options.width / baseWidth) + ")");
            }
            if (typeof navigator !== "undefined" && navigator !== null ? navigator.userAgent : void 0) {
                ua = navigator.userAgent.toLowerCase();
                if (ua.indexOf('safari') !== -1 && ua.indexOf('chrome') === -1) {
                    this.$card.addClass('safari');
                }
            }
            if (new Function("/*@cc_on return @_jscript_version; @*/")()) {
                return this.$card.addClass('ie-10');
            }
        };

        Card.prototype.attachHandlers = function() {
            var expiryFilters;
            this.$numberInput.bindVal(this.$numberDisplay, {
                fill: false,
                filters: this.validToggler('cardNumber')
            }).on('payment.cardType', this.handle('setCardType'));
            expiryFilters = [
                function(val) {
                    return val.replace(/(\s+)/g, '');
                }
            ];
            if (this.$expiryInput.length === 1) {
                expiryFilters.push(this.validToggler('cardExpiry'));
                this.$expiryInput.on('keydown', this.handle('captureTab'));
            }
            this.$expiryInput.bindVal(this.$expiryDisplay, {
                join: function(text) {
                    if (text[0].length === 2 || text[1]) {
                        return "/";
                    } else {
                        return "";
                    }
                },
                filters: expiryFilters
            });
            this.$cvcInput.bindVal(this.$cvcDisplay, {
                filters: this.validToggler('cardCVC')
            }).on('focus', this.handle('flipCard')).on('blur', this.handle('flipCard'));
            return this.$nameInput.bindVal(this.$nameDisplay, {
                fill: false,
                filters: this.validToggler('cardHolderName'),
                join: ' '
            }).on('keydown', this.handle('captureName'));
        };

        Card.prototype.handleInitialValues = function() {
            return $.each(this.options.formSelectors, (function(_this) {
                return function(name, selector) {
                    var el;
                    el = _this["$" + name];
                    if (el.val()) {
                        el.trigger('paste');
                        return setTimeout(function() {
                            return el.trigger('keyup');
                        });
                    }
                };
            })(this));
        };

        Card.prototype.handle = function(fn) {
            return (function(_this) {
                return function(e) {
                    var $el, args;
                    $el = $(e.currentTarget);
                    args = Array.prototype.slice.call(arguments);
                    args.unshift($el);
                    return _this.handlers[fn].apply(_this, args);
                };
            })(this);
        };

        Card.prototype.validToggler = function(validatorName) {
            var isValid;
            if (validatorName === "cardExpiry") {
                isValid = function(val) {
                    var objVal;
                    objVal = $.payment.cardExpiryVal(val);
                    return $.payment.validateCardExpiry(objVal.month, objVal.year);
                };
            } else if (validatorName === "cardCVC") {
                isValid = (function(_this) {
                    return function(val) {
                        return $.payment.validateCardCVC(val, _this.cardType);
                    };
                })(this);
            } else if (validatorName === "cardNumber") {
                isValid = function(val) {
                    return $.payment.validateCardNumber(val);
                };
            } else if (validatorName === "cardHolderName") {
                isValid = function(val) {
                    return val !== "";
                };
            }
            return (function(_this) {
                return function(val, $in, $out) {
                    var result;
                    result = isValid(val);
                    _this.toggleValidClass($in, result);
                    _this.toggleValidClass($out, result);
                    return val;
                };
            })(this);
        };

        Card.prototype.toggleValidClass = function(el, test) {
            el.toggleClass(this.options.classes.valid, test);
            return el.toggleClass(this.options.classes.invalid, !test);
        };

        Card.prototype.handlers = {
            setCardType: function($el, e, cardType) {
                if (!this.$card.hasClass(cardType)) {
                    this.$card.removeClass('unknown');
                    this.$card.removeClass(this.cardTypes.join(' '));
                    this.$card.addClass(cardType);
                    this.$card.toggleClass('identified', cardType !== 'unknown');
                    return this.cardType = cardType;
                }
            },
            flipCard: function($el, e) {
                return this.$card.toggleClass('flipped');
            },
            captureTab: function($el, e) {
                var keyCode, val;
                keyCode = e.keyCode || e.which;
                if (keyCode !== 9 || e.shiftKey) {
                    return;
                }
                val = $el.payment('cardExpiryVal');
                if (!(val.month || val.year)) {
                    return;
                }
                if (!$.payment.validateCardExpiry(val.month, val.year)) {
                    return e.preventDefault();
                }
            },
            captureName: function($el, e) {
                var banKeyCodes;
                banKeyCodes = [48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 106, 107, 109, 110, 111, 186, 187, 188, 189, 190, 191, 192, 219, 220, 221, 222];
                if (banKeyCodes.indexOf(e.which || e.keyCode) !== -1) {
                    return e.preventDefault();
                }
            }
        };

        $.fn.bindVal = function(out, opts) {
            var $el, i, joiner, o, outDefaults;
            if (opts == null) {
                opts = {};
            }
            opts.fill = opts.fill || false;
            opts.filters = opts.filters || [];
            if (!(opts.filters instanceof Array)) {
                opts.filters = [opts.filters];
            }
            opts.join = opts.join || "";
            if (!(typeof opts.join === "function")) {
                joiner = opts.join;
                opts.join = function() {
                    return joiner;
                };
            }
            $el = $(this);
            outDefaults = (function() {
                var _i, _len, _results;
                _results = [];
                for (i = _i = 0, _len = out.length; _i < _len; i = ++_i) {
                    o = out[i];
                    _results.push(out.eq(i).text());
                }
                return _results;
            })();
            $el.on('focus', function() {
                return out.addClass('focused');
            });
            $el.on('blur', function() {
                return out.removeClass('focused');
            });
            $el.on('keyup change paste', function(e) {
                var filter, join, outVal, val, _i, _j, _len, _len1, _ref, _results;
                val = $el.map(function() {
                    return $(this).val();
                }).get();
                join = opts.join(val);
                val = val.join(join);
                if (val === join) {
                    val = "";
                }
                _ref = opts.filters;
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    filter = _ref[_i];
                    val = filter(val, $el, out);
                }
                _results = [];
                for (i = _j = 0, _len1 = out.length; _j < _len1; i = ++_j) {
                    o = out[i];
                    if (opts.fill) {
                        outVal = val + outDefaults[i].substring(val.length);
                    } else {
                        outVal = val || outDefaults[i];
                    }
                    _results.push(out.eq(i).text(outVal));
                }
                return _results;
            });
            return $el;
        };

        return Card;

    })();

    $.fn.extend({
        card: function() {
            var args, option;
            option = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
            return this.each(function() {
                var $this, data;
                $this = $(this);
                data = $this.data('card');
                if (!data) {
                    $this.data('card', (data = new Card(this, option)));
                }
                if (typeof option === 'string') {
                    return data[option].apply(data, args);
                }
            });
        }
    });


},{"jquery.payment":1}]},{},[2])


/*global module, console*/

function MediumEditor(elements, options) {
    'use strict';
    return this.init(elements, options);
}

if (typeof module === 'object') {
    module.exports = MediumEditor;
}

(function (window, document) {
    'use strict';

    function extend(b, a) {
        var prop;
        if (b === undefined) {
            return a;
        }
        for (prop in a) {
            if (a.hasOwnProperty(prop) && b.hasOwnProperty(prop) === false) {
                b[prop] = a[prop];
            }
        }
        return b;
    }

    // http://stackoverflow.com/questions/5605401/insert-link-in-contenteditable-element
    // by Tim Down
    function saveSelection() {
        var i,
            len,
            ranges,
            sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            ranges = [];
            for (i = 0, len = sel.rangeCount; i < len; i += 1) {
                ranges.push(sel.getRangeAt(i));
            }
            return ranges;
        }
        return null;
    }

    function restoreSelection(savedSel) {
        var i,
            len,
            sel = window.getSelection();
        if (savedSel) {
            sel.removeAllRanges();
            for (i = 0, len = savedSel.length; i < len; i += 1) {
                sel.addRange(savedSel[i]);
            }
        }
    }

    // http://stackoverflow.com/questions/1197401/how-can-i-get-the-element-the-caret-is-in-with-javascript-when-using-contentedi
    // by You
    function getSelectionStart() {
        var node = document.getSelection().anchorNode,
            startNode = (node && node.nodeType === 3 ? node.parentNode : node);
        return startNode;
    }

    // http://stackoverflow.com/questions/4176923/html-of-selected-text
    // by Tim Down
    function getSelectionHtml() {
        var i,
            html = '',
            sel,
            len,
            container;
        if (window.getSelection !== undefined) {
            sel = window.getSelection();
            if (sel.rangeCount) {
                container = document.createElement('div');
                for (i = 0, len = sel.rangeCount; i < len; i += 1) {
                    container.appendChild(sel.getRangeAt(i).cloneContents());
                }
                html = container.innerHTML;
            }
        } else if (document.selection !== undefined) {
            if (document.selection.type === 'Text') {
                html = document.selection.createRange().htmlText;
            }
        }
        return html;
    }

    // https://github.com/jashkenas/underscore
    function isElement(obj) {
        return !!(obj && obj.nodeType === 1);
    }

    MediumEditor.prototype = {
        defaults: {
            allowMultiParagraphSelection: true,
            anchorInputPlaceholder: 'Paste or type a link',
            anchorPreviewHideDelay: 500,
            buttons: ['bold', 'italic', 'underline', 'anchor', 'header1', 'header2', 'quote'],
            buttonLabels: false,
            checkLinkFormat: false,
            cleanPastedHTML: false,
            delay: 0,
            diffLeft: 0,
            diffTop: -10,
            disableReturn: false,
            disableDoubleReturn: false,
            disableToolbar: false,
            disableEditing: false,
            elementsContainer: false,
            firstHeader: 'h3',
            forcePlainText: true,
            placeholder: 'Type your text',
            secondHeader: 'h4',
            targetBlank: false,
            extensions: {},
            activeButtonClass: 'medium-editor-button-active',
            firstButtonClass: 'medium-editor-button-first',
            lastButtonClass: 'medium-editor-button-last'
        },

        // http://stackoverflow.com/questions/17907445/how-to-detect-ie11#comment30165888_17907562
        // by rg89
        isIE: ((navigator.appName === 'Microsoft Internet Explorer') || ((navigator.appName === 'Netscape') && (new RegExp('Trident/.*rv:([0-9]{1,}[.0-9]{0,})').exec(navigator.userAgent) !== null))),

        init: function (elements, options) {
            this.setElementSelection(elements);
            if (this.elements.length === 0) {
                return;
            }
            this.parentElements = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre'];
            this.id = document.querySelectorAll('.medium-editor-toolbar').length + 1;
            this.options = extend(options, this.defaults);
            return this.setup();
        },

        setup: function () {
            this.isActive = true;
            this.initElements()
                .bindSelect()
                .bindPaste()
                .bindWindowActions();
        },

        initElements: function () {
            this.updateElementList();
            var i,
                addToolbar = false;
            for (i = 0; i < this.elements.length; i += 1) {
                if (!this.options.disableEditing && !this.elements[i].getAttribute('data-disable-editing')) {
                    this.elements[i].setAttribute('contentEditable', true);
                }

                this.elements[i].setAttribute('data-medium-element', true);
                this.bindParagraphCreation(i).bindReturn(i).bindTab(i);
                if (!this.options.disableToolbar && !this.elements[i].getAttribute('data-disable-toolbar')) {
                    addToolbar = true;
                }
            }
            // Init toolbar
            if (addToolbar) {
                if (!this.options.elementsContainer) {
                    this.options.elementsContainer = document.body;
                }
                this.initToolbar()
                    .bindButtons()
                    .bindAnchorForm()
                    .bindAnchorPreview();
            }
            return this;
        },

        setElementSelection: function (selector) {
            this.elementSelection = selector;
            this.updateElementList();
        },

        updateElementList: function () {
            this.elements = typeof this.elementSelection === 'string' ? document.querySelectorAll(this.elementSelection) : this.elementSelection;
            if (this.elements.nodeType === 1) {
                this.elements = [this.elements];
            }
        },

        serialize: function () {
            var i,
                elementid,
                content = {};
            for (i = 0; i < this.elements.length; i += 1) {
                elementid = (this.elements[i].id !== '') ? this.elements[i].id : 'element-' + i;
                content[elementid] = {
                    value: this.elements[i].innerHTML.trim()
                };
            }
            return content;
        },

        /**
         * Helper function to call a method with a number of parameters on all registered extensions.
         * The function assures that the function exists before calling.
         *
         * @param {string} funcName name of the function to call
         * @param [args] arguments passed into funcName
         */
        callExtensions: function (funcName) {
            if (arguments.length < 1) {
                return;
            }

            var args = Array.prototype.slice.call(arguments, 1),
                ext,
                name;

            for (name in this.options.extensions) {
                if (this.options.extensions.hasOwnProperty(name)) {
                    ext = this.options.extensions[name];
                    if (ext[funcName] !== undefined) {
                        ext[funcName].apply(ext, args);
                    }
                }
            }
        },

        bindParagraphCreation: function (index) {
            var self = this;
            this.elements[index].addEventListener('keypress', function (e) {
                var node = getSelectionStart(),
                    tagName;
                if (e.which === 32) {
                    tagName = node.tagName.toLowerCase();
                    if (tagName === 'a') {
                        document.execCommand('unlink', false, null);
                    }
                }
            });

            this.elements[index].addEventListener('keyup', function (e) {
                var node = getSelectionStart(),
                    tagName;
                if (node && node.getAttribute('data-medium-element') && node.children.length === 0 && !(self.options.disableReturn || node.getAttribute('data-disable-return'))) {
                    document.execCommand('formatBlock', false, 'p');
                }
                if (e.which === 13) {
                    node = getSelectionStart();
                    tagName = node.tagName.toLowerCase();
                    if (!(self.options.disableReturn || this.getAttribute('data-disable-return')) &&
                        tagName !== 'li' && !self.isListItemChild(node)) {
                        if (!e.shiftKey) {
                            document.execCommand('formatBlock', false, 'p');
                        }
                        if (tagName === 'a') {
                            document.execCommand('unlink', false, null);
                        }
                    }
                }
            });
            return this;
        },

        isListItemChild: function (node) {
            var parentNode = node.parentNode,
                tagName = parentNode.tagName.toLowerCase();
            while (this.parentElements.indexOf(tagName) === -1 && tagName !== 'div') {
                if (tagName === 'li') {
                    return true;
                }
                parentNode = parentNode.parentNode;
                if (parentNode && parentNode.tagName) {
                    tagName = parentNode.tagName.toLowerCase();
                } else {
                    return false;
                }
            }
            return false;
        },

        bindReturn: function (index) {
            var self = this;
            this.elements[index].addEventListener('keypress', function (e) {
                if (e.which === 13) {
                    if (self.options.disableReturn || this.getAttribute('data-disable-return')) {
                        e.preventDefault();
                    } else if (self.options.disableDoubleReturn || this.getAttribute('data-disable-double-return')) {
                        var node = getSelectionStart();
                        if (node && node.innerText === '\n') {
                            e.preventDefault();
                        }
                    }
                }
            });
            return this;
        },

        bindTab: function (index) {
            this.elements[index].addEventListener('keydown', function (e) {
                if (e.which === 9) {
                    // Override tab only for pre nodes
                    var tag = getSelectionStart().tagName.toLowerCase();
                    if (tag === 'pre') {
                        e.preventDefault();
                        document.execCommand('insertHtml', null, '    ');
                    }
                }
            });
            return this;
        },

        buttonTemplate: function (btnType) {
            var buttonLabels = this.getButtonLabels(this.options.buttonLabels),
                buttonTemplates = {
                    'bold': '<button class="medium-editor-action medium-editor-action-bold" data-action="bold" data-element="b">' + buttonLabels.bold + '</button>',
                    'italic': '<button class="medium-editor-action medium-editor-action-italic" data-action="italic" data-element="i">' + buttonLabels.italic + '</button>',
                    'underline': '<button class="medium-editor-action medium-editor-action-underline" data-action="underline" data-element="u">' + buttonLabels.underline + '</button>',
                    'strikethrough': '<button class="medium-editor-action medium-editor-action-strikethrough" data-action="strikethrough" data-element="strike"><strike>A</strike></button>',
                    'superscript': '<button class="medium-editor-action medium-editor-action-superscript" data-action="superscript" data-element="sup">' + buttonLabels.superscript + '</button>',
                    'subscript': '<button class="medium-editor-action medium-editor-action-subscript" data-action="subscript" data-element="sub">' + buttonLabels.subscript + '</button>',
                    'anchor': '<button class="medium-editor-action medium-editor-action-anchor" data-action="anchor" data-element="a">' + buttonLabels.anchor + '</button>',
                    'image': '<button class="medium-editor-action medium-editor-action-image" data-action="image" data-element="img">' + buttonLabels.image + '</button>',
                    'header1': '<button class="medium-editor-action medium-editor-action-header1" data-action="append-' + this.options.firstHeader + '" data-element="' + this.options.firstHeader + '">' + buttonLabels.header1 + '</button>',
                    'header2': '<button class="medium-editor-action medium-editor-action-header2" data-action="append-' + this.options.secondHeader + '" data-element="' + this.options.secondHeader + '">' + buttonLabels.header2 + '</button>',
                    'quote': '<button class="medium-editor-action medium-editor-action-quote" data-action="append-blockquote" data-element="blockquote">' + buttonLabels.quote + '</button>',
                    'orderedlist': '<button class="medium-editor-action medium-editor-action-orderedlist" data-action="insertorderedlist" data-element="ol">' + buttonLabels.orderedlist + '</button>',
                    'unorderedlist': '<button class="medium-editor-action medium-editor-action-unorderedlist" data-action="insertunorderedlist" data-element="ul">' + buttonLabels.unorderedlist + '</button>',
                    'pre': '<button class="medium-editor-action medium-editor-action-pre" data-action="append-pre" data-element="pre">' + buttonLabels.pre + '</button>',
                    'indent': '<button class="medium-editor-action medium-editor-action-indent" data-action="indent" data-element="ul">' + buttonLabels.indent + '</button>',
                    'outdent': '<button class="medium-editor-action medium-editor-action-outdent" data-action="outdent" data-element="ul">' + buttonLabels.outdent + '</button>'
                };
            return buttonTemplates[btnType] || false;
        },

        // TODO: break method
        getButtonLabels: function (buttonLabelType) {
            var customButtonLabels,
                attrname,
                buttonLabels = {
                    'bold': '<b>B</b>',
                    'italic': '<b><i>I</i></b>',
                    'underline': '<b><u>U</u></b>',
                    'superscript': '<b>x<sup>1</sup></b>',
                    'subscript': '<b>x<sub>1</sub></b>',
                    'anchor': '<b>#</b>',
                    'image': '<b>image</b>',
                    'header1': '<b>H1</b>',
                    'header2': '<b>H2</b>',
                    'quote': '<b>&ldquo;</b>',
                    'orderedlist': '<b>1.</b>',
                    'unorderedlist': '<b>&bull;</b>',
                    'pre': '<b>0101</b>',
                    'indent': '<b>&rarr;</b>',
                    'outdent': '<b>&larr;</b>'
                };
            if (buttonLabelType === 'fontawesome') {
                customButtonLabels = {
                    'bold': '<i class="fa fa-bold"></i>',
                    'italic': '<i class="fa fa-italic"></i>',
                    'underline': '<i class="fa fa-underline"></i>',
                    'superscript': '<i class="fa fa-superscript"></i>',
                    'subscript': '<i class="fa fa-subscript"></i>',
                    'anchor': '<i class="fa fa-link"></i>',
                    'image': '<i class="fa fa-picture-o"></i>',
                    'quote': '<i class="fa fa-quote-right"></i>',
                    'orderedlist': '<i class="fa fa-list-ol"></i>',
                    'unorderedlist': '<i class="fa fa-list-ul"></i>',
                    'pre': '<i class="fa fa-code fa-lg"></i>',
                    'indent': '<i class="fa fa-indent"></i>',
                    'outdent': '<i class="fa fa-outdent"></i>'
                };
            } else if (typeof buttonLabelType === 'object') {
                customButtonLabels = buttonLabelType;
            }
            if (typeof customButtonLabels === 'object') {
                for (attrname in customButtonLabels) {
                    if (customButtonLabels.hasOwnProperty(attrname)) {
                        buttonLabels[attrname] = customButtonLabels[attrname];
                    }
                }
            }
            return buttonLabels;
        },

        initToolbar: function () {
            if (this.toolbar) {
                return this;
            }
            this.toolbar = this.createToolbar();
            this.keepToolbarAlive = false;
            this.anchorForm = this.toolbar.querySelector('.medium-editor-toolbar-form-anchor');
            this.anchorInput = this.anchorForm.querySelector('input');
            this.toolbarActions = this.toolbar.querySelector('.medium-editor-toolbar-actions');
            this.anchorPreview = this.createAnchorPreview();

            return this;
        },

        createToolbar: function () {
            var toolbar = document.createElement('div');
            toolbar.id = 'medium-editor-toolbar-' + this.id;
            toolbar.className = 'medium-editor-toolbar';
            toolbar.appendChild(this.toolbarButtons());
            toolbar.appendChild(this.toolbarFormAnchor());
            this.options.elementsContainer.appendChild(toolbar);
            return toolbar;
        },

        //TODO: actionTemplate
        toolbarButtons: function () {
            var btns = this.options.buttons,
                ul = document.createElement('ul'),
                li,
                i,
                btn,
                ext;

            ul.id = 'medium-editor-toolbar-actions';
            ul.className = 'medium-editor-toolbar-actions clearfix';

            for (i = 0; i < btns.length; i += 1) {
                if (this.options.extensions.hasOwnProperty(btns[i])) {
                    ext = this.options.extensions[btns[i]];
                    btn = ext.getButton !== undefined ? ext.getButton() : null;
                } else {
                    btn = this.buttonTemplate(btns[i]);
                }

                if (btn) {
                    li = document.createElement('li');
                    if (isElement(btn)) {
                        li.appendChild(btn);
                    } else {
                        li.innerHTML = btn;
                    }
                    ul.appendChild(li);
                }
            }

            return ul;
        },

        toolbarFormAnchor: function () {
            var anchor = document.createElement('div'),
                input = document.createElement('input'),
                a = document.createElement('a');

            a.setAttribute('href', '#');
            a.innerHTML = '&times;';

            input.setAttribute('type', 'text');
            input.setAttribute('placeholder', this.options.anchorInputPlaceholder);

            anchor.className = 'medium-editor-toolbar-form-anchor';
            anchor.id = 'medium-editor-toolbar-form-anchor';
            anchor.appendChild(input);
            anchor.appendChild(a);

            return anchor;
        },

        bindSelect: function () {
            var self = this,
                timer = '',
                i;

            this.checkSelectionWrapper = function (e) {

                // Do not close the toolbar when bluring the editable area and clicking into the anchor form
                if (e && self.clickingIntoArchorForm(e)) {
                    return false;
                }

                clearTimeout(timer);
                timer = setTimeout(function () {
                    self.checkSelection();
                }, self.options.delay);
            };

            document.documentElement.addEventListener('mouseup', this.checkSelectionWrapper);

            for (i = 0; i < this.elements.length; i += 1) {
                this.elements[i].addEventListener('keyup', this.checkSelectionWrapper);
                this.elements[i].addEventListener('blur', this.checkSelectionWrapper);
            }
            return this;
        },

        checkSelection: function () {
            var newSelection,
                selectionElement;

            if (this.keepToolbarAlive !== true && !this.options.disableToolbar) {
                newSelection = window.getSelection();
                if (newSelection.toString().trim() === '' ||
                    (this.options.allowMultiParagraphSelection === false && this.hasMultiParagraphs())) {
                    this.hideToolbarActions();
                } else {
                    selectionElement = this.getSelectionElement();
                    if (!selectionElement || selectionElement.getAttribute('data-disable-toolbar')) {
                        this.hideToolbarActions();
                    } else {
                        this.checkSelectionElement(newSelection, selectionElement);
                    }
                }
            }
            return this;
        },

        clickingIntoArchorForm: function (e) {
            var self = this;
            if (e.type && e.type.toLowerCase() === 'blur' && e.relatedTarget && e.relatedTarget === self.anchorInput) {
                return true;
            }
            return false;
        },

        hasMultiParagraphs: function () {
            var selectionHtml = getSelectionHtml().replace(/<[\S]+><\/[\S]+>/gim, ''),
                hasMultiParagraphs = selectionHtml.match(/<(p|h[0-6]|blockquote)>([\s\S]*?)<\/(p|h[0-6]|blockquote)>/g);

            return (hasMultiParagraphs ? hasMultiParagraphs.length : 0);
        },

        checkSelectionElement: function (newSelection, selectionElement) {
            var i;
            this.selection = newSelection;
            this.selectionRange = this.selection.getRangeAt(0);
            for (i = 0; i < this.elements.length; i += 1) {
                if (this.elements[i] === selectionElement) {
                    this.setToolbarButtonStates()
                        .setToolbarPosition()
                        .showToolbarActions();
                    return;
                }
            }
            this.hideToolbarActions();
        },

        getSelectionElement: function () {
            var selection = window.getSelection(),
                range, current, parent,
                result,
                getMediumElement = function (e) {
                    var localParent = e;
                    try {
                        while (!localParent.getAttribute('data-medium-element')) {
                            localParent = localParent.parentNode;
                        }
                    } catch (errb) {
                        return false;
                    }
                    return localParent;
                };
            // First try on current node
            try {
                range = selection.getRangeAt(0);
                current = range.commonAncestorContainer;
                parent = current.parentNode;

                if (current.getAttribute('data-medium-element')) {
                    result = current;
                } else {
                    result = getMediumElement(parent);
                }
                // If not search in the parent nodes.
            } catch (err) {
                result = getMediumElement(parent);
            }
            return result;
        },

        setToolbarPosition: function () {
            var buttonHeight = 50,
                selection = window.getSelection(),
                range = selection.getRangeAt(0),
                boundary = range.getBoundingClientRect(),
                defaultLeft = (this.options.diffLeft) - (this.toolbar.offsetWidth / 2),
                middleBoundary = (boundary.left + boundary.right) / 2,
                halfOffsetWidth = this.toolbar.offsetWidth / 2;
            if (boundary.top < buttonHeight) {
                this.toolbar.classList.add('medium-toolbar-arrow-over');
                this.toolbar.classList.remove('medium-toolbar-arrow-under');
                this.toolbar.style.top = buttonHeight + boundary.bottom - this.options.diffTop + window.pageYOffset - this.toolbar.offsetHeight + 'px';
            } else {
                this.toolbar.classList.add('medium-toolbar-arrow-under');
                this.toolbar.classList.remove('medium-toolbar-arrow-over');
                this.toolbar.style.top = boundary.top + this.options.diffTop + window.pageYOffset - this.toolbar.offsetHeight + 'px';
            }
            if (middleBoundary < halfOffsetWidth) {
                this.toolbar.style.left = defaultLeft + halfOffsetWidth + 'px';
            } else if ((window.innerWidth - middleBoundary) < halfOffsetWidth) {
                this.toolbar.style.left = window.innerWidth + defaultLeft - halfOffsetWidth + 'px';
            } else {
                this.toolbar.style.left = defaultLeft + middleBoundary + 'px';
            }

            this.hideAnchorPreview();

            return this;
        },

        setToolbarButtonStates: function () {
            var buttons = this.toolbarActions.querySelectorAll('button'),
                i;
            for (i = 0; i < buttons.length; i += 1) {
                buttons[i].classList.remove(this.options.activeButtonClass);
            }
            this.checkActiveButtons();
            return this;
        },

        checkActiveButtons: function () {
            var elements = Array.prototype.slice.call(this.elements),
                parentNode = this.getSelectedParentElement();
            while (parentNode.tagName !== undefined && this.parentElements.indexOf(parentNode.tagName.toLowerCase) === -1) {
                this.activateButton(parentNode.tagName.toLowerCase());
                this.callExtensions('checkState', parentNode);

                // we can abort the search upwards if we leave the contentEditable element
                if (elements.indexOf(parentNode) !== -1) {
                    break;
                }
                parentNode = parentNode.parentNode;
            }
        },

        activateButton: function (tag) {
            var el = this.toolbar.querySelector('[data-element="' + tag + '"]');
            if (el !== null && el.className.indexOf(this.options.activeButtonClass) === -1) {
                el.className += ' ' + this.options.activeButtonClass;
            }
        },

        bindButtons: function () {
            var buttons = this.toolbar.querySelectorAll('button'),
                i,
                self = this,
                triggerAction = function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (self.selection === undefined) {
                        self.checkSelection();
                    }
                    if (this.className.indexOf(self.options.activeButtonClass) > -1) {
                        this.classList.remove(self.options.activeButtonClass);
                    } else {
                        this.className += ' ' + self.options.activeButtonClass;
                    }
                    if (this.hasAttribute('data-action')) {
                        self.execAction(this.getAttribute('data-action'), e);
                    }
                };
            for (i = 0; i < buttons.length; i += 1) {
                buttons[i].addEventListener('click', triggerAction);
            }
            this.setFirstAndLastItems(buttons);
            return this;
        },

        setFirstAndLastItems: function (buttons) {
            if (buttons.length > 0) {
                buttons[0].className += ' ' + this.options.firstButtonClass;
                buttons[buttons.length - 1].className += ' ' + this.options.lastButtonClass;
            }
            return this;
        },

        execAction: function (action, e) {
            if (action.indexOf('append-') > -1) {
                this.execFormatBlock(action.replace('append-', ''));
                this.setToolbarPosition();
                this.setToolbarButtonStates();
            } else if (action === 'anchor') {
                this.triggerAnchorAction(e);
            } else if (action === 'image') {
                document.execCommand('insertImage', false, window.getSelection());
            } else {
                document.execCommand(action, false, null);
                this.setToolbarPosition();
            }
        },

        // http://stackoverflow.com/questions/15867542/range-object-get-selection-parent-node-chrome-vs-firefox
        rangeSelectsSingleNode: function (range) {
            var startNode = range.startContainer;
            return startNode === range.endContainer &&
            startNode.hasChildNodes() &&
            range.endOffset === range.startOffset + 1;
        },

        getSelectedParentElement: function () {
            var selectedParentElement = null,
                range = this.selectionRange;
            if (this.rangeSelectsSingleNode(range)) {
                selectedParentElement = range.startContainer.childNodes[range.startOffset];
            } else if (range.startContainer.nodeType === 3) {
                selectedParentElement = range.startContainer.parentNode;
            } else {
                selectedParentElement = range.startContainer;
            }
            return selectedParentElement;
        },

        triggerAnchorAction: function () {
            var selectedParentElement = this.getSelectedParentElement();
            if (selectedParentElement.tagName &&
                selectedParentElement.tagName.toLowerCase() === 'a') {
                document.execCommand('unlink', false, null);
            } else {
                if (this.anchorForm.style.display === 'block') {
                    this.showToolbarActions();
                } else {
                    this.showAnchorForm();
                }
            }
            return this;
        },

        execFormatBlock: function (el) {
            var selectionData = this.getSelectionData(this.selection.anchorNode);
            // FF handles blockquote differently on formatBlock
            // allowing nesting, we need to use outdent
            // https://developer.mozilla.org/en-US/docs/Rich-Text_Editing_in_Mozilla
            if (el === 'blockquote' && selectionData.el &&
                selectionData.el.parentNode.tagName.toLowerCase() === 'blockquote') {
                return document.execCommand('outdent', false, null);
            }
            if (selectionData.tagName === el) {
                el = 'p';
            }
            // When IE we need to add <> to heading elements and
            //  blockquote needs to be called as indent
            // http://stackoverflow.com/questions/10741831/execcommand-formatblock-headings-in-ie
            // http://stackoverflow.com/questions/1816223/rich-text-editor-with-blockquote-function/1821777#1821777
            if (this.isIE) {
                if (el === 'blockquote') {
                    return document.execCommand('indent', false, el);
                }
                el = '<' + el + '>';
            }
            return document.execCommand('formatBlock', false, el);
        },

        getSelectionData: function (el) {
            var tagName;

            if (el && el.tagName) {
                tagName = el.tagName.toLowerCase();
            }

            while (el && this.parentElements.indexOf(tagName) === -1) {
                el = el.parentNode;
                if (el && el.tagName) {
                    tagName = el.tagName.toLowerCase();
                }
            }

            return {
                el: el,
                tagName: tagName
            };
        },

        getFirstChild: function (el) {
            var firstChild = el.firstChild;
            while (firstChild !== null && firstChild.nodeType !== 1) {
                firstChild = firstChild.nextSibling;
            }
            return firstChild;
        },

        hideToolbarActions: function () {
            this.keepToolbarAlive = false;
            if (this.toolbar !== undefined) {
                this.toolbar.classList.remove('medium-editor-toolbar-active');
            }
        },

        showToolbarActions: function () {
            var self = this,
                timer;
            this.anchorForm.style.display = 'none';
            this.toolbarActions.style.display = 'block';
            this.keepToolbarAlive = false;
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (self.toolbar && !self.toolbar.classList.contains('medium-editor-toolbar-active')) {
                    self.toolbar.classList.add('medium-editor-toolbar-active');
                }
            }, 100);
        },

        saveSelection: function() {
            this.savedSelection = saveSelection();
        },

        restoreSelection: function() {
            restoreSelection(this.savedSelection);
        },

        showAnchorForm: function (link_value) {
            this.toolbarActions.style.display = 'none';
            this.saveSelection();
            this.anchorForm.style.display = 'block';
            this.keepToolbarAlive = true;
            this.anchorInput.focus();
            this.anchorInput.value = link_value || '';
        },

        bindAnchorForm: function () {
            var linkCancel = this.anchorForm.querySelector('a'),
                self = this;
            this.anchorForm.addEventListener('click', function (e) {
                e.stopPropagation();
            });
            this.anchorInput.addEventListener('keyup', function (e) {
                if (e.keyCode === 13) {
                    e.preventDefault();
                    self.createLink(this);
                }
            });
            this.anchorInput.addEventListener('click', function (e) {
                // make sure not to hide form when cliking into the input
                e.stopPropagation();
                self.keepToolbarAlive = true;
            });
            this.anchorInput.addEventListener('blur', function () {
                self.keepToolbarAlive = false;
                self.checkSelection();
            });
            linkCancel.addEventListener('click', function (e) {
                e.preventDefault();
                self.showToolbarActions();
                restoreSelection(self.savedSelection);
            });
            return this;
        },


        hideAnchorPreview: function () {
            this.anchorPreview.classList.remove('medium-editor-anchor-preview-active');
        },

        // TODO: break method
        showAnchorPreview: function (anchorEl) {
            if (this.anchorPreview.classList.contains('medium-editor-anchor-preview-active')) {
                return true;
            }

            var self = this,
                buttonHeight = 40,
                boundary = anchorEl.getBoundingClientRect(),
                middleBoundary = (boundary.left + boundary.right) / 2,
                halfOffsetWidth,
                defaultLeft,
                timer;

            self.anchorPreview.querySelector('i').textContent = anchorEl.href;
            halfOffsetWidth = self.anchorPreview.offsetWidth / 2;
            defaultLeft = self.options.diffLeft - halfOffsetWidth;

            clearTimeout(timer);
            timer = setTimeout(function () {
                if (self.anchorPreview && !self.anchorPreview.classList.contains('medium-editor-anchor-preview-active')) {
                    self.anchorPreview.classList.add('medium-editor-anchor-preview-active');
                }
            }, 100);

            self.observeAnchorPreview(anchorEl);

            self.anchorPreview.classList.add('medium-toolbar-arrow-over');
            self.anchorPreview.classList.remove('medium-toolbar-arrow-under');
            self.anchorPreview.style.top = Math.round(buttonHeight + boundary.bottom - self.options.diffTop + window.pageYOffset - self.anchorPreview.offsetHeight) + 'px';
            if (middleBoundary < halfOffsetWidth) {
                self.anchorPreview.style.left = defaultLeft + halfOffsetWidth + 'px';
            } else if ((window.innerWidth - middleBoundary) < halfOffsetWidth) {
                self.anchorPreview.style.left = window.innerWidth + defaultLeft - halfOffsetWidth + 'px';
            } else {
                self.anchorPreview.style.left = defaultLeft + middleBoundary + 'px';
            }

            return this;
        },

        // TODO: break method
        observeAnchorPreview: function (anchorEl) {
            var self = this,
                lastOver = (new Date()).getTime(),
                over = true,
                stamp = function () {
                    lastOver = (new Date()).getTime();
                    over = true;
                },
                unstamp = function (e) {
                    if (!e.relatedTarget || !/anchor-preview/.test(e.relatedTarget.className)) {
                        over = false;
                    }
                },
                interval_timer = setInterval(function () {
                    if (over) {
                        return true;
                    }
                    var durr = (new Date()).getTime() - lastOver;
                    if (durr > self.options.anchorPreviewHideDelay) {
                        // hide the preview 1/2 second after mouse leaves the link
                        self.hideAnchorPreview();

                        // cleanup
                        clearInterval(interval_timer);
                        self.anchorPreview.removeEventListener('mouseover', stamp);
                        self.anchorPreview.removeEventListener('mouseout', unstamp);
                        anchorEl.removeEventListener('mouseover', stamp);
                        anchorEl.removeEventListener('mouseout', unstamp);

                    }
                }, 200);

            self.anchorPreview.addEventListener('mouseover', stamp);
            self.anchorPreview.addEventListener('mouseout', unstamp);
            anchorEl.addEventListener('mouseover', stamp);
            anchorEl.addEventListener('mouseout', unstamp);
        },

        createAnchorPreview: function () {
            var self = this,
                anchorPreview = document.createElement('div');

            anchorPreview.id = 'medium-editor-anchor-preview-' + this.id;
            anchorPreview.className = 'medium-editor-anchor-preview';
            anchorPreview.innerHTML = this.anchorPreviewTemplate();
            this.options.elementsContainer.appendChild(anchorPreview);

            anchorPreview.addEventListener('click', function () {
                self.anchorPreviewClickHandler();
            });

            return anchorPreview;
        },

        anchorPreviewTemplate: function () {
            return '<div class="medium-editor-toolbar-anchor-preview" id="medium-editor-toolbar-anchor-preview">' +
            '    <i class="medium-editor-toolbar-anchor-preview-inner"></i>' +
            '</div>';
        },

        anchorPreviewClickHandler: function (e) {
            if (this.activeAnchor) {

                var self = this,
                    range = document.createRange(),
                    sel = window.getSelection();

                range.selectNodeContents(self.activeAnchor);
                sel.removeAllRanges();
                sel.addRange(range);
                setTimeout(function () {
                    if (self.activeAnchor) {
                        self.showAnchorForm(self.activeAnchor.href);
                    }
                    self.keepToolbarAlive = false;
                }, 100 + self.options.delay);

            }

            this.hideAnchorPreview();
        },

        editorAnchorObserver: function (e) {
            var self = this,
                overAnchor = true,
                leaveAnchor = function () {
                    // mark the anchor as no longer hovered, and stop listening
                    overAnchor = false;
                    self.activeAnchor.removeEventListener('mouseout', leaveAnchor);
                };

            if (e.target && e.target.tagName.toLowerCase() === 'a') {

                // Detect empty href attributes
                // The browser will make href="" or href="#top"
                // into absolute urls when accessed as e.targed.href, so check the html
                if (!/href=["']\S+["']/.test(e.target.outerHTML) || /href=["']#\S+["']/.test(e.target.outerHTML)) {
                    return true;
                }

                // only show when hovering on anchors
                if (this.toolbar.classList.contains('medium-editor-toolbar-active')) {
                    // only show when toolbar is not present
                    return true;
                }
                this.activeAnchor = e.target;
                this.activeAnchor.addEventListener('mouseout', leaveAnchor);
                // show the anchor preview according to the configured delay
                // if the mouse has not left the anchor tag in that time
                setTimeout(function () {
                    if (overAnchor) {
                        self.showAnchorPreview(e.target);
                    }
                }, self.options.delay);


            }
        },

        bindAnchorPreview: function (index) {
            var i, self = this;
            this.editorAnchorObserverWrapper = function (e) {
                self.editorAnchorObserver(e);
            };
            for (i = 0; i < this.elements.length; i += 1) {
                this.elements[i].addEventListener('mouseover', this.editorAnchorObserverWrapper);
            }
            return this;
        },

        checkLinkFormat: function (value) {
            var re = /^https?:\/\//;
            if (value.match(re)) {
                return value;
            }
            return "http://" + value;
        },

        setTargetBlank: function () {
            var el = getSelectionStart(),
                i;
            if (el.tagName.toLowerCase() === 'a') {
                el.target = '_blank';
            } else {
                el = el.getElementsByTagName('a');
                for (i = 0; i < el.length; i += 1) {
                    el[i].target = '_blank';
                }
            }
        },

        createLink: function (input) {
            if (input.value.trim().length === 0) {
                this.hideToolbarActions();
                return;
            }
            restoreSelection(this.savedSelection);
            if (this.options.checkLinkFormat) {
                input.value = this.checkLinkFormat(input.value);
            }
            document.execCommand('createLink', false, input.value);
            if (this.options.targetBlank) {
                this.setTargetBlank();
            }
            this.checkSelection();
            this.showToolbarActions();
            input.value = '';
        },

        bindWindowActions: function () {
            var timerResize,
                self = this;
            this.windowResizeHandler = function () {
                clearTimeout(timerResize);
                timerResize = setTimeout(function () {
                    if (self.toolbar && self.toolbar.classList.contains('medium-editor-toolbar-active')) {
                        self.setToolbarPosition();
                    }
                }, 100);
            };
            window.addEventListener('resize', this.windowResizeHandler);
            return this;
        },

        activate: function () {
            if (this.isActive) {
                return;
            }

            this.setup();
        },

        // TODO: break method
        deactivate: function () {
            var i;
            if (!this.isActive) {
                return;
            }
            this.isActive = false;

            if (this.toolbar !== undefined) {
                this.options.elementsContainer.removeChild(this.anchorPreview);
                this.options.elementsContainer.removeChild(this.toolbar);
                delete this.toolbar;
                delete this.anchorPreview;
            }

            document.documentElement.removeEventListener('mouseup', this.checkSelectionWrapper);
            window.removeEventListener('resize', this.windowResizeHandler);

            for (i = 0; i < this.elements.length; i += 1) {
                this.elements[i].removeEventListener('mouseover', this.editorAnchorObserverWrapper);
                this.elements[i].removeEventListener('keyup', this.checkSelectionWrapper);
                this.elements[i].removeEventListener('blur', this.checkSelectionWrapper);
                this.elements[i].removeEventListener('paste', this.pasteWrapper);
                this.elements[i].removeAttribute('contentEditable');
                this.elements[i].removeAttribute('data-medium-element');
            }

        },

        htmlEntities: function (str) {
            // converts special characters (like <) into their escaped/encoded values (like &lt;).
            // This allows you to show to display the string without the browser reading it as HTML.
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        },

        bindPaste: function () {
            var i, self = this;
            this.pasteWrapper = function (e) {
                var paragraphs,
                    html = '',
                    p;

                this.classList.remove('medium-editor-placeholder');
                if (!self.options.forcePlainText && !self.options.cleanPastedHTML) {
                    return this;
                }

                if (e.clipboardData && e.clipboardData.getData && !e.defaultPrevented) {
                    e.preventDefault();

                    if (self.options.cleanPastedHTML && e.clipboardData.getData('text/html')) {
                        return self.cleanPaste(e.clipboardData.getData('text/html'));
                    }
                    if (!(self.options.disableReturn || this.getAttribute('data-disable-return'))) {
                        paragraphs = e.clipboardData.getData('text/plain').split(/[\r\n]/g);
                        for (p = 0; p < paragraphs.length; p += 1) {
                            if (paragraphs[p] !== '') {
                                if (navigator.userAgent.match(/firefox/i) && p === 0) {
                                    html += self.htmlEntities(paragraphs[p]);
                                } else {
                                    html += '<p>' + self.htmlEntities(paragraphs[p]) + '</p>';
                                }
                            }
                        }
                        document.execCommand('insertHTML', false, html);
                    } else {
                        document.execCommand('insertHTML', false, e.clipboardData.getData('text/plain'));
                    }
                }
            };
            for (i = 0; i < this.elements.length; i += 1) {
                this.elements[i].addEventListener('paste', this.pasteWrapper);
            }
            return this;
        },

        cleanPaste: function (text) {

            /*jslint regexp: true*/
            /*
             jslint does not allow character negation, because the negation
             will not match any unicode characters. In the regexes in this
             block, negation is used specifically to match the end of an html
             tag, and in fact unicode characters *should* be allowed.
             */
            var i, elList, workEl,
                el = this.getSelectionElement(),
                multiline = /<p|<br|<div/.test(text),
                replacements = [

                    // replace two bogus tags that begin pastes from google docs
                    [new RegExp(/<[^>]*docs-internal-guid[^>]*>/gi), ""],
                    [new RegExp(/<\/b>(<br[^>]*>)?$/gi), ""],

                    // un-html spaces and newlines inserted by OS X
                    [new RegExp(/<span class="Apple-converted-space">\s+<\/span>/g), ' '],
                    [new RegExp(/<br class="Apple-interchange-newline">/g), '<br>'],

                    // replace google docs italics+bold with a span to be replaced once the html is inserted
                    [new RegExp(/<span[^>]*(font-style:italic;font-weight:bold|font-weight:bold;font-style:italic)[^>]*>/gi), '<span class="replace-with italic bold">'],

                    // replace google docs italics with a span to be replaced once the html is inserted
                    [new RegExp(/<span[^>]*font-style:italic[^>]*>/gi), '<span class="replace-with italic">'],

                    //[replace google docs bolds with a span to be replaced once the html is inserted
                    [new RegExp(/<span[^>]*font-weight:bold[^>]*>/gi), '<span class="replace-with bold">'],

                    // replace manually entered b/i/a tags with real ones
                    [new RegExp(/&lt;(\/?)(i|b|a)&gt;/gi), '<$1$2>'],

                    // replace manually a tags with real ones, converting smart-quotes from google docs
                    [new RegExp(/&lt;a\s+href=(&quot;|&rdquo;|&ldquo;|“|”)([^&]+)(&quot;|&rdquo;|&ldquo;|“|”)&gt;/gi), '<a href="$2">']

                ];
            /*jslint regexp: false*/

            for (i = 0; i < replacements.length; i += 1) {
                text = text.replace(replacements[i][0], replacements[i][1]);
            }

            if (multiline) {

                // double br's aren't converted to p tags, but we want paragraphs.
                elList = text.split('<br><br>');

                this.pasteHTML('<p>' + elList.join('</p><p>') + '</p>');
                document.execCommand('insertText', false, "\n");

                // block element cleanup
                elList = el.querySelectorAll('p,div,br');
                for (i = 0; i < elList.length; i += 1) {

                    workEl = elList[i];

                    switch (workEl.tagName.toLowerCase()) {
                        case 'p':
                        case 'div':
                            this.filterCommonBlocks(workEl);
                            break;
                        case 'br':
                            this.filterLineBreak(workEl);
                            break;
                    }

                }


            } else {

                this.pasteHTML(text);

            }

        },

        pasteHTML: function (html) {
            var elList, workEl, i, fragmentBody, pasteBlock = document.createDocumentFragment();

            pasteBlock.appendChild(document.createElement('body'));

            fragmentBody = pasteBlock.querySelector('body');
            fragmentBody.innerHTML = html;

            this.cleanupSpans(fragmentBody);

            elList = fragmentBody.querySelectorAll('*');
            for (i = 0; i < elList.length; i += 1) {

                workEl = elList[i];

                // delete ugly attributes
                workEl.removeAttribute('class');
                workEl.removeAttribute('style');
                workEl.removeAttribute('dir');

                if (workEl.tagName.toLowerCase() === 'meta') {
                    workEl.parentNode.removeChild(workEl);
                }

            }
            document.execCommand('insertHTML', false, fragmentBody.innerHTML.replace(/&nbsp;/g, ' '));
        },
        isCommonBlock: function (el) {
            return (el && (el.tagName.toLowerCase() === 'p' || el.tagName.toLowerCase() === 'div'));
        },
        filterCommonBlocks: function (el) {
            if (/^\s*$/.test(el.innerText)) {
                el.parentNode.removeChild(el);
            }
        },
        filterLineBreak: function (el) {
            if (this.isCommonBlock(el.previousElementSibling)) {

                // remove stray br's following common block elements
                el.parentNode.removeChild(el);

            } else if (this.isCommonBlock(el.parentNode) && (el.parentNode.firstChild === el || el.parentNode.lastChild === el)) {

                // remove br's just inside open or close tags of a div/p
                el.parentNode.removeChild(el);

            } else if (el.parentNode.childElementCount === 1) {

                // and br's that are the only child of a div/p
                this.removeWithParent(el);

            }

        },

        // remove an element, including its parent, if it is the only element within its parent
        removeWithParent: function (el) {
            if (el && el.parentNode) {
                if (el.parentNode.parentNode && el.parentNode.childElementCount === 1) {
                    el.parentNode.parentNode.removeChild(el.parentNode);
                } else {
                    el.parentNode.removeChild(el.parentNode);
                }
            }
        },

        cleanupSpans: function (container_el) {

            var i,
                el,
                new_el,
                spans = container_el.querySelectorAll('.replace-with');

            for (i = 0; i < spans.length; i += 1) {

                el = spans[i];
                new_el = document.createElement(el.classList.contains('bold') ? 'b' : 'i');

                if (el.classList.contains('bold') && el.classList.contains('italic')) {

                    // add an i tag as well if this has both italics and bold
                    new_el.innerHTML = '<i>' + el.innerHTML + '</i>';

                } else {

                    new_el.innerHTML = el.innerHTML;

                }
                el.parentNode.replaceChild(new_el, el);

            }

            spans = container_el.querySelectorAll('span');
            for (i = 0; i < spans.length; i += 1) {

                el = spans[i];

                // remove empty spans, replace others with their contents
                if (/^\s*$/.test()) {
                    el.parentNode.removeChild(el);
                } else {
                    el.parentNode.replaceChild(document.createTextNode(el.innerText), el);
                }

            }

        }

    };

}(window, document));

/*
 _ _      _       _
 ___| (_) ___| | __  (_)___
 / __| | |/ __| |/ /  | / __|
 \__ \ | | (__|   < _ | \__ \
 |___/_|_|\___|_|\_(_)/ |___/
 |__/

 Version: 1.3.6
 Author: Ken Wheeler
 Website: http://kenwheeler.github.io
 Docs: http://kenwheeler.github.io/slick
 Repo: http://github.com/kenwheeler/slick
 Issues: http://github.com/kenwheeler/slick/issues

 */

/* global window, document, define, jQuery, setInterval, clearInterval */

(function(factory) {
    'use strict';
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else {
        factory(jQuery);
    }

}(function($) {
    'use strict';
    var Slick = window.Slick || {};

    Slick = (function() {

        var instanceUid = 0;

        function Slick(element, settings) {

            var _ = this,
                responsiveSettings, breakpoint;

            _.defaults = {
                accessibility: true,
                appendArrows: $(element),
                arrows: true,
                asNavFor: null,
                prevArrow: '<button type="button" class="slick-prev">Previous</button>',
                nextArrow: '<button type="button" class="slick-next">Next</button>',
                autoplay: false,
                autoplaySpeed: 3000,
                centerMode: false,
                centerPadding: '50px',
                cssEase: 'ease',
                customPaging: function(slider, i) {
                    return '<button type="button">' + (i + 1) + '</button>';
                },
                dots: false,
                draggable: true,
                easing: 'linear',
                fade: false,
                focusOnSelect: false,
                infinite: true,
                lazyLoad: 'ondemand',
                onBeforeChange: null,
                onAfterChange: null,
                onInit: null,
                onReInit: null,
                pauseOnHover: true,
                pauseOnDotsHover: false,
                responsive: null,
                slide: 'div',
                slidesToShow: 1,
                slidesToScroll: 1,
                speed: 300,
                swipe: true,
                touchMove: true,
                touchThreshold: 5,
                useCSS: true,
                vertical: false
            };

            _.initials = {
                animating: false,
                dragging: false,
                autoPlayTimer: null,
                currentSlide: 0,
                currentLeft: null,
                direction: 1,
                $dots: null,
                listWidth: null,
                listHeight: null,
                loadIndex: 0,
                $nextArrow: null,
                $prevArrow: null,
                slideCount: null,
                slideWidth: null,
                $slideTrack: null,
                $slides: null,
                sliding: false,
                slideOffset: 0,
                swipeLeft: null,
                $list: null,
                touchObject: {},
                transformsEnabled: false
            };

            $.extend(_, _.initials);

            _.activeBreakpoint = null;
            _.animType = null;
            _.animProp = null;
            _.breakpoints = [];
            _.breakpointSettings = [];
            _.cssTransitions = false;
            _.paused = false;
            _.positionProp = null;
            _.$slider = $(element);
            _.$slidesCache = null;
            _.transformType = null;
            _.transitionType = null;
            _.windowWidth = 0;
            _.windowTimer = null;

            _.options = $.extend({}, _.defaults, settings);

            _.originalSettings = _.options;
            responsiveSettings = _.options.responsive || null;

            if (responsiveSettings && responsiveSettings.length > -1) {
                for (breakpoint in responsiveSettings) {
                    if (responsiveSettings.hasOwnProperty(breakpoint)) {
                        _.breakpoints.push(responsiveSettings[
                            breakpoint].breakpoint);
                        _.breakpointSettings[responsiveSettings[
                            breakpoint].breakpoint] =
                            responsiveSettings[breakpoint].settings;
                    }
                }
                _.breakpoints.sort(function(a, b) {
                    return b - a;
                });
            }

            _.autoPlay = $.proxy(_.autoPlay, _);
            _.autoPlayClear = $.proxy(_.autoPlayClear, _);
            _.changeSlide = $.proxy(_.changeSlide, _);
            _.selectHandler = $.proxy(_.selectHandler, _);
            _.setPosition = $.proxy(_.setPosition, _);
            _.swipeHandler = $.proxy(_.swipeHandler, _);
            _.dragHandler = $.proxy(_.dragHandler, _);
            _.keyHandler = $.proxy(_.keyHandler, _);
            _.autoPlayIterator = $.proxy(_.autoPlayIterator, _);

            _.instanceUid = instanceUid++;

            // A simple way to check for HTML strings
            // Strict HTML recognition (must start with <)
            // Extracted from jQuery v1.11 source
            _.htmlExpr = /^(?:\s*(<[\w\W]+>)[^>]*)$/;

            _.init();

        }

        return Slick;

    }());

    Slick.prototype.addSlide = function(markup, index, addBefore) {

        var _ = this;

        if (typeof(index) === 'boolean') {
            addBefore = index;
            index = null;
        } else if (index < 0 || (index >= _.slideCount)) {
            return false;
        }

        _.unload();

        if (typeof(index) === 'number') {
            if (index === 0 && _.$slides.length === 0) {
                $(markup).appendTo(_.$slideTrack);
            } else if (addBefore) {
                $(markup).insertBefore(_.$slides.eq(index));
            } else {
                $(markup).insertAfter(_.$slides.eq(index));
            }
        } else {
            if (addBefore === true) {
                $(markup).prependTo(_.$slideTrack);
            } else {
                $(markup).appendTo(_.$slideTrack);
            }
        }

        _.$slides = _.$slideTrack.children(this.options.slide);

        _.$slideTrack.children(this.options.slide).remove();

        _.$slideTrack.append(_.$slides);

        _.$slides.each(function(index, element) {
            $(element).attr("index",index);
        });

        _.$slidesCache = _.$slides;

        _.reinit();

    };

    Slick.prototype.animateSlide = function(targetLeft,
                                            callback) {

        var animProps = {}, _ = this;

        if (_.transformsEnabled === false) {
            if (_.options.vertical === false) {
                _.$slideTrack.animate({
                    left: targetLeft
                }, _.options.speed, _.options.easing, callback);
            } else {
                _.$slideTrack.animate({
                    top: targetLeft
                }, _.options.speed, _.options.easing, callback);
            }

        } else {

            if (_.cssTransitions === false) {

                $({
                    animStart: _.currentLeft
                }).animate({
                    animStart: targetLeft
                }, {
                    duration: _.options.speed,
                    easing: _.options.easing,
                    step: function(now) {
                        if (_.options.vertical === false) {
                            animProps[_.animType] = 'translate(' +
                            now + 'px, 0px)';
                            _.$slideTrack.css(animProps);
                        } else {
                            animProps[_.animType] = 'translate(0px,' +
                            now + 'px)';
                            _.$slideTrack.css(animProps);
                        }
                    },
                    complete: function() {
                        if (callback) {
                            callback.call();
                        }
                    }
                });

            } else {

                _.applyTransition();

                if (_.options.vertical === false) {
                    animProps[_.animType] = 'translate3d(' + targetLeft + 'px, 0px, 0px)';
                } else {
                    animProps[_.animType] = 'translate3d(0px,' + targetLeft + 'px, 0px)';
                }
                _.$slideTrack.css(animProps);

                if (callback) {
                    setTimeout(function() {

                        _.disableTransition();

                        callback.call();
                    }, _.options.speed);
                }

            }

        }

    };

    Slick.prototype.applyTransition = function(slide) {

        var _ = this,
            transition = {};

        if (_.options.fade === false) {
            transition[_.transitionType] = _.transformType + ' ' + _.options.speed + 'ms ' + _.options.cssEase;
        } else {
            transition[_.transitionType] = 'opacity ' + _.options.speed + 'ms ' + _.options.cssEase;
        }

        if (_.options.fade === false) {
            _.$slideTrack.css(transition);
        } else {
            _.$slides.eq(slide).css(transition);
        }

    };

    Slick.prototype.autoPlay = function() {

        var _ = this;

        if (_.autoPlayTimer) {
            clearInterval(_.autoPlayTimer);
        }

        if (_.slideCount > _.options.slidesToShow && _.paused !== true) {
            _.autoPlayTimer = setInterval(_.autoPlayIterator,
                _.options.autoplaySpeed);
        }

    };

    Slick.prototype.autoPlayClear = function() {

        var _ = this;

        if (_.autoPlayTimer) {
            clearInterval(_.autoPlayTimer);
        }

    };

    Slick.prototype.autoPlayIterator = function() {

        var _ = this;
        var asNavFor = _.options.asNavFor != null ? $(_.options.asNavFor).getSlick() : null;

        if (_.options.infinite === false) {

            if (_.direction === 1) {

                if ((_.currentSlide + 1) === _.slideCount -
                    1) {
                    _.direction = 0;
                }

                _.slideHandler(_.currentSlide + _.options.slidesToScroll);
                if(asNavFor != null) asNavFor.slideHandler(asNavFor.currentSlide + asNavFor.options.slidesToScroll);

            } else {

                if ((_.currentSlide - 1 === 0)) {

                    _.direction = 1;

                }

                _.slideHandler(_.currentSlide - _.options.slidesToScroll);
                if(asNavFor != null) asNavFor.slideHandler(asNavFor.currentSlide - asNavFor.options.slidesToScroll);

            }

        } else {

            _.slideHandler(_.currentSlide + _.options.slidesToScroll);
            if(asNavFor != null) asNavFor.slideHandler(asNavFor.currentSlide + asNavFor.options.slidesToScroll);

        }

    };

    Slick.prototype.buildArrows = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {

            _.$prevArrow = $(_.options.prevArrow);
            _.$nextArrow = $(_.options.nextArrow);

            if (_.htmlExpr.test(_.options.prevArrow)) {
                _.$prevArrow.appendTo(_.options.appendArrows);
            }

            if (_.htmlExpr.test(_.options.nextArrow)) {
                _.$nextArrow.appendTo(_.options.appendArrows);
            }

            if (_.options.infinite !== true) {
                _.$prevArrow.addClass('slick-disabled');
            }

        }

    };

    Slick.prototype.buildDots = function() {

        var _ = this,
            i, dotString;

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            dotString = '<ul class="slick-dots">';

            for (i = 0; i <= _.getDotCount(); i += 1) {
                dotString += '<li>' + _.options.customPaging.call(this, _, i) + '</li>';
            }

            dotString += '</ul>';

            _.$dots = $(dotString).appendTo(
                _.$slider);

            _.$dots.find('li').first().addClass(
                'slick-active');

        }

    };

    Slick.prototype.buildOut = function() {

        var _ = this;

        _.$slides = _.$slider.children(_.options.slide +
        ':not(.slick-cloned)').addClass(
            'slick-slide');
        _.slideCount = _.$slides.length;

        _.$slides.each(function(index, element) {
            $(element).attr("index",index);
        });

        _.$slidesCache = _.$slides;

        _.$slider.addClass('slick-slider');

        _.$slideTrack = (_.slideCount === 0) ?
            $('<div class="slick-track"/>').appendTo(_.$slider) :
            _.$slides.wrapAll('<div class="slick-track"/>').parent();

        _.$list = _.$slideTrack.wrap(
            '<div class="slick-list"/>').parent();
        _.$slideTrack.css('opacity', 0);

        if (_.options.centerMode === true) {
            _.options.slidesToScroll = 1;
            if (_.options.slidesToShow % 2 === 0) {
                _.options.slidesToShow = 3;
            }
        }

        $('img[data-lazy]', _.$slider).not('[src]').addClass('slick-loading');

        _.setupInfinite();

        _.buildArrows();

        _.buildDots();

        _.updateDots();

        if (_.options.accessibility === true) {
            _.$list.prop('tabIndex', 0);
        }

        _.setSlideClasses(typeof this.currentSlide === 'number' ? this.currentSlide : 0);

        if (_.options.draggable === true) {
            _.$list.addClass('draggable');
        }

    };

    Slick.prototype.checkResponsive = function() {

        var _ = this,
            breakpoint, targetBreakpoint;

        if (_.originalSettings.responsive && _.originalSettings
                .responsive.length > -1 && _.originalSettings.responsive !== null) {

            targetBreakpoint = null;

            for (breakpoint in _.breakpoints) {
                if (_.breakpoints.hasOwnProperty(breakpoint)) {
                    if ($(window).width() < _.breakpoints[
                            breakpoint]) {
                        targetBreakpoint = _.breakpoints[
                            breakpoint];
                    }
                }
            }

            if (targetBreakpoint !== null) {
                if (_.activeBreakpoint !== null) {
                    if (targetBreakpoint !== _.activeBreakpoint) {
                        _.activeBreakpoint =
                            targetBreakpoint;
                        _.options = $.extend({}, _.options,
                            _.breakpointSettings[
                                targetBreakpoint]);
                        _.refresh();
                    }
                } else {
                    _.activeBreakpoint = targetBreakpoint;
                    _.options = $.extend({}, _.options,
                        _.breakpointSettings[
                            targetBreakpoint]);
                    _.refresh();
                }
            } else {
                if (_.activeBreakpoint !== null) {
                    _.activeBreakpoint = null;
                    _.options = $.extend({}, _.options,
                        _.originalSettings);
                    _.refresh();
                }
            }

        }

    };

    Slick.prototype.changeSlide = function(event) {

        var _ = this,
            $target = $(event.target);
        var asNavFor = _.options.asNavFor != null ? $(_.options.asNavFor).getSlick() : null;

        // If target is a link, prevent default action.
        $target.is('a') && event.preventDefault();

        switch (event.data.message) {

            case 'previous':
                if (_.slideCount > _.options.slidesToShow) {
                    _.slideHandler(_.currentSlide - _.options
                        .slidesToScroll);
                    if(asNavFor != null) asNavFor.slideHandler(asNavFor.currentSlide - asNavFor.options.slidesToScroll);
                }
                break;

            case 'next':
                if (_.slideCount > _.options.slidesToShow) {
                    _.slideHandler(_.currentSlide + _.options
                        .slidesToScroll);
                    if(asNavFor != null)  asNavFor.slideHandler(asNavFor.currentSlide + asNavFor.options.slidesToScroll);
                }
                break;

            case 'index':
                var index = $(event.target).parent().index() * _.options.slidesToScroll;
                _.slideHandler(index);
                if(asNavFor != null)  asNavFor.slideHandler(index);                break;

            default:
                return false;
        }

    };

    Slick.prototype.destroy = function() {

        var _ = this;

        _.autoPlayClear();

        _.touchObject = {};

        $('.slick-cloned', _.$slider).remove();
        if (_.$dots) {
            _.$dots.remove();
        }
        if (_.$prevArrow) {
            _.$prevArrow.remove();
            _.$nextArrow.remove();
        }
        if (_.$slides.parent().hasClass('slick-track')) {
            _.$slides.unwrap().unwrap();
        }
        _.$slides.removeClass(
            'slick-slide slick-active slick-visible').removeAttr('style');
        _.$slider.removeClass('slick-slider');
        _.$slider.removeClass('slick-initialized');

        _.$list.off('.slick');
        $(window).off('.slick-' + _.instanceUid);
    };

    Slick.prototype.disableTransition = function(slide) {

        var _ = this,
            transition = {};

        transition[_.transitionType] = "";

        if (_.options.fade === false) {
            _.$slideTrack.css(transition);
        } else {
            _.$slides.eq(slide).css(transition);
        }

    };

    Slick.prototype.fadeSlide = function(slideIndex, callback) {

        var _ = this;

        if (_.cssTransitions === false) {

            _.$slides.eq(slideIndex).css({
                zIndex: 1000
            });

            _.$slides.eq(slideIndex).animate({
                opacity: 1
            }, _.options.speed, _.options.easing, callback);

        } else {

            _.applyTransition(slideIndex);

            _.$slides.eq(slideIndex).css({
                opacity: 1,
                zIndex: 1000
            });

            if (callback) {
                setTimeout(function() {

                    _.disableTransition(slideIndex);

                    callback.call();
                }, _.options.speed);
            }

        }

    };

    Slick.prototype.filterSlides = function(filter) {

        var _ = this;

        if (filter !== null) {

            _.unload();

            _.$slideTrack.children(this.options.slide).remove();

            _.$slidesCache.filter(filter).appendTo(_.$slideTrack);

            _.reinit();

        }

    };

    Slick.prototype.getCurrent = function() {

        var _ = this;

        return _.currentSlide;

    };

    Slick.prototype.getDotCount = function() {

        var _ = this,
            breaker = 0,
            dotCounter = 0,
            dotCount = 0,
            dotLimit;

        dotLimit = _.options.infinite === true ? _.slideCount + _.options.slidesToShow - _.options.slidesToScroll : _.slideCount;

        while (breaker < dotLimit) {
            dotCount++;
            dotCounter += _.options.slidesToScroll;
            breaker = dotCounter + _.options.slidesToShow;
        }

        return dotCount;

    };

    Slick.prototype.getLeft = function(slideIndex) {

        var _ = this,
            targetLeft,
            verticalHeight,
            verticalOffset = 0;

        _.slideOffset = 0;
        verticalHeight = _.$slides.first().outerHeight();

        if (_.options.infinite === true) {
            if (_.slideCount > _.options.slidesToShow) {
                _.slideOffset = (_.slideWidth * _.options.slidesToShow) * -1;
                verticalOffset = (verticalHeight * _.options.slidesToShow) * -1;
            }
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                if (slideIndex + _.options.slidesToScroll > _.slideCount && _.slideCount > _.options.slidesToShow) {
                    _.slideOffset = ((_.slideCount % _.options.slidesToShow) * _.slideWidth) * -1;
                    verticalOffset = ((_.slideCount % _.options.slidesToShow) * verticalHeight) * -1;
                }
            }
        } else {
            if (_.slideCount % _.options.slidesToShow !== 0) {
                if (slideIndex + _.options.slidesToScroll > _.slideCount && _.slideCount > _.options.slidesToShow) {
                    _.slideOffset = (_.options.slidesToShow * _.slideWidth) - ((_.slideCount % _.options.slidesToShow) * _.slideWidth);
                    verticalOffset = ((_.slideCount % _.options.slidesToShow) * verticalHeight);
                }
            }
        }

        if (_.options.centerMode === true && _.options.infinite === true) {
            _.slideOffset += _.slideWidth * Math.floor(_.options.slidesToShow / 2) - _.slideWidth;
        } else if (_.options.centerMode === true) {
            _.slideOffset += _.slideWidth * Math.floor(_.options.slidesToShow / 2);
        }

        if (_.options.vertical === false) {
            targetLeft = ((slideIndex * _.slideWidth) * -1) + _.slideOffset;
        } else {
            targetLeft = ((slideIndex * verticalHeight) * -1) + verticalOffset;
        }

        return targetLeft;

    };

    Slick.prototype.init = function() {

        var _ = this;

        if (!$(_.$slider).hasClass('slick-initialized')) {

            $(_.$slider).addClass('slick-initialized');
            _.buildOut();
            _.setProps();
            _.startLoad();
            _.loadSlider();
            _.initializeEvents();
            _.checkResponsive();
        }

        if (_.options.onInit !== null) {
            _.options.onInit.call(this, _);
        }

    };

    Slick.prototype.initArrowEvents = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {
            _.$prevArrow.on('click.slick', {
                message: 'previous'
            }, _.changeSlide);
            _.$nextArrow.on('click.slick', {
                message: 'next'
            }, _.changeSlide);
        }

    };

    Slick.prototype.initDotEvents = function() {

        var _ = this;

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {
            $('li', _.$dots).on('click.slick', {
                message: 'index'
            }, _.changeSlide);
        }

        if (_.options.dots === true && _.options.pauseOnDotsHover === true && _.options.autoplay === true) {
            $('li', _.$dots)
                .on('mouseenter.slick', _.autoPlayClear)
                .on('mouseleave.slick', _.autoPlay);
        }

    };

    Slick.prototype.initializeEvents = function() {

        var _ = this;

        _.initArrowEvents();

        _.initDotEvents();

        _.$list.on('touchstart.slick mousedown.slick', {
            action: 'start'
        }, _.swipeHandler);
        _.$list.on('touchmove.slick mousemove.slick', {
            action: 'move'
        }, _.swipeHandler);
        _.$list.on('touchend.slick mouseup.slick', {
            action: 'end'
        }, _.swipeHandler);
        _.$list.on('touchcancel.slick mouseleave.slick', {
            action: 'end'
        }, _.swipeHandler);

        if (_.options.pauseOnHover === true && _.options.autoplay === true) {
            _.$list.on('mouseenter.slick', _.autoPlayClear);
            _.$list.on('mouseleave.slick', _.autoPlay);
        }

        if(_.options.accessibility === true) {
            _.$list.on('keydown.slick', _.keyHandler);
        }

        if(_.options.focusOnSelect === true) {
            $(_.options.slide, _.$slideTrack).on('click.slick', _.selectHandler);
        }

        $(window).on('orientationchange.slick.slick-' + _.instanceUid, function() {
            _.checkResponsive();
            _.setPosition();
        });

        $(window).on('resize.slick.slick-' + _.instanceUid, function() {
            if ($(window).width !== _.windowWidth) {
                clearTimeout(_.windowDelay);
                _.windowDelay = window.setTimeout(function() {
                    _.windowWidth = $(window).width();
                    _.checkResponsive();
                    _.setPosition();
                }, 50);
            }
        });

        $(window).on('load.slick.slick-' + _.instanceUid, _.setPosition);
        $(document).on('ready.slick.slick-' + _.instanceUid, _.setPosition);

    };

    Slick.prototype.initUI = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {

            _.$prevArrow.show();
            _.$nextArrow.show();

        }

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$dots.show();

        }

        if (_.options.autoplay === true) {

            _.autoPlay();

        }

    };

    Slick.prototype.keyHandler = function(event) {

        var _ = this;

        if (event.keyCode === 37) {
            _.changeSlide({
                data: {
                    message: 'previous'
                }
            });
        } else if (event.keyCode === 39) {
            _.changeSlide({
                data: {
                    message: 'next'
                }
            });
        }

    };

    Slick.prototype.lazyLoad = function() {

        var _ = this,
            loadRange, cloneRange, rangeStart, rangeEnd;

        function loadImages(imagesScope) {
            $('img[data-lazy]', imagesScope).each(function() {
                var image = $(this),
                    imageSource = $(this).attr('data-lazy');

                image
                    .css({ opacity: 0 })
                    .attr('src', imageSource)
                    .removeAttr('data-lazy')
                    .removeClass('slick-loading')
                    .load(function() { image.animate({ opacity: 1 }, 200); });
            });
        }

        if (_.options.centerMode === true || _.options.fade === true ) {
            rangeStart = _.options.slidesToShow + _.currentSlide - 1;
            rangeEnd = rangeStart + _.options.slidesToShow + 2;
        } else {
            rangeStart = _.options.infinite ? _.options.slidesToShow + _.currentSlide : _.currentSlide;
            rangeEnd = rangeStart + _.options.slidesToShow;
        }

        loadRange = _.$slider.find('.slick-slide').slice(rangeStart, rangeEnd);
        loadImages(loadRange);

        if (_.slideCount == 1){
            cloneRange = _.$slider.find('.slick-slide')
            loadImages(cloneRange)
        }else
        if (_.currentSlide >= _.slideCount - _.options.slidesToShow) {
            cloneRange = _.$slider.find('.slick-cloned').slice(0, _.options.slidesToShow);
            loadImages(cloneRange)
        } else if (_.currentSlide === 0) {
            cloneRange = _.$slider.find('.slick-cloned').slice(_.options.slidesToShow * -1);
            loadImages(cloneRange);
        }

    };

    Slick.prototype.loadSlider = function() {

        var _ = this;

        _.setPosition();

        _.$slideTrack.css({
            opacity: 1
        });

        _.$slider.removeClass('slick-loading');

        _.initUI();

        if (_.options.lazyLoad === 'progressive') {
            _.progressiveLazyLoad();
        }

    };

    Slick.prototype.postSlide = function(index) {

        var _ = this;

        if (_.options.onAfterChange !== null) {
            _.options.onAfterChange.call(this, _, index);
        }

        _.animating = false;

        _.setPosition();

        _.swipeLeft = null;

        if (_.options.autoplay === true && _.paused === false) {
            _.autoPlay();
        }

    };

    Slick.prototype.progressiveLazyLoad = function() {

        var _ = this,
            imgCount, targetImage;

        imgCount = $('img[data-lazy]').length;

        if (imgCount > 0) {
            targetImage = $('img[data-lazy]', _.$slider).first();
            targetImage.attr('src', targetImage.attr('data-lazy')).removeClass('slick-loading').load(function() {
                targetImage.removeAttr('data-lazy');
                _.progressiveLazyLoad();
            });
        }

    };

    Slick.prototype.refresh = function() {

        var _ = this,
            currentSlide = _.currentSlide;

        _.destroy();

        $.extend(_, _.initials);

        _.currentSlide = currentSlide;
        _.init();

    };

    Slick.prototype.reinit = function() {

        var _ = this;

        _.$slides = _.$slideTrack.children(_.options.slide).addClass(
            'slick-slide');

        _.slideCount = _.$slides.length;

        if (_.currentSlide >= _.slideCount && _.currentSlide !== 0) {
            _.currentSlide = _.currentSlide - _.options.slidesToScroll;
        }

        _.setProps();

        _.setupInfinite();

        _.buildArrows();

        _.updateArrows();

        _.initArrowEvents();

        _.buildDots();

        _.updateDots();

        _.initDotEvents();

        if(_.options.focusOnSelect === true) {
            $(_.options.slide, _.$slideTrack).on('click.slick', _.selectHandler);
        }

        _.setSlideClasses(0);

        _.setPosition();

        if (_.options.onReInit !== null) {
            _.options.onReInit.call(this, _);
        }

    };

    Slick.prototype.removeSlide = function(index, removeBefore) {

        var _ = this;

        if (typeof(index) === 'boolean') {
            removeBefore = index;
            index = removeBefore === true ? 0 : _.slideCount - 1;
        } else {
            index = removeBefore === true ? --index : index;
        }

        if (_.slideCount < 1 || index < 0 || index > _.slideCount - 1) {
            return false;
        }

        _.unload();

        _.$slideTrack.children(this.options.slide).eq(index).remove();

        _.$slides = _.$slideTrack.children(this.options.slide);

        _.$slideTrack.children(this.options.slide).remove();

        _.$slideTrack.append(_.$slides);

        _.$slidesCache = _.$slides;

        _.reinit();

    };

    Slick.prototype.setCSS = function(position) {

        var _ = this,
            positionProps = {}, x, y;

        x = _.positionProp == 'left' ? position + 'px' : '0px';
        y = _.positionProp == 'top' ? position + 'px' : '0px';

        positionProps[_.positionProp] = position;

        if (_.transformsEnabled === false) {
            _.$slideTrack.css(positionProps);
        } else {
            positionProps = {};
            if (_.cssTransitions === false) {
                positionProps[_.animType] = 'translate(' + x + ', ' + y + ')';
                _.$slideTrack.css(positionProps);
            } else {
                positionProps[_.animType] = 'translate3d(' + x + ', ' + y + ', 0px)';
                _.$slideTrack.css(positionProps);
            }
        }

    };

    Slick.prototype.setDimensions = function() {

        var _ = this;

        if (_.options.centerMode === true) {
            _.$slideTrack.children('.slick-slide').width(_.slideWidth);
        } else {
            _.$slideTrack.children('.slick-slide').width(_.slideWidth);
        }


        if (_.options.vertical === false) {
            _.$slideTrack.width(Math.ceil((_.slideWidth * _
                .$slideTrack.children('.slick-slide').length)));
            if (_.options.centerMode === true) {
                _.$list.css({
                    padding: ('0px ' + _.options.centerPadding)
                });
            }
        } else {
            _.$list.height(_.$slides.first().outerHeight() * _.options.slidesToShow);
            _.$slideTrack.height(Math.ceil((_.$slides.first().outerHeight() * _
                .$slideTrack.children('.slick-slide').length)));
            if (_.options.centerMode === true) {
                _.$list.css({
                    padding: (_.options.centerPadding + ' 0px')
                });
            }
        }

    };

    Slick.prototype.setFade = function() {

        var _ = this,
            targetLeft;

        _.$slides.each(function(index, element) {
            targetLeft = (_.slideWidth * index) * -1;
            $(element).css({
                position: 'relative',
                left: targetLeft,
                top: 0,
                zIndex: 800,
                opacity: 0
            });
        });

        _.$slides.eq(_.currentSlide).css({
            zIndex: 900,
            opacity: 1
        });

    };

    Slick.prototype.setPosition = function() {

        var _ = this;

        _.setValues();
        _.setDimensions();

        if (_.options.fade === false) {
            _.setCSS(_.getLeft(_.currentSlide));
        } else {
            _.setFade();
        }

    };

    Slick.prototype.setProps = function() {

        var _ = this;

        _.positionProp = _.options.vertical === true ? 'top' : 'left';

        if (_.positionProp === 'top') {
            _.$slider.addClass('slick-vertical');
        } else {
            _.$slider.removeClass('slick-vertical');
        }

        if (document.body.style.WebkitTransition !== undefined ||
            document.body.style.MozTransition !== undefined ||
            document.body.style.msTransition !== undefined) {
            if(_.options.useCSS === true) {
                _.cssTransitions = true;
            }
        }

        if (document.body.style.MozTransform !== undefined) {
            _.animType = 'MozTransform';
            _.transformType = "-moz-transform";
            _.transitionType = 'MozTransition';
        }
        if (document.body.style.webkitTransform !== undefined) {
            _.animType = 'webkitTransform';
            _.transformType = "-webkit-transform";
            _.transitionType = 'webkitTransition';
        }
        if (document.body.style.msTransform !== undefined) {
            _.animType = 'transform';
            _.transformType = "transform";
            _.transitionType = 'transition';
        }

        _.transformsEnabled = (_.animType !== null);

    };

    Slick.prototype.setValues = function() {

        var _ = this;

        _.listWidth = _.$list.width();
        _.listHeight = _.$list.height();
        if(_.options.vertical === false) {
            _.slideWidth = Math.ceil(_.listWidth / _.options
                .slidesToShow);
        } else {
            _.slideWidth = Math.ceil(_.listWidth);
        }

    };

    Slick.prototype.setSlideClasses = function(index) {

        var _ = this,
            centerOffset, allSlides, indexOffset;

        _.$slider.find('.slick-slide').removeClass('slick-active').removeClass('slick-center');
        allSlides = _.$slider.find('.slick-slide');

        if (_.options.centerMode === true) {

            centerOffset = Math.floor(_.options.slidesToShow / 2);

            if(_.options.infinite === true) {

                if (index >= centerOffset && index <= (_.slideCount - 1) - centerOffset) {
                    _.$slides.slice(index - centerOffset, index + centerOffset + 1).addClass('slick-active');
                } else {
                    indexOffset = _.options.slidesToShow + index;
                    allSlides.slice(indexOffset - centerOffset + 1, indexOffset + centerOffset + 2).addClass('slick-active');
                }

                if (index === 0) {
                    allSlides.eq(allSlides.length - 1 - _.options.slidesToShow).addClass('slick-center');
                } else if (index === _.slideCount - 1) {
                    allSlides.eq(_.options.slidesToShow).addClass('slick-center');
                }

            }

            _.$slides.eq(index).addClass('slick-center');

        } else {

            if (index > 0 && index < (_.slideCount - _.options.slidesToShow)) {
                _.$slides.slice(index, index + _.options.slidesToShow).addClass('slick-active');
            } else if ( allSlides.length <= _.options.slidesToShow ) {
                allSlides.addClass('slick-active');
            } else {
                indexOffset = _.options.infinite === true ? _.options.slidesToShow + index : index;
                allSlides.slice(indexOffset, indexOffset + _.options.slidesToShow).addClass('slick-active');
            }

        }

        if (_.options.lazyLoad === 'ondemand') {
            _.lazyLoad();
        }

    };

    Slick.prototype.setupInfinite = function() {

        var _ = this,
            i, slideIndex, infiniteCount;

        if (_.options.fade === true || _.options.vertical === true) {
            _.options.centerMode = false;
        }

        if (_.options.infinite === true && _.options.fade === false) {

            slideIndex = null;

            if (_.slideCount > _.options.slidesToShow) {

                if (_.options.centerMode === true) {
                    infiniteCount = _.options.slidesToShow + 1;
                } else {
                    infiniteCount = _.options.slidesToShow;
                }

                for (i = _.slideCount; i > (_.slideCount -
                infiniteCount); i -= 1) {
                    slideIndex = i - 1;
                    $(_.$slides[slideIndex]).clone().attr('id', '').prependTo(
                        _.$slideTrack).addClass('slick-cloned');
                }
                for (i = 0; i < infiniteCount; i += 1) {
                    slideIndex = i;
                    $(_.$slides[slideIndex]).clone().attr('id', '').appendTo(
                        _.$slideTrack).addClass('slick-cloned');
                }
                _.$slideTrack.find('.slick-cloned').find('[id]').each(function() {
                    $(this).attr('id', '');
                });

            }

        }

    };

    Slick.prototype.selectHandler = function(event) {

        var _ = this;
        var asNavFor = _.options.asNavFor != null ? $(_.options.asNavFor).getSlick() : null;
        var index = parseInt($(event.target).parent().attr("index"));
        if(!index) index = 0;

        if(_.slideCount <= _.options.slidesToShow){
            return;
        }
        _.slideHandler(index);

        if(asNavFor != null){
            if(asNavFor.slideCount <= asNavFor.options.slidesToShow){
                return;
            }
            asNavFor.slideHandler(index);
        }
    };

    Slick.prototype.slideHandler = function(index) {

        var targetSlide, animSlide, slideLeft, unevenOffset, targetLeft = null,
            _ = this;

        if (_.animating === true) {
            return false;
        }

        targetSlide = index;
        targetLeft = _.getLeft(targetSlide);
        slideLeft = _.getLeft(_.currentSlide);

        unevenOffset = _.slideCount % _.options.slidesToScroll !== 0 ? _.options.slidesToScroll : 0;

        _.currentLeft = _.swipeLeft === null ? slideLeft : _.swipeLeft;

        if (_.options.infinite === false && _.options.centerMode === false && (index < 0 || index > (_.slideCount - _.options.slidesToShow + unevenOffset))) {
            if(_.options.fade === false) {
                targetSlide = _.currentSlide;
                _.animateSlide(slideLeft, function() {
                    _.postSlide(targetSlide);
                });
            }
            return false;
        } else if (_.options.infinite === false && _.options.centerMode === true && (index < 0 || index > (_.slideCount - _.options.slidesToScroll))) {
            if(_.options.fade === false) {
                targetSlide = _.currentSlide;
                _.animateSlide(slideLeft, function() {
                    _.postSlide(targetSlide);
                });
            }
            return false;
        }

        if (_.options.autoplay === true) {
            clearInterval(_.autoPlayTimer);
        }

        if (targetSlide < 0) {
            if (_.slideCount % _.options.slidesToScroll !== 0) {
                animSlide = _.slideCount - (_.slideCount % _.options.slidesToScroll);
            } else {
                animSlide = _.slideCount - _.options.slidesToScroll;
            }
        } else if (targetSlide > (_.slideCount - 1)) {
            animSlide = 0;
        } else {
            animSlide = targetSlide;
        }

        _.animating = true;

        if (_.options.onBeforeChange !== null && index !== _.currentSlide) {
            _.options.onBeforeChange.call(this, _, _.currentSlide, animSlide);
        }

        _.currentSlide = animSlide;

        _.setSlideClasses(_.currentSlide);

        _.updateDots();
        _.updateArrows();

        if (_.options.fade === true) {
            _.fadeSlide(animSlide, function() {
                _.postSlide(animSlide);
            });
            return false;
        }

        _.animateSlide(targetLeft, function() {
            _.postSlide(animSlide);
        });

    };

    Slick.prototype.startLoad = function() {

        var _ = this;

        if (_.options.arrows === true && _.slideCount > _.options.slidesToShow) {

            _.$prevArrow.hide();
            _.$nextArrow.hide();

        }

        if (_.options.dots === true && _.slideCount > _.options.slidesToShow) {

            _.$dots.hide();

        }

        _.$slider.addClass('slick-loading');

    };

    Slick.prototype.swipeDirection = function() {

        var xDist, yDist, r, swipeAngle, _ = this;

        xDist = _.touchObject.startX - _.touchObject.curX;
        yDist = _.touchObject.startY - _.touchObject.curY;
        r = Math.atan2(yDist, xDist);

        swipeAngle = Math.round(r * 180 / Math.PI);
        if (swipeAngle < 0) {
            swipeAngle = 360 - Math.abs(swipeAngle);
        }

        if ((swipeAngle <= 45) && (swipeAngle >= 0)) {
            return 'left';
        }
        if ((swipeAngle <= 360) && (swipeAngle >= 315)) {
            return 'left';
        }
        if ((swipeAngle >= 135) && (swipeAngle <= 225)) {
            return 'right';
        }

        return 'vertical';

    };

    Slick.prototype.swipeEnd = function(event) {

        var _ = this;
        var asNavFor = _.options.asNavFor != null ? $(_.options.asNavFor).getSlick() : null;

        _.dragging = false;

        if (_.touchObject.curX === undefined) {
            return false;
        }

        if (_.touchObject.swipeLength >= _.touchObject.minSwipe) {
            $(event.target).on('click.slick', function(event) {
                event.stopImmediatePropagation();
                event.stopPropagation();
                event.preventDefault();
                $(event.target).off('click.slick');
            });

            switch (_.swipeDirection()) {
                case 'left':
                    _.slideHandler(_.currentSlide + _.options.slidesToScroll);
                    if(asNavFor != null) asNavFor.slideHandler(asNavFor.currentSlide + asNavFor.options.slidesToScroll);
                    _.touchObject = {};
                    break;

                case 'right':
                    _.slideHandler(_.currentSlide - _.options.slidesToScroll);
                    if(asNavFor != null) asNavFor.slideHandler(asNavFor.currentSlide - asNavFor.options.slidesToScroll);
                    _.touchObject = {};
                    break;
            }
        } else {
            if(_.touchObject.startX !== _.touchObject.curX) {
                _.slideHandler(_.currentSlide);
                if(asNavFor != null) asNavFor.slideHandler(asNavFor.currentSlide);
                _.touchObject = {};
            }
        }

    };

    Slick.prototype.swipeHandler = function(event) {

        var _ = this;

        if ((_.options.swipe === false) || ('ontouchend' in document && _.options.swipe === false)) {
            return;
        } else if ((_.options.draggable === false) || (_.options.draggable === false && !event.originalEvent.touches)) {
            return;
        }

        _.touchObject.fingerCount = event.originalEvent && event.originalEvent.touches !== undefined ?
            event.originalEvent.touches.length : 1;

        _.touchObject.minSwipe = _.listWidth / _.options
            .touchThreshold;

        switch (event.data.action) {

            case 'start':
                _.swipeStart(event);
                break;

            case 'move':
                _.swipeMove(event);
                break;

            case 'end':
                _.swipeEnd(event);
                break;

        }

    };

    Slick.prototype.swipeMove = function(event) {

        var _ = this,
            curLeft, swipeDirection, positionOffset, touches;

        touches = event.originalEvent !== undefined ? event.originalEvent.touches : null;

        curLeft = _.getLeft(_.currentSlide);

        if (!_.dragging || touches && touches.length !== 1) {
            return false;
        }

        _.touchObject.curX = touches !== undefined ? touches[0].pageX : event.clientX;
        _.touchObject.curY = touches !== undefined ? touches[0].pageY : event.clientY;

        _.touchObject.swipeLength = Math.round(Math.sqrt(
            Math.pow(_.touchObject.curX - _.touchObject.startX, 2)));

        swipeDirection = _.swipeDirection();

        if (swipeDirection === 'vertical') {
            return;
        }

        if (event.originalEvent !== undefined && _.touchObject.swipeLength > 4) {
            event.preventDefault();
        }

        positionOffset = _.touchObject.curX > _.touchObject.startX ? 1 : -1;

        if (_.options.vertical === false) {
            _.swipeLeft = curLeft + _.touchObject.swipeLength * positionOffset;
        } else {
            _.swipeLeft = curLeft + (_.touchObject
                .swipeLength * (_.$list.height() / _.listWidth)) * positionOffset;
        }

        if (_.options.fade === true || _.options.touchMove === false) {
            return false;
        }

        if (_.animating === true) {
            _.swipeLeft = null;
            return false;
        }

        _.setCSS(_.swipeLeft);

    };

    Slick.prototype.swipeStart = function(event) {

        var _ = this,
            touches;

        if (_.touchObject.fingerCount !== 1 || _.slideCount <= _.options.slidesToShow) {
            _.touchObject = {};
            return false;
        }

        if (event.originalEvent !== undefined && event.originalEvent.touches !== undefined) {
            touches = event.originalEvent.touches[0];
        }

        _.touchObject.startX = _.touchObject.curX = touches !== undefined ? touches.pageX : event.clientX;
        _.touchObject.startY = _.touchObject.curY = touches !== undefined ? touches.pageY : event.clientY;

        _.dragging = true;

    };

    Slick.prototype.unfilterSlides = function() {

        var _ = this;

        if (_.$slidesCache !== null) {

            _.unload();

            _.$slideTrack.children(this.options.slide).remove();

            _.$slidesCache.appendTo(_.$slideTrack);

            _.reinit();

        }

    };

    Slick.prototype.unload = function() {

        var _ = this;

        $('.slick-cloned', _.$slider).remove();
        if (_.$dots) {
            _.$dots.remove();
        }
        if (_.$prevArrow) {
            _.$prevArrow.remove();
            _.$nextArrow.remove();
        }
        _.$slides.removeClass(
            'slick-slide slick-active slick-visible').removeAttr('style');

    };

    Slick.prototype.updateArrows = function() {

        var _ = this;

        if (_.options.arrows === true && _.options.infinite !==
            true && _.slideCount > _.options.slidesToShow) {
            _.$prevArrow.removeClass('slick-disabled');
            _.$nextArrow.removeClass('slick-disabled');
            if (_.currentSlide === 0) {
                _.$prevArrow.addClass('slick-disabled');
                _.$nextArrow.removeClass('slick-disabled');
            } else if (_.currentSlide >= _.slideCount - _.options.slidesToShow) {
                _.$nextArrow.addClass('slick-disabled');
                _.$prevArrow.removeClass('slick-disabled');
            }
        }

    };

    Slick.prototype.updateDots = function() {

        var _ = this;

        if (_.$dots !== null) {

            _.$dots.find('li').removeClass('slick-active');
            _.$dots.find('li').eq(Math.floor(_.currentSlide / _.options.slidesToScroll)).addClass('slick-active');

        }

    };

    $.fn.slick = function(options) {
        var _ = this;
        return _.each(function(index, element) {

            element.slick = new Slick(element, options);

        });
    };

    $.fn.slickAdd = function(slide, slideIndex, addBefore) {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.addSlide(slide, slideIndex, addBefore);

        });
    };

    $.fn.slickCurrentSlide = function() {
        var _ = this;
        return _.get(0).slick.getCurrent();
    };

    $.fn.slickFilter = function(filter) {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.filterSlides(filter);

        });
    };

    $.fn.slickGoTo = function(slide) {
        var _ = this;
        return _.each(function(index, element) {

            var asNavFor = element.slick.options.asNavFor != null ? $(element.slick.options.asNavFor) : null;
            if(asNavFor != null) asNavFor.slickGoTo(slide);
            element.slick.slideHandler(slide);

        });
    };

    $.fn.slickNext = function() {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.changeSlide({
                data: {
                    message: 'next'
                }
            });

        });
    };

    $.fn.slickPause = function() {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.autoPlayClear();
            element.slick.paused = true;

        });
    };

    $.fn.slickPlay = function() {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.paused = false;
            element.slick.autoPlay();

        });
    };

    $.fn.slickPrev = function() {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.changeSlide({
                data: {
                    message: 'previous'
                }
            });

        });
    };

    $.fn.slickRemove = function(slideIndex, removeBefore) {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.removeSlide(slideIndex, removeBefore);

        });
    };

    $.fn.slickGetOption = function(option) {
        var _ = this;
        return _.get(0).slick.options[option];
    };

    $.fn.slickSetOption = function(option, value, refresh) {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.options[option] = value;

            if (refresh === true) {
                element.slick.unload();
                element.slick.reinit();
            }

        });
    };

    $.fn.slickUnfilter = function() {
        var _ = this;
        return _.each(function(index, element) {

            element.slick.unfilterSlides();

        });
    };

    $.fn.unslick = function() {
        var _ = this;
        return _.each(function(index, element) {

            if (element.slick) {
                element.slick.destroy();
            }

        });
    };

    $.fn.getSlick = function() {
        var s = null;
        var _ = this;
        _.each(function(index, element) {
            s = element.slick
        });

        return s;
    };

}));
$(document).ready(function(){
    //var totalPriceText = $('#total-price');
    //var checkoutBtn = $('#checkout-btn');

    $('.img-gallery').slick({
        dots: true,
        arrows: false

    });

// This example displays an address form, using the autocomplete feature
// of the Google Places API to help users fill in the information.
$('#autocomplete').show();
$('#hidden-form').hide();

var total = 0;

if (!typeof item === 'undefined') {
    item['price'] = parseFloat(item['price']);
}

/*
var doUpdatePrice = function() {
  if($('#country').val() == item['country']) {
    //Domestic shipping
    total = item['price'] + domesticShippingPrice;
    var text =
    totalPriceText.html(item['currency'] + total.toFixed(2));
  }

  else {
    //International shipping
    total = item['price'] + internationalShippingPrice;
    totalPriceText.html(item['currency'] + total.toFixed(2));
  }
    checkoutBtn.removeAttr('disabled');
}

$('#country').change(function() {

  doUpdatePrice();

});
*/

var placeSearch, autocomplete;
var componentForm = {
  street_number: 'short_name',
  route: 'long_name',
  locality: 'long_name',
  administrative_area_level_1: 'short_name',
  country: 'short_name',
  postal_code: 'short_name'
};

function initialize() {
  // Create the autocomplete object, restricting the search
  // to geographical location types.
    if(typeof google === 'undefined') {
        return;
    }
  autocomplete = new google.maps.places.Autocomplete(
      (document.getElementById('autocomplete')),
      { types: ['geocode'] });
  // When the user selects an address from the dropdown,
  // populate the address fields in the form.
  google.maps.event.addListener(autocomplete, 'place_changed', function() {
    fillInAddress();
  });
}

// [START region_fillform]
function fillInAddress() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();

  for (var component in componentForm) {
    document.getElementById(component).value = '';
    document.getElementById(component).readOnly = false;
  }

  // Get each component of the address from the place details
  // and fill the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm[addressType]) {
      var val = place.address_components[i][componentForm[addressType]];
      document.getElementById(addressType).value = val;
      document.getElementById(addressType).readOnly = true;
    }
  }

  $('#hidden-form').show();
  //doUpdatePrice();
}
// [END region_fillform]

// [START region_geolocation]
// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = new google.maps.LatLng(
          position.coords.latitude, position.coords.longitude);
      autocomplete.setBounds(new google.maps.LatLngBounds(geolocation,
          geolocation));
    });
  }
}
// [END region_geolocation]

initialize();
//geolocate();

    if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
        var msViewportStyle = document.createElement('style');
        msViewportStyle.appendChild(
            document.createTextNode(
                '@-ms-viewport{width:auto!important}'
            )
        );
        document.querySelector('head').appendChild(msViewportStyle);
    }

    $(function () {
        var nua = navigator.userAgent;
        var isAndroid = (nua.indexOf('Mozilla/5.0') > -1 && nua.indexOf('Android ') > -1 && nua.indexOf('AppleWebKit') > -1 && nua.indexOf('Chrome') === -1);
        if (isAndroid) {
            $('select.form-control').removeClass('form-control').css('width', '100%');
        }
    });
});

//Code for user.show page

var unfollowButton = $('#unfollow-btn');

unfollowButton.on('mouseover', function() {
    //console.log('MOUSEOVER');
    $(this).text('Unfollow');
    $(this).addClass('btn-danger');

});

unfollowButton.on('mouseleave', function() {
    unfollowButton.text('Following');
    unfollowButton.removeClass('btn-danger');
});

var mediumEditor = new MediumEditor('[data-md-ed]', {
    firstHeader: 'h3',
    secondHeader:'h4',
    cleanPastedHTML: false,
    buttons: ['bold', 'italic', 'anchor', 'header1', 'header2', 'blockquote', 'unorderedlist'],
    disableEditing: false


});




