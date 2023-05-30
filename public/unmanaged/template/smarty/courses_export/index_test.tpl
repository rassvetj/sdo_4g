<html>
    <head>
        <script>
            <!--
				function PageQuery(q) {
				    if(q.length > 1) this.q = q.substring(1, q.length);
				    else this.q = null;
				    this.keyValuePairs = new Array();
				    if(q) {
				        for(var i=0; i < this.q.split("&").length; i++) {
				            this.keyValuePairs[i] = this.q.split("&")[i];
				        }
				    }

				    this.getKeyValuePairs = function() { return this.keyValuePairs; }
				    this.getValue = function(s) {
				        for(var j=0; j < this.keyValuePairs.length; j++) {
				            if(this.keyValuePairs[j].split("=")[0] == s)
				            return this.keyValuePairs[j].split("=")[1];
				        }
				        return '0';
				    }
				    this.getParameters = function() {
				        var a = new Array(this.getLength());
				        for(var j=0; j < this.keyValuePairs.length; j++) {
				            a[j] = this.keyValuePairs[j].split("=")[0];
				        }
				        return a;
				    }
				    this.getLength = function() { return this.keyValuePairs.length; }
				}

				function queryString(key){
				    var page = new PageQuery(window.location.search);
				    return unescape(page.getValue(key));
				}

                function sessionTrackerObj() {
                    var startTime = (new Date()).getTime()
                    var internalData = ''
                    var userId = 0;
                    this.startSessionAction = function(type,id) {
                        if (internalData != '')
                            this.endSessionAction();
                        internalData += '<action type="'+ type +'" DB_ID="'+ id +'" start_time="'+ (new Date()).getTime() +'">'
                    }
                    this.exit = function() {
                        try {
                            var oIframe = document.getElementById("mainFrame");
                            var oDoc = (oIframe.contentWindow || oIframe.contentDocument);
                            if (oDoc.testData['normal'].ids.length != 0 && !oDoc.testFinished)
                                oDoc.saveData();
                        } catch (error) {
                        }
                        if (internalData != '')
                            this.endSessionAction();
                        var endTime = (new Date()).getTime()

                        var path = unescape(document.location.pathname.substring(1,document.location.pathname.length)).replace(/\//g,'\\');
                        var aPath = path.split('\\');
                        path = '';
                        for (var i = 0;i < aPath.length - 1 - 2;i++) {
                            path += aPath[i] + '\\'
                        }
                        var fileName = (aPath[aPath.length - 1].split('.'))[0]
                        fileName = path + "data\\" + fileName + '_' + endTime.toString() + ".xml"
                        try {
                            var fso = new ActiveXObject("Scripting.FileSystemObject");
                            var newFileObject = fso.CreateTextFile(fileName, true);
                            internalData = '<?xml version="1.0" encoding="WINDOWS-1251"?><session start_time="'+ startTime +'" end_time="'+ endTime +'" user_id="'+ userId +'" schedule_id="'+queryString('sheid')+'" course_id="{?$data.course_id?}">' + internalData + '</session>';
                            newFileObject.writeLine(internalData)
                            newFileObject.Close()
                            delete fso;
                        } catch (error) {
                            alert('{?t?}Произошла ошибка при сохранении файла отч{?/t?}ё{?t?}та{?/t?}')
                        }
                    }
                    this.setActionData = function(_xml) {
                        internalData += _xml
                    }
                    this.endSessionAction = function() {
                        internalData += '</action>'
                    }
                    this.setUserData = function(data) {
                        userId = data
                    }
                }
                var sessionTracker = new sessionTrackerObj()
                function followLink(url,type,db_id) {
                    if (url.charAt(0) == '/')
                        url = url.substring(1,url.length)
                    aUrl = url.split('/')
                    if (typeof id == "undefined")
                        id = "";
                    var path0 = '';
                    for (var i = 0;i < aUrl.length - 1;i++) {
                        path0 += aUrl[i] + '/';
                    }
                    switch (type) {
                        case 'lesson':
                        case 'test':
                            var path = path0 + "index.htm?id="+ encodeURIComponent(db_id) +"&fn="+ encodeURIComponent(aUrl[aUrl.length - 1]) +"&type="+ type;
                            break;
                        default:
                            path = path0 + aUrl[aUrl.length -1];
                            break;
                    }
                    document.getElementById("mainFrame").src = path;
                    document.getElementById("mainFrame").onreadystatechange = function() {
                        if (this.readyState == 4 || this.readyState == 'complete') {
                            sessionTracker.startSessionAction(type,db_id);
                        }
                    }
                    return false;
                }
                function loadUserData() {
                    var script = document.createElement('script');
                    script.src = "../../common/user.js";
                    script.type = 'text/javascript';
                    script.defer = true;

                    var head = document.getElementsByTagName('head').item(0);
                    script.id = "userDataScript";
                    var old = document.getElementById("userDataScript");
                    if (old) head.removeChild(old);
                    head.appendChild(script);
                }
            // -->
        </script>
    </head>
<title>eLearning Server 3000</title>
<frameset cols="100" frameborder="1" border="1" framespacing="0" id="mainFrameset" name="mainFrameset" onbeforeunload="sessionTracker.exit()" onload="loadUserData(); followLink('/content/{?$data.test_id?}.xml','test', '{?$data.test_id?}');">
  <frame src="" name="mainFrame" id="mainFrame">
</frameset>
<noframes><body>
</body></noframes>
</html>