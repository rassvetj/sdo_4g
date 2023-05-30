<div style='float: left;'><img src='{?$smarty.server.document_root?}images/icons/small_star.gif'>&nbsp;</div>
<div><a href='/providers.php?action=edit' style='text-decoration: none;'>{?t?}добавить поставщика{?/t?}</a></div>
<!--

		<div id='div_provider_add' style='display:none'>
		<form method=post>
			<input type=hidden name=action value='edit'>
			<table width=70% class=main cellspacing=0>
			<tr>
				<th colspan=2>{?t?}Добавить{?/t?}</th></tr>
			<tr>
				<td nowrap>{?t?}Название провайдера{?/t?}:</td>
				<td width='100%'><input type=text name=name size=40 value="">
					 input type=checkbox name=TRACK value=1 >"._("специальность")." </td></tr></table>
			<table cellspacing="0"  cellpadding="0" border=0 width="72%">
			<tr>
				<td align="right" valign="top">{?$okbutton?}</td></tr></table>
	</form></div>
-->
<br>
<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Название{?/t?}</th>
    <th>{?t?}Адрес{?/t?}</th>
    <th>{?t?}Контакты{?/t?}</th>
    <th>{?t?}Описание{?/t?}</th>
    <th>{?t?}Действия{?/t?}</th>
</tr>
{?if $providers?}
    {?foreach from=$providers item=provider?}
    <tr>
        <td>{?$provider.title?}</td>
        <td>{?$provider.address|nl2br?}</td>
        <td>{?$provider.contacts|nl2br?}</td>
        <td style="text-align: justify;">{?$provider.description|nl2br?}</td>
        <td>
        <a href="{?$sitepath?}providers.php?action=edit&id={?$provider.id?}">{?$icon_edit?}</a>&nbsp;
        <a href="{?$sitepath?}providers.php?action=delete&id={?$provider.id?}">{?$icon_delete?}</a>
        </td>
    </tr>
    {?/foreach?}
{?else?}
    <tr>
        <td align=center colspan=99>{?t?}нет данных для отображения{?/t?}</td>
    </tr>
{?/if?}
</table>
