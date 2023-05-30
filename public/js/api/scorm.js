//
// SCORM 1.2 API Implementation
//
function SCORMapi1_2() {
    // The SCORM 1.2 data model
    var datamodel =  elsScormDatamodel
      , errorCode
      , oldCmi
      , diffs = [];

    // paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
    function SCORM_log () {
        SCORM_log.history = SCORM_log.history || [];   // store logs to an array for reference
        SCORM_log.history.push(arguments);
        if (window.console) {
            console.log( Array.prototype.slice.call(arguments) );
        }
    };
    function underscore(str) {
        return str.replace(/\./g,"__");
    }
    
    var ObjProto = Object.prototype
      , toString = ObjProto.toString;
    
    var SCORM_isArray = Array.isArray || function (obj) {
        return toString.call(obj) === '[object Array]';
    };
    function SCORM_ElementCheckFormat (elementmodel, value) {
        var format = datamodel[elementmodel].format
          , expression
          , isValid = false
          , i;

        SCORM_isArray(format) || (format = [format]);
        for (i = 0; i < format.length; ++i) {
            expression = new RegExp(format[i], 'm');
            isValid = isValid || expression.test(value);
        }
        return isValid;
    }
    function SCORM_ElementSet (element, value) {
        var apiElement = window
          , piece;
        
        element = element.split('.').reverse();
        
        while (element.length > 1) {
            piece = element.pop();
            SCORM_udef(apiElement[piece]) && (apiElement[piece] = {});
            apiElement = apiElement[piece];
        }
        
        apiElement[ element.pop() ] = value;
    }
    function SCORM_ElementGet (element) {
        var apiElement = window;
        
        element = element.split('.').reverse();
        
        while (element.length >= 1)
            apiElement = apiElement[element.pop()];
        
        return apiElement;
    }
    function SCORM_ElementExists (element) {
        var apiElement = window;
        
        element = element.split('.').reverse();
        
        while (element.length >= 1 && !SCORM_udef(apiElement))
            apiElement = apiElement[element.pop()];
        
        return !SCORM_udef(apiElement);
    }
    function SCORM_udef (v) {
        return typeof v == 'undefined';
    }
    
    //
    // Datamodel inizialization
    //
    if (SCORM_udef(window.cmi)) {
        window.cmi = {};
    }
    if (SCORM_udef(window.nav)) {
        window.nav = {};
    }

    for (element in datamodel) {
        if (element.match(/\.n\./) == null) {
            SCORM_ElementSet(element, SCORM_udef(datamodel[element].defaultvalue) ? '' : datamodel[element].defaultvalue);
        }
    }

    if (cmi.core.lesson_status == '') {
        cmi.core.lesson_status = 'not attempted';
    }
    
    //
    // API Methods definition
    //
    var Initialized = false
      , Terminated  = false;
    
    function LMSInitialize (param) {
        errorCode = "0";
        if (param == "") {
            if (!(Initialized || Terminated)) {
                Initialized = true;
                errorCode = "0";
                
                oldCmi = DeepClone(cmi);
                
                if (elsScormDebug) {
                    SCORM_log("LMSInitialize", param, '', errorCode);
                }
                return "true";
            } else {
                errorCode = "101";
            }
        } else {
            errorCode = "201";
        }
        if (elsScormDebug) {
            SCORM_log("LMSInitialize", param, '', errorCode);
        }
        return "false";
    }
    
    function LMSFinish (param) {
        errorCode = "0";
        if (param == "") {
            if (Initialized && !Terminated) {
                Initialized = false;
                Terminated  = true;
                StoreData(cmi, true, 'sync');
                if (nav.event != '') {
                    if (nav.event == 'continue') {
                        setTimeout('top.nextSCO();',500);
                    } else {
                        setTimeout('top.prevSCO();',500);
                    }
                } else { }
                if (elsScormDebug) {
                    SCORM_log("LMSFinish", param, '', errorCode);
                }
                return "true";
            } else {
                errorCode = "301";
            }
        } else {
            errorCode = "201";
        }
        if (elsScormDebug) {
            SCORM_log("LMSFinish", param, '', errorCode);
        }
        return "false";
    }
    
    function LMSGetValue (element) {
        var expression
          , elementmodel
          , parentmodel;
        
        errorCode = "0";
        if (Initialized) {
            if (element !="") {
                expression = new RegExp(CMIIndex,'g');
                elementmodel = element.replace(expression,'.n.');
                if ( !SCORM_udef(datamodel[elementmodel]) ) {
                    if ( datamodel[elementmodel].mod != 'w' ) {
                        element = element.replace(expression, "_$1.");
                        if (SCORM_ElementExists(element)) {
                            errorCode = "0";
                            if (elsScormDebug) {
                                SCORM_log("LMSGetValue", element, SCORM_ElementGet(element), errorCode);
                            }

                            return SCORM_ElementGet(element);
                        } else {
                            errorCode = "0"; // Need to check if it is the right errorCode
                        }
                    } else {
                        errorCode = datamodel[elementmodel].readerror;
                    }
                } else {
                    if (/\._children$/.test(elementmodel)) {
                        parentmodel = elementmodel.replace(/\._children$/, '');
                        if ( !SCORM_udef(datamodel[parentmodel]) ) {
                            errorCode = "202";
                        } else {
                            errorCode = "201";
                        }
                    } else if (/\._count$/.test(elementmodel)) {
                        parentmodel = elementmodel.replace(/\._count$/, '');
                        if ( !SCORM_udef(datamodel[parentmodel]) ) {
                            errorCode = "203";
                        } else {
                            errorCode = "201";
                        }
                    } else {
                        errorCode = "201";
                    }
                }
            } else {
                errorCode = "201";
            }
        } else {
            errorCode = "301";
        }
        if (elsScormDebug) {
            SCORM_log("LMSGetValue", element, '', errorCode);
        }
        return "";
    }
    
    function LMSSetValue (element,value) {
        var elementmodel
          , subelement
          , elementIndexes
          , i
          , elementIndex
          , range;
        
        errorCode = "0";
        if (Initialized) {
            if (element != "") {
                expression = new RegExp(CMIIndex,'g');
                elementmodel = element.replace(expression,'.n.');
                if ( !SCORM_udef(datamodel[elementmodel]) ) {
                    if ( datamodel[elementmodel].mod != 'r' ) {
                        value = value+'';
                        value = value.replace(/^\s/g, '').replace(/\s+$/g, '');
                        
                        if (SCORM_ElementCheckFormat(elementmodel, value)) {
                            //Create dynamic data model element
                            if (element != elementmodel) {
                                elementIndexes = element.split('.');
                                subelement = 'cmi';
                                for (i=1;i < elementIndexes.length-1;i++) {
                                    elementIndex = elementIndexes[i];
                                    if (elementIndexes[i+1].match(/^\d+$/)) {
                                        if (!SCORM_ElementExists(subelement+'.'+elementIndex)) {
                                            SCORM_ElementSet(subelement+'.'+elementIndex, {});
                                            SCORM_ElementSet(subelement+'.'+elementIndex+'._count', 0);
                                        }
                                        if (parseInt(elementIndexes[i+1], 10) == SCORM_ElementGet(subelement+'.'+elementIndex+'._count')) {
                                            SCORM_ElementSet(
                                                subelement+'.'+elementIndex+'._count'
                                              , SCORM_ElementGet(subelement+'.'+elementIndex+'._count') + 1
                                            );
                                        }
                                        if (parseInt(elementIndexes[i+1], 10) > SCORM_ElementGet(subelement+'.'+elementIndex+'._count')) {
                                            errorCode = "201";
                                        }
                                        subelement = subelement.concat('.'+elementIndex+'_'+elementIndexes[i+1]);
                                        i++;
                                    } else {
                                        subelement = subelement.concat('.'+elementIndex);
                                    }
                                    if (!SCORM_ElementExists(subelement)) {
                                        SCORM_ElementSet(subelement, {});
                                        if (/^cmi\.objectives/.test(subelement)) {
                                            SCORM_ElementSet(subelement+'.score', {});
                                            SCORM_ElementSet(subelement+'.score._children', score_children);
                                            SCORM_ElementSet(subelement+'.score.raw', "");
                                            SCORM_ElementSet(subelement+'.score.min', "");
                                            SCORM_ElementSet(subelement+'.score.max', "");
                                        }
                                        if (/^cmi\.interactions/.test(subelement)) {
                                            SCORM_ElementSet(subelement+'.objectives', {});
                                            SCORM_ElementSet(subelement+'.objectives._count', 0);
                                            SCORM_ElementSet(subelement+'.correct_responses', {});
                                            SCORM_ElementSet(subelement+'.correct_responses._count', 0);
                                        }
                                    }
                                }
                                element = subelement.concat('.'+elementIndexes[elementIndexes.length-1]);
                            }
                            //Store data
                            if (errorCode == "0") {
                                if ( !SCORM_udef(datamodel[elementmodel].range) ) {
                                    range = datamodel[elementmodel].range;
                                    if ( (typeof datamodel[elementmodel].rangeF == 'function' && datamodel[elementmodel].rangeF(value))
                                            ||  ((parseFloat(value) >= range[0]) && (parseFloat(value) <= range[1])) ) {
                                        SCORM_ElementSet(element, value);
                                        errorCode = "0";
                                        if (elsScormDebug) {
                                            SCORM_log("LMSSetValue", element, value, errorCode);
                                        }
                                        return "true";
                                    } else {
                                        errorCode = datamodel[elementmodel].writeerror;
                                    }
                                } else {
                                    if (element == 'cmi.comments') {
                                        SCORM_ElementSet(element, '' + (SCORM_ElementGet(element) || '') + value + '');
                                    } else {
                                        SCORM_ElementSet(element, '' + value + '');
                                    }
                                    errorCode = "0";
                                    if (elsScormDebug) {
                                        SCORM_log("LMSSetValue", element, value, errorCode);
                                    }
                                    return "true";
                                }
                            }
                        } else {
                            errorCode = datamodel[elementmodel].writeerror;
                        }
                    } else {
                        errorCode = datamodel[elementmodel].writeerror;
                    }
                } else {
                    errorCode = "201"
                }
            } else {
                errorCode = "201";
            }
        } else {
            errorCode = "301";
        }
        if (elsScormDebug) {
            SCORM_log("LMSSetValue", element, value, errorCode);
        }
        return "false";
    }
    
    function LMSCommit (param) {
        errorCode = "0";
        if (param == "") {
            if (Initialized) {
                StoreData(cmi, false, 'async');
                if (elsScormDebug) {
                    SCORM_log("LMSCommit", param, '', errorCode);
                }
                return "true";
            } else {
                errorCode = "301";
            }
        } else {
            errorCode = "201";
        }
        if (elsScormDebug) {
            SCORM_log("LMSCommit", param, '', errorCode);
        }
        return "false";
    }
    
    function LMSGetLastError () {
        if (elsScormDebug) {
            SCORM_log("LMSGetLastError", '', '', errorCode);
        }
        return errorCode;
    }
    
    function LMSGetErrorString (param) {
        if (param != "") {
            var errorString = {
                "0":   "No error",
                "101": "General exception",
                "201": "Invalid argument error",
                "202": "Element cannot have children",
                "203": "Element not an array - cannot have count",
                "301": "Not initialized",
                "401": "Not implemented error",
                "402": "Invalid set value, element is a keyword",
                "403": "Element is read only",
                "404": "Element is write only",
                "405": "Incorrect data type"
            };
            return errorString[param];
        } else {
           return "";
        }
    }
    
    function LMSGetDiagnostic (param) {
        if (param == "") {
            param = errorCode;
        }
        return param;
    }

    function AddTime (first, second) {
        var sFirst = first.split(":");
        var sSecond = second.split(":");
        var cFirst = sFirst[2].split(".");
        var cSecond = sSecond[2].split(".");
        var change = 0;

        FirstCents = 0;  //Cents
        if (cFirst.length > 1) {
            FirstCents = parseInt(cFirst[1],10);
        }
        SecondCents = 0;
        if (cSecond.length > 1) {
            SecondCents = parseInt(cSecond[1],10);
        }
        var cents = FirstCents + SecondCents;
        change = Math.floor(cents / 100);
        cents = cents - (change * 100);
        if (Math.floor(cents) < 10) {
            cents = "0" + cents.toString();
        }

        var secs = parseInt(cFirst[0],10)+parseInt(cSecond[0],10)+change;  //Seconds
        change = Math.floor(secs / 60);
        secs = secs - (change * 60);
        if (Math.floor(secs) < 10) {
            secs = "0" + secs.toString();
        }

        mins = parseInt(sFirst[1],10)+parseInt(sSecond[1],10)+change;   //Minutes
        change = Math.floor(mins / 60);
        mins = mins - (change * 60);
        if (mins < 10) {
            mins = "0" + mins.toString();
        }

        hours = parseInt(sFirst[0],10)+parseInt(sSecond[0],10)+change;  //Hours
        if (hours < 10) {
            hours = "0" + hours.toString();
        }

        if (cents != '0') {
            return hours + ":" + mins + ":" + secs + '.' + cents;
        } else {
            return hours + ":" + mins + ":" + secs;
        }
    }

    function TotalTime() {
        return AddTime(cmi.core.total_time, cmi.core.session_time);
    }

    function DeepClone(data) {
        var clone = {};
        for (property in data) {
            if (typeof data[property] == 'object')
                clone[property] = DeepClone(data[property]);
            else
                clone[property] = data[property];
        }
        return clone;
    }
    
    function DeepDiff(before, after) {
        var diff = {};
        for (property in before) {
            if (typeof before[property] == 'object') {
                diff[property] = DeepDiff(before[property], after[property]);
            } else if (before[property] != after[property]) {
                diff[property] = after[property];
            }
        }
        for (property in after) {
            if (typeof before[property] == 'undefined') {
                diff[property] = after[property];
            }
        }
        return diff;
    }
    
    function EncodeData(data, parent) {
        var datastring = []
          , element
          , expression
          , elementmodel;
        for (property in data) {
            if (typeof data[property] == 'object') {
                datastring = datastring.concat(EncodeData(data[property], parent+'.'+property));
            } else {
                element = parent+'.'+property;
                expression = new RegExp(CMIIndex,'g');
                elementmodel = element.replace(expression,'.n.');
                if ( !SCORM_udef(datamodel[elementmodel]) && datamodel[elementmodel].mod != 'r' ) {
                    datastring.push(underscore(element)+'='+encodeURIComponent(data[property]));
                }
            }
        }
        return datastring;
    }
    
    function MergeDiff(first, second) {
        var merge = DeepClone(first);
        for (property in second) {
            if (typeof second[property] == 'object') {
                merge[property] = MergeDiff(merge[property], second[property]);
            } else {
                merge[property] = second[property];
            }
        }
        return merge;
    }
    
    function MergeDiffs(diffs) {
        var merge = {};
        for (var i = 0; i < diffs.length; i++) {
            merge = MergeDiff(merge, diffs[i]);
        }
        return merge;
    }

    function StoreData(data, storetotaltime, requestType) {
        var datastring;
        if (storetotaltime) {
            if (cmi.core.lesson_status == 'not attempted') {
                cmi.core.lesson_status = 'completed';
            }
            if (cmi.core.lesson_mode == 'normal') {
                if (cmi.core.credit == 'credit') {
                    if (cmi.core.lesson_status == 'completed') {
                        if (cmi.student_data.mastery_score != '') {
                            if (parseFloat(cmi.core.score.raw) >= parseFloat(cmi.student_data.mastery_score)) {
                                cmi.core.lesson_status = 'passed';
                            } else {
                                cmi.core.lesson_status = 'failed';
                            }
                        }
                    }
                }
            }
            if (cmi.core.lesson_mode == 'browse') {
                if (datamodel['cmi.core.lesson_status'].defaultvalue == '') {
                    cmi.core.lesson_status = 'browsed';
                }
            }
            cmi.core.total_time = TotalTime();
        }

        diffs.push(DeepDiff(oldCmi, cmi));

        oldCmi = DeepClone(cmi);

        ScormQueuePush(diffs[diffs.length - 1], requestType);

        if (elsScormDebug) {
            SCORM_log("StoreData", data, EncodeData(diffs[diffs.length - 1], 'cmi').join('&'), errorCode);
        }
    }
    
    function ScormQueuePush (diff, type) {
        ScormQueueNext.queue.push(diff);
        return ScormQueueNext(type);
    }
    function ScormQueueNext (type) {
        var dataStr
          , failed = false;
        if ( ScormQueueNext.queue.length == 0 || (type == 'async' && ScormQueueNext.xhr) ) return;

        type != 'async' && ScormQueueNext.xhr && ScormQueueNext.xhr.abort();
        ScormQueueNext.queue = [ MergeDiffs(ScormQueueNext.queue) ]

        dataStr  = EncodeData(ScormQueueNext.queue[0], 'cmi').join('&');
        if (elsScormDebug) {
            SCORM_log("ScormQueueNext", dataStr);
        }
        ScormQueueNext.xhr = $.ajax({
            url: ScormQueueNext.url,
            type: 'POST',
            data: dataStr,
            async: type == 'async',
            dataType: 'text',
            complete: function () { ScormQueueNext.xhr = null; }
        }).done(function () {
            ScormQueueNext.queue.shift();
            setTimeout(function () { ScormQueueNext('async'); }, 1);
        }).fail(function () {
            setTimeout(function () {
                if (ScormQueueNext.queue.length > 1) {
                    ScormQueueNext('async');
                }
            }, 1);
            failed = true;
        });
        return failed ? "false" : "true";
    }
    ScormQueueNext.queue = [];
    ScormQueueNext.url = elsScormRequestUrl;

    this.LMSInitialize = LMSInitialize;
    this.LMSFinish = LMSFinish;
    this.LMSGetValue = LMSGetValue;
    this.LMSSetValue = LMSSetValue;
    this.LMSCommit = LMSCommit;
    this.LMSGetLastError = LMSGetLastError;
    this.LMSGetErrorString = LMSGetErrorString;
    this.LMSGetDiagnostic = LMSGetDiagnostic;
}