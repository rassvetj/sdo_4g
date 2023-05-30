{?php?} 
$GLOBALS['controller']->setView('DocumentBlank');
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<link rel="stylesheet" href="{?$sitepath?}/styles/report.css" type="text/css">
{?if $repData?}
{?foreach from=$repData.data item=data?}
    <center>
        УЧЕБНАЯ КАРТОЧКА СТУДЕНТА<br />
        Московского городского психолого-педагогического университета
    </center>
    <table width="700" cellpadding="0" cellspacing="0">
        <tr>
            <td><i>Личное дело №     </i></td>
            <td><i>Зачетная книжка № </i></td>
        </tr>
    </table>
    <br />
    <table width='700' cellpadding="0" cellspacing="0">
        <tr>
            <td>Факультет:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Специальность:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Форма обучения:</td>
            <td colspan='99'>
                заочная
                <br />
                контракт № 	/05/ПоЗД 	04.10.05 
            </td>
        </tr>
        <tr>
            <td>Фамилия:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Имя:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Отчество:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Дата рождения:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Место рождение:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Гражданство:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Паспорт:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Адрес места жительства:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Почтовый индекс:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Домашний телефон:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Адрес места работы:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Должность:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Почтовый индекс:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Рабочий телефон:</td>
            <td colspan='99'></td>
        </tr>
        <tr>
            <td>Образование:</td>
            <td colspan='99'></td>
        </tr>
    </table>

    <p style="page-break-before: always">
    
    {?/foreach?}    
{?/if?}
<script language="JavaScript" type="text/javascript">
<!--
window.print();
// -->
</script>

{?include file="rep/rep_footer.tpl"?}
