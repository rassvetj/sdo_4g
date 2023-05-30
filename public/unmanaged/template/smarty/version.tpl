<div id=version>
<div id=logo_hm></div>
<div id=version_text>
<p>{?t?}Версия{?/t?}: {?if $smarty.const.ELS_VERSION?}{?$smarty.const.ELS_VERSION?}{?else?}{?t?}нет данных{?/t?}{?/if?}</p>
<p>{?t?}Сборка{?/t?}: {?$build?}</p>
{?if $smarty.const.ELS_REGNUM?}<p>{?t?}Рег. номер{?/t?}: {?$smarty.const.ELS_REGNUM?}</p>{?/if?}
{?if $modules?}
<p>{?t?}Дополнительные модули{?/t?}:</p>
<ul>
{?foreach from=$modules item=module?}
<li>{?$module?}
{?/foreach?}
</ul>
{?/if?}
</div>
<div class=clear_both></div>
<div id=version_copyright>&copy;&nbsp;{?$year?}&nbsp;HyperMethod IBS</div>
</div>