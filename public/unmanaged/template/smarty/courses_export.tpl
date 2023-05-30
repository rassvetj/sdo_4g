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
<form action="" method="POST">
<input name="action" type="hidden" value="generate">

<table width=100% class=main cellspacing=0>
     <tr>
         <th width=1% nowrap><input type="checkbox" value="1" checked onClick="select_all_items('checkbox_course',this.checked)"></th>
         <th>{?t?}Название курса{?/t?}</th>
     </tr>
{?if $this->courses?}
    {?assign var="i" value="1"?}
    {?foreach from=$this->courses item=course?}
    <tr>
        <td><input name="courses[]" id="checkbox_course_{?$i++?}" type="checkbox" value="{?$course->id?}" checked></td>
        <td>{?$course->attributes.title?}</td>
    </tr>
    {?/foreach?}
{?/if?}
</table>
<br>
{?$okbutton?}
</form>