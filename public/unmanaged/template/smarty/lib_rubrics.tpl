{?include file="all_header.tpl"?}

{?php?}
echo ph(_('Библиотека учебных материалов'));
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="import_rubrics" value="import_rubrics">

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Импортировать рубрики{?/t?}</th></tr>
<tr>
    <td>{?t?}Файл рубрик{?/t?} (.xml): </td>
    <td>
    <input style="width: 300px;" type="file" name="rubrics">
    </td>
</tr>
<tr>
    <td colspan=2>
    <table border=0 align=right>
    <tr>
        <td>
        {?php?}echo okbutton(_('Импортировать'));{?/php?}            
        </td>
        <td>
        {?php?}echo okbutton(_('Отмена'),'','Cancel',"document.location.href=\"{$GLOBALS['sitepath']}lib.php?page={$GLOBALS['page']}\"; return false;");{?/php?}    
        </td>
    </tr>
    </table>
    </td>
</tr>
</table>
<p>                                                       
</form>
{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
