<html>
<head>
<title>{?t?}Прогресс выполнения{?/t?}</title>
<meta http-equiv="Content-Type" content="text/html; charset={?$encoding?}" />
<link rel="stylesheet" type="text/css" href="{?$sitepath?}template/smarty/skins/default/stylesheets/screen.css" />
<link rel="stylesheet" type="text/css" href="{?$sitepath?}skin.css.php" />
<script type="text/javascript" src="{?$sitepath?}js/jquery.js"></script>
<script type="text/javascript" language="JavaScript">
<!--
$.loadCounter = function(url, callback) {
        var rnd = Math.random();
        url = url + '?rnd='+Number(rnd);                
        $.ajax({
            type: "GET",
            url: url,
            dataType: "text",
            success: callback,
            error: function() {
                errorCounter++;
                timer = setTimeout("updateProcess()",1000);
                if (errorCounter > 1) {
                    var progress = 100;
                    $("#progress1").html("<table width=80% border=0 cellspacing=0 cellpadding = 2><tr height=10><td class=preview-color-1 colspan=2 width="+Number(progress)+"% id='progressBar'></td></tr></table>");
                    if (!upload) {
                        $("#progress2").html("<table width=80% border=0 cellspacing=0 cellpadding = 2><tr height=10><td class=preview-color-1 id='progressBar' width="+progress+"% colspan=2></td></tr><tr><td align=center valign=middle colspan=2>"+progress+"%</td></tr></table>");
                    }
                    clearTimeout(timer);
                    clearTimeout(timerFlow);
                    if (upload) clearTimeout(timerUpload);
                    window.close();
                }
            }
        })
}

updateProcess = function() {
    $.loadCounter('{?$url?}', function(xml) {
            var line = new String(xml.responseText);
            var parts = line.split('|');
            if (parts.length == 2) {
                var action   = new String(parts[0]);
                var progress = new Number(parts[1]);
            } else {
                var progress = -1;
            }
            if (progress < 0) {
                progress = -1;
            }
            if (progress > 100) progress = 100;
            if (upload) {
                    $("#action2").html(action);
                    $("#progress2").html("");                
            } else {
                if (progress == 100) {
                    $("#action2").html(action);
                    $("#progress2").html("<table width=80% border=0 cellspacing=0 cellpadding = 2><tr height=10><td class=preview-color-1 colspan=2 id='progressBar' width="+progress+"%></td></tr><tr><td align=center valign=middle colspan=2>"+progress+"%</td></tr></table>");
                } else {
                    $("#action2").html(action);
                    if (progress == -1) {
                        $("#progress1").html("<table width=80% border=0 cellspacing=0 cellpadding = 2><tr height=10><td class=preview-color-1 colspan=2 width=100% id='progressBar'></td></tr></table>");
                        $("#progress2").html("<table width=80% border=0 cellspacing=0 cellpadding = 2><tr height=10><td class=preview-color-1 colspan=2 id='progressBar' width="+progress+"%></td></tr><tr><td align=center valign=middle colspan=2>100%</td></tr></table>");
                    } else {
                        $("#progress2").html("<table width=80% border=0 cellspacing=0 cellpadding = 2><tr height=10><td class=preview-color-1 id='progressBar' width="+progress+"%></td><td style='background: #CCCCCC' width="+Number(100-progress)+"%></td></tr><tr><td align=center valign=middle colspan=2>"+progress+"%</td></tr></table>");
                    }
                }
            }
            if (progress < 0) {
                clearTimeout(timer);
                clearTimeout(timerFlow);
                if (upload) clearTimeout(timerUpload);
                window.close();
            } else {
                timer = setTimeout("updateProcess()",1000);
            }
    });    
}

updateFlow = function() {
    if (flowCount > 100) flowCount = 0;
    if (flowCount == 100) {
        $("#progress1").html("<table width=80% border=0 cellspacing=0><tr height=10><td class=preview-color-1 colspan=2 width="+Number(flowCount)+"% id='progressBar'></td></tr></table>");
    } else {
        $("#progress1").html("<table width=80% border=0 cellspacing=0><tr height=10><td class=preview-color-1 width="+Number(flowCount)+"% id='progressBar'></td><td width="+Number(100-flowCount)+"% style='background: #CCCCCC'></td></tr></table>");
    }
    flowCount++;
    timerFlow = setTimeout('updateFlow()',10);
}
counter = 0;
updateUpload = function() {
    var rnd = Math.random();
    var url = '{?$sitepath?}upload.php?id={?$id?}&rnd='+Number(rnd);
    $.ajax({
            type: "GET",
            url: url,
            dataType: "script",
            success: function (xml) {
                var size = new Number(xml.responseText);
                if ((size > 0) && upload) {
                    timerUpload = setTimeout('updateUpload()',1000);                    
                } else {
                    clearTimeout(timerUpload);
                }
            },
            error: function() {
                clearTimeout(timerUpload);
            }
    })
}
    
//-->
</script>
</head>
<body>
<div class="els-content">
<div style="padding:8px">
<table width=100% height=90% border=0 cellpadding=0 cellspacing=0 class="card-person" style="width: 100%">
    <tr>
        <td align=center>
            <b><div id="title">{?$title|escape?}</div></b>
        </td>
    </tr>
    <tr>
        <td align=center height=10>
            <div id="action1">{?$headAction|escape?}</div>
        </td>
    </tr>
    <tr>
        <td align=center height=10>
            <div id="progress1"></div>
        </td>
    </tr>
    <tr>
        <td align=center height=10>
            <div id="action2"></div>
        </td>
    </tr>
    <tr>
        <td align=center height=10>
            <div id="progress2"></div>
        </td>
    </tr>
    <tr>
        <td align=center height=10>
            <div id="comments">{?$comments|escape?}</div>
        </td>
    </tr>
</table>

<script type="text/javascript">
<!--

flowCount = 0; errorCounter = 0;
upload         = {?if $upload?}true{?else?}false{?/if?};
timerFlow      = setTimeout('updateFlow()',1000);
timer          = setTimeout("updateProcess()",1000);

if (upload) {
    timerUpload = setTimeout('updateUpload()',1000);
}
//-->
</script>
</div>
</div>
</body>
</html>
