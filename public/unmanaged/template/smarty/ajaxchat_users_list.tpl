		{?if $users?}
			{?foreach from=$users key=uid item=user?}
			    <a href="javascript:void();" title="{?t?}Информация о пользователе{?/t?}" onClick="window.open('{?$sitepath?}userinfo.php?mid={?$uid?}', 'userinfo', 'toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400 , height=300')"><img border=0 src="{?$sitepath?}images/icons/reference.gif"></a> <a onClick="top.mainFrame.addNick('{?$user.user|escape?}')" href="javascript:void();">{?$user.user?}</a> {?if strlen($user.LastName) || strlen($user.FirstName)?}({?$user.LastName?}{?if strlen($user.FirstName)?} {?$user.FirstName?}{?/if?}){?/if?}<br>
			{?/foreach?}
		{?/if?}
        