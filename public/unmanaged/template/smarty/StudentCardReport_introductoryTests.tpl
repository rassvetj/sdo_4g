<table cellspacing="0" cellpadding=4 class="print_report_table" width='70%'>
    <tr>
    {?foreach from = $items key=key item=item?}        
        {?cycle values="</tr><tr>,<td>&nbsp;</td>"?}
        <td>{?$key?}</td>
        {?if $item.mark?}
            <td>
                {?$item.mark?}
                {?if $item.alias?}
                    ({?$item.alias?})
                {?/if?}
            </td>
        {?else?}
            <td>&nbsp;</td>
        {?/if?}
    {?/foreach?}
    </tr>    
</table>