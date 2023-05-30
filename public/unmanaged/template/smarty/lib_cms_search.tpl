<form action="{?$sitepath?}lib_cms.php" method="POST">

<input type="hidden" name="search_items" value="search_items">

<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/library.js"></script>

<table width=100% class=main cellspacing=0>
<tr><th colspan=99>{?t?}Поиск{?/t?}</th></tr>
<tr>
    <td>
    {?t?}Курс{?/t?}:
    </td>
    <td width="100%">
    {?$categories?}
    </td>
</tr>
<tr>
    <td nowrap>
    {?t?}Ключевые слова:{?/t?}
    </td>
    <td>
    <input style="width: 100%" type="text" name="search[keywords]" value="{?$search.keywords|escape?}">
    </td>
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
<br>