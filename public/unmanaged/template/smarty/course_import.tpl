<form enctype='multipart/form-data' method=post action='{?$sitepath?}teachers/organization_exp.php?send=1&cid={?$CID?}' onSubmit="{?if $materialsExist?}if (!confirm('{?t?}Данная операция удалит существующую структуру курса и все сопутствующие материалы. Продолжить?{?/t?}')) return false;{?/if?}wopen('{?$sitepath?}progress.php?id={?$progressId?}&title={?$progressTitle|urlencode?}&action={?$progressAction|urlencode?}&comments={?$progressComments|urlencode?}&upload=true','progress',400,200);">
<input type='hidden' name='progressId' value='{?$progressId?}'>
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=2>{?t?}Импорт курса{?/t?}</th>
</tr>
<tr>
    <td nowrap>{?t?}Формат пакета{?/t?} </td>
    <td>
	    <select name="import_type" id="import_type" onChange="jQuery('#test_delete').get(0).disabled = false; jQuery('#materials_delete').get(0).disabled = false; if (this.value == '{?$smarty.const.IMPORT_TYPE_ZIP?}') {jQuery('#test_delete').get(0).disabled = true; jQuery('#materials_delete').get(0).disabled = true;} document.getElementById('ch_info_text').innerHTML = (this.value == {?$smarty.const.IMPORT_TYPE_ZIP?}) ? '&nbsp;{?t?}зарегистрировать каждый файл как отдельный материал{?/t?}' :'&nbsp;{?t?}переписать заголовок курса{?/t?}'">
			<option value="0">---</option>
			<option value="{?$smarty.const.IMPORT_TYPE_EAU3?}">{?t?}публикация eAuthor 3{?/t?}</option>
			<option value="{?$smarty.const.IMPORT_TYPE_SCORM?}">{?t?}пакет SCORM{?/t?}</option>
			<option value="{?$smarty.const.IMPORT_TYPE_AICC?}">{?t?}пакет AICC{?/t?}</option>
		{?if $perm!=3?}
			<option value="{?$smarty.const.IMPORT_TYPE_ZIP?}">ZIP-архив</option>
		{?/if?}
	    </select>&nbsp;&nbsp;{?$tooltip->display_variable('course_import_zip', 'import_type', 5)?}
    </td>
</tr>
<tr>
    <td nowrap>{?t?}Файл пакета{?/t?} </td>
    <td><input name=zipfile type=file></td>
</tr>
<tr>
    <td class=schedule colspan=2>
        <input name='ch_info' type='checkbox' id='ch_info' value='1'>
        <span id="ch_info_text">&nbsp;{?t?}переписать заголовок курса{?/t?}</span>
        <br />
    </td>
</tr>
<tr>
    <td colspan=2 align=right>{?$okbutton?}</td>
</tr>
</table>
</form>
