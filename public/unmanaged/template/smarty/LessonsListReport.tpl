{?if $ret?}
    <table cellspacing="0" cellpadding=4 class="print_report_table" width='70%' align='center'>
    <tr>
        <th>{?t?}Название{?/t?}</th>
        <th>{?t?}Тип занятия{?/t?}</th>
        <th>{?t?}Длительность (академ. часов){?/t?}</th>            
    </tr>    
    {?foreach from=$ret item=info key=name?}
        <tr>
            <td>{?$name?}</td>
            <td>
                {?if $info.item_type?}
                    {?$info.item_type?}
                {?else?}
                    &nbsp;
                {?/if?}                    
            </td>
            <td>
                {?if $info.item_duration?}
                    {?$info.item_duration?}
                {?else?}
                    &nbsp;
                {?/if?}                    
            </td>            
        </tr>    
    {?/foreach?}
    </table>
{?/if?}