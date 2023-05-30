<h3>Статистика отсутствий за предыдущую неделю</h3>

<table width=100% cellpadding=4 cellspacing=0 border=1 style="font-size: 11px;">
<tr>
    <th>ФИО</th>
    <th>Занятие</th>
    <th>Начало</th>
    <th>Конец</th>
    <th>Преподаватель</th>
</tr>
{?if $people?}
    {?foreach from=$people item=person?}
    <tr>
        <td>{?$person.lname?} {?$person.fname?} {?$person.mname?}</td>
        {?foreach name="schedules" from=$person.schedules item=she?}
        {?if !$smarty.foreach.schedules.first?}
        </tr><tr>
        <td></td>
        {?/if?}
        <td>{?$she.Title?}</td>
        <td>{?$she.begin|date_format:"%d.%m.%Y %H:%M:%S"?}</td>
        <td>{?$she.end|date_format:"%d.%m.%Y %H:%M:%S"?}</td>
        <td>{?$she.teacher_lname?} {?$she.teacher_fname?} {?$she.teacher_mname?}</td>
        {?/foreach?}
    </tr>
    {?/foreach?}
{?/if?}
</table>
