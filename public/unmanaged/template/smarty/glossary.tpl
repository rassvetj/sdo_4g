{?if count($menu)?}
<table width=100% class=main cellspacing=0>
<tr>
    <td align=center>
    {?foreach from=$menu key=key item=item?}
    <a href="glossary.php?cid={?$cid?}&letter={?$key?}">
    {?if $letter == $key?}<b>{?/if?}
        {?$item|escape?}
    {?if $letter == $key?}</b>{?/if?}
    </a>&nbsp;
    {?/foreach?}
    </td>
</tr>
</table><p>
{?/if?}

{?if $letter && count($words)?}
<table width=100% class=main cellspacing=0>
    {?foreach name="words" from=$words key=key item=word?}
        {?if $smarty.foreach.words.iteration mod 2 == 1?}
        <tr>
        {?/if?}
        <td width=50%>
            <table border=0><tr><td>
            <a href="javascript:void(0);" onClick="window.open('glossary.php?mini&cid={?$cid?}&word={?$word->attributes.id?}','glossary','toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=300')">{?$word->attributes.name|escape?}</a>
            </td>
            {?if $permission?}
            <td>
            <a href="glossary.php?cid={?$cid?}&letter={?$letter?}&action=delete&id={?$word->attributes.id?}" onClick="if (confirm('{?t?}Удалить термин?{?/t?}')) return true; else return false;">{?$icon_delete?}</a>
            </td>
            {?/if?}
            </tr>
            </table>
        </td>        
        {?if $smarty.foreach.words.iteration mod 2 != 1?}
        </tr>
        {?/if?}
    {?/foreach?}
    {?if $smarty.foreach.words.iteration mod 2 == 1?}
    <td></td></tr>
    {?/if?}    
</table>
{?/if?}

{?if $cid?}
<form action="" method="POST">
<input type="hidden" name="action" value="add">
<input type="hidden" name="form[cid][int]" value="{?$cid?}">
<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Добавить{?/t?}</th></tr>
<tr>
    <td nowrap>{?t?}Термин{?/t?} </td>
    <td width=99%><input type="text" name="form[name][string]" value="" style="width: 100%"></td>
</tr>
<tr>
    <td>{?t?}Определение{?/t?} </td>
    <td>
    {?$fckEditor?}
    </td>
</tr>
<tr>
    <td colspan=2>{?$okbutton?}</td>
</tr>
</table>
</form>
{?else?}
{?t?}Для редактирования глоссария выберите курс.{?/t?}
{?/if?}