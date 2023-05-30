{?include file="all_header.tpl"?}

{?php?}
echo ph(_('Библиотека учебных материалов'));
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<script language="javascript" type="text/javascript">
<!--
    function changeMaterialType(type) {
    	var elm1, elm2;
    	if ((elm1 = document.getElementById('ematerial')) && (elm2 = document.getElementById('tmaterial'))) {
            switch(type) {
            	case '0':
            	    elm1.style.display = 'block';
            	    elm2.style.display = 'none';
            	break;
            	default:
            	    elm1.style.display = 'none';
            	    elm2.style.display = 'block';
            }
    	}
    }
//-->
</script>

<!--form method="POST" enctype="multipart/form-data">

<input type="hidden" name="import_items_to_lib" value="import_items_to_lib">

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Импортировать материал{?/t?}</th></tr>
<tr>
    <td>{?t?}Файл учебных материалов{?/t?} (.zip): </td>
    <td>
    <input style="width: 300px;" type="file" name="lop">
    <input type="Submit" name="Submit" value="{?t?}Импортировать{?/t?}">
    </td>
</tr>
</table>
<p>
</form-->

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="add_lib_item" value="add_lib_item">
<input type="hidden" name="is_active_version" value="1">

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Добавить новое издание{?/t?}</th></tr>
<tr>
    <td>{?t?}Тип издания:{?/t?} <font color=red>*</font></td>
    <td>
    <select name="type" onChange="changeMaterialType(this.value)">
    {?html_options options=$lo_types?}
    </select>
    </td>
</tr>
<tr>
    <td>{?t?}Название:{?/t?} <font color=red>*</font></td>
    <td><input style="width: 300px" type="text" name="title" value="{?$title|htmlspecialchars?}"></td>
</tr>
<tr>
    <td>{?t?}Автор:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="author" value=""></td>
</tr>
<tr>
    <td>{?t?}Инвентарный номер издания:{?/t?} </td>
    <td>
    <input style="width: 50px" type="text" name="uid" value="">
    {?$tooltip->display('book_number')?}
    </td>
</tr>
<tr>
    <td>{?t?}Количество экземпляров:{?/t?} </td>
    <td>
    <input style="width: 50px" type="text" name="quantity" value="">
    {?$tooltip->display('book_quantity')?}
    </td>
</tr>
<tr>
    <td>{?t?}Краткое описание (аннотация){?/t?}: </td>
    <td><textarea name="description" style="width: 300px;"></textarea></td>
</tr>
<tr>
    <td>{?t?}Ключевые слова:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="keywords" value=""></td>
</tr>
<tr>
    <td>{?t?}Издатель:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="publisher" value=""></td>
</tr>
<tr>
    <td>{?t?}Год:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="publish_date" value=""></td>
</tr>
<tr>
    <td>URL: </td>
    <td>
        <input style="width: 300px" type="text" name="location" value="">
        {?$tooltip->display('book_url')?}

    </td>
</tr>
<tr>
    <td>{?t?}Уровень доступа:{?/t?} </td>
    <td>
    <select name="need_access_level">
        {?php?}
        global $book;
        for($i=1;$i<=10;$i++) {

            echo "<option value=\"$i\"";
            //if ($i==10) echo " selected ";
            echo "> $i";

        }
        echo "<option value=\"0\" selected> 0";
        {?/php?}
    </select>
    {?$tooltip->display('book_access_level')?}
    </td>
</tr>
<tr>
    <td>{?t?}Электронная версия издания:{?/t?} </td>
    <td>
    <div id="ematerial">
    <input style="width: 300px" type="file" name="material" value="">
    {?$tooltip->display('book_el_version')?}
    </div>
    <div id="tmaterial" style="display: none;">
    нет
    </div>
    </td>
</tr>
<tr>
    <td colspan=2>{?t?}Рубрики{?/t?}</td>
<tr>
    <td colspan=2>
    <table width=100% border=0>
    <tr><td width=50%>{?$categories?}</td>
    <td valign=middle align=middle>
        <input type="button" value=">>" onClick="addCategories();">
        <input type="button" value="<<" onClick="removeCategories()">
    </td>
    <td width=50%>
    <select size=10 id="cats" name="cats[]" multiple style="width: 100%">
    </select>
    </td></tr>
    </table>
    </td>
</tr>
<tr>
    <td>{?t?}Файл метаописания издания в формате RUSLOM (.xml):{?/t?} </td>
    <td>
    <input style="width: 300px" type="file" name="metafile" value="">
    {?$tooltip->display('book_ruslom')?}
    </td>
</tr>
</table>
<table border=0 align=right>
    <tr>
        <td>{?php?}echo okbutton(_('Добавить'),'','Ok','selectAllCategories();');{?/php?}</td>
        <td>{?php?}echo okbutton(_('Отмена'),'','Cancel',"document.location.href=\"{$GLOBALS['sitepath']}lib.php?page={$GLOBALS['page']}\"; return false;");{?/php?}</td>
    </tr>
</table>
<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/library.js"></script>
</form>
{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
