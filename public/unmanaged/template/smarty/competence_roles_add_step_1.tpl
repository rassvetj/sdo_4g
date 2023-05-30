<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>

<form action="competence_roles.php?action=add" method="POST" onSubmit="select_list_select_all('competences');">
<table width=100% cellspacing=0 class=main>
<tr>
    <th>{?t?}Добавление вида оценки{?/t?}: {?t?}Шаг{?/t?} 1</th>
</tr>
<tr>
    <td>{?t?}Название{?/t?}: </td>
    <td><input name="data[name][string]" type="text" value="{?if $values?}{?$values.name|escape?}{?/if?}" style="width: 300px;"></td>
</tr>
<tr>
    <td>{?t?}Способ оценки{?/t?}: </td>
    <td>
    <select name="data[formula][integer]">
    <option value="0"> {?t?}Выберите шкалу оценки{?/t?}</option>
    {?if $formulas?}
        {?foreach from=$formulas item=formula?}
            <option {?if $values.formula eq $formula->attributes.id?}selected{?/if?} value="{?$formula->attributes.id?}"> {?$formula->attributes.name|escape?}</option>
        {?/foreach?}
    {?/if?}
    </select>
    </td>
</tr>
<tr>
    <td colspan=2>{?t?}Критерии{?/t?}: </td>
</tr>
<tr>
    <td colspan=2>
    <table width=100% border=0>
    <tr>
        <td width=50% align=center>
            <select size=5 id="all_competences" name="all_competences[]" multiple style="width: 300px">
            {?if $all_competences?}
                {?foreach from=$all_competences key=k item=i?}
                    <option value="{?$k?}"> {?$i?}</option>
                {?/foreach?}
            {?/if?}
            </select>
        </td>
        <td valign=middle align=middle>
            <input type="button" value=">>" onClick="select_list_move('all_competences','competences','select_list_cmp_by_text')">
            <input type="button" value="<<" onClick="select_list_move('competences','all_competences','select_list_cmp_by_text')">
        </td>
            <td width=50% align=center>
            <select size=5 id="competences" name="data[competences][array][]" multiple style="width: 300px">
            {?if $competences?}
                {?foreach from=$competences key=k item=i?}
                    <option value="{?$k?}"> {?$i?}</option>
                {?/foreach?}
            {?/if?}
            </select>
        </td>
    </tr>
    </table>
</tr>
<tr>
    <td colspan=2 align=right><input type="button" value="{?t?}На главную{?/t?}" onClick="document.location.href='competence_roles.php'"> &nbsp; <input type="submit" value="{?t?}Далее{?/t?} >>"></td>
</tr>
</table>
</form>