<html>
	<head>
		<style type="text/css">
			@import "{?$skin_url?}/oldstyle.css";
			@import "{?$root_url?}/skin.css.php";
		</style>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
		<style type="text/css">
			body, html {
				margin:0;
				padding: 0;
				height: 55px;
				overflow: hidden;
				background: #BEE2E5;
			}
		</style>
		<script type="text/javascript" src="{?$root_url?}/js/c3StateButtons.js"></script>
		<script type="text/javascript">
		// <!--
			function r(cid) {
				new Image().src = '{?$root_url?}/favorites_add.php?CID='+cid;
			}
			var Class = {
				create: function() {
					return function() {
						this.initialize.apply(this, arguments);
					}
				}
			}
			Object.extend = function(destination, source) {
				for (var property in source) {
					destination[property] = source[property];
				}
				return destination;
			};
			var PagerClass = Class.create();
			PagerClass.prototype = {
				initialize: function () {
				},
				execute: function() {
					var cid = Number('{?$cid?}');
					var xmlUrl = "{?$root_url?}/course_structure_current.php?cid={?$cid?}";
					var callback = function(data) {
						if (window.ActiveXObject) {
							var xmlDocument = data;
						} else {
							var xmlDocument = (new DOMParser()).parseFromString(data.responseText, "text/xml");
						}
						$("#pagerPrev").attr({href: 'javascript:void(0);'});
						$("#pagerNext").attr({href: 'javascript:void(0);'});
						if (cid > 0) {
							$("#pager").html('0 : 0');
						}
						$.each($("//item", xmlDocument), function(i, n) {
							var module = n.getAttribute('module');
							if (!module) module = 1;
							var oid = n.getAttribute('id');
							var tests = '';
							if (oid.charAt(0) == '_') {
							    tests = '&tests='+oid;
							}
							var url = "{?$root_url?}/lib_get.php?bid="+module+"&cid={?$cid?}&oid="+n.getAttribute('id')+tests;
							var current=0, total=0;

							if((n.getAttribute('type') == 'current') && (cid > 0)) {
								current = n.getAttribute('position');
								total   = n.getAttribute('total');
								$("#pager").html(current+' : '+total);
							}

							if(n.getAttribute('type') == 'prev') {
								$("#pagerPrev").attr({href: url});
							}

							if(n.getAttribute('type') == 'next') {
								$("#pagerNext").attr({href: url});
							}
						});
					}
					if (window.ActiveXObject) {
						xmlDoc = new ActiveXObject("Microsoft.XMLDOM")
						xmlDoc.async = false
						xmlDoc.validateOnParse="false";
						xmlDoc.setProperty("SelectionLanguage", "XPath");
						xmlDoc.load(xmlUrl);
						callback(xmlDoc)
					} else {
						$.ajax({
							type: "GET",
							url: xmlUrl,
							dataType: "xml",
							success: callback
						});
					}
					//timer = setTimeout('Pager.execute()',5000);
				}
			};
			function collapseToc() {
				parent.leftFrame.expanded(!parent.leftFrame.expand);
			}
		//-->
		</script>
	</head>
	<body>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr height="55">
				<td valign="top" nowrap="nowrap">
					<a href="#" class="tri-state" onclick="collapseToc(); return false;"><img
						title="{?t?}Каталог{?/t?}" border="0" src="{?$skin_url?}/images/buttons/catalog.gif" width="74" height="55"><img
						title="{?t?}Каталог{?/t?}" border="0" src="{?$skin_url?}/images/buttons/catalog-mouseover.gif" style="display: none;"><img
						title="{?t?}Каталог{?/t?}" border="0" src="{?$skin_url?}/images/buttons/catalog-mousedown.gif" style="display: none;"></a>
					<a id="pagerPrev" class="tri-state" target="mainFrame" onClick="if (parent.leftFrame) parent.leftFrame.AllowedItemHandler();" href="javascript:void(0);"><img
						title="{?t?}Назад{?/t?}" border="0" src="{?$skin_url?}/images/buttons/prev.gif" width="74" height="55"><img
						title="{?t?}Назад{?/t?}" border="0" src="{?$skin_url?}/images/buttons/prev-mouseover.gif" style="display: none;"><img
						title="{?t?}Назад{?/t?}" border="0" src="{?$skin_url?}/images/buttons/prev-mousedown.gif" style="display: none;"></a>
				</td>
				<td valign="middle" nowrap="nowrap"><span id="pager"></span></td>
				<td width="100%" valign="top">
					<a id="pagerNext" class="tri-state" target="mainFrame" onClick="if (parent.leftFrame) parent.leftFrame.AllowedItemHandler();" href="javascript:void(0);"><img
						title="{?t?}Вперёд{?/t?}" border="0" src="{?$skin_url?}/images/buttons/forward.gif" width="74" height="55"><img
						title="{?t?}Вперёд{?/t?}" border="0" src="{?$skin_url?}/images/buttons/forward-mouseover.gif" style="display: none;"><img
						title="{?t?}Вперёд{?/t?}" border="0" src="{?$skin_url?}/images/buttons/forward-mousedown.gif" style="display: none;"></a>
				</td>
				{?if !$disabled_favorites?}
				<td align="right" valign="top"><a
					href="#" class="tri-state" onclick="javascript:r('{?$cid?}');alert('{?t?}Элемент успешно добавлен в избранное{?/t?}');"><img
						title="{?t?}Добавить в избранное{?/t?}" border="0" src="{?$skin_url?}/images/buttons/bookmarks.gif" width="74" height="55"><img
						title="{?t?}Добавить в избранное{?/t?}" border="0" src="{?$skin_url?}/images/buttons/bookmarks-mouseover.gif" style="display: none;"><img
						title="{?t?}Добавить в избранное{?/t?}" border="0" src="{?$skin_url?}/images/buttons/bookmarks-mousedown.gif" style="display: none;"></a></td>
				{?/if?}
			</tr>
		</table>
		<script type="text/javascript">
		// <!--
			var Pager = new PagerClass();
			Pager.execute();
			load3StateButtons();
		//-->
		</script>
	</body>
</html>
