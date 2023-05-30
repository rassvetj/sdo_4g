<table cellpadding=4>
    <col width="40%">
    <col width="60%">
	{?if !$data.sheid?}
    <tr>
		<td colspan="2"><strong>{?t?}Внимание! Сеанс запущен в режиме просмотра. Используются настройки по умолчанию, оценка не выставляется. {?/t?}</strong></td>
	</tr>
    <tr><td colspan="2">&nbsp;</td></tr>
	{?/if?}   
    <tr>
        <td><strong>{?t?}Название задания:{?/t?}</strong></td>
        <td>{?$data.title?}</td>
    </tr>
    <tr>
        <td><strong>{?t?}Режим прохождения{?/t?}:</strong></td>
        <td>{?$data.mode?}</td>
    </tr>
    <tr>
        <td><strong>{?t?}Количество вариантов:{?/t?}</strong></td>
        <td>{?$data.questions?}</td>
    </tr>
    <tr>
        <td><strong>{?t?}Ограничение по времени, мин:{?/t?}</strong></td>
        <td>{?$data.timelimit?}</td>
    </tr>
    {?if $data.themes?}
    <tr>
        <td><strong>{?t?}Будут присутствовать варианты на следующие темы{?/t?}:</strong></td>
        <td>{?$data.themes?}</td>
    </tr>
    {?/if?}
    {?if $data.comments?}
    <tr><td colspan="2"><strong>{?t?}Комментарий{?/t?}:</strong></td></tr>
    <tr><td colspan="2">{?$data.comments|nl2br?}</td></tr>
    {?/if?}
	<tr><td colspan="2">&nbsp;</td></tr>    
    <tr>
        <td colspan=2>{?t?}Приступить к выполнению данного задания?{?/t?}</td>
    </tr>
</table>