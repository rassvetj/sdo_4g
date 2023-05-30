//tinyMCEPopup.requireLangPack();
var waitforAMTcgiloc = true;

var AsciimathDialog = {
	init : function() {
		AMTcgiloc = "http://www.imathas.com/cgi-bin/mimetex.cgi";
	},

	set : function(val) {
		//tinyMCEPopup.restoreSelection();
		// Insert the contents from the input into the document
		//tinyMCEPopup.editor.execCommand('mceAsciimath', val);
        window.top.WikiEditor.getInstance().encloseSelection(' '+val+' ');
        window.top.$('#mathML').dialog("close");
	}
};


