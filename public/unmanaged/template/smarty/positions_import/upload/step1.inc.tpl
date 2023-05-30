{?if $this->model->updates?}
<script language="javascript" type="text/javascript">
<!--
    function select_all_items(elm_prefix,checked) {
        var i=1;
        elm = document.getElementById(elm_prefix+'_'+(i++));
        while (elm) {
            elm.checked = checked;
            elm = document.getElementById(elm_prefix+'_'+(i++));
        }
    }
//-->
</script>
<form name="form1" method="post" action="" onSubmit="wopen('progress.php?id={?$progressId?}&title={?$progressTitle|urlencode?}&action={?$progressAction|urlencode?}','progress',400,200);">
<input name="progressId" type="hidden" value="{?$progressId?}">
<input name="step" type="hidden" value="2">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
		{?if $this->model->updates.StructureUpdatePersonDelete?}
		<h3>{?t?}Будут удалены (заблокированы) учетные записи{?/t?}</h3>
		<table width=100% class=main cellspacing=0>
		  <tr>
			<th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_structureupdatepersondelete',this.checked);" checked></th>		  
			<th nowrap>{?t?}Имя{?/t?}</th>
		  </tr>
		{?foreach from=$this->model->updates.StructureUpdatePersonDelete item=item key=key?}  
		  <tr>
			<td><input type="checkbox" id="ch_structureupdatepersondelete_{?$key+1?}" name="ch_structureupdatepersondelete[]" value="{?$item->get_unique_user_field()?}" checked></td>
			<td nowrap>{?$item->get_unique_user_name()?}</td>
		  </tr>
		{?/foreach?}
		</table><br>
	{?/if?}
	</td>
  </tr>
  <tr>
    <td>
		{?if $this->model->updates.StructureUpdatePersonAdd?}
		<h3>{?t?}Будут добавлены учетные записи{?/t?}</h3>
		<table width=100% class=main cellspacing=0>
		  <tr>
			<th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_structureupdatepersonadd',this.checked);" checked></th>		  
			<th nowrap>{?t?}Имя{?/t?}</th>
		  </tr>
		{?foreach from=$this->model->updates.StructureUpdatePersonAdd item=item key=key?}  
		  <tr>
			<td><input type="checkbox" id="ch_structureupdatepersonadd_{?$key+1?}" name="ch_structureupdatepersonadd[]" value="{?$item->get_unique_user_field()?}" checked></td>
			<td>{?$item->get_unique_user_name()?}</td>
		  </tr>
		{?/foreach?}
		</table><br>
	{?/if?}
	</td>
  </tr>
  <tr>
    <td>
		{?if $this->model->updates.StructureUpdatePersonAttribute?}
		<h3>{?t?}Будут изменены атрибуты учетных записей{?/t?}</h3>
		<table width=100% class=main cellspacing=0>
		  <tr>
			<th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_structureupdatepersonattribute',this.checked);" checked></th>		  
			<th nowrap>{?t?}Учетная запись{?/t?}</th>
			<th nowrap>{?t?}Свойство{?/t?}</th>		
			<th nowrap>{?t?}Текущее значение{?/t?}</th>		
			<th nowrap>{?t?}Новое значение{?/t?}</th>		
		  </tr>
		{?foreach from=$this->model->updates.StructureUpdatePersonAttribute item=item key=key?}  
		  <tr>
			<td><input type="checkbox" id="ch_structureupdatepersonattribute_{?$key+1?}" name="ch_structureupdatepersonattribute[]" value="{?$item->get_unique_user_field()?}" checked></td>
			<td nowrap>{?$item->get_unique_user_name()?}</td>
			<td>{?$item->get_key()?}</td>
			<td>{?$item->existing?}</td>
			<td>{?$item->get_new()?}</td>		
		  </tr>
		{?/foreach?}
		</table><br>
	{?/if?}
	</td>
  </tr>
  <tr>
    <td>
		{?if $this->model->updates.StructureUpdateDelete?}
		<h3>{?t?}Будут удалены орг. единицы{?/t?}</h3>
		<table width=100% class=main cellspacing=0>
		  <tr>
			<th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_structureupdatedelete',this.checked);" checked></th>		  
			<th nowrap>{?t?}Орг. еденица{?/t?}/{?t?}должность{?/t?}</th>
			<th nowrap>{?t?}В должности{?/t?}</th>		
		  </tr>
		{?foreach from=$this->model->updates.StructureUpdateDelete item=item key=key?}  
		  <tr>
			<td><input type="checkbox" id="ch_structureupdatedelete_{?$key+1?}" name="ch_structureupdatedelete[]" value="{?$item->get_unique_structure()?}" checked></td>
			<td nowrap>{?$item->attributes.name?}</td>
			<td>{?$item->get_unique_user_name()?}</td>
		  </tr>
		{?/foreach?}
		</table><br>
	{?/if?}
	</td>
  </tr>
  <tr>
    <td>
		{?if $this->model->updates.StructureUpdateAdd?}
		<h3>{?t?}Будут добавлены орг. единицы{?/t?}</h3>
		<table width=100% class=main cellspacing=0>
		  <tr>
			<th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_structureupdateadd',this.checked);" checked></th>		  		  
			<th nowrap>{?t?}Орг. еденица{?/t?}/{?t?}должность{?/t?}</th>
			<th nowrap>{?t?}В должности{?/t?}</th>		
		  </tr>
		{?foreach from=$this->model->updates.StructureUpdateAdd item=item key=key?}  
		  <tr>
			<td><input type="checkbox" id="ch_structureupdateadd_{?$key+1?}" name="ch_structureupdateadd[]" value="{?$item->get_unique_structure()?}" checked></td>		  
			<td nowrap>{?$item->attributes.name?}</td>
			<td>{?$item->get_unique_user_name()?}</td>
		  </tr>
		{?/foreach?}
		</table><br>
		{?/if?}
	</td>
  </tr>
  <tr>
    <td>
		{?if $this->model->updates.StructureUpdateAttribute?}
		<h3>{?t?}Будут измненены свойства орг. единиц{?/t?}</h3>
		<table width=100% class=main cellspacing=0>
		  <tr>
			<th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_structureupdateattribute',this.checked);" checked></th>		  		  
			<th nowrap>{?t?}Орг. еденица{?/t?}/{?t?}должность{?/t?}</th>
			<th nowrap>{?t?}Свойство{?/t?}</th>		
			<th nowrap>{?t?}Текущее значение{?/t?}</th>		
			<th nowrap>{?t?}Новое значение{?/t?}</th>		
		  </tr>
		{?foreach from=$this->model->updates.StructureUpdateAttribute item=item key=key?}  
		  <tr>
			<td><input type="checkbox" id="ch_structureupdateattribute_{?$key+1?}" name="ch_structureupdateattribute[]" value="{?$item->get_unique_structure()?}" checked></td>		  		  
			<td nowrap>{?$item->get_unique_structure()?}</td>
			<td>{?$item->get_key()?}</td>
			<td>{?$item->existing?}</td>
			<td>{?$item->get_new()?}</td>		
		  </tr>
		{?/foreach?}
		</table><br>
		{?/if?}
	</td>
  </tr>
  <tr>
    <td>
		{?if $this->model->updates.StructureUpdatePosition?}
		<h3>{?t?}Будут назначены{?/t?}</h3>
		<table width=100% class=main cellspacing=0>
		  <tr>
			<th nowrap><input type="checkbox" title="{?t?}Отметить{?/t?}/{?t?}снять все галочки{?/t?}" onClick="select_all_items('ch_structureupdateposition',this.checked);" checked></th>		  		  
			<th nowrap>{?t?}Орг. еденица{?/t?}/{?t?}должность{?/t?}</th>
			<th nowrap>{?t?}Назначен сейчас{?/t?}</th>		
			<th nowrap>{?t?}Будет назначен{?/t?}</th>		
		  </tr>
		{?foreach from=$this->model->updates.StructureUpdatePosition item=item key=key?}  
		  <tr>
			<td><input type="checkbox" id="ch_structureupdateposition_{?$key+1?}" name="ch_structureupdateposition[]" value="{?$item->get_unique_structure()?}" checked></td>		  		  
			<td nowrap>{?$item->attributes.name?}</td>
			<td>{?$item->get_unique_user_name($item->person_existing)?}</td>
			<td>{?$item->get_unique_user_name()?}</td>
		  </tr>
		{?/foreach?}
		</table><br>
		{?/if?}
	</td>
  </tr>
	  <tr>
		<td align="right">
		<img src="template/smarty/skins/default/images/b_cancel.gif" border="0" class="noborder" onClick="javascript:history.back()">&nbsp;
		<input type="image" name="ok" value="" src="template/smarty/skins/default/images/ok.gif" border="0" class="noborder"></td>
	  </tr>
</table>
</form>	
{?/if?}