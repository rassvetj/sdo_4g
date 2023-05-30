{?include file="all_header.tpl"?}

{?php?}
echo ph(_('Библиотека учебных материалов'));
{?/php?}

{?if $perm>1?}
<input style="width: 120px;" type="button" name="Add" value="{?t?}Добавить материал{?/t?}" onClick="document.location.href='{?$sitepath?}lib_cms.php?action=add'">
<input style="width: 120px;" type="button" name="Rubrics" value="{?t?}Импорт рубрик{?/t?}" onClick="document.location.href='{?$sitepath?}lib_cms.php?action=rubrics'">
{?/if?}

{?php?}
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

{?*include file="lib_cms_search.tpl"*?}

<form action="{?$sitepath?}lib_cms.php?page={?$page?}" method="POST">
<div style='padding-bottom: 5px;'>
    <div style='float: left;'><img src='{?$sitepath?}images/icons/small_star.gif'>&nbsp;</div>
    <div><a href='{?$sitepath?}lib_cms.php?action=add&cid={?$search.categories?}' style='text-decoration: none;'>{?t?}создать материал{?/t?}</a></div>
</div>
<table width=100% class=main cellspacing=0>
<tr>
{?if $perm>1 && $can_add?}
    <th><input type=checkbox onClick="var i=1; while(elm=document.getElementById('book_'+i)) {elm.checked = this.checked; i++;}" title="{?t?}Отметить все{?/t?}" /></th>
{?/if?}
    <th><a href="{?$sitepath?}lib_cms.php?search[categories]={?$search.categories?}&page={?$page?}&sort=0">#</a>
    {?if $sort==0?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib_cms.php?search[categories]={?$search.categories?}&page={?$page?}&sort=1">{?t?}Наименование{?/t?}</a>
    {?if $sort==1?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib_cms.php?search[categories]={?$search.categories?}&page={?$page?}&sort=2">{?t?}Имя файла{?/t?}</a>
    {?if $sort==2?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
{?if $perm>1?}
    <th colspan=99 width=1%>{?t?}Действия{?/t?}</th>
{?/if?}
</tr>
{?if $books?}
{?foreach from=$books item=book?}
<tr>
{?if ($perm>1) && $can_add?}
    <td>
    <input type="checkbox" id="book_{?counter?}" name="bids[]" value="{?$book.bid?}">
    </td>
{?/if?}
    <td>{?$book.bid?}</td>
    <td>
    {?$book.title?}
    <br>
    {?if $book.description?}
    <i>{?$book.description?}</i><br>
    {?/if?}
    {?if trim($book.location)?}
    <a href="{?$book.location?}" target="_blank"><b>[ {?t?}онлайн{?/t?} ]</b></a><br><br>
    {?/if?}
    </td>
    <td>
    {?if $book.filename?}
    {?php?}
    $begin = strrpos($this->_tpl_vars['book']['filename'],'/')+1;
    $end = strrpos($this->_tpl_vars['book']['filename'],'?');
    $length = (($end - $begin)>0)?($end - $begin):99;
    echo substr($this->_tpl_vars['book']['filename'],$begin,$length);
    {?/php?}
    {?/if?}
    </td>
    <td nowrap>
    {?if $book.filename?}
    <a href="{?$sitepath?}lib_get.php?bid={?$book.bid?}&CID={?$search.categories?}" title="{?t?}Открыть материал{?/t?}" target=_blank>{?$icon_view?}</a>&nbsp;
    {?/if?}
    {?if $book.is_edit?}
    <a href="{?$sitepath?}lib_cms.php?bid={?$book.bid?}&action=edit&page={?$page?}" title="{?t?}Редактировать материал{?/t?}">
    <img alt="{?t?}Редактировать{?/t?}" border=0 src="images/icons/edit.gif"></a>&nbsp;
    <a onClick="if (confirm('{?t?}Удалить материал?{?/t?}')) return true; else return false;" href="{?$sitepath?}lib_cms.php?del={?$book.bid?}&page={?$page?}" title="{?t?}Удалить материал{?/t?}">
        <img alt="{?t?}Удалить{?/t?}" border=0 src="images/icons/delete.gif">
    </a>
    {?/if?}
    </td>
</tr>
{?/foreach?}
{?if ($perm>1) && $can_add?}
<tr>
    <td colspan=5 align=right>
    <p>{?t?}Выполнить действие{?/t?} &nbsp;
    <select id="action" name="action" onchange="if (this.options[this.selectedIndex].value == 'del') document.getElementById('cid4copy').style.display = 'none'; else document.getElementById('cid4copy').style.display = 'inline';">
        <option selected value="copy"> {?t?}копировать{?/t?} </option>
        <option          value="del"> {?t?}удалить{?/t?} </option>
    </select>
    <span id='cid4copy'>
        {?t?}в{?/t?}
        <select name="cid" id="cid">
        {?foreach from=$courses item=course key=cid?}
            <option value="{?$cid?}"> {?$course|escape?}</option>
        {?/foreach?}
        </select>
    </span>
    <br><br>
    {?php?}
    echo okbutton('&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;', '', 'ok', 'if (jQuery("#action").get(0).value == "del") {if (!confirm("'._('Удалить материалы?').'")) return false;}');
    {?/php?}
    </td>
</tr>
{?/if?}
{?else?}
<tr>
    <td colspan=99 align=center>{?t?}нет данных для отображения{?/t?}</td>
</tr>
{?/if?}
</table>
{?if $books?}
	<p>
	{?$pages?}
{?/if?}
<p>
</form>
<p>
{?*if $can_add?}
    <form method="POST" action="{?$sitepath?}lib_cms.php?action=add">
        <table width=100% class=main cellspacing=0>
            <tr><th colspan=2>{?t?}Добавить{?/t?}</th></tr>
            <tr><td>{?t?}Название{?/t?} </td><td><input type="text" name="title" value="" style="width: 300px;"></td></tr>
            <tr>
                <td colspan=2>
                    <input type='hidden' name='cid' value='{?$search.categories?}' />
                    <input type='hidden' name='page' value='{?$page?}' />
                    {?php?}echo okbutton();{?/php?}
                </td>
            </tr>
        </table>
    </form>
{?/if*?}
{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}
{?include file="all_footer.tpl"?}
