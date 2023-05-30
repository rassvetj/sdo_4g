{?php?} 
$GLOBALS['controller']->setView('DocumentBlank');
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<link rel="stylesheet" href="{?$sitepath?}/styles/report.css" type="text/css">
{?if $repData?}
{?foreach from=$repData.data item=data?}
    <table width="717" align='center' cellspacing="0" cellpadding="0" class='print_report_table'>
        <tbody>
            <tr>
                <td width="28" rowspan="2">
                <div align="center">Шкала пересчёта</div>
                </td>
                <td width="69">
                <div align="center">15-и</div>
                <div align="center">балльная</div>
                </td>
                <td width="38">
                <div align="center"><strong>15</strong></div>
                </td>
                <td width="38">
                <div align="center"><strong>14</strong></div>
                </td>
                <td width="38">
                <div align="center"><strong>13</strong></div>
                </td>
                <td width="35">
                <div align="center"><strong>12</strong></div>
                </td>
                <td width="38">
                <div align="center"><strong>11</strong></div>
                </td>
                <td width="35">
                <div align="center"><strong>10</strong></div>
                </td>
                <td width="45">
                <div align="center"><strong>9</strong></div>
                </td>
                <td width="47">
                <div align="center"><strong>8</strong></div>
                </td>
                <td width="53">
                <div align="center"><strong>7</strong></div>
                </td>
                <td width="36">
                <div align="center"><strong>6,0</strong></div>
                </td>
                <td width="36">
                <div align="center"><strong>5,0</strong></div>
                </td>
                <td width="36">
                <div align="center"><strong>4,0</strong></div>
                </td>
                <td width="36">
                <div align="center"><strong>3,0</strong></div>
                </td>
                <td width="36">
                <div align="center"><strong>2,0</strong></div>
                </td>
                <td width="36">
                <div align="center"><strong>1,0</strong></div>
                </td>
                <td width="36">
                <div align="center"><strong>0,1</strong></div>
                </td>
            </tr>
            <tr>
                <td width="69">
                <div align="center">5-и</div>
                <div align="center">балльная</div>
                </td>
                <td width="113" colspan="3">
                <div align="center"><strong>отл</strong>ично</div>
                </td>
                <td width="107" colspan="3">
                <div align="center"><strong>хор</strong>ошо</div>
                </td>
                <td width="145" colspan="3">
                <div align="center"><strong>удовл</strong>етворительно</div>
                </td>
                <td width="254" colspan="7">
                <div align="center"><strong>неуд</strong>овлетворительно</div>
                </td>
            </tr>
        </tbody>
    </table>
    <br />
    <table align='center' cellspacing="0" cellpadding="2" class='print_report_table'>
        <tbody>
            <tr>
                <td width="78" valign="top">
                <div>Зачёт</div>
                </td>
                <td width="174" valign="top">
                <div>&laquo;Зачтено&raquo;: 10 баллов</div>
                </td>
                <td width="174" valign="top">
                <div>&laquo;Незачёт&raquo;: 0,1 балла</div>
                </td>
            </tr>
        </tbody>
    </table>
    <br />
    <p align="center"><strong>МОСКОВСКИЙ ГОРОДСКОЙ</strong></p>
    <p align="center"><strong>ПСИХОЛОГО-ПЕДАГОГИЧЕСКИЙ УНИВЕРСИТЕТ</strong></p>
    <br />
    <p align="center">Заочно-дистанционное обучение</p>
    <br />
    <p align="center"><u>Первичный</u><span>, повторный, комиссия (подчеркнуть)</p>
    <p>&nbsp;</p>
    <p align="center"><strong>ЭКЗАМЕНАЦИОННЫЙ ЛИСТ №</strong> <u>_____</u></p>
    <p align="center">(подшивается к основной ведомости группы)</p>
    <p>&nbsp;</p>
    <table cellpadding="0" cellspacing="0" align='center' width='500'>
        <tr><td>Семестр: {?$data.term?} {?$data.year?} учебного года</td></tr>
        <tr><td>Форма контроля: контрольная работа, реферат, зачёт, экзамен, отчет, курсовая</td></tr>
        <tr><td align='center'><sup><small>(нужное подчеркнуть)</small></sup></td></tr>
        <tr><td>Факультет: {?$data.department?}</td></tr>
        <tr><td>Курс: {?$data.course?} Группа: {?$data.group?} </td></tr>
        <tr><td>Дисциплина: {?$data.discipline?}</td></tr>
        <tr><td>Общее количество часов по учебному плану: {?$data.hours?}</td></tr>
        <tr><td>Экзаменатор: {?$data.examiner?}</td></tr>
        <tr><td>Фамилия и инициалы студента: {?$data.fio?}</td></tr>     
        <tr><td>&nbsp;</td></tr>
        <tr><td align='center'><strong>Рецензия</strong></td></tr>
        <tr><td height="300" valign="top">{?$data.comments|nl2br?}</td></tr>
    </table>
    <br />
    <table width="600" align='center'>
        <tr><td colspan="2">Оценка: {?$data.bal?}</td></tr>
        <tr>
            <td>Дата сдачи: {?$data.time?}</td>
            <td>Подпись экзаменатора______________</td>
        </tr>
        <tr>
            <td>
                Декан факультета_________________________
            </td>
        </tr>
    </table>
{?/foreach?}
{?/if?}
<script language="JavaScript" type="text/javascript">
<!--
window.print();
// -->
</script>

{?include file="rep/rep_footer.tpl"?}
