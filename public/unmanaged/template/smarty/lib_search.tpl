<form action="{?$sitepath?}lib.php" method="POST">

<input type="hidden" name="search_items" value="search_items">

<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/library.js"></script>

<table width=100% class=main cellspacing=0>
<tr><th colspan=99>{?t?}Поиск{?/t?}</th></tr>
<tr>
    <td>
    {?t?}Ключевые слова{?/t?}
    </td>
    <td>
    <input style="width: 100%;" type="text" name="search[title]" value="{?$search.title|escape?}">
    </td>
</tr>
<tr>
    <td>
    {?t?}Автор{?/t?}
    </td>
    <td>
    <select style="width: 150px;" name="search[author]">
    <option value=''> </option>
    {?if $authors?}
        {?foreach from=$authors item=author?}
        <option value="{?$author|escape?}" {?if $author == $search.author?}selected{?/if?}> {?$author|escape?}</option>
        {?/foreach?}
    {?/if?}
    </select>
    <!--input style="width: 150px;" type="text" name="search[author]" value="{?$search.author|escape?}"-->
    </td>
</tr>
<!--tr>
    <td>
    {?t?}Описание{?/t?}
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[description]" value="{?$search.description|escape?}">
    </td>
</tr-->
<!--tr>
    <td>
    {?t?}Ключевые слова{?/t?}
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[keywords]" value="{?$search.keywords|escape?}">
    </td>
</tr-->
<tr>
    <td>
    {?t?}Издательство{?/t?}
    </td>
    <td>
    <select style="width: 150px;" name="search[publisher]">
    <option value=''> </option>
    {?if $authors?}
        {?foreach from=$publishers item=publisher?}
        <option value="{?$publisher|escape?}" {?if $publisher == $search.publisher?}selected{?/if?}> {?$publisher|escape?}</option>
        {?/foreach?}
    {?/if?}
    </select>
    <!--input style="width: 150px;" type="text" name="search[publisher]" value="{?$search.publisher|escape?}"-->
    </td>
</tr>
<!--tr>
    <td>
    {?t?}Год{?/t?}
    </td>
    <td>
    {?t?}с{?/t?} <input style="width: 60px;" type="text" name="search[publish_date_from]" value="{?$search.publish_date_from|escape?}">-
    {?t?}по{?/t?} <input style="width: 60px;" type="text" name="search[publish_date_to]" value="{?$search.publish_date_to|escape?}">
    </td>
</tr-->
<!--tr>
    <td>
    {?t?}Инвентарный номер{?/t?}
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[uid]" value="{?$search.uid|escape?}">
    </td>
</tr-->
<!--tr>
    <td>
    {?t?}На руках у:{?/t?}
    </td>
    <td>
    <input style="width: 150px;" type="text" name="search[mid]" value="{?$search.mid|escape?}">
    </td>
</tr-->
<tr>
    <td>
        {?t?}Рубрикатор{?/t?}
    </td>
    <td>
        {?$categories?}
    </td>
</tr>
<tr>
    <td align=right colspan=4>
    <table border=0 align=rigth>
    <tr>
        <td>{?php?}echo okbutton(_('Искать'));{?/php?}</td>
        <td>{?php?}echo okbutton(_('Очистить'),'','Clear','clearFields(); return false;');{?/php?}</td>
        <td>{?php?}echo okbutton(_('Сбросить'),'','Empty','clearFields();');{?/php?}</td>
    </tr>
    </table>
    </td>
</tr>
</table>
<p>
{?literal?}
<script type="text/javascript" language="JavaScript">
<!--
    var cats = document.getElementById('categories');
    for(var i=0;i<cats.options.length;i++) {
        if (cats.options[i].value=={?/literal?}'{?$search.categories?}'{?literal?}) cats.options[i].selected=true;
    }
// -->
</script>
{?/literal?}
</form>
<br>