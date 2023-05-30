<script type="text/javascript" language="Javascript">
<!--

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


EUDetection = {
    BrowserDetect: {
        detect: function() {
            var browser = this.searchString(this.dataBrowser) || "EU_UNKNOWN_BROWSER";
            var ver = new version([0,0,0]);
            ver = this.searchVersion(navigator.userAgent)
                || this.searchVersion(navigator.appVersion)
                || "EU_UNKNOWN_VERSION";
            return {
                name: browser.toLowerCase(),
                appName: browser,
                version: ver
            };
        },
        searchString: function(data) {
            for (var i=0;i<data.length;i++) {
                var dataString = data[i].string;
                var dataProp = data[i].prop;
                this.versionSearchString = data[i].versionSearch || data[i].identity;
                if (dataString) {
                    if (dataString.indexOf(data[i].subString) != -1)
                        return data[i].identity;
                }
                else if (dataProp)
                    return data[i].identity;
            }
        },
        searchVersion: function (dataString) {
            var index = dataString.indexOf(this.versionSearchString);
            if (index == -1) return new version([0,0,0]);
            return new version(dataString.substring(index+this.versionSearchString.length+1).split('.'));
        },
        dataBrowser: [{
                string: navigator.vendor,
                subString: "Apple",
                identity: "Safari"
            },{
                prop: window.opera,
                identity: "Opera"
            },{
                string: navigator.vendor,
                subString: "Konqueror",
                identity: "Konqueror"
            },{
                string: navigator.userAgent,
                subString: "Firefox",
                identity: "Firefox"
            },{
                string: navigator.userAgent,
                subString: "SeaMonkey",
                identity: "SeaMonkey"
            },{
                string: navigator.vendor,
                subString: "Camino",
                identity: "Camino"
            },{// for newer Netscapes (6+)
                string: navigator.userAgent,
                subString: "Netscape",
                identity: "Netscape"
            },{
                string: navigator.userAgent,
                subString: "MSIE",
                identity: "Explorer",
                versionSearch: "MSIE"
            },{
                string: navigator.userAgent,
                subString: "Gecko",
                identity: "Mozilla",
                versionSearch: "rv"
            }]
    },
    MSXMLVersion: function(){
        var MSXMLVersion = new version([0,0,0]);
        var progIDs = [{
                ID:      'Msxml2.DOMDocument.6.0',
                version: [6,0,0]
            },{
                ID:      'Msxml2.DOMDocument.3.0',
                version: [3,0,0]
            },{
                ID:      'Msxml2.DOMDocument.4.0',
                version: [4,0,0]
            },{
                ID:      'Msxml2.DOMDocument.5.0',
                version: [5,0,0]
            }];
        var axo;
        for (var i = 0; i < progIDs.length; ++i) {
            try {
                axo = new ActiveXObject(progIDs[i].ID);
                return new version(progIDs[i].version);
            } catch (e) {}
        }
        return MSXMLVersion;
    },
    RunEXEVersion: function() {
        var RunEXEVersion = new version([0,0,0]);
        var progIDs = [{
                ID: 'RUNEXE.RunEXECtrl.1',
                version: [1,0,0]
            }];
        var axo;
        for (var i = 0; i < progIDs.length; ++i) {
            try {
                axo = new ActiveXObject(progIDs[i].ID);
                return new version(progIDs[i].version);
            } catch (e) {}
        }
        return RunEXEVersion;
    },
    /** This code has been taken from SWFObject Library
     * ========================================================================================
     * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
     *
     * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:
     * http://www.opensource.org/licenses/mit-license.php
     * ========================================================================================
     */
    adobeFlashPlayerVersion: function(){
        var playerVersion = new version([0,0,0]);
        if (navigator.plugins && navigator.mimeTypes.length) {
            var x = navigator.plugins["Shockwave Flash"];
            if (x && x.description) {
                playerVersion = new version(x.description.replace(/([a-zA-Z]|\s)+/, "").replace(/(\s+r|\s+b[0-9]+)/, ".").split("."));
            }
        } else if (navigator.userAgent && navigator.userAgent.indexOf("Windows CE") >= 0) { // if Windows CE
            var axo = 1;
            var counter = 3;
            while(axo) {
                try {
                    counter++;
                    axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+ counter);
                    // document.write("player v: "+ counter);
                    playerVersion = new deconcept.PlayerVersion([counter,0,0]);
                } catch (e) {
                    axo = null;
                }
            }
        } else { // Win IE (non mobile)
            // do minor version lookup in IE, but avoid fp6 crashing issues
            // see http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
            try {
                var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
            } catch(e) {
                try {
                    var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
                    playerVersion = new version([6,0,21]);
                    axo.AllowScriptAccess = "always"; // error if player version < 6.0.47 (thanks to Michael Williams @ Adobe for this code)
                } catch(e) {
                    if (playerVersion.major == 6) {
                        return playerVersion;
                    }
                }
                try {
                    axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
                } catch(e) {}
            }
            if (axo != null) {
                playerVersion = new version(axo.GetVariable("$version").split(" ")[1].split(","));
            }
        }
        return playerVersion;
    },
    _cache: {},
    _checkBrowser: function(){
        if (!this._cache.require.browser[this._cache.available.browser.name])
            return false;
        if (this._cache.available.browser.name == 'explorer')
            return (
                   this._cache.available.browser.version.compare(this._cache.require.browser.explorer) >= 0
                && this._cache.available.msxml.compare(this._cache.require.msxml) >= 0
            );
        return (this._cache.available.browser.version.compare(
            this._cache.require.browser[this._cache.available.browser.name]
        ) >= 0);
    },
    _check: function(){
        if (!this._cache.require) {
            this._cache.require = {
                browser: {
                    explorer: new version([5,5,0]),
                    opera: new version([9,0,0]),
                    safari: new version([2,0,0]),
                    firefox: new version([1,5,0]),
                    camino: new version([1,0,0]),
                    seamonkey: new version([1,0,0]),
                    mozilla: new version([1,8,0]),
                    netscape: new version([7,0,0])
                },
                fp:    new version([9,0,0]),
                msxml: new version([3,0,0]),
                runexe: new version([1,0,0])
            };
        }
        if (!this._cache.available) {
            var cObj = this;
            this._cache.available = {
                fp:    cObj.adobeFlashPlayerVersion(),
                msxml: cObj.MSXMLVersion(),
                runexe: cObj.RunEXEVersion()
            };
            this._cache.available.browser = this.BrowserDetect.detect();
        }
        if (typeof this._cache.passed == 'undefined') {
            this._cache.browserPassed = this._checkBrowser();
            this._cache.passed = (
                   this._cache.browserPassed
                && (this._cache.available.fp.compare(this._cache.require.fp) >= 0)
            );
            this._cache.iePassed = (
                   this._cache.passed
                && this._cache.available.browser.name == 'explorer'
            );
        }
    },
    ieFeedback: false,
    check: function(){
        this._check();
        this.ieFeedback = false;
        return this._cache.passed
    },
    courseCheck: function(){
        this._check();
        this.ieFeedback = true;
        return this._cache.iePassed;
    },
    checkWithFeedback: function(passed){
        var testTableCell = function(data){
            return '<tr class="'+(data.passed ? 'passed' : 'failed')+'"><td class="app-name">'+ data.appName +'</td><td class="app-version">'+(data.version == '0.0.0' ? '<span class="failed">{?t?}не установлен{?/t?}</span>' : data.version)+'</td><td class="app-require">'+data.require+'</td><td class="app-passed">'+(data.passed ? '<span class="passed">{?t?}Тест пройден{?/t?}</span>' : '<span class="failed">{?t?}Тест НЕ пройден{?/t?}</span>')+'</td></tr>';
        }
        this._check();
        var feedback = '';
        if (!this._cache.require.browser[this._cache.available.browser.name])
            return '<div class="EUDetectionCheck"><div class="failed"><h1>{?t?}Ваш браузер не поддерживается{?/t?}</h1></div>';
        if ((this.ieFeedback && this._cache.iePassed) || (!this.ieFeedback && this._cache.passed)) {
            feedback = '<div class="EUDetectionCheck"><h1 class="passed">{?t?}Ваша система содержит все необходимые компоненты{?/t?}</h1>';
        } else {
            feedback = '<div class="EUDetectionCheck"><div class="failed"><h1>{?t?}Ваша система не содержит всех необходимых компонент для корректного отображения содержимого{?/t?}</h1>';
        }
        var browser = {
            passed:  null,
            version: null,
            require: null,
            appName: null
        };
        var fp = {
            passed:  null,
            version: null,
            require: null,
            appName: 'Adobe Flash Player'
        };
        var msxml = {
            passed:  null,
            version: null,
            require: null,
            appName: 'MSXML'
        };
        var runexe = {
            passed:  null,
            version: null,
            require: null,
            install: '{?$sitepath?}help/runexe/setup.exe',
            appName: 'RunEXE'
        };
        browser.passed = this._cache.browserPassed;
        fp.passed = (this._cache.available.fp.compare(this._cache.require.fp) >= 0);
        msxml.passed = (this._cache.available.msxml.compare(this._cache.require.msxml) >= 0);
        runexe.passed = (this._cache.available.runexe.compare(this._cache.require.runexe) >= 0);

        browser.version = this._cache.available.browser.version.toString();
        fp.version = this._cache.available.fp.toString();
        msxml.version = this._cache.available.msxml.toString();
        runexe.version = this._cache.available.runexe.toString();

        browser.require = this._cache.require.browser[this._cache.available.browser.name].toString();
        fp.require = this._cache.require.fp.toString();
        msxml.require = this._cache.require.msxml.toString();
        runexe.require = this._cache.require.runexe.toString();

        browser.appName = this._cache.available.browser.appName;

        feedback += '<br /><table cellpadding="3" cellspacing="0">';
        feedback += '<tr><th class="app-name">{?t?}Название продукта{?/t?}</th><th class="app-version">{?t?}Установленная версия{?/t?}</th><th class="app-require">{?t?}Необходимая версия{?/t?}</th><th class="app-passed">{?t?}Тест{?/t?}</th></tr>';
        feedback += testTableCell(browser);
        if (this._cache.available.browser.name == 'explorer') {
            feedback += testTableCell(msxml);
            feedback += testTableCell(runexe);
        }
        feedback += testTableCell(fp);
        feedback += '</table>';
        feedback += '<br /><div class="comment"><b>{?t?}Примечание 1{?/t?}:</b> {?t?}Adobe Flash Player не обязателен для корректной работы сервера, но может понадобиться при отображении курсов.{?/t?}</div>'
        feedback += '<div class="comment"><b>{?t?}Примечание 2{?/t?}:</b> {?t?}Курсы, сделанные в eAuthor 3.1, работают только в Microsoft Internet Explorer версии 5.5 или старше.{?/t?}</div>'
        if (!runexe.passed && (this._cache.available.browser.name == 'explorer')) {
            feedback += '<div class="comment"><b>{?t?}Примечание 3{?/t?}:</b> {?t?}Установить RunEXE Вы можете {?/t?}<a href="'+runexe.install+'"><strong>{?t?}отсюда{?/t?}</strong></a>.</div>'
        }
        feedback += '</div>';
        return feedback;
    }
// Добавлена возможность проверки установленного ПО
};

//-->
</script>
<span id="EUDetectionContainer"></span>
<script type="text/javascript">

	function EUDetect() {
 	    if (document.getElementById('EUDetectionContainer')) {
		var feedback = EUDetection.checkWithFeedback();
		document.getElementById("EUDetectionContainer").innerHTML = feedback;
		}
	}

	setTimeout('EUDetect()', 1000);
</script>
<noscript>
<div class="EUDetectionCheck"><div class="failed">
<h1>{?t?}В браузере отключена поддержка выполнения сценариев JavaScript.{?/t?}</h1>
</div></div>
</noscript>