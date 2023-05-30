{?if $news.show || ($smarty.session.s.perm == 3)?}
    <table width=100% class=main cellspacing=0 height=100%>
        <tr>
            <th align="right"><a href="{?$sitepath?}news.php4">&larr;&nbsp;{?t?}все новости{?/t?}</a></th>
        </tr>
        <tr>
            <td>
                <strong>{?$news.Title?}</strong>
                <br />
                <p>{?$news.message?}</p>        
            </td>        
        </tr>
        <tr>
            <td align='right'>
                {?$news.author?}&nbsp;&nbsp;&nbsp;{?$news.date|date_format:"%d.%m.%y"?}
            </td>        
        </tr>
    </table>
{?else?}
    <p>{?t?}Просмотр новости запрещён{?/t?}</p>
{?/if?}
    