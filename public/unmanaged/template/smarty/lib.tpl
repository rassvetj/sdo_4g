{?include file="all_header.tpl"?}

{?php?}
echo ph(_('Библиотека учебных материалов'));
{?/php?}

{?if $perm>1?}
<input style="width: 120px;" type="button" name="Add" value="{?t?}Добавить материал{?/t?}" onClick="document.location.href='{?$sitepath?}lib.php?action=add'">
<input style="width: 120px;" type="button" name="Rubrics" value="{?t?}Импорт рубрик{?/t?}" onClick="document.location.href='{?$sitepath?}lib.php?action=rubrics'">
{?/if?}

{?php?}
$tmp_materials = get_on_hands_material($_SESSION['s']['mid']);
$GLOBALS['controller']->captureFromOb(!empty($tmp_materials)?'m170101':CONTENT);
//$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

{?include file="lib_search.tpl"?}

<form action="{?$sitepath?}lib.php?page={?$page?}" method="POST">
<table width=100% class=main cellspacing=0>
<tr>
{?if $perm>1 && $can_assign && 1==0?}
    <th></th>
{?/if?}
    <!--th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=0">#</a>
    {?if $sort==0?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th-->
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=1">{?t?}Название{?/t?}</a>
    {?if $sort==1?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <!--th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=2">{?t?}Автор{?/t?}</a>
    {?if $sort==2?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=3">{?t?}Издатель{?/t?}</a>
    {?if $sort==3?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=4">{?t?}Год{?/t?}</a>
    {?if $sort==4?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th-->
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=5">{?t?}Тип издания{?/t?}</a>
    {?if $sort==5?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <!--th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=6">{?t?}Экз.{?/t?}</a>
    {?if $sort==6?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=7">{?t?}Уровень доступа{?/t?}</a>
    {?if $sort==7?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th-->
    <th>{?t?}Действия{?/t?}</th>
</tr>
{?if $books?}
{?foreach from=$books item=book?}
<tr>
{?if ($perm>1) && $can_assign && 1==0?}
    <td>
    <input type="checkbox" name="bids[]" value="{?$book.bid?}" {?if $book.quantity<=0?}disabled{?/if?}>
    </td>
{?/if?}
    <!--td>{?if $book.uid?}{?$book.uid?}{?/if?}</td-->
    <td>
    <b><a href="javascript:void(0);" onClick="wopen('{?$sitepath?}lib.php?action=view&bid={?$book.bid?}','libinfo',420,300);">{?$book.title?}</a></b>
    <br>
    {?if $book.description?}
    <i>{?$book.description?}</i><br>
    {?/if?}
    {?if trim($book.location)?}
    [ <a href="{?$book.location?}" target="_blank"><b>{?t?}онлайн{?/t?}</b></a> ]<br><br>
    {?/if?}
    </td>
    <!--td>{?$book.author?}</td>
    <td>{?$book.publisher?}</td>
    <td>{?$book.publish_date?}</td-->
    <td>{?$book.type|escape?}</td>
    <!--td>
    {?if $book.quantity?}
    {?$book.quantity|escape?}
    {?/if?}
    </td>
    <td>{?$book.need_access_level|escape?}</td-->
    <td nowrap>
    {?if trim($book.filename)?}
    <a href="{?$sitepath?}lib_get.php?bid={?$book.bid?}" target=_blank>
        <img border=0 title="{?t?}Открыть издание{?/t?}" src="{?$sitepath?}images/icons/look.gif">
    </a>
    {?/if?}
    {?if $book.uid && $book.quantity && $smarty.session.s.mid?}
    <a href="{?$sitepath?}lib.php?bid={?$book.bid?}&action=pre_assign&page={?$page?}">
    <img title="{?t?}Зарезервировать издание{?/t?}" alt="{?t?}Зарезервировать издание{?/t?}" border=0 width=15 src="{?$sitepath?}images/icons/people.gif">
    </a>
    {?if $book.is_reserved?}
    <a onClick="if (confirm('{?t?}Вы действительно хотите отменить резервирование?{?/t?}')) return true; else return false;" href="{?$sitepath?}lib.php?bid={?$book.bid?}&action=delete_assign&page={?$page?}">
    <img title="{?t?}Отменить резервирование{?/t?}" alt="{?t?}Отменить резервирование{?/t?}" border=0 width=15 src="{?$sitepath?}images/icons/delete.gif">
    </a>
    {?/if?}
    {?/if?}
{?if $perm>1?}
    {?if ($book.pointId > 0)?}
    <a href="{?$sitepath?}webinar/index/prepare/pointId/{?$book.pointId?}" onClick="if (confirm('{?t?}Вы уверены что хотите подготовить и опубликовать файлы вебинара?{?/t?}')) return true; return false;">
    <img alt="{?t?}Опубликовать{?/t?}" border=0 src="images/icons/ok.gif"></a>
    &nbsp;    
    {?/if?}
    {?if ($book.is_edit)?}
    <a href="{?$sitepath?}lib.php?bid={?$book.bid?}&action=edit&page={?$page?}">
    <img alt="{?t?}Редактировать{?/t?}" border=0 src="images/icons/edit.gif"></a>
    &nbsp;
    {?/if?}
    {?if $book.uid && $book.quantity && $can_assign?}
    <a href="{?$sitepath?}lib.php?bid={?$book.bid?}&action=history&page={?$page?}">
    <img alt="{?t?}Распределение издания{?/t?}" border=0 width=15 src="images/icons/people.gif"></a>
    &nbsp;
    {?/if?}
    {?if ($book.is_edit)?}
    <a onClick="if (confirm('{?t?}Удалить издание?{?/t?}')) return true; else return false;" href="{?$sitepath?}lib.php?del={?$book.bid?}&page={?$page?}"><img alt="{?t?}Удалить издание{?/t?}" border=0 src="images/icons/delete.gif"></a>
    {?/if?}
{?/if?}
    </td>
</tr>
{?/foreach?}
{?else?}
<tr>
    <td colspan=13 align=center>{?t?}ничего не найдено{?/t?}</td>
</tr>
{?/if?}
</table>
<p>
{?$pages?}
<p>
</form>
<p>
{?if $can_add?}
    <form method="POST" action="{?$sitepath?}lib.php?action=add">
        <table width=100% class=main cellspacing=0>
            <tr><th colspan=2>{?t?}Добавить издание{?/t?}</th></tr>
            <tr><td>{?t?}Название{?/t?}: </td><td><input type="text" name="title" value="" style="width: 300px;"></td></tr>
            <tr><td colspan=2>{?php?}echo okbutton();{?/php?}</td></tr>
        </table>
    </form>
{?/if?}
{?php?}
$GLOBALS['controller']->captureStop(!empty($tmp_materials)?'m170101':CONTENT);
//$GLOBALS['controller']->captureStop(CONTENT);
if (!empty($tmp_materials)) {
    $GLOBALS['controller']->captureFromReturn('m170102',$tmp_materials);
}
{?/php?}
{?include file="all_footer.tpl"?}
