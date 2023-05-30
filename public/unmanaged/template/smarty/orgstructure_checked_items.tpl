<table width="100" border=0 cellpadding=2 cellspacing=1 class="orgstructure_checked_items">
{?if $checked?}
<tr>
    <th align="left" nowrap><span class="header">{?t?}Выбранные элементы{?/t?}</span></th>
    <th style="padding-left:15px; padding-right:15px;"><span class="webdna" style="color: #aeaeae; font-size: 30px; text-align: right;">&#8594;</span></th>
    <th align="left" nowrap><span class="header">{?t?}Действия с выбранными элементами{?/t?}</span></th>
</tr>
<tr>
    <td valign="top" nowrap>
        <div class="block">
        {?if $filters?}
	        <div class="filter">
		        {?$filters.specialization.title?}:&nbsp;
		        {?$filters.specialization.value?}
	        </div>
        {?/if?}
        {?foreach from=$checked item=item?}
            {?$item.name|escape?}<br/>
        {?/foreach?}
        <br/>
        </div>
    </td>
    <td></td>
    <td valign="top" nowrap>
        <div class="block">
            <form id="checked_items_form" action="" method="GET">
            <table>
            <tr>
            <td>
            <select name="checked_items_actions" id="checked_items_actions">
                <option value="">{?t?}-- выберите действие --{?/t?}</option>
                {?if !$teacher ?}
                <option value="set_mark">{?t?}назначить аттестацию{?/t?}</option>
                <option value="add_courses">{?t?}назначить/удалить курсы{?/t?}</option>
                <option value="set_marks">{?t?}назначить/удалить виды оценки{?/t?}</option>
                <!--option value="add_mark">{?t?}добавить виды оценки{?/t?}</option-->
                <!--option value="delete_mark">{?t?}удалить виды оценки{?/t?}</option-->
                <!--option value="delete_courses">{?t?}удалить курсы{?/t?}</option-->
                {?/if?}
                <option value="reports">{?t?}сгенерировать отчеты{?/t?}</option>
                <option value="clear_list">{?t?}очистить список выбранных элементов{?/t?}</option>
            </select>
            </td>
            <td>
            <div style='float: right;' class='button'><a href='javascript:void(0);' onclick="if (document.getElementById('checked_items_actions').value == 'clear_list') {uncheck_items(); parent.location.reload(); return false;} else {document.getElementById('checked_items_form').submit(); return false;}">{?t?}ok{?/t?}</a></div><input type='button' value='{?t?}ok{?/t?}' style='display: none;'/><div class='clear-both'></div>
            </td>
            </tr>
            </table>
            </form>
            <br/>
        </div>
    </td>
</tr>
{?else?}
<tr>
    <th align="left" nowrap><span class="header">{?t?}Выбранные элементы{?/t?}</span></th>
</tr>
<tr>
    <td>
        {?t?}ничего не выбрано{?/t?}
    </td>
</tr>
{?/if?}
</table>