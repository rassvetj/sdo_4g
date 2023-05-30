    <table cellspacing="0" cellpadding=4 class="print_report_table">
        <tr>
            <td>{?t?}Наименование дисциплины{?/t?}</td>
            <td>{?t?}Кол-во часов{?/t?}</td>
            <td>{?t?}Экзам. оценка{?/t?}</td>
            <td>{?t?}Отметка о зачёте{?/t?}</td>
            <td>{?t?}Дата и номер ведомости{?/t?}</td>
        </tr>
{?foreach from = $items item = item key = discipline?}
        <tr>
            <td>{?$discipline?}</td>
            <td>{?$item.hours?}</td>
            <td>{?$item.mark?}</td>
            <td>{?$item.text_mark?}</td>
            <td>{?$item.date?}{?$item.number?}</td>
        </tr>    
{?/foreach?}
    </table>