<form action="" method="POST" onSubmit="if($('#file').val()) {$('#filename').val($('#file').val());} return true;">
<input type="hidden" name="action" value="update">
<input type="hidden" name="cid" value="{?if $run?}{?$run->attributes.cid?}{?else?}{?$cid?}{?/if?}">
<input type="hidden" name="filename" value="{?$run->attributes.path|escape?}" id="filename">
<table width=100% class=main cellspacing=0>
<tr>
    <td>{?t?}Название{?/t?}</td>
    <td><input type="text" name="name" value="{?$run->attributes.name|escape?}" style="width: 300px" /></td>
</tr>
<tr>
    <td>{?t?}Локальный путь к файлу{?/t?}</td>
    <td><input id="file" type="text" name="file" value="{?$run->attributes.path|escape?}" style="width: 300px" />
    {?$tooltip->display('program_file')?}
    </td>
</tr>
<tr>
    <td colspan=99><strong>{?t?}Внимание!{?/t?}</strong> {?t?}Программа должна быть установлена на каждом клиентском компьютере.{?/t?}</td>
</tr>
</table>
<br>
    <table align='right'>
        <tr>
            <td>{?$cencelbutton?}</td>
            <td>{?$okbutton?}</td>
        </tr>        
    </table>
</form>
