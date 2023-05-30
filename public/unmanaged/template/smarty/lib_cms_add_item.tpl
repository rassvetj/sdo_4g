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
<input type="hidden" id="categories" name="cid" value="{?$cid?}">

<table width=100% class=main cellspacing=0>
<tr>
    <td>{?t?}Название{?/t?} <font color=red>*</font></td>
    <td><input style="width: 300px" type="text" name="title" value="{?$title?}"></td>
</tr>
<tr>
    <td>{?t?}Краткое описание (аннотация){?/t?} </td>
    <td><textarea name="description" style="width: 300px;"></textarea></td>
</tr>
<tr>
    <td>{?t?}Ключевые слова{?/t?} </td>
    <td><input style="width: 300px" type="text" name="keywords" value=""></td>
</tr>
<tr>
    <td>{?t?}Электронная версия материала{?/t?} </td>
    <td>
    <div id="ematerial">
    <input style="width: 300px" type="file" name="material" value="">
	{?$tooltip->display('mat_el_version')?}
    </div>
    <div id="tmaterial" style="display: none;">
    нет
    </div>
    </td>
</tr>
<tr>
    <td>{?t?}Или электронная версия материала из уже загруженных{?/t?} </td>
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
<tr>
    <td>{?t?}Текстовый вариант{?/t?} (.txt) </td>
    <td>
    <input style="width: 300px" type="file" name="index" value="">
	{?$tooltip->display('material_txt')?}
    </td>
</tr>
<!--tr>
    <td valign=top>{?t?}Курс{?/t?}</td>
    <td>
	<table border=0 cellpadding=0 cellspacing=0>
	<tr>
    <td>
    {?$categories?}
	</td>
	<td>
	{?$tooltip->display('mat_course')?}
	</td>
	</tr>
	</table>
    </td>
</tr-->
</table>
<table border="0" align="right">
    <tr>
        <td>{?$cancel?}</td>
        <td>{?$save?}</td>
    </tr>
</table>

<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/library.js"></script>
</form>
{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
