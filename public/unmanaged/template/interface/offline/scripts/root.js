// BEGIN: РќР°С…РѕРґРёРј РёРјСЏ С„Р°Р№Р»Р° xml
var TestXml = null;
var QParams = document.location.toString().toQueryParams();
if (QParams['id']) {
	TestXml = QParams['id'] + '.xml';
}
// END: РќР°С…РѕРґРёРј РёРјСЏ С„Р°Р№Р»Р° xml

// BEGIN: Р—Р°РіСЂСѓР¶Р°РµРј Xml С„Р°Р№Р» СЃ С‚РµСЃС‚РѕРј Рё Xslt С‚Р°Р±Р»РёС†Сѓ СЃС‚РёР»РµР№
var XmlRoot = null,
    XsltTest = null,
    TestNode = null;

(function() {

if (!TestXml) { return; }

eAu.preloader.add(TestXml, function(data) {
	if (!data) { return; }
	XmlRoot = data.documentElement;
	var testNodes = XmlRoot.getElementsByTagName('test');
	for (var i = 0, length = testNodes.length; i < length; ++i) {
		if (_X.id(testNodes[i]) == QParams['id']) {
			TestNode = testNodes[i];
			break;
		}
	}
});
eAu.preloader.add('xslt/quest.xsl', function(data) {
	if (!data) { return; }
	XsltTest = data;
});

})();
// END: Р—Р°РіСЂСѓР¶Р°РµРј Xml С„Р°Р№Р» СЃ С‚РµСЃС‚РѕРј Рё Xslt С‚Р°Р±Р»РёС†Сѓ СЃС‚РёР»РµР№




