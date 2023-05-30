(function() {

if (!self.eAu) { self.eAu = {}; }
if (eAu.contentBlocker) { return; }

var events = ['selectstart', 'dragstart', 'dblclick', 'mousedown', 'contextmenu'];
var unblockedElements = ['input', 'button', 'textarea', 'select', 'option', 'embed', 'object'];

eAu.contentBlocker = {
	enable: function(obj) {
		obj = obj || document;
		var $document = $(obj);
		$document.bind(events.join(' '), function(e) {
			if (unblockedElements.indexOf(e.target.tagName.toLowerCase()) == -1)  {
				return false;
			}
			return true;
		});
		/*@cc_on @*/
		/*@if (@_win32)
			$document.bind('load', function() {
				var images = document.getElementsByTagName("img");
				if (!images) { return; }
				for (var i = 0, length = images.length; i < length; ++i) {
					images[i].setAttribute("galleryimg", "no");
				}
			});
		/*@end @*/
	}
};

})();
