<script language="javascript" type="text/javascript">
<!--
    function select_all_items(elm_prefix,checked) {
        var i=1;
        elm = document.getElementById(elm_prefix+'_'+(i++));
        while (elm) {
            elm.checked = checked;
            elm = document.getElementById(elm_prefix+'_'+(i++));
        }
    }
//-->
</script>
<form name="form1" method="post" action="" onSubmit="wopen('progress.php?id={?$progressId?}&title={?$progressTitle|urlencode?}&action={?$progressAction|urlencode?}','progress',400,200);">
<input name="post" type="hidden" value="generate">
<input name="progressId" type="hidden" value="{?$progressId?}">
{?if $is_specialities_exists?}
{?foreach from=$this->tracks key=key_track item=track?}
{?assign var="i" value=1?}
<input name="hid_tracks[]" type="hidden" value="{?$track->id?}">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td>
        <h3>{?t?}Специальность{?/t?} "{?$track->attributes.title?}"</h3>
        <table width=100% class=main cellspacing=0>
          <tr>
            <th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_track_{?$track->id?}',this.checked);" checked></th>
            <th nowrap width="100%">{?t?}Название курса{?/t?}</th>
          </tr>
         {?foreach from=$track->levels item=level key=key_level?}
            {?if $key_level != $smarty.const.COURSE_FREE?}
              <tr>
                <td colspan=2>{?$key_level?}-{?t?}й семестр{?/t?}</td>
              </tr>
            {?foreach from=$level item=course?}
              <tr>
                <td><input type="checkbox" id="ch_track_{?$track->id?}_{?$i++?}" name="ch_track_{?$track->id?}[]" value="{?$course->id?}" checked></td>
                <td nowrap>{?$course->attributes.title?}</td>
              </tr>
            {?/foreach?}
            {?/if?}
         {?/foreach?}
        </table><br>
    </td>
  </tr>
 </table>
{?/foreach?}
{?/if?}
{?if $this->courses_free?}
<input name="hid_tracks[]" type="hidden" value="{?$smarty.const.COURSE_FREE?}">
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td>
        {?if $is_specialities_exists?}
        <h3>{?t?}Свободные курсы{?/t?}</h3>
        {?/if?}
        <table width=100% class=main cellspacing=0>
          <tr>
            <th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_track_free',this.checked);" checked></th>
            <th nowrap width="100%">{?t?}Название курса{?/t?}</th>
          </tr>
         {?assign var="i" value=1?}
         {?foreach from=$this->courses_free item=course_free?}
         <tr>
            <td><input type="checkbox" id="ch_track_free_{?$i++?}" name="ch_track_free[]" value="{?$course_free->id?}" checked></td>
            <td nowrap>{?$course_free->attributes.title?}</td>
          </tr>
         {?/foreach?}
        </table><br>
    </td>
  </tr>
</table>
  {?/if?}
<table width=100% class=main cellspacing=0>
<tr><td>
{?t?}Способ доступа к материалам курсов:{?/t?} &nbsp;
    <input name="mode" type="radio" value="1" checked> {?t?}через программу курсов{?/t?}
    <input name="mode" type="radio" value="2"> {?t?}через расписание{?/t?}
    <br><br>
    <input type="checkbox" name="doZip" value="1"> {?t?}архивировать (zip){?/t?}    
</td></tr>
</table>
<table border="0" cellspacing="5" cellpadding="0" width="100%">
      <tr>
        <td align="right" width="99%">
        <div style='float: right;' class='button'><a href='javascript:history.back();'>{?t?}Отмена{?/t?}</a></div><input type='button' value='{?t?}отмена{?/t?}' style='display: none;'/><div class='clear-both'></div>
        </td>
        <td align="right" width="1%">
        {?$okbutton?}
        </td>
      </tr>
</table>
</form>