<script type="text/javascript" language="JavaScript">
<!--

       function selectByPrefix(prefix, total, postfix, checked) {
           var i = 1;
           var j = 1;
           var elm;

           for(i = 1; i <= total; i++) {
               j = 1;
               while(elm = document.getElementById(prefix+'_'+i+'_'+postfix+'_'+j)) {
                   elm.checked = checked;
                   j++;
               }
           }
       }

       function selectLine(prefix, checked) {
           var i = 1;
           var elm;
           while(elm = document.getElementById(prefix+'_1_'+i)) {
               elm.checked = checked;
               i++;
           }

           i = 1;
           while(elm = document.getElementById(prefix+'_2_'+i)) {
               elm.checked = checked;
               i++;
           }

           i = 1;
           while(elm = document.getElementById(prefix+'_3_'+i)) {
               elm.checked = checked;
               i++;
           }

           i = 1;
           while(elm = document.getElementById(prefix+'_4_'+i)) {
               elm.checked = checked;
               i++;
           }
       }

       function selectAll(prefix, total, checked) {
           selectByPrefix(prefix, total, '1', checked);
           selectByPrefix(prefix, total, '2', checked);
           selectByPrefix(prefix, total, '3', checked);
           selectByPrefix(prefix, total, '4', checked);

           var i=1;
           var elm;
           while(elm = document.getElementById(prefix+'_'+i)) {
               elm.checked = checked;
               i++;
           }

           if (elm = document.getElementById('selectPeople_'+prefix)) {
               elm.checked = checked;
           }
           if (elm = document.getElementById('selectBosses_'+prefix)) {
               elm.checked = checked;
           }
           if (elm = document.getElementById('selectCollegues_'+prefix)) {
               elm.checked = checked;
           }
           if (elm = document.getElementById('selectSubordinates_'+prefix)) {
               elm.checked = checked;
           }
       }

       function checkForm() {
           var elm;

           if (elm = document.getElementById('poll_name')) {
               if ((elm.value == '') && (!elm.disabled)) {
                   alert('{?t?}Введите название аттестации{?/t?}');
                   return false;
               }
           }

           if (elm = document.getElementById('event')) {
               if ((elm.value == 0) && (!elm.disabled)) {
                   alert('{?t?}Выберите тип занятия{?/t?}');
                   return false;
               }
           }

           return true;
       }
//-->
</script>

<form action="" method="POST" onSubmit="return checkForm();">
<input type="hidden" name="action" value="assign" id="action">
{?if $events?}
    {?foreach name="events" from=$events item=event?}
    {?if $smarty.foreach.events.first?}
    <input type="hidden" name="event" value="{?$event.id|escape?}">
    {?/if?}
    {?/foreach?}
{?/if?}
{?if $items?}
	{?foreach name="units" from=$items key=owner_soid item=persons?}
	    {?if $persons?}
	        <table width=100% class=main cellspacing=0>
	        {?foreach name="persons" from=$persons item=person?}
	            {?if $smarty.foreach.persons.first?}
	            {?if $person->attributes.owner_name?}
	            <tr>
	                <!--th><input type="checkbox" checked onClick="checkSlaves('{?$person->attributes.owner_soid?}',this.checked)"></th-->
	                <th colspan=99>{?$person->attributes.owner_name|escape?}</th>
	            </tr>
	            {?/if?}
	            <tr>
	            	<th><input checked type='checkbox' onClick="selectAll('item_{?$smarty.foreach.units.iteration?}',{?$smarty.foreach.persons.total?},checked);"> {?t?}Оценку данного сотрудника назначить{?/t?}</th>
	                {?if $allow_self?}<th><input checked type='checkbox' name='selectPeople_item_{?$smarty.foreach.units.iteration?}' id='selectPeople_item_{?$smarty.foreach.units.iteration?}' onClick="selectByPrefix('item_{?$smarty.foreach.units.iteration?}',{?$smarty.foreach.persons.total?},'1',checked)"> {?t?}Самому сотруднику{?/t?}</th>{?/if?}
	                {?if $allow_boss?}<th><input checked type='checkbox' name='selectBosses_item_{?$smarty.foreach.units.iteration?}' id='selectBosses_item_{?$smarty.foreach.units.iteration?}' onClick="selectByPrefix('item_{?$smarty.foreach.units.iteration?}',{?$smarty.foreach.persons.total?},'2',checked)"> {?t?}Его руководителю{?/t?}</th>{?/if?}
	                {?if $allow_colleg?}<th><input checked type='checkbox' name='selectCollegues_item_{?$smarty.foreach.units.iteration?}' id='selectCollegues_item_{?$smarty.foreach.units.iteration?}' onClick="selectByPrefix('item_{?$smarty.foreach.units.iteration?}',{?$smarty.foreach.persons.total?},'3',checked)"> {?t?}Коллегам{?/t?}</th>{?/if?}
	                {?if $allow_subord?}<th><input checked type='checkbox' name='selectSubordinates_item_{?$smarty.foreach.units.iteration?}' id='selectSubordinates_item_{?$smarty.foreach.units.iteration?}' onClick="selectByPrefix('item_{?$smarty.foreach.units.iteration?}',{?$smarty.foreach.persons.total?},'4',checked)"> {?t?}Подчиненным{?/t?}</th>{?/if?}
	            </tr>
	            {?/if?}
                <tr>
                    <td><input checked id="item_{?$smarty.foreach.units.iteration?}_{?$smarty.foreach.persons.iteration?}" type='checkbox' onClick="selectLine('item_{?$smarty.foreach.units.iteration?}_{?$smarty.foreach.persons.iteration?}',checked);"> {?$person->attributes.person|escape?}</td>
                    {?if $allow_self?}<td><input checked id="item_{?$smarty.foreach.units.iteration?}_{?$smarty.foreach.persons.iteration?}_1_1" type="checkbox" name="assigned_mids[{?$person->attributes.soid?}][]" value="{?$person->attributes.mid?}"> {?$person->attributes.person|escape?}</td>{?/if?}
                    {?if $allow_boss?}<td>
                        {?if $person->attributes.boss && $person->attributes.boss.mid?}
                            <input checked id="item_{?$smarty.foreach.units.iteration?}_{?$smarty.foreach.persons.iteration?}_2_1" type="checkbox" name="assigned_mids[{?$person->attributes.soid?}][]" value="{?$person->attributes.boss.mid?}"> {?$person->attributes.boss.lastname|escape?} {?$person->attributes.boss.firstname|escape?}
                        {?/if?}
                    </td>{?/if?}
                    {?if $allow_colleg?}<td>
                        {?if $person->attributes.collegues?}
                            {?foreach name="collegues" from=$person->attributes.collegues item=collegue?}
                                <input checked id="item_{?$smarty.foreach.units.iteration?}_{?$smarty.foreach.persons.iteration?}_3_{?$smarty.foreach.collegues.iteration?}" type="checkbox" name="assigned_mids[{?$person->attributes.soid?}][]" value="{?$collegue.mid?}"> {?$collegue.lastname|escape?} {?$collegue.firstname|escape?}<br>
                            {?/foreach?}
                        {?/if?}
                    </td>{?/if?}
                    {?if $allow_subord?}<td>
                        {?if $person->attributes.subordinates?}
                            {?foreach name="subordinates" from=$person->attributes.subordinates item=subordinate?}
                                <input checked id="item_{?$smarty.foreach.units.iteration?}_{?$smarty.foreach.persons.iteration?}_4_{?$smarty.foreach.subordinates.iteration?}" type="checkbox" name="assigned_mids[{?$person->attributes.soid?}][]" value="{?$subordinate.mid?}"> {?$subordinate.lastname|escape?} {?$subordinate.firstname|escape?}<br>
                            {?/foreach?}
                        {?/if?}
                    </td>{?/if?}
                </tr>
	        {?/foreach?}
	        </table>
	        <br>
	    {?/if?}
	{?/foreach?}
{?else?}
<table width=100% class=main cellspacing=0>
<tr>
    <td colspan=99 align=center>{?t?}Не выбрано ни одного элемента{?/t?}</td>
</tr>
</table>
{?/if?}

{?foreach from=$step1 key=key item=value?}
<input type="hidden" value="{?$value?}" name="{?$key?}">
{?/foreach?}

<table width=100% border=0 cellpadding=10 cellspacing=1>
<tr>
<td align="right" width="100%" class="button-option">
&nbsp;</td>
<td>
<div class="button ok" style="float: right;">
<a onclick="return eLS.utils.form.submit(this);" href="javascript:void(0);" id="button-ok">Готово</a>
</div>
</td>
</tr>
</table>
</form>