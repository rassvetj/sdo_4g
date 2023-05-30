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

<form method="POST" id='mainEditForm' enctype="multipart/form-data">

<input type="hidden" name="edit_lib_item" value="edit_lib_item">

<input type="hidden" name="bid" value="{?$book.bid?}">
<input type="hidden" name="filename" value="{?$book.filename?}">
<input type="hidden" name="is_package" value="{?$book.is_package?}">
<input type="hidden" name="is_active_version" value="{?$book.is_active_version?}">

<table width=100% class=main cellspacing=0>
<tr>
    <th class="intermediate" colspan=2>{?t?}Редактировать данные{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Название{?/t?} </td>
    <td><input style="width: 300px" type="text" name="title" value="{?$book.title?}"></td>
</tr>
<tr>
    <td>{?t?}Краткое описание (аннотация){?/t?} </td>
    <td><textarea name="description" style="width: 300px;">{?$book.description?}</textarea></td>
</tr>
<tr>
    <td>{?t?}Ключевые слова{?/t?} </td>
    <td><input style="width: 300px" type="text" name="keywords" value="{?$book.keywords|escape?}"></td>
</tr>
<tr>
    <td>{?t?}Текстовой вариант{?/t?} (.txt) </td>
    <td>
    <input style="width: 300px" type="file" name="index" value="">
    {?$tooltip->display('material_txt')?}
    </td>
</tr>
{?if $book.versions?}
<tr>
    <td>{?t?}Версии:{?/t?} </td>
    <td>
    <table border=0 cellpadding=0 cellspacing=0>
    {?foreach name=versions from=$book.versions item=v?}
    <tr>
    <td title="{?$v.filename?}"><input type="radio" name="active_version" value="{?$v.bid?}" {?if $v.is_active_version?}checked{?/if?}></td>
    <td title="{?$v.filename?}">{?$v.upload_date|date_format:"%d.%m.%Y, %H:%M:%S"?}</td>
    <td>
    {?if $v.filename?}
    <a href="{?$sitepath?}library{?$v.filename?}" target=_blank title="{?t?}Просмотреть файл{?/t?}">
        <img border=0 src="{?$sitepath?}images/icons/save.gif">
    </a>
    {?/if?}
    </td>
    <td>
    {?if $v.bid!=$book.bid?}
    <a onClick="if (confirm('{?t?}Удалить версию?{?/t?}')) return true; else return false;" href="{?$sitepath?}lib_cms.php?bid={?$book.bid?}&action=edit&del={?$v.bid?}&page={?$page?}" title="{?t?}Удалить версию{?/t?}">
        <img border=0 src="images/icons/delete.gif">
    </a>
    {?/if?}
    </td>
    {?if !$counter?}
    <td rowspan={?$smarty.foreach.versions.total?}>
        {?assign var='counter' value='1'?}
        {?$tooltip->display('material_versions')?}
    </td>
    {?/if?}
    </tr>
    {?/foreach?}
    </table>
    </td>
</tr>
{?/if?}
<!--tr>
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
</tr-->
<tr>
    <td>{?t?}Доп. информация{?/t?} </td>
    <td>
    <table border=0 cellpadding=0 cellspacing=0>
    <tr><td style="padding:2px;">{?t?}Опубликовал{?/t?} </td><td style="padding:2px;"> {?$book.uploaded_by?}</td></tr>
    <tr><td style="padding:2px;">{?t?}Дата публикации{?/t?} </td><td style="padding:2px;"> {?$book.upload_date|date_format:"%d.%m.%Y %H:%M:%S"?}</td></tr>
    </table>
    </td>
</tr>
</table>
<table border="0" align="right">
    <tr>
        <td>{?$cancel?}</td>
        <td>{?$save?}</td>
    </tr>
</table>
</form>
<div class="clear-both">&nbsp;</div>
{?if !$book.is_package && !$book.type?}
<form id="editform" name="editform" method="POST" enctype="multipart/form-data">

<input type="hidden" name="add_item_version" value="add_item_version">
<input type="hidden" name="parent" value="{?$book.bid?}">
<table width=100% class=main cellspacing=0>
<tr>
    <th class="intermediate" colspan=2>{?t?}Добавить новую электронную версию материала{?/t?}</th>
</tr>
<tr>
    <td>
        <table>
            <tr>
                <td>{?t?}Новый файл{?/t?} </td>
                <td>
                    <input type="file" name="material" value="" />
                </td>
                <td rowspan="2">
                    <table border="0">
                        <tr>
                            <td>

                            </td>
                            <td>
                    {?$tooltip->display('material_version')?}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>{?t?}Из уже загруженных{?/t?} </td>
                <td>
                    <table border="0">
                        <tr>
                            <td>
                    <input id='book_file_name' type="text" style="width: 140px;" />
                    <input name='loaded_material' id='book_file_path' type="hidden" />
                            </td>
                            <td>
                                {?$addMaterialButton?}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
</table>
<table border="0" align="right">
    <tr>
        <td>{?$submit?}</td>
    </tr>
</table>

</form>
{?else?}

{?/if?}



<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/library.js"></script>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
