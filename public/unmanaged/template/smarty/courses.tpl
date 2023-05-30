{?if $links?}
<table width=100% class=main cellspacing=0>
    <tr>
        <td align=center>{?$links?}</td>
    </tr>
<table>
<br>
{?/if?}

{?if $smarty.session.s.perm >= 3?}
    {?include file="common/add_link.tpl" url=$add_url caption=$add_caption?}
{?/if?}

<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Название{?/t?}</th>
    <th>{?t?}Статус{?/t?}</th>
    <th>{?t?}Период{?/t?}</th>
    <th>{?t?}Длительность{?/t?}</th>
    <th width=1%>{?t?}Действия{?/t?}</th>
</tr>
{?if $courses?}
    {?foreach from=$courses item=course?}
     <tr>
        <td>
        <a href="{?$sitepath?}course_structure.php?CID={?$course.CID?}" title="{?t?}Открыть курс{?/t?}">
        {?if $course.Status > 1 && $course.active?}<b>{?/if?}
        {?$course.Title?}
        {?if $course.Status > 1 && $course.active?}</b>{?/if?}
        </a>
        </td>
        <td>{?$course.StatusName?}</td>
        <td>{?$course.timeline?}</td>
        <td>{?$course.longtime?}</td>
        <td nowrap>
        <a href="{?$sitepath?}course_structure.php?CID={?$course.CID?}" title="{?t?}Просмотреть курс{?/t?}">{?$icon_view?}</a>
		{?if $perm_manage_content && !$course.locked?}
	        <a href="{?$sitepath?}course_constructor.php?CID={?$course.CID?}" title="{?t?}Редактировать программу курса{?/t?}">{?$icon_struct?}</a>
        {?/if?}
		{?if $perm_manage?}
    	    <a href='courses.php4?copy={?$course.CID?}' title='{?t?}Копировать курс{?/t?}' onClick='if (!confirm("{?t?}Вы действительно желаете скопировать курс? При этом будут скопированы все модули, входящие в состав данного курса.{?/t?}")) return false;'>{?$icon_copy?}</a>
        {?/if?}
		{?if ($perm_manage_content && !$course.locked) || $smarty.session.s.perm >= 3?}
            <a href='teachers/course_import.php?CID={?$course.CID?}' title='{?t?}Импортировать курс{?/t?}'>{?$icon_import?}</a>
        {?/if?}
		{?if $perm_manage?}
	        {?if $course.locked?}
	            <a href='courses.php4?unlock={?$course.CID?}' title='{?t?}Разблокировать{?/t?}' onClick='if (!confirm("{?t?}Вы действительно желаете разблокировать курс?{?/t?}")) return false;'>{?$icon_lock?}</a>
	        {?else?}
	            <a href='courses.php4?lock={?$course.CID?}' title='{?t?}Заблокировать{?/t?}' onClick='if (!confirm("{?t?}Вы действительно желаете заблокировать курс? При этом любые операции со структурой курса и с модулями курса будут невозможны.{?/t?}")) return false;'>{?$icon_unlock?}</a>
	        {?/if?}
	    {?/if?}
	    {?if $perm_manage_groups?}
	        <a href="glossary.php?cid={?$course.CID?}" title='{?t?}Редактировать глоссарий{?/t?}'><img border=0 src="{?$sitepath?}images/icons/book.gif"></a>
	    {?/if?}
        {?if $perm_manage && !$course.locked?}
            <a href="courses.php4?redCID={?$course.CID?}#edit" title='{?t?}Редактировать{?/t?}'>{?$icon_edit?}</a>
        {?/if?}
        {?if $perm_manage && !$course.locked?}
            <a href="courses.php4?delete={?$course.CID?}" title="{?t?}Удалить курс{?/t?}" onClick='if (!confirm("{?t?}Вы действительно желаете удалить курс? При этом все материалы курса и вся статистика обучения по курсу будут удалены.{?/t?}")) return false;'>{?$icon_delete?}</a>
        {?/if?}
        </td>
     </tr>
    {?/foreach?}
{?else?}
<tr><td colspan=4 align=center>{?t?}не найдено{?/t?}</td></tr>
{?/if?}
</table>

{?if $links?}
<br>
<table width=100% class=main cellspacing=0>
    <tr>
        <td align=center>{?$links?}</td>
    </tr>
</table>
<br>
{?/if?}