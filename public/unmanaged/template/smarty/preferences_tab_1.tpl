<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
<form enctype="multipart/form-data" method="POST" onSubmit="select_list_select_all('addons');">
<input type="hidden" name="action" value="save">
<input type="hidden" name="options[regform_items][array]" value="">
<table width=100% class=main cellspacing=0>
    <tr>
        <th colspan=2>{?t?}Общие настройки{?/t?}</th>
    </tr>
    <tr>
        <td valign=top width="30%">{?t?}Название Системы{?/t?} </td>
        <td valign=top>
            <input type="text" name="options[windowTitle][text]" value="{?if $options.windowTitle?}{?$options.windowTitle|escape?}{?/if?}" style = "width:90%">
            &nbsp;&nbsp;{?$tooltip->display('browser_name')?}
        </td>
    </tr>
    <tr>
        <td valign=top>{?t?}Название администрации Портала (поле 'От' в письмах, рассылаемых Системой){?/t?} </td>
        <td valign=top>
			<input type="text" name="options[dekanName][text]" value="{?if $options.dekanName?}{?$options.dekanName|escape?}{?/if?}" style="width: 90%;">
			&nbsp;&nbsp;{?$tooltip->display('dean_name')?}
		</td>
    </tr>
    <tr>
        <td valign=top>{?t?}E-mail администрации Портала{?/t?} </td>
        <td valign=top>
			<input type="text" name="options[dekanEMail][text]" value="{?if $options.dekanEMail?}{?$options.dekanEMail|escape?}{?/if?}" style="width: 90%;">
			&nbsp;&nbsp;{?$tooltip->display('dean_mail')?}
		</td>
    </tr>
    <tr>
        <td valign=top width="30%">{?t?}Название головного подразделения оргструктуры{?/t?} </td>
        <td valign=top>
            <input type="text" name="options[headStructureUnitName][text]" value="{?if $options.headStructureUnitName?}{?$options.headStructureUnitName|escape?}{?/if?}" style = "width:90%">
            &nbsp;&nbsp;{?$tooltip->display('browser_name')?}
        </td>
    </tr>
    <tr>
        <td valign=top width="30%">{?t?}Количество строк в таблице{?/t?} </td>
        <td valign=top>
            <input type="text" name="options[grid_rows_per_page][text]" value="{?if $options.grid_rows_per_page?}{?$options.grid_rows_per_page|escape?}{?/if?}" style = "width:90%">
            &nbsp;&nbsp;{?$tooltip->display('browser_name')?}
        </td>
    </tr>
    <tr>
        <td valign=top width="30%">{?t?}Количество сообщений, отображаемых в рабочей области канала чата{?/t?} </td>
        <td valign=top>
            <input type="text" name="options[chat_messages_show_in_channel][text]" value="{?if $options.chat_messages_show_in_channel?}{?$options.chat_messages_show_in_channel|escape?}{?/if?}" style = "width:90%">
        </td>
    </tr>
    <!--tr>
        <td>{?t?}Название головного элемента учебной структуры{?/t?}: </td>
        <td><input type="text" name="options[edo_subdivision_root_name][text]" value="{?if $options.edo_subdivision_root_name?}{?$options.edo_subdivision_root_name|escape?}{?/if?}" style="width: 100%;"></td>
    </tr-->
    <tr>
        <td>{?t?}Валюта по умолчанию{?/t?}: </td>
        <td>
        	<select name="options[default_currency][text]">
        		{?foreach from=$currencies key=cur_name item=cur_title?}
                <option value="{?$cur_name?}" {?if $cur_name == $options.default_currency?}selected{?/if?}>{?$cur_title?}</option>
                {?/foreach?}
        	</select>
    </tr>
    <tr>
        <td width="30%">{?t?}Исключать выходные и праздничные дни при расчете относительных дат{?/t?} </td>
        <td>
        <select name="options[use_holidays][int]">
            <option value="1" {?if $options.use_holidays?}selected{?/if?}>{?t?}да{?/t?}</option>
            <option value="0" {?if !$options.use_holidays?}selected{?/if?}>{?t?}нет{?/t?}</option>
        </select>
        </td>
    </tr>    
{?if $smarty.const.USE_NTLM eq 1?}
    <tr>
        <td>{?t?}Домен{?/t?} Active Directory:</td>
        <td><input type="text" name="options[ntlm_domain][text]" value="{?if $options.ntlm_domain?}{?$options.ntlm_domain|escape?}{?/if?}" style="width: 100%;"></td>
        <td>{?t?}Домен Active Directory для автоматической авторизации пользователей{?/t?}</td>
    </tr>
{?/if?}
{?if $smarty.const.USE_ACTIVE_DIRECTORY_SUPPORT?}
    <tr>
        <td>{?t?}Хост{?/t?} Active Directory: </td>
        <td><input type="text" name="options[ldap_host][text]" value="{?if $options.ldap_host?}{?$options.ldap_host|escape?}{?/if?}" style="width: 100%;"></td>
        <td>{?t?}Адрес контроллера домена{?/t?} Active Directory ({?t?}для импорта и синхронизации списка учетных записей{?/t?})</td>
    </tr>
    <tr>
        <td>{?t?}Пользователь{?/t?} Active Directory: </td>
        <td><input type="text" name="options[ldap_user][text]" value="{?if $options.ldap_user?}{?$options.ldap_user|escape?}{?/if?}" style="width: 100%;"></td>
        <td>{?t?}Имя пользователя{?/t?} Active Directory ({?t?}для импорта и синхронизации списка учетных записей{?/t?})</td>
    </tr>
{?/if?}
</table><br>
{?if $smarty.const.USE_WEBINAR?}
<table width=100% class=main cellspacing=0>
    <tr>
        <th colspan=2>{?t?}Интеграция с iWebinar{?/t?}</th>
    </tr>
    <tr>
        <td nowrap>{?t?}Адрес точки публикации вебинаров{?/t?} (rtmp://): </td>
        <td width="100%"><input type="text" name="options[webinar_media][text]" value="{?if $options.webinar_media?}{?$options.webinar_media|escape?}{?/if?}" style="width: 90%;"></td>
    </tr>
</table><br>
{?/if?}
{?if $smarty.const.USE_CONNECT_PRO?}
<table width=100% class=main cellspacing=0>
    <tr>
        <th colspan=2>{?t?}Интеграция с Connect Pro{?/t?}</th>
    </tr>
    <tr>
        <td width="30%">{?t?}Хост{?/t?} (http://): </td>
        <td><input type="text" name="options[cp_host][text]" value="{?if $options.cp_host?}{?$options.cp_host|escape?}{?/if?}" style="width: 100%;"></td>
    </tr>
    <tr>
        <td>{?t?}Логин администратора{?/t?}: </td>
        <td><input type="text" name="options[cp_admin_login][text]" value="{?if $options.cp_admin_login?}{?$options.cp_admin_login|escape?}{?/if?}" style="width: 100%;"></td>
    </tr>
    <tr>
        <td>{?t?}Пароль администратора{?/t?}: </td>
        <td><input type="password" name="options[cp_admin_password][text]" value="{?if $options.cp_admin_password?}{?$options.cp_admin_password|escape?}{?/if?}" style="width: 100%;"></td>
    </tr>
    <tr>
        <td>{?t?}Пароль по умолчанию для новых пользователей{?/t?}: </td>
        <td><input type="text" name="options[cp_default_password][text]" value="{?if $options.cp_default_password?}{?$options.cp_default_password|escape?}{?/if?}" style="width: 100%;"></td>
    </tr>
</table><br>
{?/if?}
<table width=100% class=main cellspacing=0>
    <tr>
        <th colspan=2>{?t?}Безопасность{?/t?}</th>
    </tr>
    <tr>
        <td width="30%">{?t?}Запретить просмотр персональной информации всем, кроме модераторов Портала{?/t?} </td>
        <td>
        <select name="options[disable_personal_info][int]">
            <option value="1" {?if $options.disable_personal_info?}selected{?/if?}>{?t?}да{?/t?}</option>
            <option value="0" {?if !$options.disable_personal_info?}selected{?/if?}>{?t?}нет{?/t?}</option>
        </select>
        </td>
    </tr>
    <tr>
        <td width="30%">{?t?}Запретить рассылку сообщений всем, кроме модераторов соответствующих виртуальных кабинетов{?/t?} </td>
        <td>
        <select name="options[disable_messages][int]">
            <option value="1" {?if $options.disable_messages?}selected{?/if?}>{?t?}да{?/t?}</option>
            <option value="0" {?if !$options.disable_messages?}selected{?/if?}>{?t?}нет{?/t?}</option>
        </select>
        </td>
    </tr>
    <tr>
        <td width="30%">{?t?}Включить логирование доступа к основным элементам системы{?/t?} </td>
        <td>
            <select name="options[security_logger][int]">
                <option value="1" {?if $options.security_logger?}selected{?/if?}>{?t?}да{?/t?}</option>
                <option value="0" {?if !$options.security_logger?}selected{?/if?}>{?t?}нет{?/t?}</option>
            </select>
        </td>
    </tr>
    <tr>
        <td width="30%">{?t?}Включить отладочную информацию SCORM API{?/t?} </td>
        <td>
            <select name="options[scorm_tracklog][int]">
                <option value="1" {?if $options.scorm_tracklog?}selected{?/if?}>{?t?}да{?/t?}</option>
                <option value="0" {?if !$options.scorm_tracklog?}selected{?/if?}>{?t?}нет{?/t?}</option>
            </select>
        </td>
    </tr>


    <!--tr>
        <td width="30%">{?t?}Запретить слушателю выделять и копировать содержимое страниц{?/t?} </td>
        <td>
        <select name="options[disable_copy_material][int]">
            <option value="1" {?if $options.disable_copy_material?}selected{?/if?}>{?t?}да{?/t?}</option>
            <option value="0" {?if !$options.disable_copy_material?}selected{?/if?}>{?t?}нет{?/t?}</option>
        </select>
		{?$tooltip->display('material_copy')?}
        </td>
    </tr>
    <tr>
        <td>{?t?}Запретить одновременное нахождение на сервере нескольких пользователей с одним логином{?/t?} </td>
        <td>
        <select name="options[enable_check_session_exist][int]" id="enable_check_session_exist">
            <option value="1" {?if $options.enable_check_session_exist?}selected{?/if?}>{?t?}да{?/t?}</option>
            <option value="0" {?if !$options.enable_check_session_exist?}selected{?/if?}>{?t?}нет{?/t?}</option>
        </select>&nbsp;&nbsp;
		{?$tooltip->display_variable('several_users', 'enable_check_session_exist', 1)?}
        </td>
    </tr-->
</table>

<p>
{?$okbutton?}
</form>
