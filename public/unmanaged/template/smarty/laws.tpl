<table width=100% class=main cellspacing=0>
  <tr align="center">
    <th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=11{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">#</a>{?if $sort==11?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?} </th>
    <th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=0{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}Наименование{?/t?}</a> {?if $sort==0?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?} </th>
    <th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=1{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}Автор{?/t?}</a> {?if $sort==1?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}<!--br />
        <a href="{?$sitepath?}laws.php?page={?$page?}&sort=5{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}инициатор{?/t?}</a> {?if $sort==5?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}<br /--></th>
    {?if $ModID<=0?} {?/if?}
    <th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=2{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}Тип{?/t?}</a> {?if $sort==2?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}<!--br />
        <a href="{?$sitepath?}laws.php?page={?$page?}&sort=3{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}регион{?/t?}</a> {?if $sort==3?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?} <br /--></th>
    {?if $ModID<=0?}
    <!--th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=6{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}Область прим.{?/t?}</a> {?if $sort==6?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?} </th-->
    {?/if?}
    <th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=4{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}Дата созд.{?/t?}</a> {?if $sort==4?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}<!--br />
        <a href="{?$sitepath?}laws.php?page={?$page?}&sort=7{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}срок дейст.{?/t?}</a> {?if $sort==7?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?} <br /--></th>
    {?*if $ModID<=0?}
    <th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=8{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}Дата изм.{?/t?},</a> {?if $sort==8?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}<br>      
      <a href="{?$sitepath?}laws.php?page={?$page?}&sort=9{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}причина ред.{?/t?}</a> {?if $sort==9?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}  </th>
    <th nowrap><a href="{?$sitepath?}laws.php?page={?$page?}&sort=10{?if $ModID>0?}&ModID={?$ModID?}{?/if?}">{?t?}Уровень доступа{?/t?}</a> {?if $sort==10?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?} </th>
    {?/if*?}
    <th></th>
  </tr>
  {?if $laws?} {?foreach from=$laws item=law?}
  <tr valign="top">
    <td align="center"> {?$law.id?} </td>
    <td width="100%"> {?if $law.filename?} <a href="{?$sitepath?}laws_get.php?id={?$law.id?}" target=_blank>{?$law.title|escape?}</a> {?else?} {?$law.title|escape?} {?/if?} <br>
          {?$law.annotation|escape?} {?if is_array($law.assign) && count($law.assign)?} <br>
          <br>
      {?foreach from=$law.assign item=v key=k?} {?$v[0]?} : {?$v[1]?}<br>
      {?/foreach?} {?/if?} </td>
    <td>{?$law.author|escape|default:"[нет данных]"?}<br>
      {?*$law.initiator|escape|default:"[нет данных]"*?}</td>
    {?if $ModID<=0?} {?/if?}
    <td nowrap>{?$law.type|escape|default:"[нет данных]"?}<br>
      {?*$law.region|lower|escape|default:"[нет данных]"*?}</td>
    {?if $ModID<=0?}
    <!--td>{?$law.area_of_application|escape|default:"[нет данных]"?}</td-->
    {?/if?}
    <td>{?$law.create_date|date_format:"%d.%m.%Y"|default:"[нет данных]"?}
      {?*$law.expire|escape|default:"[нет данных]"*?}</td>
    {?*if $ModID<=0?}
    <td>{?$law.modify_date|date_format:"%d.%m.%Y"|default:"[нет данных]"?}
    {?$law.edit_reason|escape|default:"[нет данных]"?}</td>
    <td>{?$law.access_level|escape|default:"[нет данных]"?}</td>
    {?/if*?}
    <td align="right" nowrap> {?if $ModID>0?} {?if $law.filename?} <a href="{?$sitepath?}laws.php?action=get&ModID={?$ModID?}&id={?$law.id?}"><img border=0 alt="{?t?}Добавить документ{?/t?}" src="{?$sitepath?}images/icons/right.gif"></a> <a href="{?$sitepath?}laws_get.php?id={?$law.id?}" target=_blank><img border=0 alt="{?t?}Открыть документ{?/t?}" src="{?$sitepath?}images/mod/dir.gif"></a> {?/if?} {?else?} {?if $law.filename?} <a href="{?$sitepath?}laws_get.php?id={?$law.id?}" target=_blank><img border=0 alt="{?t?}Открыть документ{?/t?}" src="{?$sitepath?}images/mod/dir.gif"></a> {?/if?} {?if ($perm > 1) && $law.is_edit?} <a href="{?$sitepath?}laws.php?id={?$law.id?}&action=edit&page={?$page?}&sort={?$sort?}"> <img alt="{?t?}Редактировать{?/t?}" border=0 src="images/icons/edit.gif"></a> <a onClick="if (confirm('{?t?}Удалить материал?{?/t?}')) return true; else return false;" href="{?$sitepath?}laws.php?action=delete&id={?$law.id?}&page={?$page?}&sort={?$sort?}"><img alt="{?t?}Удалить учебный материал{?/t?}" border=0 src="images/icons/delete.gif"></a> {?/if?} {?/if?} </td>
  </tr>
  {?/foreach?} {?else?}
  <tr>
    <td colspan="99">{?t?}Ничего не найдено{?/t?}</td>    
  </tr>
  {?/if?}
</table>
<p>{?$pages?}