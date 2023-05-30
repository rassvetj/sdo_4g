<form name="form1" method="post" action="" enctype="multipart/form-data" onSubmit="wopen('progress.php?id={?$progressId?}&title={?$progressTitle|urlencode?}&action={?$progressAction|urlencode?}','progress',400,200);">
<input name="progressId" type="hidden" value="{?$progressId?}">
<table width=100% class=main cellspacing=0>
  <tr>
    <th colspan="2" nowrap>{?t?}Синхронизация структуры: шаг{?/t?} 1</th>
  </tr>
  <tr>
    <td nowrap>{?t?}Укажите файл:{?/t?} </td>
    <td><input name="step" type="hidden" value="1"><input type="file" name="structure_new"></td>
  </tr>
  <tr>
    <td colspan="2" nowrap>{?$okbutton?}</td>
  </tr>
</table>
</form>