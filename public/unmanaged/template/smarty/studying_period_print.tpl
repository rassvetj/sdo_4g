{?foreach from=$periods item=period name=periods?}{?/foreach?}
<html>
<title>{?t?}Классный журнал{?/t?}</title>
<head>
<link rel="stylesheet" href="styles/style_print.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
</head>
<body>
<div align="center">
  <p><b>{?t?}Классный журнал{?/t?}</b><br>
  <b>{?$program?}</b></p>
<!--  <p align="left">{?t?}Группа:{?/t?} {?$group?} <br> {?t?}Старший:{?/t?} {?$group_leader?} <br> -->
  </p>
</div>
<table width="100%"  border="1" cellpadding="5" cellspacing="1">
<!--
  <tr bgcolor="#FFFFFF">
    <td colspan="2"> {?t?}Учебная неделя{?/t?} </td>
    <td colspan="{?$smarty.foreach.periods.total*$tabledays?}" align="center">{?$week?}</td>
  </tr>
-->
  <tr bgcolor="#FFFFFF">
    <td colspan="2"> {?t?}Дата{?/t?} </td>
    <td colspan="{?$smarty.foreach.periods.total*$tabledays?}" align="center">{?$dates?}</td>
  </tr>
  <tr bgcolor="#FFFFFF">
    <td colspan="2">{?t?}Дни недели{?/t?} </td>
{?foreach from=$days item=day?}
    <td colspan="{?$smarty.foreach.periods.total?}" align="center">{?$day?}</td>
{?/foreach?}
  </tr>
  <tr bgcolor="#FFFFFF">
    <td colspan="2">{?t?}Учебные часы{?/t?}</td>
{?foreach from=$days item=day?}
{?foreach from=$periods item=period?}
    <td nowrap style="writing-mode: tb-rl; filter:flipv() fliph();">{?$period.name?}</td>
{?/foreach?}
{?/foreach?}  </tr>
  <tr bgcolor="#FFFFFF">
    <td colspan="2">{?t?}Вид занятия{?/t?} </td>
{?foreach from=$days key=day item=day_item?}
{?foreach from=$periods key=period item=period_item?}
    <td nowrap style="writing-mode: tb-rl; filter:flipv() fliph();">{?$journal.schedules.$day.$period.kindnum?}{?if $journal.schedules.$day.$period?}{?$journal.schedules.$day.$period.title?}{?else?}&nbsp;{?/if?}</td>
{?/foreach?}
{?/foreach?}  </tr>
  <tr bgcolor="#FFFFFF">
    <td style="writing-mode: tb-rl; filter:flipv() fliph();" align="center">№ п/п </td>
    <td>{?t?}Преподаватели{?/t?}/{?t?}обучаемые{?/t?}</td>
    {?foreach from=$days key=day item=day_item?}
{?foreach from=$periods key=period item=period_item?}
    <td style="writing-mode: tb-rl; filter:flipv() fliph();">{?if $journal.schedules.$day.$period?}{?$journal.schedules.$day.$period.teacher?}{?else?}&nbsp;{?/if?}</td>
{?/foreach?}
{?/foreach?}  </tr>
{?if $journal.schedules?}
{?foreach from=$journal.students item=student name=students?}
  <tr bgcolor="#FFFFFF">
    <td>{?$smarty.foreach.students.iteration?}</td>
    <td>{?$student.name?}</td>
{?foreach from=$days key=day item=day_item?}
{?foreach from=$periods key=period item=period_item?}
    <td align="center">
    {?if $student.marks.$day.$period == -2?}Б{?/if?}
    {?if $student.marks.$day.$period == -3?}Н{?/if?}
    {?if $student.marks.$day.$period > 0?}{?$student.marks.$day.$period?}{?else?}&nbsp;{?/if?}
    </td>
{?/foreach?}
{?/foreach?}
  </tr>
{?/foreach?}
{?else?}
    <td colspan="100" align="center">{?t?}Нет ни одного занятия{?/t?}</td>
{?/if?}
  <tr bgcolor="#FFFFFF">
    <td colspan="2">{?t?}Подпись преподавателя{?/t?}<br><br><br></td>
{?foreach from=$days key=day item=day_item?}
{?foreach from=$periods key=period item=period_item?}
    <td>&nbsp;</td>
{?/foreach?}
{?/foreach?}
  </tr>
</table>

</body>
</html>
