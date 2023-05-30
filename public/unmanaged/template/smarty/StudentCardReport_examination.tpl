    <table cellspacing="0" cellpadding=4 class="print_report_table">
        <tr>
            <td>{?t?}№ п/п{?/t?}</td>
            <td>{?t?}Наименование дисциплины{?/t?}</td>
            <td>{?t?}Оценка{?/t?}</td>            
            <td>{?t?}Дата{?/t?}</td>
        </tr>
{?foreach from = $items item = item key = discipline?}
        <tr>
            <td>{?$item.number?}</td>            
            <td>{?$discipline?}</td>            
            <td>{?$item.mark?}</td>
            <td>{?$item.date?}</td>
        </tr>    
{?/foreach?}
    </table>