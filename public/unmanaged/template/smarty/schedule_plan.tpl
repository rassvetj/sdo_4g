<html>
 <style>
  td {
  font-size: 10pt;
  font-family: Courier New;
  
 
  }
 </style>
<body>
<pre>
{?t?}Утверждаю

Должность{?/t?}__________________


«____»_____________{?$current_year?} г.

                          <b>{?t?}ПЛАН ЗАНЯТИЙ{?/t?}</b>

1.<b>{?t?}Вид занятия:{?/t?}</b> {?$schedule.type?}

2.<b>{?t?}Тема:{?/t?}</b> {?$schedule.subject?}

3.<b>{?t?}Учебные цели:{?/t?}</b> {?foreach from=$schedule.targets item=target?} {?$target?} {?/foreach?}


4.<b>{?t?}Время:{?/t?}</b> {?if $schedule.time neq ""?} {?$schedule.time.begin?} - {?$schedule.time.end?} {?/if?}


<table border="1" cellspacing="0" cellpadding="3" width="600">
    <tr>
     <td align="center">{?t?}Учебная группа{?/t?}</td><td align="center">{?t?}Дата{?/t?}</td><td align="center">{?t?}Учебные часы{?/t?}</td><td align="center">{?t?}Место проведения занятия{?/t?}</td>
    </tr>
    
    <tr>
     <td>&nbsp;</td><td align="center">{?$schedule.date?}</td><td align="center">{?$schedule.period?}</td><td align="center">{?$schedule.room?}</td>
    </tr>
    
</table>

5. <b>{?t?}Плановая таблица{?/t?}</b>

<table border="1" cellspacing="0" cellpadding="3" width="600">
    <tr>
     <td align="center">№ п/п</td>
     <td align="center">{?t?}Основные вопросы темы{?/t?}</td>
     <td align="center">{?t?}Ориентировочное время минут{?/t?}</td>
     <td align="center">{?t?}Методические указания{?/t?}</td>
    </tr>
    <tr>
     <td align="center">1</td><td align="left"><b>{?t?}контрольный опрос по теме предыдущего занятия{?/t?}</b></td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr height="300">
     <td align="center">2</td><td align="left" valign="top"><b>{?t?}Вводная часть{?/t?}</b></td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    <tr>
     <td align="center">3</td><td align="left"><b>{?t?}Основные вопросы темы (отрабатываемые эпизоды, приемы, действия, вводные){?/t?}</b></td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
    {?foreach from=$schedule.studiedproblems key=number item=studiedproblem?}
    <tr>
     <td align="center">3.{?$number+1?}</td>
     <td>
        <b>{?$studiedproblem.title?}</b>
     </td>
     <td>&nbsp;</td><td>&nbsp;</td>
    </tr>
      {?foreach from=$studiedproblem.texts item=text?}
       <tr>
        <td>&nbsp;</td><td>{?$text?}</td><td>&nbsp;</td><td>&nbsp;</td>
       </tr>
      {?/foreach?}
    {?/foreach?}
    <tr height="300">
     <td align="center">4</td><td align="left" valign="top"><b>{?t?}Заключительная часть{?/t?}</b></td><td>&nbsp;</td><td>&nbsp;</td>
    </tr>
</table>



"__" __________ {?$current_year?} г.        {?t?}Руководитель занятия{?/t?}
                                                
                                            
                {?t?}Фамилия{?/t?}


</pre>
</body>
</html>