<script language="JavaScript" type="text/JavaScript">
function set_department_type(department_type){
	var number_of_claimants = 10;

	var department_type_title = new Array();
	department_type_title['regional'] = '{?t?}орган власти в Правительстве Ленинградской области{?/t?}';
	department_type_title['municipal'] = '{?t?}орган муниципальной власти{?/t?}';
	document.getElementById('greet').innerHTML = '<div  style="width:70%">{?t?}Укажите{?/t?} ' + department_type_title[department_type] + ', {?t?}которую Вы представляете, Вашу  Ф.И.О., Вашу должность, телефон и служебную электронную почту, а также Ф.И.О.{?/t?},  {?t?}должность, телефон и служебную электронную почту представителя кадровой служб, с которым согласована подача заявки на обучение.{?/t?}</div>';

	document.getElementById('ov').innerHTML = document.getElementById('ov_' + department_type).innerHTML;
	
	for(i = 0; i < number_of_claimants; i++) {
		force_code = '';
//(!i) ? '<a href="#" onClick="javascript:force_down(\'sel_department\')"><img src="{?$sitepath?}images/icons/note.gif" border="0" align="absmiddle"></a>' : '';
		document.getElementById('sel_department_' + i).innerHTML = '<select name="sel_department_"' + i + '"><option value="0"> - {?t?}выберите орган власти{?/t?} - </option>' + document.getElementById('sel_department_' + department_type).innerHTML + '</select>' + force_code;
	}
}
function force_down(field){
	var number_of_claimants = 10;
	force_value = document.getElementById(field + '_0').value ;
	for(i = 0; i < number_of_claimants; i++) {
		document.getElementById(field + '_' + i).value = force_value;
	}
	
}
</script>
<div class="hidden" id="ov_regional"><br>
      <table width=100% class=main cellspacing=0>
        <tr align="left" valign="top">
          <th> {?t?}Наименование органов власти в Правительстве ЛО{?/t?} </th>
          <th> {?t?}Ф.И.О. представителя ОГВ в Правительстве ЛО, который подает заявку на обучение{?/t?} </th>
          <th> {?t?}Должность{?/t?} </th>
          <th>{?t?}Тел. и служебная{?/t?} 
            {?t?}электронная{?/t?} 
          {?t?}почта, если есть{?/t?} </th>
          <th> {?t?}Ф.И.О. представителя кадровой службы в Правительстве ЛО, с которым согласована подача заявки на обучение{?/t?} </th>
          <th> {?t?}Должность{?/t?} </th>
          <th>{?t?}Тел. и служебная{?/t?} 
            {?t?}электронная{?/t?} 
          {?t?}почта, если есть{?/t?} </th>
        </tr>
{?foreach from=$departments.regional item=department?}
        <tr>
          <td>{?$department.name?}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
{?/foreach?}
      </table>
</div>
<div class="hidden" id="ov_municipal"><br>
      <table width=100% class=main cellspacing=0>
        <tr align="left" valign="top">
          <th>{?t?}Муниципальный район{?/t?}
          {?t?}городской округ{?/t?}</th>
          <th>{?t?}Муниципальное образование{?/t?}
          ({?t?}наименование){?/t?}</th>
          <th> {?t?}Ф.И.О. представителя ОГВ в Правительстве ЛО, который подает заявку на обучение{?/t?} </th>
          <th> {?t?}Должность{?/t?} </th>
          <th>{?t?}Тел. и служебная{?/t?} 
            {?t?}электронная{?/t?} 
          {?t?}почта, если есть{?/t?} </th>
          <th> {?t?}Ф.И.О. представителя кадровой службы в Правительстве ЛО, с которым согласована подача заявки на обучение{?/t?} </th>
          <th> {?t?}Должность{?/t?} </th>
          <th>{?t?}Тел. и служебная{?/t?} 
            {?t?}электронная{?/t?} 
          {?t?}почта, если есть{?/t?} </th>
        </tr>
{?foreach from=$departments.municipal item=department?}
        <tr>
          <td>{?$department.name?}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
{?/foreach?}
      </table>
</div>
<div class="hidden" id="sel_department_regional">
{?foreach from=$departments.regional item=department?}
<option value="{?$department.did?}">{?$department.name|truncate:30?}</option>
{?/foreach?}
</div>

<div class="hidden" id="sel_department_municipal">
{?foreach from=$departments.municipal item=department?}
<option value="{?$department.did?}">{?$department.name|truncate:30?}</option>
{?/foreach?}
</div>

{?t?}Тип регистрации:{?/t?}
<input name="department_type" type="radio" value="regional" checked onClick="javascript:set_department_type(this.value)"> 
{?t?}органы власти в правительстве ЛО{?/t?}
<input name="department_type" type="radio" value="municipal" onClick="javascript:set_department_type(this.value)"> 
  {?t?}муниципальные органы власти ЛО{?/t?}
<h3 align="justify" id="greet">&nbsp;</h3>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th>
<span id='plus_ov' class=shown onClick="putElem('ov');  removeElem('plus_ov'); putElem('minus_ov');" >
	  <span title='{?t?}показать{?/t?}' class=webdna style='cursor:hand'>4</span>
</span>
<span id='minus_ov' class=hidden onClick="removeElem('ov'); removeElem('minus_ov'); putElem('plus_ov');" >
	  <span title='{?t?}убрать{?/t?}' class=webdna style='cursor:hand'>6</span>
</span>
</th>
<th width="100%">{?t?}органы власти{?/t?}</th>
  </tr>
  <tr>
    <td id="ov" class="hidden" colspan="2">
</td>
  </tr>
</table><br>

<br>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th>
<span id='plus_courses' class=shown onClick="putElem('courses');  removeElem('plus_courses'); putElem('minus_courses');" >
	  <span title='{?t?}показать{?/t?}' class=webdna style='cursor:hand'>4</span>
</span>
<span id='minus_courses' class=hidden onClick="removeElem('courses'); removeElem('minus_courses'); putElem('plus_courses');" >
	  <span title='{?t?}убрать{?/t?}' class=webdna style='cursor:hand'>6</span>
</span>
</th>
<th width="100%">{?t?}действующие курсы обучения{?/t?}</th>
  </tr>
  <tr>
    <td id="courses" class="hidden" colspan="2"><br>
<table width=100% class=main cellspacing=0>
        <tr align="left" valign="top">
          <th>{?t?}Наименование{?/t?}
          {?t?}курсов обучения{?/t?} </th>
          <th>{?t?}Обучение{?/t?}
            {?t?}с поддержкой{?/t?}
          {?t?}преподавателей{?/t?} </th>
          <th> {?t?}Период{?/t?}
          {?t?}обучения{?/t?} </th>
          <th> {?t?}Дата{?/t?}
          {?t?}аттестации{?/t?} </th>
          <th>{?t?}Стоимость{?/t?}
            {?t?}обучения{?/t?}
            {?t?}одного{?/t?}
          {?t?}человека{?/t?} </th>
          <th> {?t?}Стоимость{?/t?}
            {?t?}аттестации{?/t?}
            {?t?}одного{?/t?}
          {?t?}человека{?/t?} </th>
        </tr>
{?foreach from=$courses item=course?}
        <tr>
          <td>{?$course.Title?}</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
{?/foreach?}
      </table>
</td>
  </tr>
</table>
<br>
<br>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
        <table width=100% class=main cellspacing=0>
          <tr align="left" valign="top">
            <th> {?t?}Наименование курса{?/t?}              </th>
            <th>{?t?}Ф.И.О.{?/t?}
                {?t?}обучаемого{?/t?} </th>
            <th>
              {?t?}Подразделение и должность{?/t?}</th>
            <th>{?t?}Категория{?/t?}
                {?t?}персонала{?/t?} </th>
            <th>
              {?t?}Тел.{?/t?},
              {?t?}служебная электронная почта, если есть{?/t?} </th>
            <th>{?t?}Обучение{?/t?}
            {?t?}с аттестацией{?/t?} </th>
            <th>{?t?}Период{?/t?}
            {?t?}обучения{?/t?} </th>
            <th> {?t?}Дата аттестации{?/t?} </th>
        </tr>
{?section name=customer loop=10 name=claimant?}
          <tr valign="top">
            <td nowrap><select name="sel_course_{?$smarty.section.claimant.index?}" id="sel_course_{?$smarty.section.claimant.index?}">
              <option value="0"> - {?t?}выберите курс{?/t?} - </option>
{?foreach from=$courses item=course?}
              <option value="{?$course.CID?}">{?$course.Title|truncate:30?}</option>
{?/foreach?}
            </select>
{?if !$smarty.section.claimant.index?}<a href="#" onClick="javascript:force_down('sel_course')"><img src="{?$sitepath?}images/icons/note.gif" border="0" align="absmiddle"></a>{?/if?}
			</td>
            <td nowrap><textarea name="txa_name_{?$smarty.section.claimant.index?}" rows="3" cols="30"></textarea></td>
            <td nowrap id="sel_department_{?$smarty.section.claimant.index?}">
			</td>
            <td nowrap><select name="sel_category_{?$smarty.section.claimant.index?}">
              <option value="a">А</option>
              <option value="b">Б</option>
              <option value="v">В</option>
            </select>
{?if !$smarty.section.claimant.index?}<a href="#" onClick="javascript:force_down('sel_category')"><img src="{?$sitepath?}images/icons/note.gif" border="0" align="absmiddle"></a>{?/if?}			
			</td>
            <td nowrap><textarea name="txa_contacts_{?$smarty.section.claimant.index?}" rows="3" cols="30"></textarea></td>
            <td align="center" nowrap><input name="ch_attestment_{?$smarty.section.claimant.index?}" type="checkbox" id="ch_attestment_{?$smarty.section.claimant.index?}" value="checkbox">
			</td>
            <td nowrap><input name="txt_period_{?$smarty.section.claimant.index?}3" type="text" id="txt_period_{?$smarty.section.claimant.index?}3" size="20"></td>
            <td nowrap><input name="txt_date_{?$smarty.section.claimant.index?}4" type="text" id="txt_date_{?$smarty.section.claimant.index?}4" size="20"></td>
          </tr>
{?/section?}
      </table></td>
  </tr>
<tr><td align="right"><br>
<input name="" type="submit" value="{?t?}Отправить заявку{?/t?}"></td></tr>
</table>
<script language="JavaScript" type="text/JavaScript">set_department_type('regional');</script>