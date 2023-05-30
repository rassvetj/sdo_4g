<div style="padding:8px">
{?if empty($msg)?}
    <table border=0 cellpadding=10 cellspacing=0 class="card-person">
    <tr>
        <td valign=top>{?$info.photo?}</td>
        <td width="100%" valign="top">

        <span class="lastname">{?$info.LastName|escape?}</span><br />
        <span class="name">{?$info.FirstName|escape?}&nbsp;{?$info.Patronymic|escape?}</span>
        <br/><br/>

        <table cellpadding="1" cellspacing="0" class="card-person-info">

        {?foreach from=$info.soid_info item=orgunit?}
        {?if $orgunit.name?}
            <tr><td><b>{?t?}Подразделение:{?/t?}&nbsp;</b></td><td>{?$orgunit.name?}{?if $orgunit.code?}&nbsp;({?$orgunit.code?}){?/if?}</td></tr>
        {?/if?}
        {?if $orgunit.position?}
            <tr><td><b>{?t?}Должность:{?/t?}</b></td><td><span class="position">{?$orgunit.position?}</span></td></tr>
        {?/if?}
        {?/foreach?}

        {?if $info.groups?}
            <tr>
                <td nowrap valign=top><b>{?t?}Группы:{?/t?}</b> </td>
                <td width=100%>
                    {?foreach name="groups" from=$info.groups item=group?}
                    {?$group|escape?}{?if !$smarty.foreach.groups.last?}, {?/if?}
                    {?/foreach?}
                </td>
            </tr>
        {?/if?}

        {?if $info.hiscode?}
            <tr>
                <td nowrap><b>{?t?}Номер отделения:{?/t?}</b></td>
                <td width="100%">{?$info.hiscode|escape?}</td>
            </tr>
        {?/if?}

        {?if $info.EMail?}
            <tr>
                <td nowrap><b>E-mail:</b> </td>
                <td width="100%"><a href="mailto:{?$info.EMail|escape?}">{?$info.EMail|escape?}</a></td>
            </tr>
        {?/if?}

        {?foreach from=$info.meta item=i key=k?}
            <tr>
                <td nowrap><b>{?if !empty($info.titles[$k])?}{?$info.titles[$k]?}:{?/if?}</b> </td>
                <td>{?$i?}</td>
            </tr>
        {?/foreach?}

        </table>
        </td>
    </tr>
    </table>
{?else?}
    {?$msg?}
{?/if?}
</div>