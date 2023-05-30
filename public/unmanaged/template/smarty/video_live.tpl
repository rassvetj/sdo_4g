<table cellpadding=2 cellspacing=0 border=0 align=center>
<tr><td valign=top>
<OBJECT ID="Player" {?if $fullscreen?}width="640" height="480"{?/if?}
  CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6">
  <PARAM name="autoStart" value="True">
  <PARAM name="URL" value="{?$smarty.const.VIDEO_LIVE_URL?}">
  <PARAM name="uiMode" value="mini">
  <PARAM name="enableContextMenu" value="0">
</OBJECT>
</td><td valign=top>
{?if $controller_enable?}
<script language="JavaScript"> 
<!--
var g_objEncoder = new ActiveXObject("WMEncEng.WMEncoder");
g_objEncoder.Load("{?$smarty.const.VIDEO_LIVE_CONFIG?}");

function start() { 
document.getElementById('Player').controls.play();
g_objEncoder.Start(); 
} 
function stop() { 
document.getElementById('Player').controls.stop();
g_objEncoder.Stop(); 
} 
//-->
</script> 
<p align=center>
<button onclick="start()" style="width:100px;">{?t?}Начать{?/t?}</button><br>
<button onclick="stop()" style="width:100px;">{?t?}Завершить{?/t?}</button>
{?/if?}
</td></tr>
</table>