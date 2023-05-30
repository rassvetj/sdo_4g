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
<center>
<h3>{?t?}Учебная нагрузка преподавателей{?/t?}</h3>
<h4>{?t?}за период{?/t?} {? if $from|lower eq $to|lower ?}
{?$from|lower?}
{?else?}
{?$from|lower?} - {?$to|lower?}
{? /if ?}</h4>
</center>
<h1>{?$course.department?}</h1>
<table border="0" cellspacing="1" cellpadding="3">
 <tr>
  <td>№<br />п/п</td>
  <td align="center"><b>{?t?}Фамилия И.О.{?/t?}</b></td>
  {?foreach from=$types_of_studies key=type_id item=type_of_studies?}
  <td style="writing-mode: tb-rl; filter:flipv() fliph();" nowrap valign="middle" align="center">{?$type_of_studies?}&nbsp;</td>
  {?/foreach?}
 </tr>
 {?counter name=counter_in_first_column start=0 skip=1 print=false?}
 {?foreach from=$teachers key=mid item=teacher?}
 <tr>
  <td>{?counter?}&nbsp;</td>
  <td>{?$teacher.first_name?} {?$teacher.last_name?}&nbsp;</td>
  {?foreach from=$types_of_studies key=type_id item=type_of_studies?}
  <td>{?if $count_hours.$mid.$type_id?}{?$count_hours.$mid.$type_id?}&nbsp;{?else?}0{?/if?}</td>
  {?/foreach?}
 </tr>
 {?/foreach?}
</table>
<br /><br />
</div>
<pre>
{?include file="blank_footer.tpl"?}
</body>