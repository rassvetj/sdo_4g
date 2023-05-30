<div style="padding:8px" align=center>
<table border=0 cellpadding=10 cellspacing=0 class="card-person">
<tr>
    <td nowrap><strong>{?t?}Название:{?/t?}</strong> </td>
    <td width=99%>{?$book.title|escape?}</td>
</tr>
<tr>
    <td nowrap><strong>{?t?}Тип издания:{?/t?}</strong> </td>
    <td width=99%>
    {?$lo_types[$book.type]?}
    </td>
</tr>
<tr>
    <td nowrap><strong>{?t?}Описание{?/t?}:</strong> </td>
    <td width=99%>{?if $book.description?}{?$book.description?}{?else?}{?t?}нет{?/t?}{?/if?}</td>
</tr>
{?if trim($book.location)?}
<tr>
    <td nowrap><strong>{?t?}URL:{?/t?}</strong> </td>
    <td width=99%><a href="{?$book.location?}" target=_blank>{?$book.location?}</a></td>
</tr>
{?/if?}
<tr>
    <td nowrap><strong>{?t?}Автор:{?/t?}</strong> </td>
    <td width=99%>{?if $book.author?}{?$book.author|escape?}{?else?}{?t?}нет{?/t?}{?/if?}</td>
</tr>
<tr>
    <td nowrap><strong>{?t?}Издательство:{?/t?}</strong> </td>
    <td width=99%>{?if $book.publisher?}{?$book.publisher|escape?}{?else?}{?t?}нет{?/t?}{?/if?}</td>
</tr>
<tr>
    <td nowrap><strong>{?t?}Год:{?/t?}</strong> </td>
    <td width=99%>{?if $book.publish_date?}{?$book.publish_date|escape?}{?else?}{?t?}нет{?/t?}{?/if?}</td>
</tr>
{?if $book.quantity?}
<tr>
    <td nowrap><strong>{?t?}Экземпляров:{?/t?}</strong> </td>
    <td width=99%>
        {?$book.quantity?}
    </td>
</tr>
{?/if?}
<tr>
    <td nowrap><strong>{?t?}Уровень доступа:{?/t?}</strong> </td>
    <td width=99%>{?$book.need_access_level?}</td>
</tr>
</table>
</div>