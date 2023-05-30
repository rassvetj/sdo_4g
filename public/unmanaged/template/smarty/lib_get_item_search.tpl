<form name="library_search_form" action="{?$sitepath?}lib.php?ModID={?$ModID?}" method="POST">

<input type="hidden" name="search_items" value="search_items">

<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/library.js"></script>

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Поиск{?/t?}</th></tr>
<tr>
    <td>
    {?t?}Наименование:{?/t?} 
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[title]" value="{?$search.title?}">
    </td>
    <td>
    {?t?}Автор:{?/t?}
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[author]" value="{?$search.author?}">
    </td>
</tr>
<tr>
    <td>
    {?t?}Описание:{?/t?} 
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[description]" value="{?$search.description?}">
    </td>
    <td>
    {?t?}Ключевые слова:{?/t?} 
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[keywords]" value="{?$search.keywords?}">
    </td>
</tr>
<tr>
    <td>
    {?t?}Издатель:{?/t?}
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[publisher]" value="{?$search.publisher?}">
    </td>
    <td>
    {?t?}Год:{?/t?}
    </td>
    <td>
    с <input style="width: 60px;" type="text" name="search[publish_date_from]" value="{?$search.publish_date_from?}">-
    {?t?}по{?/t?} <input style="width: 60px;" type="text" name="search[publish_date_to]" value="{?$search.publish_date_to?}">
    </td>
</tr>
<tr>
    <td>
    {?t?}Уникальный номер:{?/t?}
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[uid]" value="{?$search.uid?}">
    </td>
    <td>
    {?t?}На руках у:{?/t?}
    </td>
    <td>
    <select name="search[mid]" style="width: 150px;">
        <option value="0"> {?t?}не имеет значения{?/t?}</option>
    {?foreach from=$people item=p?}
        <option {?if $search.mid==$p.MID?}selected{?/if?} value="{?$p.MID?}"> {?$p.LastName?} {?$p.FirstName?}</option>
    {?/foreach?}
    </select>
    </td>
</tr>
<tr>
    <td colspan=4>{?$categories?}</td>
</tr>
<tr>
    <td align=right colspan=4>
    <input style="width: 60px;" type="submit" name="Submit" value="{?t?}Искать{?/t?}">
    <input style="width: 60px;" onClick="clearFields();" type="button" name="Clear" value="{?t?}Очистить{?/t?}">
    <input style="width: 60px;" onClick="clearFields(); document.forms[0].submit();" type="button" name="Clear" value="{?t?}Сбросить{?/t?}">
    </td>
</tr>
</table>
<p>
{?literal?}
<script type="text/javascript" language="JavaScript">
<!--
    var cats = document.getElementById('categories');
    for(var i=0;i<cats.options.length;i++) {
        if (cats.options[i].value=={?/literal?}{?$search.categories?}{?literal?}) cats.options[i].selected=true;
    }
// -->
</script>
{?/literal?}
</form> 