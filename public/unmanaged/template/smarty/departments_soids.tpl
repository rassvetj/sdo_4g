<table width=100% class=main cellspacing=0>
{?if $orgunits?}
<tr>
    <th nowrap></th>
    {?foreach name="orgunits" from=$orgunits item=orgunit?}
        <th align=center>{?$orgunit?}</th>
    {?/foreach?}
    <th align=center>{?t?}Вне структуры{?/t?}</th>
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
            {?foreach from=$orgunits key=cid item=orgunit?}
                {?if in_array($cid, $department.orgunits)?}
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
    {?foreach name="deans" from=$deans item=dean?}
        <tr>
            <td width=10% nowrap valign=top>
                {?$dean.lastname?} {?$dean.firstname?} ({?$dean.login?})
            </td>
            {?section name=orgunits loop=$orgunits?}
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
<tr><td align=center>{?t?}нет оргединиц{?/t?}</td></tr>
{?/if?}
</table>