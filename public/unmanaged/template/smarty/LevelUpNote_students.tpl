{?foreach from = $items key = level item = info?}
    <center>{?t?}Просьба перевести на{?/t?} {?$level+1?}{?t?}-й{?/t?} {?t?}курс{?/t?} {?t?}студентов{?/t?}:</center>
    <table width="650" cellspacing="0" cellpadding=4 class="print_report_table" align="center">    
        {?foreach from = $info item = student?}
        <tr>
            <td>&nbsp;{?$student?}&nbsp;</td><td>&nbsp;{?$level?}&nbsp;</td><td width='100'>&nbsp;______________&nbsp;</td>
        </tr>
        {?/foreach?}
    </table>
    <br />    
{?/foreach?}