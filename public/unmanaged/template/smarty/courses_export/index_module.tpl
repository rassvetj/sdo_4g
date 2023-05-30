<html>
	<head>
		<title></title>
		<script type="text/javascript" src="../common/user.js"></script>
		<script type="text/javascript" src="../common/js/lib/prototype/prototype-latest.js"></script>
		<script type="text/javascript">
			window.Options = {
				metadataFile:   'metadata_{?$data.course_id?}.html',
				navigationFile: 'navigation_{?$data.course_id?}.html',
				thisFile:       'index_{?$data.course_id?}.html',
				courseId:       '{?$data.course_id?}'
			};
		</script>
		<script type="text/javascript">
			var eLearning_server_metadata = {
				course_options: {
					use_internal_navigation: Boolean('{?$enable_course_navigation?}')
				}
			};
		</script>
		<script>
			function f_isFrameAccessible(frame) {
				try {
					frame.window;
					frame.window.document;
				} catch(error) { return false; }
				return true;
			}

			var QParams = document.location.toString().toQueryParams();
			if (QParams['type'] == 'module' && QParams['id'] != '0') {
				window.eLSChildOptions = {
					treeview: { extractSubtree: 'org_' + QParams['id'] }
				};
			}

			var SessionTracker = Class.create({
				initialize: function(filePrefix) {
					this.__valid = false;
					if (SessionTracker._hasInstance) { return; }
					SessionTracker._hasInstance = true;

					var OpenFilePath = decodeURI(document.location.pathname)
						.replace(/\//g,'\\')
						.split('\\')
						.without('');
					var savePath = OpenFilePath.slice(0, OpenFilePath.length - 2).join('\\') + '\\';
					var filePrefix = OpenFilePath
						.last()
						.split('.')
						.first();
					var fileName = savePath + "data\\" + filePrefix + '_' + (new Date()).getTime().toString() + ".xml"
					try {
						this.__fso = new ActiveXObject("Scripting.FileSystemObject");
						this.__file = this.__fso.CreateTextFile(fileName, true);
						this.__valid = true;
					} catch (e) { }
					var This = this;
					This.__sessionStart()
					Event.observe(window, 'unload', function() {
						This.__sessionTerminate.call(This);
					});
				},
				__sessionStart: function() {
					if (!this.__valid || this.__sessionStarted) { return; }
					this.__actionsData = [];
					this.__sessionStarted = true;
					this.__sessionData = $H({
						start_time:  (new Date()).getTime(),
						user_id:     window.user,
						module_id:   QParams['type'] == 'test' ? '0' : (QParams['id'] || 0),
						course_id:   window.Options.courseId,
						schedule_id: (QParams['sheid'] || 0)
					});
				},
				__sessionTerminate: function() {
					if (!this.__valid || !this.__sessionStarted) { return; }
					this.__sessionData.set('end_time', (new Date()).getTime());
					var StringData = [];
					var TagString = '';

					StringData.push('<?xml version="1.0" encoding="WINDOWS-1251"?>');

					this.__sessionData.each(function(pair) {
						TagString += ' ' + pair.key + '="' + pair.value + '"';
					});
					StringData.push('<session' + TagString + '>');

					for (var i = 0, length = this.__actionsData.length; i < length; ++i) {
						TagString = '';
						this.__actionsData[i].each(function(pair) {
							if (pair.key.startsWith('!')) {
								return;
							}
							TagString += ' ' + pair.key + '="' + pair.value + '"';
						});
						StringData.push('	<action' + TagString + '>'+ (this.__actionsData[i].get('!xmlContent') ? this.__actionsData[i].get('!xmlContent') : '') +'</action>');
					}

					StringData.push('</session>');

					this.__file.writeLine(StringData.join('\n'));
					this.__file.Close();
				},
				action: function(DOMElement) {
					if (!this.__valid || !this.__sessionStarted) { return; }
					var ModuleType = DOMElement.getAttribute('type');
					var attr = ModuleType ? DOMElement.getAttribute(ModuleType + '_id') : '0';
					var ModuleId = attr ? attr	 : '0';
					var lastAction = this.__actionsData.last();
					if (lastAction && lastAction.get('type') == ModuleType && lastAction.get('DB_ID') == ModuleId) {
						lastAction.set('start_time', (new Date()).getTime());
					} else {
						this.__actionsData.push($H({
							type: ModuleType,
							DB_ID: ModuleId,
							start_time: (new Date()).getTime()
						}));
					}
				},
				actionXmlData: function(DOMElement, data) {
					if (!this.__valid || !this.__sessionStarted) { return; }
					var ModuleType = DOMElement.getAttribute('type');
					var attr = ModuleType ? DOMElement.getAttribute(ModuleType + '_id') : '0';
					var ModuleId = attr ? attr	 : '0';
					var lastAction = this.__actionsData.last();
					if (lastAction && lastAction.get('type') == ModuleType && lastAction.get('DB_ID') == ModuleId) {
						lastAction.set('!xmlContent', data);
					}
				},
				actionSetData: function(DOMElement, data) {
					if (!this.__valid || !this.__sessionStarted) { return; }
					var ModuleType = DOMElement.getAttribute('type');
					var attr = ModuleType ? DOMElement.getAttribute(ModuleType + '_id') : '0';
					var ModuleId = attr ? attr	 : '0';
					var lastAction = this.__actionsData.last();
					if (lastAction && lastAction.get('type') == ModuleType && lastAction.get('DB_ID') == ModuleId) {
						for (var i in data) {
							lastAction.set(i, data[i]);
						}
					}
				}
			});
			$P(document).observe('dom:loaded', function() {
				var mainFrame = window.frames.mainFrame;

				if (QParams['type'] != 'test') {
					(function() {
						var leftFrame = window.frames.leftFrame;
						if (leftFrame && leftFrame.window && leftFrame.window.eLS && leftFrame.window.eLS.isDOMLoaded) {
							var treeViews = leftFrame.window.document.getElementsByClassName('tree-view');
							$A(treeViews).each(function(val) {
								$P(val).observe('click', function(event) {
									var element = event.element();
									if (!element) { return; }
									element = element.up('li');
									if (!element || element.down('li')) { return; }
									if (!element) { return; }
									window.tracker.action.call(window.tracker, element);
								});
							});
							return;
						}
						setTimeout(arguments.callee, 10);
					})();
					mainFrame.location.href = Options.metadataFile;
					return;
				}

				(function() {
					var leftFrame = window.frames.leftFrame;
					if (leftFrame && leftFrame.window && leftFrame.window.eLS) {
						var eLS = leftFrame.window.eLS;
						if (eLS.utils && eLS.utils.frame) {
							eLS.utils.frame.collapse();
							eLS.utils.frame.disable();
							return;
						}
					}
					setTimeout(arguments.callee, 10);
				})();

				mainFrame.location.href = '../tests/index-test.htm?' + Object.toQueryString({ id: QParams['id'], 'launched-by-tid': true });
			});

			window.tracker = new SessionTracker();
		</script>
	</head>
	<frameset cols="300,*" frameborder="0" border="0" framespacing="0" id="mainFrameset" name="mainFrameset">
		<frame src="navigation_{?$data.course_id?}.html" name="leftFrame" noresize="noresize" scrolling="yes">
		<frame src="about:blank" name="mainFrame" id="mainFrame" onload="$P(document).fire('main-frame:loaded');">
	</frameset>
</html>