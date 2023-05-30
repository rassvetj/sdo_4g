// BEGIN: libpreloader
(function() {

if (!self.eAu) { self.eAu = {}; }
if (eAu.preloader) { return; }
//------------------------------------------------------------------------------
var handlers = {};
//------------------------------------------------------------------------------
function registerNewPreloader(extension, handler) {
	if (typeof handlers[extension] == "function") {
		return;
	}
	extension = extension.split(':')
	for (var i = 0, length = extension.length; i < length; ++i) {
		handlers[extension[i]] = handler;
	}
}
//------------------------------------------------------------------------------
function add(filename, callback, options) {
	options = Object.extend({
		async: false
	}, options || {});
	var matches = filename.match(/\.([A-Za-z0-9\-_]+)$/);
	if (!matches || !matches[1]) { return; }
	if (!handlers[matches[1]]) {
		return 'РќРµ Р·Р°СЂРµРіРёСЃС‚СЂРёСЂРѕРІР°РЅРѕ РЅРё РѕРґРЅРѕРіРѕ РѕР±СЂР°Р±РѕС‚С‡РёРєР° РґР»СЏ СЂР°СЃС€РёСЂРµРЅРёСЏ: "'+matches[1]+'"';
	}
	handlers[matches[1]].apply(null, [filename, callback, options]);
}
//------------------------------------------------------------------------------
registerNewPreloader('xml:php', function(filename, callback, options) {
	try {
		new Ajax.Request(filename, {
			method: 'get',
			asynchronous: options.async,
			onSuccess: function(transport) {
				if (!transport || !transport.responseText) { callback(null); return; }
				

				var XmlData = (new DOMParser()).parseFromString(transport.responseText, "text/xml");

				if (!XmlData || !XmlData.documentElement) { callback(null); return; }

				try {
					XmlData.setProperty("SelectionLanguage", "XPath");
				} catch (error) {}

				callback(XmlData);
			},
			onException: function() { callback(null); },
			onFailure: function() { callback(null); }
		});
	} catch (error) {
		callback(null);
	}
});
//------------------------------------------------------------------------------
registerNewPreloader('xsl', function(filename, callback, options) {
	var tmpXslt = new XSLTProcessor();
	if (!tmpXslt) {
		callback(null)
		return;
	}
	try {
		new Ajax.Request(filename, {
			method: 'get',
			asynchronous: options.async,
			onSuccess: function(transport) {
				if (!transport || !transport.responseText) { callback(null); return; }

				var XmlData = (new DOMParser()).parseFromString(transport.responseText, "text/xml");

				if (!XmlData || !XmlData.documentElement) { callback(null); return; }

				try {
					XmlData.setProperty("SelectionNamespaces", "xmlns:xsl='http://www.w3.org/1999/XSL/Transform'");
				} finally {}
				tmpXslt.importStylesheet(XmlData);
				callback(tmpXslt);
			},
			onException: function() { callback(null); },
			onFailure: function() { callback(null); }
		});
	} catch (error) {
		callback(null);
	}
});
//------------------------------------------------------------------------------
eAu.preloader = {
	add: function (filename, callback, options){
		if (!filename || filename == '' || !callback || typeof callback != 'function') { return false; }
		return add.apply(null, arguments);
	},
	register: function (extension, handler) {
		if (!extension || extension == '' || !handler) { return false; }
		return registerNewPreloader.apply(null, arguments);
	}
};

})();
// END: libpreloader

// BEGIN: libtimer
(function() {

if (!self.eAu) { self.eAu = {}; }
if (eAu.Timer) { return; }

eAu.Timer = Class.create({
	initialize: function(duration, options) {
		if (!Object.isNumber(duration) || duration <= 0) {
			return;
		}
		this._options = Object.extend({
			start:     Prototype.K, /* Р