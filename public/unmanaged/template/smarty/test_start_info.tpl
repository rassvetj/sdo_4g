<table cellpadding=4>
    <col width="45%">
    <col width="55%">
	{?if !$data.sheid?}
    <tr>
		<td colspan="2"><strong>{?t?}Внимание! Сеанс запущен в режиме просмотра. Используются настройки по умолчанию, оценка не выставляется. {?/t?}</strong></td>
	</tr>
    <tr><td colspan="2">&nbsp;</td></tr>
	{?/if?}
	<tr>
        <td><strong>{?t?}Название{?/t?}:</strong></td>
        <td>{?$data.title?}</td>
    </tr>
    <tr>
        <td><strong>{?t?}Режим прохождения{?/t?}:</strong></td>
        <td>{?$data.mode?}</td>
    </tr>
    <tr>
        <td><strong>{?t?}Количество вопросов:{?/t?}</strong></td>
        <td>{?$data.questions?}</td>
    </tr>			
    <tr>
        <td><strong>{?t?}Ограничение по времени, мин:{?/t?}</strong></td>
        <td>{?$data.timelimit?}</td>
    </tr>
    {?if $data.themes?}
    <tr>
        <td><strong>{?t?}Будут заданы вопросы на следующие темы{?/t?}:</strong></td>
        <td>{?$data.themes?}</td>
    </tr>
    {?/if?}
    {?if $data.comments?}
    <tr><td colspan="2"><strong>{?t?}Комментарий{?/t?}:</strong></td></tr>
    <tr><td colspan="2">{?$data.comments|nl2br?}</td></tr>
    {?/if?}
	<tr>
		<td colspan="2">
			<table style="width:100%; border-spacing: 0px; border-collapse: collapse; text-align: center;">
				<tr style="font-weight: bold; align-items: center; margin: 0 auto;"><td style="border: 1px solid black; padding: 3px;">Количество попыток</td><td style="border: 1px solid black; padding: 3px;">Израсходовано</td><td style="border: 1px solid black; padding: 3px;">Осталось</td></tr>
				<tr><td style="border: 1px solid black; padding: 3px;">{?$data.startlimit?}</td><td style="border: 1px solid black; padding: 3px;">{?$data.attempts_spent?}</td><td style="border: 1px solid black; padding: 3px;">{?$data.attempts_remaining?}</td></tr>
			</table>
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>    
    <tr>
        <td colspan=2>{?t?}Приступить к выполнению?{?/t?}</td>
    </tr>
</table>