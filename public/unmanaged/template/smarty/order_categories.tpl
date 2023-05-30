{?if $cat!='-1' || $categories?}
<table width=100% class=main cellspacing=0>
    <tr>
        <th colspan={?$itemsPerRow?}>
            <span style='float: left;''>{?$bc?}</span>
{?if $cat>-1?}
                <a style='float: right;' href='{?$sitepath?}order.php?cat={?$cat?}'>&larr;&nbsp;{?t?}на уровень выше{?/t?}</a>
{?/if?}
        </th>
    </tr>
{?if $categories?}
{?foreach from=$categories item=cat name=categoriesIterator?}
{?assign var='isLast' value=`$smarty.foreach.categoriesIterator.last`?}
{?assign var='iteration' value=`$smarty.foreach.categoriesIterator.iteration`?}
{?if ($iteration % $itemsPerRow) == 1?}
    <tr>
{?/if?}
        <td valign="top" align='center' valign='bottom' style='padding:0px;margin:0px;'>
{?if $cat.file_image?}
            <a href='{?$sitepath?}order.php?cat={?$cat.did?}' style='padding:0px;margin:0px;'>
                <img style='padding-top:3px;margin:0px;' src='{?$sitepath?}temp/courses_groups/{?$cat.did?}/{?$cat.file_image?}' title='{?$cat.name?}'/>
            </a>
{?/if?}
            <a href='{?$sitepath?}order.php?cat={?$cat.did?}' {?if !$cat.file_image?}style="display:block;padding-top:122px;margin:0;"{?/if?}>
                <h3>{?$cat.name?} ({?$cat.allCourses?})</h3>
            </a>
        </td>
        {?if $isLast && ($iteration % $itemsPerRow) != 0 ?}
        <td colspan='{?math equation='ipr - (i % ipr)' i=$iteration ipr=$itemsPerRow ?}'></td>
        {?/if?}
{?if ($iteration % $itemsPerRow) == 0 || $isLast ?}
    </tr>
{?/if?}
{?/foreach?}
{?else?}
    <tr><td colspan={?$itemsPerRow?} align='center'>{?t?}рубрики отсутствуют{?/t?}</td></tr>
{?/if?}
</table>
{?/if?}
