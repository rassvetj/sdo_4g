<script language='javascript' type="text/javascript">
<!--
    function checkAll(element) {
        var i=1;
        elm = document.getElementById('pip'+i);
        while (elm){
            elm.checked = element.checked;
            i++;
            elm = document.getElementById('pip'+i);
        }
    }
    
    function checkAction(element) {
        $('#div_message').hide();
        $('#div_add_money').hide();
        $('#div_other').hide();
        $('#div_'+element.value).show();
        
    }
//-->    
</script>
<form action="" method="POST">
<input type="hidden" name="TRACKS" value="{?$trid?}">
<input type="hidden" name="GROUPS" value="{?$grid?}">
<input type="hidden" name="LEVEL" value="{?$level?}">
<input type="hidden" name="post_flag" value="{?php?}echo md5(time());{?/php?}">
{?if $pager?}
<table border=0 cellpadding=0 cellspacing=0 width=100%>
<tr>
    <td align=center>
        {?$pager?}
    </td>
</tr>
</table>
<br>
{?/if?}
<table width=100% class=main cellspacing=0>
<tr>
    <th><input type="checkbox" onclick="checkAll(this)"></th>
    <th>{?t?}ФИО{?/t?}</th>
    <th>{?t?}Сем.{?/t?}</th>
    <th>{?t?}Принят{?/t?}</th>
    <th>{?t?}Переведен{?/t?}</th>
    <th>{?t?}Останов.{?/t?}/<BR>{?t?}продол.{?/t?}</th>
    <th>{?t?}Баланс{?/t?}</th>
    <th>{?t?}Оценки за курсы{?/t?}</th>
</tr>
{?if $people?}
    {?foreach name="people" from=$people item=person?}
<tr>
    <td><input type="checkbox" name="pip[]" id="pip{?$smarty.foreach.people.iteration?}" value="{?$person.MID?}"></td>
    <td>{?$person.LastName?} {?$person.FirstName?} {?$person.Patronymic?}</td>
    <td>{?$person.level?}</td>
    <td>{?if $person.started?}{?$person.started|date_format:"%d.%m.%Y"?}{?/if?}</td>
    <td>{?if $person.changed?}{?$person.changed|date_format:"%d.%m.%Y"?}{?/if?}</td>
    <td>{?if $person.stoped?}{?$person.stoped|date_format:"%d.%m.%Y"?}{?/if?}</td>
    <td>{?$person.money|intval?}</td>
    <td><a href="{?$sitepath?}plan.php?mid={?$person.MID?}&trid={?$person.trid?}" target="_blank">{?$icon_edit?}</a></td>
</tr>
    {?/foreach?}
{?else?}
<tr>
    <td colspan=99 align=center class="nodata">{?t?}Нет данных для отображения{?/t?}</td>
</tr>
{?/if?}
</table>
<br>
<table width=100% border=0 cellpadding=0 cellspacing=0><tr><td align=right>
{?t?}Выполнить действие{?/t?}: &nbsp;
<select name="c" onChange="checkAction(this);" style="vertical-align:top">
<option value="-1">--{?t?}Выбрать{?/t?}--</option>
<option value="message">{?t?}Послать сообщение{?/t?}</option>
<option value="nextlevel">{?t?}Перевести на СЛЕД. семестр{?/t?}</option>
<option value="prevlevel">{?t?}Вернуть на ПРЕД. семестр{?/t?}</option>
<option value="finish">{?t?}Закончить обучение{?/t?}</option>
<option value="break">{?t?}Отчислить{?/t?}</option>
<option value="freeze">{?t?}Остановить обучение{?/t?}</option>
<option value="continue">{?t?}Продолжить обучение{?/t?}</option>
{?if defined('USE_BOLOGNA_SYSTEM') && !$smarty.const.USE_BOLOGNA_SYSTEM?}
<option value="add_money">{?t?}Добавить денег{?/t?}</option>
{?else?}
<option value="add_money">{?t?}Добавить кредиты{?/t?}</option>
{?/if?}
<option value="other">{?t?}Зачислить на другую специальность{?/t?}</option>
<!--option value="erase">{?t?}Удалить навсегда{?/t?}</option-->
</select>
<span id="div_message" style="display: none">
    <textarea cols=50 rows=3 name="message">{?t?}Текст сообщения{?/t?}</textarea>
</span>
<span id="div_add_money" style="display: none">
    {?t?}сумма{?/t?}: <input type="text" name="money" value="0" size=3>
</span>
<span id="div_other" style="display: none">
    <select name="track_id">
    {?if $specs?}
        {?foreach from=$specs key=specId item=specName?}
        <option value="{?$specId?}"> {?$specName|escape?}</option>
        {?/foreach?}
    {?else?}
        <option value="0"> {?t?}нет{?/t?}</option>
    {?/if?}
    </select>
</span>
<br><br>
{?$okbutton?}
</td></tr></table>
{?if $pager?}
<br>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
<tr>
    <td align=center>
        {?$pager?}
    </td>
</tr>
</table>
{?/if?}

</form>