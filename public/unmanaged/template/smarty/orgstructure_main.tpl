{?if $id?}
{?if $smarty.get.type != "add"?}
<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
<form method="POST" action="" onSubmit="if (document.getElementById('roles')) select_list_select_all('roles');">
<input type="hidden" name="action" value="update">
<input type="hidden" name="id" value="{?$id|escape?}">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}Редактирование свойств{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Название{?/t?}</td>
    <td><input type="text" style="width: 300px" name="name" value="{?if $item?}{?$item->attributes.name|escape?}{?/if?}"></td>
</tr>
<tr>
    <td>{?t?}Тип{?/t?}</td>
    <td>
        <select name="type">
            <option value="0" {?if $item?}{?if $item->attributes.type == 0?}selected{?/if?}{?/if?}> {?t?}должность{?/t?}</option>
            <option value="1" {?if $item?}{?if $item->attributes.type == 1?}selected{?/if?}{?/if?}> {?t?}рук. должность{?/t?}</option>
            <option value="2" {?if $item?}{?if $item->attributes.type == 2?}selected{?/if?}{?/if?}> {?t?}оргединица{?/t?}</option>
        </select>
    </td>
</tr>
<tr>
    <td>{?t?}Входит в{?/t?}</td>
    <td>{?$owner?}</td>
</tr>
<tr>
    <td>{?t?}Код оргединицы/табельный номер{?/t?}</td>
    <td><input type="text" style="width:300px" name="code" value="{?if $item?}{?$item->attributes.code|escape?}{?/if?}"></td>
</tr>
<tr>
    <td>{?t?}Описание{?/t?}
    <td><textarea name="info" cols=53 rows=5>{?if $item?}{?$item->attributes.info|escape?}{?/if?}</textarea></td>
</tr>
</table>
<br>

{?if $item?}

    {?if $item->attributes.type != 2?}
<script type="text/javascript" language="JavaScript">
<!--
    {?$sajaxJavascript?}
    function show_user_select(html) {
        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select id="mid" name="mid" style="width: 100%">'+html+'</select>';
    }

    function get_user_select(str) {

        var elm = document.getElementById('people');
        if (elm) elm.innerHTML = '<select style="width: 100%"><option>{?t?}Загружаю данные...{?/t?}</option></select>';

        x_search_people_unused(str, ".(int) $mid.", show_user_select);
    }
//-->
</script>
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}В должности{?/t?}</th>
</tr>
<tr>
    <td>Назначение</td>
    <td width="100%">
        <input type="button" value="{?t?}Все{?/t?}" style="width: 10%" onClick="if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');">
        <input type="text" id="search_people" value="{?$search?}" style="width: 88%" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);"><br>
        <div id="people">
        <select id="mid" name='mid' style="width: 100%">"
        {?$people?}
        </select>
        </div>
    </td>
</tr>
</table>
<br>
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}Виды оценки{?/t?}</th>
</tr>
<tr>
    <td colspan=99>{?$competenceRolesList2ListHtml?}</td>
</tr>
</table>
    {?else?}
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}Настройки{?/t?}</th>
</tr>
<tr>
    <td colspan=99><input type="checkbox" name="allow_own_results" value="1" {?if $item->attributes.own_results?}checked{?/if?}> {?t?}сотрудники могут просматривать свои ответы{?/t?}</td>
</tr>
<tr>
    <td colspan=99><input type="checkbox" name="allow_enemy_results" value="1" {?if $item->attributes.enemy_results?}checked{?/if?}> {?t?}сотрудники могут просматривать ответы, данные другими сотрудниками{?/t?}</td>
</tr>
<!--tr>
    <td>{?t?}Способ отображения оценки{?/t?}</td>
    <td>
        <input type="radio" name="result_display_method" value="0" {?if !$item->attributes.display_results?}checked{?/if?}> общий (ответы сотрудников не группируются)<br>
        <input type="radio" name="result_display_method" value="1" {?if $item->attributes.display_results?}checked{?/if?}> с разделением на группы (руководители, коллеги, подчинённые)
    </td>
</tr-->
<!--tr>
    <td>{?t?}Порог прохождения анкетирования, %{?/t?}</td>
    <td>
        <select name="threshold">
        {?section name="threshold" start=0 loop=101?}
        <option value="{?$smarty.section.threshold.index?}"  {?if $item->attributes.threshold == $smarty.section.threshold.index?}selected{?/if?}> {?$smarty.section.threshold.index?}</option>
        {?/section?}
        </select>
    </td>
</tr-->
</table>
    {?/if?}
{?/if?}
<br>
{?if $permStructureEdit?}
    {?$okbutton?}
    <br/>
{?/if?}
</form>
{?else?}
{?if $item && $item->attributes.type == 2 && $permStructureEdit?}
<form method="POST" action="">
<input type="hidden" name="action" value="add">
<input type="hidden" name="owner" value="{?$id|escape?}">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}Добавить{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Название{?/t?}</td>
    <td><input type="text" style="width: 300px" name="name" value="Название"></td>
</tr>
<tr>
    <td>{?t?}Тип{?/t?}</td>
    <td>
    <input checked type="radio" name="type" value="0"> <img border=0 align=absmiddle alt="{?t?}должность{?/t?}" src="{?$sitepath?}images/icons/positions_type_0.gif"> {?t?}должность{?/t?} &nbsp;
    <input type="radio" name="type" value="1"> <img border=0 align=absmiddle alt="{?t?}рук. должность{?/t?}" src="{?$sitepath?}images/icons/positions_type_1.gif"> {?t?}рук. должность{?/t?} &nbsp;
    {?if $permStructureEdit?}
    <input type="radio" name="type" value="2"> <img border=0 align=absmiddle alt="{?t?}оргединица{?/t?}" src="{?$sitepath?}images/icons/positions_type_2.gif"> {?t?}оргединица{?/t?} &nbsp;
    {?/if?}
    </td>
</tr>
<tr>
    <td>{?t?}Код оргединицы/табельный номер{?/t?}</td>
    <td><input type="text" style="width: 300px" name="code" value=""></td>
</tr>
</table>
<br>
{?$okbutton?}
</form>
{?/if?}
{?/if?}
{?else?}
{?t?}Выберите элемент структуры организации{?/t?}
{?/if?}