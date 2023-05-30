<div align="left">
<!--<h1>{?$course.department?}</h1>-->
<table width=100% class="main TD" cellspacing=0>
 <tr>
  <th  colspan="3">{?$course.department?}{?t?}Все преподаватели{?/t?}</th>
  <th><a href=# onclick="wopen('../teachers_load_print_get.php?date[from][year]={?$g_fr_year?}&date[from][month]={?$g_fr_month?}&date[from][day]={?$g_fr_day?}&date[to][year]={?$g_to_year?}&date[to][month]={?$g_to_month?}&date[to][day]={?$g_to_day?}&mid={?$g_mid?}')" title="{?t?}Печать{?/t?}">{?$print?}</a></th>
  <tr>
  <td>№<br />п/п</td>
  <td align="center"><b>{?t?}Фамилия И.О.{?/t?}</b></td>
  {?foreach from=$types_of_studies key=type_id item=type_of_studies?}
  <td style="writing-mode: tb-rl; filter:flipv() fliph();" nowrap valign="middle" align="center">{?$type_of_studies?}</td>
  {?/foreach?}
 </tr>
 {?counter name=counter_in_first_column start=0 skip=1 print=false?}
 {?foreach from=$teachers key=mid item=teacher?}
 <tr>
  <td>{?counter?}</td>
  <td>{?$teacher.first_name?} {?$teacher.last_name?}</td>
  {?foreach from=$types_of_studies key=type_id item=type_of_studies?}
  <td>{?if $count_hours.$mid.$type_id?}{?$count_hours.$mid.$type_id?}{?else?}0{?/if?}</td>
  {?/foreach?}
 </tr>
 {?/foreach?}
</table>
<br /><br />
</div>
