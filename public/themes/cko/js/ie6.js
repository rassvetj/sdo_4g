(function ($, root, doc, undef) {

var btns
  , i
  , length
  , item
  , interval
  , prefix = 'ie6-'
  , prefixRe = new RegExp('(:?^' + prefix + ')')
  , spacesRe = /[\s\xA0]+/
  , debouncedProcessInput = _.debounce(processInput);


function updateInputsTypeClass () {
	var inputs = document.getElementsByTagName('input');
	for (i = 0, length = inputs.length; i < length; ++i) {
		updateInputTypeClass(inputs[i]);
		processInput(inputs[i]);
	}
}
function updateInputTypeClass (input) {
	if (!input.addedie6icn) {
		input.addedie6icn = true;
		input.className = (input.className || '') + ' ' + (input.type || '');
	}
}
function processInput (inp) {
	var cls = _.compact($.trim(inp.className || '').split(spacesRe))
	  , preffixed = {}
	  , unpreffixed = {}
	  , preffixedLength = 0
	  , changed
	  , toadd
	  , pref = prefix + (inp.type || 'text') + '-'
	  , prefRe = new RegExp('(:?^' + pref + ')');
	if (inp.type == 'hidden' || !cls.length) {
		return;
	}
	for (var i = cls.length - 1; i >= 0; --i) {
		if (prefRe.test(cls[i])) {
			preffixedLength++;
			preffixed[cls[i]] = true;
		} else if (!prefixRe.test(cls[i])) {
			unpreffixed[cls[i]] = true;
		} else {
			changed = true;
		}
	}
	// detect added classes
	if (!changed) {
		for (var c in unpreffixed) { if (unpreffixed.hasOwnProperty(c)) {
			if (!preffixed[pref + c]) {
				changed = true;
				break;
			} else {
				preffixedLength--;
				delete preffixed[pref + c];
			}
		} }
	}
	// removed classes
	if (!changed)
		changed = preffixedLength;
	if (changed) {
		toadd = _.keys(unpreffixed);
		inp.className = _.map(toadd, function (c) { return pref + c; }).join(' ') + ' ' + toadd.join(' ');
	}
}

$(doc).delegate('input', 'mouseover mouseout mousemove click mousedown mouseup keup keydown keypress', function (event) {
	updateInputTypeClass(event.currentTarget);
	debouncedProcessInput(event.currentTarget);
});
$(doc).ajaxStop(function () {
	updateInputsTypeClass();
});
$(function () {
	updateInputsTypeClass();
	interval = setInterval(updateInputsTypeClass, 1000);
});
$(root).unload(function () {
	if (interval)
		clearInterval(interval);
});

})(jQuery, window, document);
