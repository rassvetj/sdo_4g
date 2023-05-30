<html>
 <head>
 <title>{?t?}Предварительный просмотр{?/t?}</title>
  <style>
   td {
       background-color: white;
   }
   table {
      background-color: black;
   }
   body, td {
           font-family: Verdana;
           font-size: 10pt;
   }
  </style>
 <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"></head>
<body>
<div align="center">
 <h3>{?t?}Учебная нагрузка преподавателя{?/t?} {?$teacher.last_name?}</h3>
<h4>{?t?}за период{?/t?} {?if $from|lower eq $to|lower?}
{?$from|lower?}
{?else?}
{?$from|lower?} - {?$to|lower?}
{?/if?}</h4>
 <table border="0" cellspacing="1" cellpadding="3">
  <tr>
   <td align="center"><b>{?t?}Учебные недели,{?/t?}<br />{?t?}месяц{?/t?}</b></td>
   {?foreach from=$types_of_studies key=type_id item=type_of_studies?}
   <td style="writing-mode: tb-rl; filter:flipv() fliph();" nowrap valign="middle" align="center">{?$type_of_studies?}</td>
   {?/foreach?}
   <td align="center">{?t?}Суммарная{?/t?} <br />{?t?}учебная нагрузка{?/t?}</td>
  </tr>
  {?foreach from=$time_marks key=year item=year_marks name=time_marks?}
    <tr>
     <td colspan="{?$count_of_types+4?}"><strong>{?$year?} {?t?}год{?/t?}</strong></td>
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
</body>
</html>
