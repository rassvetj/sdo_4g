{?if $logItems?}
    {?foreach from=$logItems item=item?}
        {?assign var=name value=$item.name?}
        {?assign var=log value=$item.log?}
{?if $name?}
<table width="100%" cellspacing="0" cellpadding="0"  valign="top">
<tr>
 <td  align="right" width="60%" class="th-before-corner"><img src="{?$smarty.server.document_root?}images/spacer.gif" width="1" height="1" alt=""></td>
 <td  class="th3" align="right" background="{?$skin_url?}/images/corner03.gif" width="25px" border="0" height="15px">
            <img src="{?$smarty.server.document_root?}images/spacer.gif" width="25" height="1" alt="">
 </td>
 <td  align="right" class="th3" nowrap ><b>{?$name?}</b>&nbsp;</td>
</tr>
</table>
{?/if?}
<table width=100% class=main cellspacing=0>
    <tr>
        <th width=25%>{?t?}Курс{?/t?}</th>
        <th width=25%>{?t?}Согласующее лицо{?/t?}</th>
        <th width=20%>{?t?}Статус{?/t?}</th>
        <th width=20%>{?t?}Комментарий{?/t?}</th>
        <th width=10%>{?t?}Дата{?/t?}</th>
    </tr>
{?if $log?}
    {?foreach from=$log key=k item=i?}
        {?if $i.items?}
            {?foreach name="foreach" from=$i.items item=ii?}
     <tr>
        {?if $smarty.foreach.foreach.first?}
        <td width=25% rowspan={?$i.count?} valign=top>{?$k?}</td>
        {?/if?}
        <td width=25%>{?$ii.subject?}</td>
        <td width=20%>{?$ii.status?}</td>
        <td width=20%>{?$ii.comment?}</td>
        <td width=10%>{?$ii.date|date_format:"%d.%m.%Y %H:%M:%S"?}</td>
     </tr>
            {?/foreach?}
        {?/if?}
    {?if $agreem=='yes'?}
    <tr>
        <td colspan=6 align=center>
            <input type="button" name="accept" value="{?t?}Принять{?/t?}" style="width=70px;" onClick="if (confirm('{?t?}Принять обучаемого на курс?{?/t?}')) document.location.href='{?$sitepath?}orders.php?action=accept&cid={?$i.cid?}&mid={?$i.mid?}'"> &nbsp;
            <input type="button" name="deny" value="{?t?}Отклонить{?/t?}" style="width=70px;" onClick="if (confirm('{?t?}Отклонить заявку обучаемого?{?/t?}')) document.location.href='{?$sitepath?}orders.php?action=deny&cid={?$i.cid?}&mid={?$i.mid?}'">
        </td>
    </tr>
    {?/if?}
    {?/foreach?}
{?/if?}
</table>
{?/foreach?}
{?else?}
<table width=100% class=main cellspacing=0>
    <tr>
        <th width=25%>{?t?}Курс{?/t?}</th>
        <th width=25%>{?t?}Согласующее лицо{?/t?}</th>
        <th width=20%>{?t?}Статус{?/t?}</th>
        <th width=20%>{?t?}Комментарий{?/t?}</th>
        <th width=10%>{?t?}Дата{?/t?}</th>
    </tr>
    <tr>
        <td align=center colspan="99">
            {?t?}В данный период нет заявок, требующих согласования{?/t?}
        </td>        
    </tr>
</table>
{?/if?}
