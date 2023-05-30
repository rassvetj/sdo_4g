{?foreach from = $items key = level item = info?}
<table border='0' width="650" cellpadding="0" cellspacing="0" align="center">    
    <tr>
        <td>
            {?t?}Прошу подготовить приказ об отчислении {?/t?}{?t?}студентов{?/t?} {?$level/2|ceil?} {?t?}курса{?/t?}, направления "{?$track?}":
        </td>
    </tr>    
        {?foreach from = $info item = student?}
        <tr>
            <td>&nbsp;<b>{?$student?}</b>&nbsp;</td>
        </tr>
        {?/foreach?}    
    <tr>
        <td>
            Как не выполнивших индивидуальный учебный план {?if $level%2?}1{?else?}2{?/if?}-го семестра {?$years.$level?} уч. года за период с ____________г. по ____________г.
        </td>
    </tr>
</table>
    <br />
{?/foreach?}