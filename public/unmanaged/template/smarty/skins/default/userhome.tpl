{?assign var='currentRole' value=''?}
{?assign var='serverRoot' value=$smarty.const.URL_ROOT?}
{?if $this->objects.user and $this->objects.user->isAuthorized()?}
{?assign var='isAuthorized' value=1?}
{?else?}
{?assign var='isAuthorized' value=0?}
{?/if?}
{?if $isAuthorized?}
{?foreach from=$this->objects.user->profiles item=profile?}
{?if $profile->current?}
{?assign var='currentRole' value=$profile?}
{?/if?}
{?/foreach?}
{?/if?}
{?php?}
$this->assign('confirm_text', json_encode( array(
	'ok'     => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Выйти")),
	'cancel' => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Остаться")),
	'text'   => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Вы действительно хотите выйти из системы?")),
	'title'  => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Выход"))
) ));
{?/php?}
{?php?}
$this->assign('confirm_text_restore', json_encode( array(
	'ok'     => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Выйти")),
	'cancel' => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Остаться")),
	'text'   => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Вы действительно хотите выйти из режима работы от имени другого пользователя?")),
	'title'  => iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, "UTF-8", _("Выход"))
) ));
{?/php?}
{?php?}
    global $profiles_inheritance; 
    $this->assign('inheritUsers', json_encode($profiles_inheritance)); 
{?/php?}
<span id="user-block-wrapper" class="{?if ($this->objects.userRestore)?}user-block-wrapper-restoreable{?else?}user-block-wrapper-default{?/if?}"><div class="block" id="user-block">
	{?if $isAuthorized?}
        <span class="welcome-text">{?t?}Добро пожаловать{?/t?}</span>
        {?if count($this->objects.user->profiles) > 1 ?}
            <span id="hm-role-switcher"></span>
            <script type="text/javascript">
                HM.create('hm.module.user.ui.role.switcher.RoleSwitcher', {
                    renderTo: '#hm-role-switcher',
                    userRoles: {?$this->objects.user->profiles|@json_encode?},
                    inheritUsers: {?$inheritUsers?}
                });
            </script>
        {?/if?}
	{?/if?}
	{?if ($this->objects.lang_chooser->enabled())?}
	<select class="language-select">
		{?foreach from=$this->objects.lang_chooser->objects.lang_controller->langs item=lang?}
		<option {?if $lang->id == $this->objects.lang_chooser->objects.lang_controller->lang_current->id?}selected{?/if?} value="{?$serverRoot?}/language/lang/{?$lang->id?}" data-language="{?$lang->id?}">{?$lang->id?} {?$lang->title?}</option>
		{?/foreach?}
	</select>
	{?/if?}
	{?if $isAuthorized?}
        <h3 class="name">
        {?if ($this->objects.userRestore)?}
            {?if ($this->objects.userRestore)?}{?$this->objects.userRestore->FirstName?} {?$this->objects.userRestore->Patronymic?} {?t?}от имени пользователя{?/t?} {?/if?}
            {?$smarty.session.s.user.lname?}&nbsp;{?$smarty.session.s.user.fname?}&nbsp;{?$smarty.session.s.user.patronymic?}
			&nbsp;<span>({?$smarty.now|date_format:"%d.%m.%Y %H:%M"?})</span>
        {?else?}
            <div class="edit-profile action-link">
                <a href="{?$smarty.const.URL_ROOT?}/user/edit/card/user_id/{?$this->objects.user->id?}">{?$smarty.session.s.user.fname?}&nbsp;{?$smarty.session.s.user.patronymic?}</a>
            </div>
        {?/if?}
        </h3>
        <div class="logout action-link">
            {?if ($this->objects.userRestore)?}
            <a href="{?$serverRoot?}/restore" data-confirm="{?$confirm_text_restore|escape?}">{?t?}Выйти из режима{?/t?}</a>
            {?else?}
            <a href="{?$serverRoot?}/logout" data-confirm="{?$confirm_text|escape?}">{?t?}Выйти{?/t?}</a>
            {?/if?}
        </div>
	{?else?}
	<div class="login action-link">
		<a href="{?$serverRoot?}">{?t?}Войти{?/t?}</a>
	</div>
	{?/if?}
</div></span>
<script type="text/javascript">document.documentElement.className += ' has-user-block'</script>