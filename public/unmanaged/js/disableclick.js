function IsClickable(e)
{
	return !!e.target && !!e.target.nodeName && (e.target.nodeName.toLowerCase() == 'input' || e.target.nodeName.toLowerCase() == 'button' || e.target.nodeName.toLowerCase() == 'textarea' || e.target.nodeName.toLowerCase() == 'select' || e.target.nodeName.toLowerCase() == 'option')
}
function disableClickInObject(obj)
{
	if (!obj)
		return
	var appendToEvent = (obj.addEventListener) ? "" : "on"
	var addEvent = (obj.addEventListener) ? obj.addEventListener : obj.attachEvent
	if (obj.attachEvent && !obj.addEventListener) {
		addEvent("onselectstart",
			function(e) {
				if (!e)
					e = window.event;
				if (!e.target)
					e.target = e.srcElement;
				if (!IsClickable(e)) {
					return false;
				} else {
					return true;
				}
			}
		)
		addEvent("ondragstart",
			function(e) {
				if (!e)
					e = window.event;
				if (!e.target)
					e.target = e.srcElement;
				if (!IsClickable(e))
					return false;
				else
					return true;
			}
		)
		addEvent("ondblclick",
			function(e) {
				if (!e)
					e = window.event;
				if (!e.target)
					e.target = e.srcElement;
				if (!IsClickable(e))
					return false;
				else
					return true;
			}
		)
		addEvent("onmousedown",
			function(e) {
				if (!e)
					e = window.event;
				if (!e.target)
					e.target = e.srcElement;
				if (!IsClickable(e))
					return false;
				else
					return true;
			}
		)
		addEvent("oncontextmenu",
			function(e) {
				if (!e)
					e = window.event;
				if (!e.target)
					e.target = e.srcElement;
				if (!IsClickable(e))
					return false;
				else
					return true;
			}
		)
	} else {
		obj.onselectstart = function(e) {
			if (!e)
				e = window.event;
			if (!e.target)
				e.target = e.srcElement;
			if (!IsClickable(e)) {
				return false;
			} else {
				return true;
			}
		}
		obj.ondragstart = function(e) {
			if (!e)
				e = window.event;
			if (!e.target)
				e.target = e.srcElement;
			if (!IsClickable(e))
				return false;
			else
				return true;
		}
		obj.ondblclick = function(e) {
			if (!e)
				e = window.event;
			if (!e.target)
				e.target = e.srcElement;
			if (!IsClickable(e))
				return false;
			else
				return true;
		}
		obj.onmousedown = function(e) {
			if (!e)
				e = window.event;
			if (!e.target)
				e.target = e.srcElement;
			if (!IsClickable(e))
				return false;
			else
				return true;
		}
		obj.oncontextmenu = function(e) {
			if (!e)
				e = window.event;
			if (!e.target)
				e.target = e.srcElement;
			if (!IsClickable(e))
				return false;
			else
				return true;
		}
	}
	addEvent(appendToEvent+"load",
		function() {
			for (i = 0;i < document.images.length;i++) {
				document.images[i].setAttribute('galleryimg','no');
			}
		},
		true
	)
}
function disableClickInFrames(id)
{
	var getFrameDom = function(id) {
		var oIframe = document.getElementById(id);
		var tempDocumentObject = null;

		if (!oIframe) { return null; }
		
		try {
			if (oIframe.contentDocument) {
				tempDocumentObject = oIframe.contentDocument;
			} else if (oIframe.contentWindow) {
				tempDocumentObject = oIframe.contentWindow.document;
			} else {
				tempDocumentObject = null;
			}
		} catch (error) {}
		
		return tempDocumentObject;
	}
	
	var oDom = getFrameDom(id);
	
	if (!oDom) { return; }
	
	disableClickInObject(oDom);
	
	// Теперь пройдёмся по вложенным фреймам
	// только для IE
	try {
		for (var i = 0; i < oDom.frames.length; i++) {
			disableClickInObject(oDom.frames[i].document)
		}
		
	} catch (error) {}

	try {
		for (var i = 0; i < oDom.frames.length; i++) { }
	} catch (error) {}

	try {
		for (var i = 0; i < oDom.images.length;i++) {
			oDom.images[i].setAttribute('galleryimg','no');
		}
	} catch (error) {}
}

disableClickInObject(document)
