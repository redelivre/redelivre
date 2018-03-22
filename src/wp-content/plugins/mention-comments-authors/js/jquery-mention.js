/**
 * jQuery plugin for getting position of cursor in textarea

 * @license under GNU license
 * @author Bevis Zhao (i@bevis.me, http://bevis.me)
 */
jQuery(function($) {

	var calculator = {
		// key styles
		primaryStyles: ['fontFamily', 'fontSize', 'fontWeight', 'fontVariant', 'fontStyle',
			'paddingLeft', 'paddingTop', 'paddingBottom', 'paddingRight',
			'marginLeft', 'marginTop', 'marginBottom', 'marginRight',
			'borderLeftColor', 'borderTopColor', 'borderBottomColor', 'borderRightColor',
			'borderLeftStyle', 'borderTopStyle', 'borderBottomStyle', 'borderRightStyle',
			'borderLeftWidth', 'borderTopWidth', 'borderBottomWidth', 'borderRightWidth',
			'line-height', 'outline', 'text-align'],

		specificStyle: {
			'word-wrap': 'break-word',
			'overflow-x': 'hidden',
			'overflow-y': 'auto'
		},

		simulator : $('<div id="textarea_simulator"/>').css({
				position: 'absolute',
				top: 0,
				left: 0,
				visibility: 'hidden'
			}).appendTo(document.body),

		toHtml : function(text) {
			return text.replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g, '<br>')
				.split(' ').join('<span style="white-space:prev-wrap">&nbsp;</span>');
		},
		// calculate position
		getCaretPosition: function() {
			var cal = calculator, self = this, element = self[0], elementOffset = self.offset();

			// IE has easy way to get caret offset position
			if ($.browser.msie && $.browser.version <= 9) {
				// must get focus first
				element.focus();
			    var range = document.selection.createRange();
			    $('#hskeywords').val(element.scrollTop);
			    return {
			        left: range.boundingLeft - elementOffset.left,
			        top: parseInt(range.boundingTop) - elementOffset.top + element.scrollTop
						+ document.documentElement.scrollTop + parseInt(self.getComputedStyle("fontSize"))
			    };
			}

			cal.simulator.empty();
			// clone primary styles to imitate textarea
			$.each(cal.primaryStyles, function(index, styleName) {
				self.cloneStyle(cal.simulator, styleName);
			});

			// caculate width and height
			cal.simulator.css($.extend({
				'width': self.width(),
				'height': self.height()
			}, cal.specificStyle));

			var value = (self.val() || self.text()), cursorPosition = self.getCursorPosition();
			var beforeText = value.substring(0, cursorPosition),
				afterText = value.substring(cursorPosition);

			var before = $('<span class="before"/>').html(cal.toHtml(beforeText)),
				focus = $('<span class="focus"/>'),
				after = $('<span class="after"/>').html(cal.toHtml(afterText));

			cal.simulator.append(before).append(focus).append(after);
			var focusOffset = focus.offset(), simulatorOffset = cal.simulator.offset();
			// alert(focusOffset.left  + ',' +  simulatorOffset.left + ',' + element.scrollLeft);
			return {
				top: focusOffset.top - simulatorOffset.top - element.scrollTop
					// calculate and add the font height except Firefox
					+ ($.browser.mozilla ? 0 : parseInt(self.getComputedStyle("fontSize"))),
				left: focus[0].offsetLeft -  cal.simulator[0].offsetLeft - element.scrollLeft
			};
		}
	};

	$.fn.extend({
		setCursorPosition : function(position){
	    if(this.length == 0) return this;
	    return $(this).setSelection(position, position);
		},
		setSelection: function(selectionStart, selectionEnd) {
	    if(this.length == 0) return this;
	    input = this[0];

	    if (input.createTextRange) {
	        var range = input.createTextRange();
	        range.collapse(true);
	        range.moveEnd('character', selectionEnd);
	        range.moveStart('character', selectionStart);
	        range.select();
	    } else if (input.setSelectionRange) {
	        input.focus();
	        input.setSelectionRange(selectionStart, selectionEnd);
	    } else {
	    	var el = this.get(0);

				var range = document.createRange();
				range.collapse(true);
				range.setStart(el.childNodes[0], selectionStart);
				range.setEnd(el.childNodes[0], selectionEnd);

				var sel = window.getSelection();
				sel.removeAllRanges();
				sel.addRange(range);
	    }

	    return this;
		},
		getComputedStyle: function(styleName) {
			if (this.length == 0) return;
			var thiz = this[0];
			var result = this.css(styleName);
			result = result || ($.browser.msie ?
				thiz.currentStyle[styleName]:
				document.defaultView.getComputedStyle(thiz, null)[styleName]);
			return result;
		},
		// easy clone method
		cloneStyle: function(target, styleName) {
			var styleVal = this.getComputedStyle(styleName);
			if (!!styleVal) {
				$(target).css(styleName, styleVal);
			}
		},
		cloneAllStyle: function(target, style) {
			var thiz = this[0];
			for (var styleName in thiz.style) {
				var val = thiz.style[styleName];
				typeof val == 'string' || typeof val == 'number'
					? this.cloneStyle(target, styleName)
					: NaN;
			}
		},
		getCursorPosition : function() {
			var element = input = this[0];
			var value = (input.value || input.innerText)

		    if(!this.data("lastCursorPosition")){
		    	this.data("lastCursorPosition",0);
		    }

		    var lastCursorPosition = this.data("lastCursorPosition");

		  if (document.selection) {
		     input.focus();
		      var sel = document.selection.createRange();
		      var selLen = document.selection.createRange().text.length;
		      sel.moveStart('character', -value.length);
		      lastCursorPosition = sel.text.length - selLen;
		  } else if (input.selectionStart || input.selectionStart == '0') {
		  	return input.selectionStart;
		  } else if (typeof window.getSelection != "undefined" && window.getSelection().rangeCount>0) {
		  	  try{
		  	  var selection = window.getSelection();
		      var range = selection.getRangeAt(0);
		      var preCaretRange = range.cloneRange();
		      preCaretRange.selectNodeContents(element);
		      preCaretRange.setEnd(range.endContainer, range.endOffset);
		      lastCursorPosition =  preCaretRange.toString().length;
		  	}catch(e){
		  		lastCursorPosition = this.data("lastCursorPosition");	
		  	}
		  } else if (typeof document.selection != "undefined" && document.selection.type != "Control") {
		      var textRange = document.selection.createRange();
		      var preCaretTextRange = document.body.createTextRange();
		      preCaretTextRange.moveToElementText(element);
		      preCaretTextRange.setEndPoint("EndToEnd", textRange);
		      lastCursorPosition =  preCaretTextRange.text.length;
		  }

    		this.data("lastCursorPosition",lastCursorPosition);
		  return lastCursorPosition;
	  },
		getCaretPosition: calculator.getCaretPosition
	});
});

/**
 * jQuery plugin for getting position of cursor in textarea
 * @license under dfyw (do the fuck you want)
 * @author leChantaux (@leChantaux)
 */

(function ($, window, undefined) {
	// Create the defaults once
	var elementFactory = function (element, value) {
		element.text(value.val);
	};

	var pluginName = 'sew',
		defaults = {
			token: '@',
			elementFactory: elementFactory,
			values: [],
			unique: false,
			repeat: true
		};

	function Plugin(element, options) {
		this.element = element;
		this.$element = $(element);
		this.$itemList = $(Plugin.MENU_TEMPLATE);

		this.options = $.extend({}, defaults, options);
		this.reset();

		this._defaults = defaults;
		this._name = pluginName;

		this.expression = new RegExp('(^|\\b|\\s)' + this.options.token + '([\\w.]*)$');
		this.cleanupHandle = null;

		this.init();
	}

	Plugin.MENU_TEMPLATE = "<div class='-sew-list-container' style='display: none; position: absolute;'><ul class='-sew-list'></ul></div>";

	Plugin.ITEM_TEMPLATE = '<li class="-sew-list-item"></li>';

	Plugin.KEYS = [40, 38, 13, 27, 9];

	Plugin.prototype.init = function () {
		if(this.options.values.length < 1) return;

		this.$element.unbind()
									.bind('keyup', $.proxy(this.onKeyUp, this))
									.bind('keydown', $.proxy(this.onKeyDown, this))
									.bind('focus', $.proxy(this.renderElements, this, this.options.values))
									.bind('blur', $.proxy(this.remove, this));
	};

	Plugin.prototype.reset = function () {
		if(this.options.unique) {
			this.options.values = Plugin.getUniqueElements(this.options.values);
		}

		this.index = 0;
		this.matched = false;
		this.dontFilter = false;
		this.lastFilter = undefined;
		this.filtered = this.options.values.slice(0);
	};

	Plugin.prototype.next = function () {
		this.index = (this.index + 1) % this.filtered.length;
		this.hightlightItem();
	};

	Plugin.prototype.prev = function () {
		this.index = (this.index + this.filtered.length - 1) % this.filtered.length;
		this.hightlightItem();
	};

	Plugin.prototype.select = function () {
		this.replace(this.filtered[this.index].val);
		this.$element.trigger('mention-selected',this.filtered[this.index]);
		this.hideList();
	};

	Plugin.prototype.remove = function () {
		this.$itemList.fadeOut('slow');

		this.cleanupHandle = window.setTimeout($.proxy(function () {
			this.$itemList.remove();
		}, this), 1000);
	};

	Plugin.prototype.replace = function (replacement) {
		var startpos = this.$element.getCursorPosition();

		var fullStuff = this.getText();
		var val = fullStuff.substring(0, startpos);
		val = val.replace(this.expression, '$1' + this.options.token + replacement);

		var posfix = fullStuff.substring(startpos, fullStuff.length);
		var separator = posfix.match(/^\s/) ? '' : ' ';

		var finalFight = val + separator + posfix;
		this.setText(finalFight);
		this.$element.setCursorPosition(val.length + 1);
	};

	Plugin.prototype.hightlightItem = function () {
		this.$itemList.find(".-sew-list-item").removeClass("selected");

		var container = this.$itemList.find(".-sew-list-item").parent();
		var element = this.filtered[this.index].element.addClass("selected");

		var scrollPosition = element.position().top;
		container.scrollTop(container.scrollTop() + scrollPosition);
	};

	Plugin.prototype.renderElements = function (values) {
		$("body").append(this.$itemList);

		var container = this.$itemList.find('ul').empty();
		values.forEach($.proxy(function (e, i) {
			var $item = $(Plugin.ITEM_TEMPLATE);

			this.options.elementFactory($item, e);

			e.element = $item.appendTo(container).bind('click', $.proxy(this.onItemClick, this, e)).bind('mouseover', $.proxy(this.onItemHover, this, i));
		}, this));

		this.index = 0;
		this.hightlightItem();
	};

	Plugin.prototype.displayList = function () {
		if(!this.filtered.length) return;

		this.$itemList.show();
		var element = this.$element;
		var offset = this.$element.offset();
		var pos = element.getCaretPosition();

		this.$itemList.css({
			left: offset.left + pos.left,
			top: offset.top + pos.top
		});
	};

	Plugin.prototype.hideList = function () {
		this.$itemList.hide();
		this.reset();
	};

	Plugin.prototype.filterList = function (val) {
		if(val == this.lastFilter) return;

		this.lastFilter = val;
		this.$itemList.find(".-sew-list-item").remove();
		var values = this.options.values;


		var vals = this.filtered = values.filter($.proxy(function (e) {
			var exp = new RegExp('\\W*' + this.options.token + e.val + '(\\W|$)');
			if(!this.options.repeat && this.getText().match(exp)) {
				return false;
			}

			return	val === "" ||
							e.val.toLowerCase().indexOf(val.toLowerCase()) >= 0 ||
							(e.meta || "").toLowerCase().indexOf(val.toLowerCase()) >= 0;
		}, this));

		if(vals.length) {
			this.renderElements(vals);
			this.$itemList.show();
		} else {
			this.hideList();
		}
	};

	Plugin.getUniqueElements = function (elements) {
		var target = [];

		elements.forEach(function (e) {
			var hasElement = target.map(function (j) { return j.val; }).indexOf(e.val) >= 0;
			if(hasElement) return;
			target.push(e);
		});

		return target;
	};

	Plugin.prototype.getText = function () {
		return(this.$element.val() || this.$element.text());
	};

	Plugin.prototype.setText = function (text) {
		if(this.$element.is('input,textarea')) {
			this.$element.val(text);
		} else {
			this.$element.html(text);
		}
	};

	Plugin.prototype.onKeyUp = function (e) {
		var startpos = this.$element.getCursorPosition();
		var val = this.getText().substring(0, startpos);
		var matches = val.match(this.expression);

		if(!matches && this.matched) {
			this.matched = false;
			this.dontFilter = false;
			this.hideList();
			return;
		}

		if(matches && !this.matched) {
			this.displayList();
			this.lastFilter = "\n";
			this.matched = true;
		}

		if(matches && !this.dontFilter) {
			this.filterList(matches[2]);
		}
	};

	Plugin.prototype.onKeyDown = function (e) {
		var listVisible = this.$itemList.is(":visible");
		if(!listVisible || (Plugin.KEYS.indexOf(e.keyCode) < 0)) return;

		switch(e.keyCode) {
			case 9:
			case 13:
				this.select();
				break;
			case 40:
				this.next();
				break;
			case 38:
				this.prev();
				break;
			case 27:
				this.$itemList.hide();
				this.dontFilter = true;
				break;
		}

		e.preventDefault();
	};

	Plugin.prototype.onItemClick = function (element, e) {
		if(this.cleanupHandle) window.clearTimeout(this.cleanupHandle);

		this.replace(element.val);
		this.$element.trigger('mention-selected',this.filtered[this.index]);
		this.hideList();
	};

	Plugin.prototype.onItemHover = function (index, e) {
		this.index = index;
		this.hightlightItem();
	};

	// $.fn[pluginName] = function (options) {
	// 	return this.each(function () {
	// 		if(!$.data(this, 'plugin_' + pluginName)) {
	// 			$.data(this, 'plugin_' + pluginName, new Plugin(this, options));
	// 		}
	// 	});
	// };
	$.fn.extend( {
		sew: function( options ) {
		    return this.each( function() {
		    	new Plugin(this, options);
		    } );
		}
	} );
}(jQuery, window));