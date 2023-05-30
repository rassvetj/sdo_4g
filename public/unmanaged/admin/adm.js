function get_image(type) {
	var val;
	val=1;
	if (type==1) {
		var s = window.showModalDialog("preview/dls.php4", val, "dialogHeight:600px;dialogWidth:800px;center:yes;resizable:yes;status:yes;help:yes;scroll:yes");
	}
	if (type==2) {
		var s = window.showModalDialog("preview/menu.php4", val, "dialogHeight:400px;dialogWidth:800px;center:yes;resizable:yes;status:yes;help:yes;scroll:yes");
	}
	if (type==3) {
		var s = window.showModalDialog("preview/list.php4", val, "dialogHeight:600px;dialogWidth:800px;center:yes;resizable:yes;status:yes;help:yes;scroll:yes");
	}
	if (s && s != '') {
		document.forms['act'].elements['image'].value=s;
	}
}

function user_logout() {
    if (typeof(event) != 'undefined'){
    	if (event.clientX <0 && event.clientY < 0) { 
    		document.location.href='logout.php?exit=true';
    	}
    }
}

//window.onunload = user_logout;

var Class = {
	create: function() {
		return function() {
			this.initialize.apply(this, arguments);
		};
	}
};
var version = Class.create();
version.prototype = {
	initialize: function(versionArray){
		this.major = versionArray[0] != null ? parseInt(versionArray[0], 10) : 0;
		this.minor = versionArray[1] != null ? parseInt(versionArray[1], 10) : 0;
		this.rev   = versionArray[2] != null ? parseInt(versionArray[2], 10) : 0;
	},
	compare: function(flashVersion){
		if (this.major < flashVersion.major) return -1;
		if (this.major > flashVersion.major) return  1;
		if (this.minor < flashVersion.minor) return -1;
		if (this.minor > flashVersion.minor) return  1;
		if (this.rev   < flashVersion.rev)   return -1;
		if (this.rev   > flashVersion.rev)   return  1;
		return 0;
	},
	major: -1,
	minor: -1,
	rev: -1,
	toString: function(){
		return [this.major, this.minor, this.rev].join('.');
	}
};

