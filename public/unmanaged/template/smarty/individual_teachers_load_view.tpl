<div align="left">
<table width=100% class="main TD" cellspacing=0>
 <tr>
  <th colspan="3">{?t?}Учебная нагрузка преподавателя{?/t?} {?$teacher.last_name?}</th>
  <th><a href=# onclick="wopen('../teachers_load_print_get.php?date[from][year]={?$g_fr_year?}&date[from][month]={?$g_fr_month?}&date[from][day]={?$g_fr_day?}&date[to][year]={?$g_to_year?}&date[to][month]={?$g_to_month?}&date[to][day]={?$g_to_day?}&mid={?$g_mid?}')" title="{?t?}Печать{?/t?}"><div align="right">{?$print?}</div></a></th>
 </tr>
 <tr>
  <td colspan="4">{?t?}за период{?/t?} {?$from|lower?} - {?$to|lower?}</td>
 </tr>
 <tr>
   <td align="center"><b>{?t?}Учебные недели,{?/t?}<br />{?t?}месяц{?/t?}</b></td>
   {?foreach from=$types_of_studies key=type_id item=type_of_studies?}
   <td style="writing-mode: tb-rl; filter:flipv() fliph();" nowrap valign="middle" align="center">{?$type_of_studies?}</td>
   {?/foreach?}
   <td align="center">{?t?}Суммарная{?/t?} <br />{?t?}учебная нагрузка{?/t?}</td>
  </tr>
  {?foreach from=$time_marks key=year item=year_marks name=time_marks?}
    <tr>
     <td colspan="{?$count_of_types+2?}"><strong>{?$year?} {?t?}год{?/t?}</strong></td>
    </tr>
    {?foreach from=$year_marks key=month_number item=month?}
    <tr>
     <td>{?$month?}</td>
     {?foreach from=$types_of_studies key=type_id item=type_of_studies?}
     <td align="center">{?if $count_hours.$year.$month_number.$type_id?}{?$count_hours.$year.$month_number.$type_id?}{?else?}0{?/if?} </td>
     {?/foreach?}
     <td align="center">{?if $sum_count_hours.$year.$month_number?}{?$sum_count_hours.$year.$month_number?}{?else?}0{?/if?}</td>
    </tr>
    {?/foreach?}
  {?/foreach?}


 </table>
</div>