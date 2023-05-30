<div style="padding:8px; width: 98%; height: 300px">
<table style="width: 100%; height: 100%" cellpadding=4 cellspacing=0 border=0 class="card-person">
<tr>
<td valign=top>

{?if $word?}
<table width=100% class=main cellspacing=0>
     <tr>
        <td valign=top>
            <a href="glossary.php?mini&cid={?$cid?}">{?t?}Главная{?/t?}</a> • <a href="glossary.php?mini&cid={?$cid?}&letter={?$word->attributes.name.0|ord?}">{?$word->attributes.name.0?}</a>
        </td>
    </tr>
</table><p>
{?/if?}

{?if $menu && !$word?}
<table width=100% class=main cellspacing=0>
<tr>
    <td align=center>
    {?foreach name="menu" from=$menu key=key item=item?}
    <a href="glossary.php?mini&cid={?$cid?}&letter={?$key?}">
    {?if $letter == $key?}<b>{?/if?}
        {?$item|escape?}
    {?if $letter == $key?}</b>{?/if?}
    </a>
    {?if !$smarty.foreach.menu.last?} | {?/if?}
    {?/foreach?}
    </td>
</tr>
</table><p>
{?/if?}

{?if $letter && $words && !$word?}
<table width=100% class=main cellspacing=0>
    {?foreach name="words" from=$words key=key item=word?}
        {?if $smarty.foreach.words.iteration mod 2 == 1?}
        <tr>
        {?/if?}
        <td width=50%><a href="glossary.php?mini&cid={?$cid?}&word={?$word->attributes.id?}">{?$word->attributes.name|escape?}</a></td>
        {?if $smarty.foreach.words.iteration mod 2 != 1?}
        </tr>
        {?/if?}
    {?/foreach?}
    {?if $smarty.foreach.words.iteration mod 2 == 1?}
    <td></td></tr>
    {?/if?}
</table>
{?/if?}

{?if $word && !$letter?}
<table width=100% class=main cellspacing=0>
    <tr>
        <td valign=top algin=center><b>{?$word->attributes.name|escape?}</b></td>
    </tr>
    <tr>
        <td valign=top>{?$word->attributes.description?}</td>
    </tr>
</table>
{?/if?}

{?if $empty?}
<table width=100% class=main cellspacing=0>
     <tr>
        <td align=center>
        {?t?}глоссарий по курсу не создан{?/t?}
        </td>
    </tr>
</table><p>
{?/if?}

</td>
</tr>
</table>
</div>

