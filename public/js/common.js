// remap jQuery to $
(function ($, doc, root, undef) {

var elsUrls = getRootUrls()
  , rootUrlRegEx = new RegExp('^' + elsUrls.root, 'i')
  , absUrlRegEx = /^(https?:)?\/\//i
  , langs;

// getting language attribute
// TODO export this api
var locale = $(doc.documentElement).data('locale') || {};
langs = _.compact(_.map((locale.language || '').split(','), function (item) {
	return jQuery.trim(item);
}));

function getRootUrls () {
	var i
	  , scriptNodes
	  , styleNodes
	  , scriptName = ''
	  , styleName = ''
	  , themeName;

	scriptNodes = document.getElementsByTagName('script');

	for (i = scriptNodes.length - 1; i >= 0; --i) {
		scriptName = (scriptNodes[i].getAttribute('src') || '').replace(new RegExp("[\\/\\\\]+", "g"), '/').replace(new RegExp("^(https?):/", "i"), "$1://");
		if (scriptName) {
			scriptName = scriptName.split('?');
			if (scriptName.length <= 2 && /common\.js$/i.test(scriptName[0])) {
				scriptName = scriptName[0].replace(/\/?js\/common\.js$/i, '/');
				break;
			}
		}
		scriptName = '';
	}
	
	styleNodes = document.getElementsByTagName('link');
	
	for (i = styleNodes.length - 1; i >= 0; --i) {
		styleName = (styleNodes[i].getAttribute('href') || '').replace(new RegExp("[\\/\\\\]+", "g"), '/').replace(new RegExp("^(https?):/", "i"), "$1://");
		if (styleName) {
			styleName = styleName.split('?');
			if (styleName.length <= 2 && /themes\/([^\/])+\/([^\/]+\/)?[^\/]+\.css$/i.test(styleName[0])) {
				styleName = styleName[0].replace(/themes\/([^\/]+)\/([^\/]+\/)?[^\/]+\.css$/i, 'themes/$1/');
				break;
			}
		}
		styleName = '';
	}
	return (scriptName && styleName) ? {root: scriptName || '', theme: styleName || ''} : '';
}
if (elsUrls) {
	if (absUrlRegEx.test(elsUrls.root)) {
		elsUrls.path = elsUrls.root.replace(absUrlRegEx, '').replace(/^[^\/]+/, '');
	} else {
		elsUrls.path = elsUrls.root;
	}

	yepnope.addFilter(function yepnopeElsFilter (resourceObj) {
		if (absUrlRegEx.test(resourceObj.url)) {
			//log('skipping absolute url\'s ' + resourceObj.url);
			resourceObj.bypass = true;
		} else {
			if (_.indexOf(resourceObj.prefixes, 'theme') != -1) {
				resourceObj.url = elsUrls.theme + resourceObj.url.replace(/^[\\\/]*/, '');
			} else {
				resourceObj.url = elsUrls.root + resourceObj.url.replace(/^[\\\/]*/, '');
			}
			resourceObj.charset = 'UTF-8';
		}
		//log('loading ' + resourceObj.url);
		return resourceObj;
	});
}
root.elsHelpers = _.extend(root.elsHelpers || {}, {
	url: function (type) {
		return elsUrls ? elsUrls[type || 'root'] : undef;
	}
});

var wndCache = {};
function getWindow (url, name) {
	var windowIsAccessible
	  , woptions = [ 'location=no', 'menubar=no', 'status=no', 'resizable=yes', 'scrollbars=yes', 'directories=no', 'toolbar=no' ].join(',')
	  , wnd;

	name = name.replace(/-/g, '_');
	try {
		windowIsAccessible = !!wndCache[name].location.href;
	} catch ( error ) {
		windowIsAccessible = false;
	}

	wnd = windowIsAccessible
		? wndCache[name]
		: root.open(url, name, woptions);
	if (wnd) {
		if (windowIsAccessible && url != wnd.elsLoadedLocation) {
			wnd.location.href = url;
		}
		wnd.opener = root;
		wnd.elsLoadedLocation = url;
		wnd.focus();
		wndCache[name] = wnd;
	}
	return {
		isBlocked: !wnd,
		isNew: !windowIsAccessible,
		wnd: wnd
	};
}
root.elsHelpers = _.extend(root.elsHelpers || {}, {
	popup: function (url, name) {
		if (name && _.isString(name)) {
			return getWindow(url, name).wnd;
		} else {
			// TODO
		}
	},
	alert: function (message, title, options) {
		var deferred = jQuery.Deferred()
		  , $dlg = $('<div/>');

		options = _.extend({}, window.eLS_translations.alert, options);

		if (options.html) {
			$dlg.html(message);
		} else {
			$dlg.text(message);
		}

		$dlg.dialog({
			modal: true,
			resizable: false,
			title: title || window.eLS_translations.alert.title,
			close: function () {
				deferred.resolve();
			},
			buttons: [{
				text: options.ok,
				click: function () {
					deferred.resolve();
				}
			}]
		});
		deferred.always(function () {
			$dlg.dialog('close');
			_.defer(function () {
				$dlg.dialog('destroy').remove();
			});
		});
		return deferred.promise();
	},
	confirm: function (message, title, options) {
		var deferred = jQuery.Deferred()
		  , $dlg = $('<div/>');

		options = _.extend({}, window.eLS_translations.confirm, options);

		if (options.html) {
			$dlg.html(message);
		} else {
			$dlg.text(message);
		}

		$dlg.dialog({
			modal: true,
			resizable: false,
			title: title || window.eLS_translations.confirm.title,
			close: function () {
				deferred.reject();
			},
			buttons: [{
				text: options.ok,
				click: function () {
					deferred.resolve();
				}
			}, {
				text: options.cancel,
				click: function () {
					deferred.reject();
				}
			}]
		});
		deferred.always(function () {
			$dlg.dialog('close');
			_.defer(function () {
				$dlg.dialog('destroy').remove();
			});
		});
		return deferred.promise();
	}
});

$(doc).delegate('a', 'click', function (event) {
	var $target = $(event.target)
	  , target = $target.attr('target');
	if ( target && target.charAt(0) != '_' ) {
		event.preventDefault();
		root.elsHelpers.popup($target.attr('href'), target);
	}
});

// Help button helper
$(doc).delegate('.help-activator', 'click', function helpActivatorDelegate (event) {
	var $target = $(event.currentTarget);

	event.preventDefault();
	if (helpActivatorDelegate.lightdialogIsLoading) {
		return;
	}

	helpActivatorDelegate.lightdialogIsLoading = true;
	yepnope({
		test: $.ui.lightdialog,
		nope: '/js/lib/jquery/jquery-ui.lightdialog.js',
		complete: function () { _.defer(function () { $(function () {
			var url = ((root.location.pathname || '/') + root.location.search)
			          .substr(elsUrls ? elsUrls.path.length : 0);

			helpActivatorDelegate.lightdialogIsLoading = false;

			$target.attr('href', $target.data('help-url').replace(/\/$/, '') + "?url=" + encodeURIComponent(url));
			
			$target.lightdialog({
				title: $target.attr('title'),
				dialogClass: 'help-card',
				width: 750,
				modal: false,
				contentMaxHeight: 800,
				position: ['center', $target.offset().top + $target.height() - $(document).scrollTop()]
			}).lightdialog('open');
		}); }); }
	});
});

// lightdialog button helper
$(doc).delegate('.dialog-activator', 'click', function dialogActivatorDelegate (event) {
	var $target = $(event.currentTarget);

	event.preventDefault();
	if (dialogActivatorDelegate.lightdialogIsLoading) {
		return;
	}

	dialogActivatorDelegate.lightdialogIsLoading = true;
	yepnope({
		test: $.ui.lightdialog,
		nope: '/js/lib/jquery/jquery-ui.lightdialog.js',
		complete: function () { _.defer(function () { $(function () {
			var url = $(this).attr('url');

			dialogActivatorDelegate.lightdialogIsLoading = false;
			//$target.attr('href', encodeURIComponent($target.attr('href')));
			
			$target.lightdialog({
				title: $target.attr('title'),
				dialogClass: 'help-card',
				width: 750,
				modal: false,
				contentMaxHeight: 800,
				position: ['center', $target.offset().top + $target.height() - $(document).scrollTop()]
			}).lightdialog('open');
		}); }); }
	});
});

// HACK wrap $.fn.datepicker to allow loading lang file ondemand
langs.length && (function () {
var l10nFilesLoading
  , l10nFilesLoaded = 0
  , langsToLoad = _.filter(langs, function (lang) { return !/^en$/i.test(lang); })
  , datepickersToRefresh = [];

$.fn.originaldatepicker = $.fn.datepicker;
$.datepicker.originalSetDefaults = $.datepicker.setDefaults;
$.fn.datepicker = function () {
	var result;
	if (!l10nFilesLoading && langsToLoad.length) {
		l10nFilesLoading = true;
		_.each(langsToLoad, function (lang) {
			yepnope({
				test: $.datepicker.regional[lang],
				nope: '/js/lib/jquery/i18n/jquery.ui.datepicker-'+ lang +'.js',
				callback: function () {
					l10nFilesLoaded++;
					if (l10nFilesLoaded == langsToLoad.length) {
						_.defer(function () {
							_.each(datepickersToRefresh, function (dp) {
								dp.datepicker('refresh');
							});
							datepickersToRefresh = [];
						});
					}
				}
			});
		});
	}
	result = this.originaldatepicker.apply(this, arguments);
	if (typeof arguments[0] == 'object' && l10nFilesLoading && l10nFilesLoaded != langsToLoad.length) {
		datepickersToRefresh.push(result);
	}
	return result;
}
$.datepicker.regional[''].dateFormat = 'dd.mm.yy';
// ВАЖНО — предполагается, что языковые файлы ВСЕГДА подгружаются асинхронно!!!
$.datepicker.setDefaults($.datepicker.regional['']);
$.datepicker.setDefaults = function (data) {
	if (data != null && _.isString(data.dateFormat)) {
		data.dateFormat = 'dd.mm.yy';
	}
	_.each($.datepicker.regional, function (data) {
		data.dateFormat = 'dd.mm.yy';;
	});
	var ret = $.datepicker.originalSetDefaults(data);
	$(window).trigger('datepicker-set-defaults');
	return ret;
}
	
})();

$.fn.propAttr || $.fn.extend({
	propAttr: $.fn.prop || $.fn.attr
});
$.fn.extend({
	disableSelectionLight: function () {
		return this.bind( ( $.support.selectstart ? "selectstart" : "mousedown" ) +
			".ui-disableSelection", function( event ) {
				$(event.target).is('input, textarea') || event.preventDefault();
			});
	}
});

var uiErrorBoxNonModalMessageTemplate;
$.widget( "ui.errorbox", {
	options: {
		// new option
		level: 'success',
		disabled: false
	},

	_create: function () {
		var o = _.extend({}, this.options)
		    , self = this
		    , map = { success: ['success', 'check'], notice: ['highlight', 'info'], error: ['error', 'alert'] }
		    , tabs;

		uiErrorBoxNonModalMessageTemplate || ( uiErrorBoxNonModalMessageTemplate = _.template([
			'<div class="ui-state-<%= state %> ui-corner-all">',
				'<span class="ui-icon ui-icon-<%= icon %>"></span><div class="ui-message-here"><%= message %></div>',
			'</div>'
		].join('')) );

		if (map[o.level]) {
			this.element
				.addClass("ui-widget ui-els-flash-message")
				.html(uiErrorBoxNonModalMessageTemplate({
					state: map[o.level][0],
					icon: map[o.level][1],
					message: this.element.html()
				}));
			//tabs = this.element.closest('.ui-local-errorbox');
			this.element.appendTo($.ui.errorbox.box(this.element));
			/*if (this.element.closest('#tabs').length) {
				this.element.appendTo(
					this.element.closest('#tabs').find('.error-box')
				);
			} else {
				this.element.appendTo('#error-box');
			}*/
		} else {
			this.element.dialog({
				closeOnEscape: true,
				modal: true,
				draggable: false,
				resizable: true
			});
		}
	},

	destroy: function () {
		this.element
			.removeClass("ui-widget ui-els-flash-message")
			.html(this.element.find('.ui-message-here').html());

		$.Widget.prototype.destroy.apply(this, arguments);
	},

	_setOption: function (key, value) {
		// TODO
		$.Widget.prototype._setOption.apply(this, arguments);
	}
});
$.extend($.ui.errorbox, {
	version: "0.1.0",
	clear: function (element) {
		if (element === 'all') {
			$('#error-box, .error-box, .ui-local-errorbox').html('');
		} else if (element) {
			$.ui.errorbox.box(element).html('');
		} else {
			$('#error-box').html('');
		}
	},
	box: function (element) {
		var current = element
		  , prevItems
		  , box;
		while (current && current.length) {
			box = current.children('.error-box:first');
			if (box.length) { break; }
			current = current.parent();
		}
		if (box && box.length) {
			return box;
		} else {
			return $('#error-box');
		}
	}
});

( function ( window, doc, undef ) {
	// Takes a preloaded css obj (changes in different browsers) and injects it into the head
	yepnope.injectCss = function( href, cb, attrs, timeout, /* Internal use */ err, internal, insert ) {

		// Create stylesheet link
		var link = document.createElement( "link" )
		  , onload = function() { if ( ! done ) { done = 1; link.removeAttribute("id"); setTimeout( cb, 0 ); } }
		  , id = "yn" + +new Date
		  , ref
		  , done
		  , i;

		cb = internal ? yepnope.executeStack : ( cb || function(){} );
		timeout = timeout || yepnope.errorTimeout;
		// Add attributes
		link.href = href;
		link.rel  = "stylesheet";
		link.type = "text/css";
		link.id = id;

		// Add our extra attributes to the link element
		for ( i in attrs ) {
			link.setAttribute( i, attrs[ i ] );
		}

		if ( ! err ) {
			if (insert) {
				insert(link);
			} else {
				ref = document.getElementsByTagName('base')[0] || document.getElementsByTagName('script')[0];
				ref.parentNode.insertBefore( link, ref );
			}
			link.onload = onload;

			function poll() {
				try {
					var sheets = document.styleSheets;
					for(var j=0, k=sheets.length; j<k; j++) {
						if(sheets[j].ownerNode.id == id) {
							// this throws an exception, I believe, if not full loaded (was originally just "sheets[j].cssRules;")
							if (sheets[j].cssRules.length)
								return onload();
						}
					}
					// if we get here, its not in document.styleSheets (we never saw the ID)
					throw new Error;
				} catch(e) {
					// Keep polling
					setTimeout(poll, 20);
				}
			}
			poll();
		}
	}
})( root, doc );

(function () {

var themeLinkStatuses = {}
  , head;

function insertStylesheetLink (link) {
	var links;
	
	if (!head) {
		head = document.getElementsByTagName('head')[0];
	}
	if (head) {
		links = head.getElementsByTagName('link');
		head.insertBefore(link, links[links.length - 1]);
	}
}

function updateThemeCssPosition () {
	var link = updateThemeCssPosition.link
	  , src
	  , linkNodeId
	  , shouldITry;

	if (!head) {
		head = document.getElementsByTagName('head')[0];
	}
	if (!link && (src = doc.getElementById('theme-css-file'))) {
		updateThemeCssPosition.link = link = src.getAttribute('href');
	}

	if (link && head) {
		shouldITry = (function (link) {
			var links = head.getElementsByTagName('link');
			for (var i = links.length - 1; i >= 0; --i) {
				// TODO AND MEDIA="SCREEN"
				if (/\.css$/i.test(links[i].getAttribute('href')) || links[i].getAttribute('rel') == 'stylesheet') {
					return links[i].getAttribute('href') != link;
				}
			}
			return true;
		})(link);

		// requires modified yepnope.css plugin
		shouldITry && yepnope.injectCss(link, function () {
			themeLinkStatuses = _.reduce(themeLinkStatuses, function (memo, item, id) {
				if (item.state != null) {
					item.node.parentNode && item.node.parentNode.removeChild(item.node);
				} else {
					memo[id] = item;
				}
				return memo;
			}, {});

			if (themeLinkStatuses[linkNodeId]) {
				themeLinkStatuses[linkNodeId].state = true;
				if (src) {
					src.parentNode.removeChild(src);
				}
			}
		}, {}, 0, false, false, function (l) {
			linkNodeId = l.id;
			_.each(themeLinkStatuses, function (item) {
				item.state = false;
			});
			themeLinkStatuses[linkNodeId] = {
				state: null,
				node:  l
			};
			insertStylesheetLink(l);
		});
	}
}

function moveCssFromBody () {
	var headLinks
	  , bodyLinks;

	if (!head) {
		head = document.getElementsByTagName('head')[0];
	}
	if (head && doc.body) {
		headLinks = _.reduce(_.toArray(head.getElementsByTagName('link')), function (memo, link) {
			var href = link.getAttribute('href');
			if (href) { memo[href] = true; }
			return memo;
		}, {});
		_.each(_.toArray(doc.body.getElementsByTagName('link')), function (link) {
			var href = link.getAttribute('href');
			if (href && !headLinks[href]) {
				headLinks[href] = true;
				/*head.appendChild(link);*/
				insertStylesheetLink(link);
			}
		}, {});
	}
}

$.ajaxPrefilter( function( options, originalOptions, jqXHR ) {
	_.defer(function () {
		jqXHR
			.always(moveCssFromBody)
			.always(updateThemeCssPosition);
	});
});

})();

$.ajaxPrefilter('script', function (options) {
	var url = options.url
	  , _root = root.elsHelpers.url('root')
	  , _path = root.elsHelpers.url('path');
	if (url && (url.indexOf(_root + 'js/') == 0 || url.indexOf(_path + 'js/') == 0)) {
		options.cache = true;
	}
});

root.elsHelpers = _.extend(root.elsHelpers || {}, {
	isDevelopment: $(doc.documentElement).data('env') == 'development'
});

root.elsHelpers = _.extend(root.elsHelpers || {}, {
	store: root.PStore
});
root.elsHelpers.store.init();

// PING server
elsHelpers.store.done(function () {
	var $doc = $(doc.documentElement)
	  , uiConfig = $(doc.documentElement).data('config') || {}
	  , pingInterval = (parseInt(uiConfig.pingInterval || 0, 10) || 700) * 1000
	  , pingUrl = uiConfig.pingUrl || elsHelpers.url('root') + 'ping.php'
	  , store = this;

	function ping (store) {
		var lastPingTime = store.get('lastPingTime') || 0
		  , dt = (new Date()).getTime();
		if ((dt - lastPingTime) >= pingInterval) {
			store.set('lastPingTime', dt, true);
			$.ajax(pingUrl, { global: false, cache: false });
		}
	}
	setInterval(function () {
		store.refresh().done(function () {
			ping(store);
		});
	}, 60 * 1000);
	ping(store);
});

$(document.documentElement).bind('dragover dragenter dragleave drop', function (event) {
	event.preventDefault();
});

$.fn.draghover = function (options) {
	return this.each(function () {
		var collection = $()
		  , self = $(this);

		self.on('dragenter', function (e) {
			if (collection.size() === 0) {
				self.trigger('draghoverstart', [e]);
			}
			collection = collection.add(e.target);
		});

		self.on('dragleave', function (e) {
			// timeout is needed because Firefox 3.6 fires the dragleave event on
			// the previous element before firing dragenter on the next one
			setTimeout( function () {
				collection = collection.not(e.target);
				if (collection.size() === 0) {
					self.trigger('draghoverend', [e]);
				}
			}, 1);
		});
	});
};

$(document.documentElement).draghover().on({
	draghoverstart: function (e, event) {
		$(this).addClass('ui-dragover');
	},
	draghoverend: function (e, event) {
		$(this).removeClass('ui-dragover');
	}
});

})(this.jQuery, document, window);

// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function f(){ log.history = log.history || []; log.history.push(arguments); if(this.console) { var args = arguments, newarr; try { args.callee = f.caller } catch(e) {}; newarr = [].slice.call(args); if (typeof console.log === 'object') log.apply.call(console.log, console, newarr); else console.log.apply(console, newarr);}};

// make it safe to use console.log always
(function(a){function b(){}for(var c="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn".split(","),d;!!(d=c.pop());){a[d]=a[d]||b;}})
(function(){try{console.log();return window.console;}catch(a){return (window.console={});}}());

// catch all document.write() calls
(function(doc){
	var write = doc.write;
	doc.write = function (q) {
		log('document.write(): ',arguments); 
		if (/docwriteregexwhitelist/.test(q) || true) write.apply(doc,arguments);  
	};
})(document);
