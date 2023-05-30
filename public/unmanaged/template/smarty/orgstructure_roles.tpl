<script type="text/javascript" language="JavaScript">
<!--
function checkSlaves(prefix, checked) {
    var i = 1;
    var elm;
    
    while(elm = document.getElementById(prefix+'_'+i)) {
        elm.checked = checked;
        i++;
    }
}
//-->
</script>
<form action="" method="POST">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}Назначение видов оценок{?/t?}</th>
</tr>
{?if $items?}
    <tr>
        <td width=50% valign=top>
    {?foreach from=$items item=persons?}
        {?if $persons?}
            <table width=100% class=main cellspacing=0>
            {?foreach name="persons" from=$persons item=person?}
                {?if $smarty.foreach.persons.first?}
                <tr>
                    <th><input type="checkbox" checked onClick="checkSlaves('{?$person->attributes.owner_soid?}',this.checked)"></th>
                    <th>{?$person->attributes.owner_name|escape?}</th>
                </tr>
                {?/if?}
                <tr>
                    <td><input id="{?$person->attributes.owner_soid?}_{?$smarty.foreach.persons.iteration?}" type="checkbox" name="items[]" value="{?$person->attributes.soid?}" checked></td>
                    <td width=99%>{?$person->attributes.name|escape?}</td>
                </tr>
            {?/foreach?}
            </table>
            <br>
        {?/if?}
    {?/foreach?}
        </td>
        <td valign=top>
        {?if $courses?}
            <table width=100% class=main cellspacing=0>
            <tr><th>{?t?}Виды оценок{?/t?}</th></tr>
            {?foreach from=$courses key=id item=course?}
                <tr><td><input type="checkbox" name="courses[]" value="{?$id?}"> {?$course|escape?}</td></tr>
            {?/foreach?}
            </table>
        {?/if?}
        </td>
    </tr>
{?else?}
<tr>
    <td colspan=99 align=center>{?t?}Не выбрано ни одного элемента{?/t?}</td>
</tr>
{?/if?}
</table>
<br>
<table width=100% class=main cellspacing=0>
<tr>
    <td align=right>
        {?t?}Выполнить: {?/t?}
        <select name="action">
            <option value="1"> {?t?}Назначение видов оценок{?/t?}</option>
            <option value="2"> {?t?}Удаление видов оценок{?/t?}</option>
        </select>
    </td>
</tr>
</table>
<br>
{?$okbutton?}
</form>