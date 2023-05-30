<br>
<div id="help-footer">
    <hr width=100%>
    <input type="checkbox" {?if isset($smarty.session.s.user.helpAlwaysShow) && $smarty.session.s.user.helpAlwaysShow?}checked{?/if?} name="always_show" value="1" onClick="ajaxCallFunction('ToggleHelpAlwaysShow', new Array(this.checked))"> {?t?}показывать вкладку{?/t?} &laquo;{?t?}Помощь{?/t?}&raquo; {?t?}по умолчанию развернутой{?/t?}
</div>
