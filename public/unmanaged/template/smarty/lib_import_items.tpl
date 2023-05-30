{?include file="all_header.tpl"?}

{?php?}
echo ph('Библиотека учебных материалов');
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="import_items_to_lib" value="import_items_to_lib">

<table width=100% class=main cellspacing=0>
<tr><th colspan=3>{?t?}Импортировать материал{?/t?}</th></tr>
<tr>
    <td>{?t?}Файл учебных материалов{?/t?} (.zip): </td>
    <td align=left>
    <input style="width: 300px;" type="file" name="lop">
    </td>
    <td>
    {?php?}echo okbutton(_('Импортировать'));{?/php?}    
    </td>
</tr>
</table>
<p>
</form>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
