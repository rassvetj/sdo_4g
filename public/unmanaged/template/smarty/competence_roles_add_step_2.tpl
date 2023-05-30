<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>

<form action="competence_roles.php?action=add" method="POST">
<input name="step" type="hidden" value="2">
<table width=100% cellspacing=0 class=main>
<tr>
    <th>{?t?}Добавление вида оценки{?/t?}: {?t?}Шаг{?/t?} 2</th>
</tr>
<tr>
    <td colspan=2>{?t?}Введите пороги критериев{?/t?}</td>
</tr>
{?if $competences?}
    {?foreach from=$competences key=k item=i?}
		<tr>
		    <td>{?$i?}</td>
		    <td>
            <input type="text" name="data[{?$k?}][double]" size=5 value="{?$values.thresholds[$k]?}">
		    </td>
		</tr>
    {?/foreach?}
{?/if?}
<tr>
    <td colspan=2 align=right>
    <input type="button" value="<< {?t?}Назад{?/t?}" onClick="document.location.href='competence_roles.php?action=add&step=1'"> &nbsp;
    <input type="button" value="{?t?}На главную{?/t?}" onClick="document.location.href='competence_roles.php'"> &nbsp; <input type="submit" value="{?t?}Готово{?/t?} >>">
    </td>
</tr>
</table>
</form>