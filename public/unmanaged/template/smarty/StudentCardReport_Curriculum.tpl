{?foreach from = $items key = level item = item?}
    {?cycle assign=counter values="1,0"?}
    
    {?if $counter?}
        <table cellpadding='0' cellspacing='0' align='center' border='0'>
            <tr>
                <td align = 'center'>
                    {?$item.course?} {?t?}курс{?/t?},  {?$item.year?} {?t?}учебный год{?/t?}
                </td>
            </tr>        
            <tr>
                <td align='center'>
                    {?$level?} {?t?}семестр{?/t?}
                </td>            
            </tr>
        </table>
    {?else?}
        <br />
        <table cellpadding='0' cellspacing='0' align='center' border='0'>            
            <tr>
                <td align='center'>
                    {?$level?} {?t?}семестр{?/t?}
                </td>            
            </tr>
        </table>
    {?/if?}
    
    <table border='0'>
        <tr>
            <td>
                <table cellspacing="0" cellpadding=4 class="print_report_table" align='center'>    
                    <tr>
                        <td>
                            {?t?}Название дисциплины{?/t?}
                        </td>
                        <td>
                            {?t?}Количество часов{?/t?}
                        </td>
                        <td>
                            {?t?}Экз. оценка{?/t?}
                        </td>
                        <td>
                            {?t?}Отметка о зачёте{?/t?}
                        </td>
                        <td>
                            {?t?}Дата и ноомер ведомости{?/t?}
                        </td>            
                    </tr>
                
                {?foreach from = $item.disciplines key = discipline item = info?}
                    <tr>
                        <td>
                            {?$discipline?}
                        </td>
                        <td>
                            {?$info.hours?}
                        </td>
                        <td>
                            {?if $info.mark?}{?$info.mark?}{?else?}&nbsp;{?/if?}
                        </td>
                        <td>
                            {?if $info.text_mark?}{?$info.text_mark?}{?else?}&nbsp;{?/if?}
                        </td>
                        <td>
                            {?$info.date?}  {?$info.number?}
                        </td>            
                    </tr>
                {?/foreach?}
                </table>
            </td>
        </tr>
        <tr>
            <td>
                {?if !$counter?}
                    <table cellpadding='0' cellspacing='0' align='left' border='0' width="100%">    
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        {?if $level<12?}
                            <tr>
                                <td align = 'left'>
                                    {?t?}Переведёт(а) на {?/t?} {?$item.course+1?} {?t?}курс{?/t?}
                                </td>
                            </tr>        
                            <tr>
                                <td align='left'>
                                    {?t?}Приказ №{?/t?} {?$item.order?} {?t?}от{?/t?} {?$item.number?}
                                </td>            
                            </tr>
                        {?/if?}
                        <tr>
                            <td align='right'>
                                {?t?}Декан факультета{?/t?}
                            </td>            
                        </tr>
                    </table>
                    <p style='page-break-before: always;'>&nbsp;</p>
                    <br />
                {?else?}        
                    <br />
                {?/if?}
            </td>
        </tr>
    </table>                
    
{?/foreach?}