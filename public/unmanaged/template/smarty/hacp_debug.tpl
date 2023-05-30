<input type="button" name="clean" value="Очистить" onClick="if (confirm('{?t?}Очистить{?/t?}?')) document.location.href='hacp_debug.php?action=clean'">
<br><br>
<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Дата{?/t?}</th><th>{?t?}Направление{?/t?}</th><th>{?t?}Данные{?/t?}</th>
</tr>
{?if $messages?}
    {?foreach from=$messages item=message?}
    <tr>
        <td>{?$message.date|date_format:"%d.%m.%Y %H:%M:%S"?}</td>
        <td>{?if $message.direction?}{?t?}курсу{?/t?}{?else?}{?t?}от курса{?/t?}{?/if?}</td>
        <td>
        {?if is_array($message.message)?}
            {?foreach from=$message.message key=key item=value?}
            <b>{?$key|escape?}:</b><br>
            {?$value|escape|nl2br?}<br>            
            {?/foreach?}
        {?else?}
            {?$message.message|escape|nl2br?}
        {?/if?}
        </td>
        
    </tr>
    {?/foreach?}
{?else?}
<tr><td colspan=3 align=center>{?t?}Нет данных{?/t?}</td></tr>
{?/if?}
</table> 