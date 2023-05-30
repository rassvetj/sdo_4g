    <table cellspacing="0" cellpadding=4 class="print_report_table">
        <tr>
            <td>{?t?}Наименование практики{?/t?}</td>
            <td>{?t?}Кол-во недель{?/t?}</td>
            <td>{?t?}Семестр{?/t?}</td>
            <td>{?t?}Отметка о зачёте{?/t?}</td>
            <td>{?t?}Дата и номер ведомости{?/t?}</td>
        </tr>
{?foreach from = $items item = item key = discipline?}
        <tr>
            <td>{?$discipline?}</td>
            <td>{?$item.weeks?}</td>
            <td>{?$item.level?}</td>
            <td>{?$item.text_mark?}</td>
            <td>{?$item.date?}{?$item.number?}</td>
        </tr>    
{?/foreach?}
    </table>