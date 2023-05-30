<table width=100% class=main cellspacing=0>
{?if $groups?}
<tr>
    <th nowrap></th>
    {?foreach name="groups" from=$groups item=group?}
        <th align=center>{?$group?}</th>
    {?/foreach?}
    <th align=center>{?t?}Вне групп{?/t?}</th>
</tr>

{?if $departments || $deans?}

{?if $departments?}
    {?foreach name="departments" from=$departments item=department?}
        <tr>
            <td width=10% nowrap valign=top>
                {?if $department.name?}
                    <b>{?$department.name?}</b><br>
                {?/if?}
                {?if $department.lastname || $department.login?}
                    {?$department.lastname?} {?$department.firstname?} ({?$department.login?})
                {?/if?}
            </td>
            {?foreach from=$groups key=cid item=group?}
                {?if in_array($cid, $department.groups)?}
                    <td align=center style="background: #DDDDDD;" title="{?$department.lastname?} {?$department.firstname?} ({?$department.login?})">+</td>
                {?else?}
                    <td title="{?$department.lastname?} {?$department.firstname?} ({?$department.login?})"></td>
                {?/if?}
            {?/foreach?}
            {?if $department.not_in?}
                <td align=center style="background: #DDDDDD;" title="{?$department.lastname?} {?$department.firstname?} ({?$department.login?})">+</td>
            {?else?}
                <td title="{?$department.lastname?} {?$department.firstname?} ({?$department.login?})"></td>
            {?/if?}
        </tr>
    {?/foreach?}
{?/if?}

{?if $deans?}
    <tr>
        <th colspan=99>{?t?}Учебные администраторы вне учебной структуры{?/t?}</th>
    </tr>
    {?foreach name="deans" from=$deans item=dean?}
        <tr>
            <td width=10% nowrap valign=top>
                {?$dean.lastname?} {?$dean.firstname?} ({?$dean.login?})
            </td>
            {?section name=groups loop=$groups?}
                <td align=center style="background: #DDDDDD;" title="{?$dean.lastname?} {?$dean.firstname?} ({?$dean.login?})">+</td>
            {?/section?}
            <td align=center style="background: #DDDDDD;" title="{?$dean.lastname?} {?$dean.firstname?} ({?$dean.login?})">+</td>
        </tr>
    {?/foreach?}
{?/if?}

{?else?}
<tr><td align=center colspan=99>никого</td></tr>
{?/if?}

{?else?}
<tr><td align=center>{?t?}нет групп{?/t?}</td></tr>
{?/if?}
</table>