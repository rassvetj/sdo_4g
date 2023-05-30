<html>
<head>
<link rel="stylesheet" href="styles/style_print.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251"></head>
<body>
<pre>
            <b>{?t?}РАСПИСАНИЕ ЗАНЯТИЙ ГРУПП{?/t?}</b>
            
 {?foreach from=$smarty_array item=smarty_item?}
                с {?$smarty_item.begin_day?} {?t?}по{?/t?} {?$smarty_item.end_day?}
                
<table width="600" border="1" cellspacing="0" cellpadding="6">
 <tr>
  <td style="writing-mode: tb-rl; filter:flipv() fliph();" align="center" nowrap>{?t?}Дата{?/t?}</td>
  <td style="writing-mode: tb-rl; filter:flipv() fliph();" align="center" nowrap>{?t?}Дни недели{?/t?}</td>
  <td style="writing-mode: tb-rl; filter:flipv() fliph();" align="center" nowrap>{?t?}Часы занятий{?/t?}</td>
  <td align="center" nowrap>{?t?}Код занятия{?/t?}</td>
  <td align="center" nowrap>{?t?}Руководитель занятия{?/t?}</td>
  <td align="center" nowrap>{?t?}Место проведения{?/t?}</td>
 </tr>
 {?foreach from=$smarty_item.week_schedule item=day_schedule?}
    {?if $day_schedule.count_studies neq "0"?}
        {?foreach from=$day_schedule.studies key=key item=study name="studies"?}
        <tr>
            {?if $smarty.foreach.studies.first?}
                <td align="center" {?if $day_schedule.count_studies neq "1"?} rowspan="{?$day_schedule.count_studies?}"{?/if?} style="writing-mode: tb-rl; filter:flipv() fliph();" align="center" nowrap>{?$day_schedule.date?}</td>
                <td align="center" {?if $day_schedule.count_studies neq "1"?} rowspan="{?$day_schedule.count_studies?}"{?/if?} style="writing-mode: tb-rl; filter:flipv() fliph();" align="center" nowrap>{?$day_schedule.day_name?}</td>
            {?/if?}
            <td nowrap>{?$study.period?}&nbsp;</td><td>{?$study.kindnum?}{?$study.name?}&nbsp;</td><td>{?$study.teacher?}&nbsp;</td><td>{?$study.room?}&nbsp;</td>
        </tr>
        {?/foreach?}
    {?elseif $day_schedule.count_studies eq "0"?}
        <tr>
         <td align="center" style="writing-mode: tb-rl; filter:flipv() fliph();" align="center" nowrap>{?$day_schedule.date?}</td>
         <td align="center" style="writing-mode: tb-rl; filter:flipv() fliph();" align="center" nowrap>{?$day_schedule.day_name?}</td>
         <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
        </tr>
    {?/if?}
 {?/foreach?} 
</table>
<br><br>
{?/foreach?}

{?include file="blank_footer.tpl"?}
</pre>
</body>
</html>