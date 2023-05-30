<table width=100% class=main cellspacing=0>
    <tbody>
        <tr>
            <th align="center" width="5%" valign="top">#</th>
            <th nowrap="" align="center" width="40%" valign="top">{?t?}ФИО{?/t?}</th>
            <th nowrap="" align="center" width="40%" valign="top">{?t?}Логин{?/t?}</th>
        </tr>
        {?foreach from=$classmates item=dude key=key?}
            <tr>
                <td>{?counter?}</td>
                <td>
                    <a href='javascript:void(0);' onclick="wopen('{?$sitepath?}userinfo.php?mid={?$key?}','',600,425)">
                        {?$dude.LastName?}&nbsp;{?$dude.FirstName?}&nbsp;{?$dude.Patronymic?}
                    </a>
                </td>
                <td>{?$dude.Login?}</td>
            </tr>
        {?/foreach?}
    </tbody>
</table>