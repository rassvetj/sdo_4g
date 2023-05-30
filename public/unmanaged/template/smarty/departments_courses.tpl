<table width=100% class=main cellspacing=0>
{?if $courses?}
<tr>
    <th nowrap></th>
    {?foreach name="courses" from=$courses item=course?}
        <th align=center>{?$course?}</th>
    {?/foreach?}
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

            {?foreach from=$courses key=cid item=course?}
                {?if in_array($cid, $department.courses)?}
                    <td align=center style="background: #DDDDDD;" title="{?$department.lastname?} {?$department.firstname?} ({?$department.login?})">+</td>
                {?else?}
                    <td title="{?$department.lastname?} {?$department.firstname?} ({?$department.login?})"></td>
                {?/if?}
            {?/foreach?}
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
            {?section name=courses loop=$courses?}
                <td align=center style="background: #DDDDDD;" title="{?$dean.lastname?} {?$dean.firstname?} ({?$dean.login?})">+</td>
            {?/section?}
        </tr>
    {?/foreach?}

{?/if?}

{?else?}
<tr><td align=center colspan=99>никого</td></tr>
{?/if?}

{?else?}
<tr><td align=center>нет курсов</td></tr>
{?/if?}
</table>