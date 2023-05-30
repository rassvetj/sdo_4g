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

{?if !$book.is_package && !$book.type?}
<form id="editform" name="editform" method="POST" enctype="multipart/form-data" onSubmit="selectAllCategories();">

<input type="hidden" name="add_item_version" value="add_item_version">
<input type="hidden" name="parent" value="{?$book.bid?}">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=3>{?t?}Добавить новую электронную версию издания{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Версия:{?/t?} </td>
    <td>
    <input style="width: 300px" type="file" name="material" value="">
    </td>
    <td>
    {?php?}echo okbutton(_('Добавить'));{?/php?}    
    </td>
</tr>
</table>
</form>
<br>
{?else?}

{?/if?}

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="edit_lib_item" value="edit_lib_item">

<input type="hidden" name="bid" value="{?$book.bid?}">
<input type="hidden" name="filename" value="{?$book.filename?}">
<input type="hidden" name="is_package" value="{?$book.is_package?}">
<input type="hidden" name="is_active_version" value="{?$book.is_active_version?}">

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Редактировать издание{?/t?}</th></tr>
<tr>
    <td>{?t?}Тип издания:{?/t?} </td>
    <td>
    <select name="type" onChange="changeMaterialType(this.value)">
    {?html_options options=$lo_types selected=$book.type?}
    </select>
    </td>
</tr>
<tr>
    <td>{?t?}Название:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="title" value="{?$book.title|escape?}"></td>
</tr>
<tr>
    <td>{?t?}Автор:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="author" value="{?$book.author|escape?}"></td>
</tr>
<tr>
    <td>
        {?t?}Инвентарный номер издания:{?/t?}        
    </td>
    <td>
        <input style="width: 50px" type="text" name="uid" value="{?if $book.uid?}{?$book.uid?}{?/if?}">
        {?$tooltip->display('book_number')?}
    </td>
</tr>
<tr>
    <td>{?t?}Количество экземпляров:{?/t?} </td>
    <td>
        <input style="width: 50px" type="text" name="quantity" value="{?if $book.quantity?}{?$book.quantity?}{?/if?}">
        {?$tooltip->display('book_quantity')?}
    </td>
</tr>
<tr>
    <td>{?t?}Краткое описание (аннотация){?/t?}: </td>
    <td><textarea name="description" style="width: 300px;">{?$book.description?}</textarea></td>
</tr>
<tr>
    <td>{?t?}Ключевые слова:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="keywords" value="{?$book.keywords|escape?}"></td>
</tr>
<tr>
    <td>{?t?}Издатель:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="publisher" value="{?$book.publisher|escape?}"></td>
</tr>
<tr>
    <td>{?t?}Год:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="publish_date" value="{?$book.publish_date|escape?}"></td>
</tr>
<tr>
    <td>URL: </td>
    <td>
        <input style="width: 300px" type="text" name="location" value="{?$book.location|escape?}">
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
            if ($i==$book['need_access_level']) echo " selected ";
            echo "> $i";

        }
        echo "<option value=\"0\"";
        if ($book['need_access_level']==0) echo "selected";
        echo "> 0";
        {?/php?}
    </select>
    {?$tooltip->display('book_access_level')?}
    </td>
</tr>
    {?if !$book.is_package?}
<tr>
    <td>{?t?}Электронная версия издания:{?/t?} </td>
    <td>
    <div id="ematerial" {?if $book.type?}style="display: none;"{?/if?}>
    <input style="width: 300px" type="file" name="material" value="">
    {?$tooltip->display('book_el_version')?}<br>
    </div>
    <div id="tmaterial" {?if !$book.type?}style="display: none;"{?/if?}>
    нет
    </div>
<!--
    {?if $book.filename?}
    <a href="{?$sitepath?}library{?$book.filename?}" target=_blank><b>[ {?t?}просмотр{?/t?} ]</b></a>
    {?/if?}
// -->
    </td>
</tr>
    {?/if?}
{?if $book.filename && $book.versions && !$book.type?}
<tr>
    <td>{?t?}Версии:{?/t?} </td>
    <td>
    <table border=0 cellpadding=0 cellspacing=0>
    {?foreach from=$book.versions item=v?}
    <tr>
    <td><input type="radio" name="active_version" value="{?$v.bid?}" {?if $v.is_active_version?}checked{?/if?}></td>
    <td>{?$v.upload_date|date_format:"%d.%m.%Y, %H:%M:%S"?}</td>
    <td>
    {?if $v.filename?}
    <a href="{?$sitepath?}library{?$v.filename?}" target=_blank><img border=0 src="{?$sitepath?}images/icons/save.gif"></a>
    {?/if?}
    </td>
    <td>
    {?if $v.bid!=$book.bid?}
    <a onClick="if (confirm('{?t?}Удалить версию?{?/t?}')) return true; else return false;" href="{?$sitepath?}lib.php?bid={?$book.bid?}&action=edit&del={?$v.bid?}&page={?$page?}"><img alt="{?t?}Удалить версию{?/t?}" border=0 src="images/icons/delete.gif"></a>
    {?/if?}
    </td>
    </tr>
    {?/foreach?}
    </table>
    </td>
</tr>
{?/if?}
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
{?if $book.cats?}
    {?foreach from=$book.cats item=i?}
    <option value="{?$i.catid?}">{?$i.name?}</option>
    {?/foreach?}
{?/if?}
    </select>
    </td></tr>
    </table>
    </td>
</tr>
<tr>
    <td>{?t?}Доп. информация:{?/t?} </td>
    <td>
    <table border=0 cellpadding=0 cellspacing=0>
    <tr><td style="padding:2px;">{?t?}Опубликовал:{?/t?} </td><td style="padding:2px;"> {?$book.uploaded_by?}</td></tr>
    <tr><td style="padding:2px;">{?t?}Дата публикации:{?/t?} </td><td style="padding:2px;"> {?$book.upload_date|date_format:"%d.%m.%Y %H:%M:%S"?}</td></tr>
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
        <td>{?php?}echo okbutton(_('Сохранить'),'','Ok','selectAllCategories();');{?/php?}</td>
        <td>{?php?}echo okbutton(_('Отмена'),'','Cancel',"document.location.href=\"{$GLOBALS['sitepath']}lib.php?page={$GLOBALS['page']}\"; return false;");{?/php?}</td>
    </tr>
</table>
</form>

<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/library.js"></script>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
