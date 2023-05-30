<script type="text/javascript">
// <!--
	jQuery.treeSelectloadXML = function(xmlUrl, callback) {
		jQuery.ajax({
			url: xmlUrl,
			dataType: "xml",
			success: function() {
				callback.apply(null, arguments);
			}
		});
	}
	function treeSelectGet(value) {
		if (typeof(value)=='undefined') { value = 0; }
		jQuery("#{?$container_name?}")
			.html('<select id="{?$list_name?}" name="{?$list_name?}" size=5 {?$list_extra?}><option> {?t?}Загружаю данные.{?/t?}</option></select>');
		jQuery.ajax({
			url: '{?$url?}?mode=select&soid='+Number(value),
			dataType: "xml",
			success: function(xml) {
				var html, selected;
				html = ['<option value="0"> .</option>'];
				if (xml && xml.documentElement) {
					html.push('<option value="'+ xml.documentElement.getAttribute('owner') +'"> ..</option>');
					var nodes = xml.documentElement.childNodes;
					for (var i = 0, length = nodes.length; i < length; ++i) {
						var n = nodes.item(i);
						if (n.getAttribute('type')=='2') {
							selected = '';
							if (n.getAttribute('id') == {?$list_selected?}) {
								selected = 'selected';
							}
							html.push("<option value=\""+n.getAttribute('id')+"\" "+selected+"> "+n.getAttribute('value')+"</option>");
						}
					}
				}
				jQuery("#{?$container_name?}")
					.html('<select id="{?$list_name?}" name="{?$list_name?}" size=5 {?$list_extra?} onDblClick=\"treeSelectGet(this.value);\">'+html.join('')+'</select>');
			}
		});
	}
//-->
</script>

<div id="{?$container_name?}">
</div>

<script type="text/javascript">
// <!--
	treeSelectGet('{?$list_default_value?}');
//-->
</script>
