{?php?} echo show_tb(); {?/php?}

{?php?}
echo ph(_('Импорт пользователей из Active Directory'));
$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->setHeader(_('Импорт пользователей из Active Directory'));
{?/php?}

<table id="imp_ad" width="100%"  border="0" cellspacing="0" cellpadding="0">
   <form action="{?$sitepath?}people_import_ad.php" method="post" name="form_imp_ad">
   <input type="hidden" name="import_from_ad" value="import_from_ad">
   <input type="hidden" name="do" value="listusers">
        <tr class=questt>
    <td class=tabheader>
<table align="center" border="0" cellspacing="0" cellpadding="5" width="100%"  style="font-size:13px" class=shedaddform>
  <tr class=questt>
    <td>{?t?}Домен:{?/t?} </td>
    <td width="100%">
      <input name="domain_name" type=text class=s8 value="{?$ldap_host?}">
    </td>
  </tr>
  <tr class=questt>
    <td>{?t?}Логин:{?/t?} </td>
    <td width="100%">
      <input name="username" type=text class=s8 value="{?$ldap_user?}">
    </td>
  </tr>
  <tr class=questt>
    <td>{?t?}Пароль:{?/t?} </td>
    <td width="100%">
      <input name="password" type=password class=s8>
    </td>
  </tr>
{?if $checkbox?}
  <tr><td colspan=2><input type="checkbox" name="use_exists_settings" value='1'> {?t?}использовать существующие настройки (небходимо указать только пароль){?/t?}</td></tr>
{?/if?}
</table>
    </td>
  </tr>
  <tr>
    <td><br>
{?php?}
        echo okbutton();
{?/php?}
    </td>
  </tr>
  </form>
</table>
<br>
{?php?} 
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();
{?/php?}
