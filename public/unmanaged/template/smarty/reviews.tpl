{?if $reviews?}
    {?foreach from=$reviews item=review?}
    <table width=100% class=main cellspacing=0>
    <tr>
        <th>{?$review.date|date_format:"%d.%m.%Y"?}, {?$review.person?}</th>
    </tr>    
    <tr>
        <td>{?$review.review?}</td>
    </tr>    
    </table>
    <br>
    {?/foreach?}
{?else?}
<table width=100% class=main cellspacing=0>
<tr>
    <td align=center>{?t?}отзывов не найдено{?/t?}</td>
</tr>
</table>
{?/if?}
<br>
<form action="" method="POST">
<input type="hidden" name="blank" value="{?$blank?}">
<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Добавить отзыв{?/t?}</th></tr>
<tr>
    <td>{?t?}Отзыв{?/t?}</td>
    <td><textarea rows=10 cols=60 name="review"></textarea></td>
</tr>
</table>
<br>
{?$okbutton?}
</form>