<html>
<head>
  <link rel="stylesheet" href="{?$sitepath?}styles/report.css" type="text/css">
</head>
<body>
<center>
<b>{?t?}РАСПИСАНИЕ ЗАНЯТИЙ ГРУПП{?/t?}</b><br>

{?t?}с{?/t?} {?$begin_day|date_format:"%d.%m.%Y"?} {?t?}по{?/t?} {?$end_day|date_format:"%d.%m.%Y"?}<br>
</center>
<p>
<table width="600" border="0" cellspacing="0" cellpadding="4" class="print_report_table" align=center>
 <tr>
  <th align="center" nowrap>{?t?}Дата{?/t?}</th>
  <th align="center" nowrap>{?t?}День недели{?/t?}</th>
  <th align="center" nowrap>{?t?}Часы{?/t?}</th>
  <th align="center" nowrap>{?t?}Название занятия{?/t?}</th>
  <th align="center" nowrap>{?t?}Преподаватель{?/t?}</th>
  <th align="center" nowrap>{?t?}Место проведения{?/t?}</th>
 </tr>
 {?foreach from=$week_schedule item=day_schedule?}
    {?if $day_schedule.count_studies neq "0"?}
        {?foreach name="studies" from=$day_schedule.studies key=key item=study?}
        <tr>
            {?if $smarty.foreach.studies.first?}
                <td align="center" {?if $day_schedule.count_studies neq "1"?} rowspan="{?$day_schedule.count_studies?}"{?/if?} align="center" nowrap>&nbsp;{?$day_schedule.date?}</td>
                <td align="center" {?if $day_schedule.count_studies neq "1"?} rowspan="{?$day_schedule.count_studies?}"{?/if?} align="center" nowrap>&nbsp;{?$day_schedule.day_name?}</td>
            {?/if?}
            <td>&nbsp;{?$study.period?}</td><td>&nbsp;{?$study.name?}</td><td>&nbsp;{?$study.teacher?}</td><td>&nbsp;{?$study.room?}</td>
        </tr>
        {?/foreach?}
    {?elseif $day_schedule.count_studies eq "0"?}
        <tr>
         <td align="center" align="center" nowrap>&nbsp;{?$day_schedule.date?}</td>
         <td align="center" align="center" nowrap>&nbsp;{?$day_schedule.day_name?}</td>
         <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        </tr>
    {?/if?}
 {?/foreach?}
</table>
<script type="text/javascript">
<!--
window.print();
//-->
</script>
</body>
</html>