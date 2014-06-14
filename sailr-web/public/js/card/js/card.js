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
        var validToggler;

        Card.prototype.template = "<div class=\"card-container\">\n    <div class=\"card\">\n        <div class=\"front\">\n                <div class=\"logo visa\">visa</div>\n                <div class=\"logo mastercard\">MasterCard</div>\n                <div class=\"logo amex\"></div>\n                <div class=\"logo discover\">discover</div>\n            <div class=\"lower\">\n                <div class=\"shiny\"></div>\n                <div class=\"cvc display\">••••</div>\n                <div class=\"number display\">•••• •••• •••• ••••</div>\n                <div class=\"name display\">Full name</div>\n                <div class=\"expiry display\">••/••</div>\n            </div>\n        </div>\n        <div class=\"back\">\n            <div class=\"bar\"></div>\n            <div class=\"cvc display\">•••</div>\n            <div class=\"shiny\"></div>\n        </div>\n    </div>\n</div>";

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
            }
        };

        function Card(el, opts) {
            this.options = $.extend({}, this.defaults, opts);
            this.$el = $(el);
            if (!this.options.container) {
                console.log("Please provide a container");
                return;
            }
            this.$container = $(this.options.container);
            this.render();
            this.attachHandlers();
            this.handleInitialValues();

            this.$nameInput.bindVal(this.$nameDisplay, {
                fill: false
            });
        }

        Card.prototype.render = function() {
            var baseWidth, ua;
            this.$container.append(this.template);
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
                this.$expiryInput.payment('formatCardExpiry');
                this.$cvcInput.payment('formatCardCVC');
            }
            if (this.options.width) {
                baseWidth = parseInt(this.$cardContainer.css('width'));
                this.$cardContainer.css("transform", "scale(" + (this.options.width / baseWidth) + ")");
            }
            if (typeof navigator !== "undefined" && navigator !== null ? navigator.userAgent : void 0) {
                ua = navigator.userAgent.toLowerCase();
                if (ua.indexOf('safari') !== -1 && ua.indexOf('chrome') === -1) {
                    return this.$card.addClass('no-radial-gradient');
                }
            }
        };

        Card.prototype.attachHandlers = function() {
            this.$numberInput.bindVal(this.$numberDisplay, {
                fill: false,
                filters: validToggler('validateCardNumber')
            }).on('payment.cardType', this.handle('setCardType'));
            this.$expiryInput.bindVal(this.$expiryDisplay, {
                filters: [
                    function(val) {
                        return val.replace(/(\s+)/g, '');
                    }, validToggler('validateCardExpiry')
                ]
            }).on('keydown', this.handle('captureTab'));
            this.$cvcInput.bindVal(this.$cvcDisplay, validToggler('validateCardCVC')).on('focus', this.handle('flipCard')).on('blur', this.handle('flipCard'));
            return this.$nameInput.bindVal(this.$nameDisplay, {
                fill: false
            });
        };

        Card.prototype.handleInitialValues = function() {
            return $.each(this.options.formSelectors, (function(_this) {
                return function(name, selector) {
                    var el;
                    el = _this["$" + name];
                    if (el.val()) {
                        console.log('hi');
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

        Card.prototype.handlers = {
            setCardType: function($el, e, cardType) {
                if (!this.$card.hasClass(cardType)) {
                    this.$card.removeClass('unknown');
                    this.$card.removeClass(this.cardTypes.join(' '));
                    this.$card.addClass(cardType);
                    return this.$card.toggleClass('identified', cardType !== 'unknown');
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
            }
        };

        $.fn.bindVal = function(out, opts) {
            var $el, i, o, outDefaults;
            if (opts == null) {
                opts = {};
            }
            opts.fill = opts.fill || false;
            opts.filters = opts.filters || [];
            if (!(opts.filters instanceof Array)) {
                opts.filters = [opts.filters];
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
                var filter, outVal, val, _i, _j, _len, _len1, _ref, _results;
                val = $el.val();
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

        validToggler = function(validatorName) {
            return function(val, $in, $out) {
                $out.toggleClass('valid', $.payment[validatorName](val));
                return val;
            };
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