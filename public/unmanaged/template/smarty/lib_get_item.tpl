<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<head>
<META content="text/html; charset=windows-1251" http-equiv="Content-Type">

<SCRIPT src="{?$sitepath?}/js/FormCheck.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="{?$sitepath?}/js/img.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="{?$sitepath?}/js/hide.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="{?$sitepath?}admin/adm.js" language="JScript" type="text/javascript"></script>
<script language="JavaScript" src="/js/mm_menu.js"></script>

<title>eLearning Server 3000</title>
<link rel="stylesheet" href="{?$sitepath?}styles/style.css" type="text/css">
</head>


<BODY  class="cPageBG" leftmargin="0" rightmargin="0" marginwidth="0" topmargin="0" marginheight="0">

{?php?}
echo ph(_('Библиотека учебных материалов'));
$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_('Библиотека учебных материалов'));
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

{?include file="lib_get_item_search.tpl"?}

<table width=100% class=main cellspacing=0>
<tr>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=0&ModID={?$ModID?}">#</a>
    {?if $sort==0?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=1&ModID={?$ModID?}">{?t?}Наименование{?/t?}</a>
    {?if $sort==1?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=2&ModID={?$ModID?}">{?t?}Автор{?/t?}</a>
    {?if $sort==2?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=3&ModID={?$ModID?}">{?t?}Издатель{?/t?}</a>
    {?if $sort==3?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th><a href="{?$sitepath?}lib.php?page={?$page?}&sort=4&ModID={?$ModID?}">{?t?}Год{?/t?}</a>
    {?if $sort==4?}<img src="{?$sitepath?}images/sort_down.gif" border=0>{?/if?}
    </th>
    <th></th>
</tr>
{?if $books?}
{?foreach from=$books item=book?}
<tr>
    <td>
    {?if $book.uid?}{?$book.uid?}{?/if?}
    <br>
    <td>
    <b>{?$book.title?}</b>
    <br>
    {?if $book.description?}
    <i>{?$book.description?}</i><br>
    {?/if?}
    {?if $book.location?}
    <a href="{?$book.location?}" target="_blank"><b>[ {?t?}онлайн{?/t?} ]</b></a><br><br>
    {?/if?}
    </td>
    <td>{?$book.author?}</td>
    <td>{?$book.publisher?}</td>
    <td>{?$book.publish_date?}</td>
    <td nowrap>
    {?if $book.filename?}    
    <a href="{?$sitepath?}lib.php?ModID={?$ModID?}&itemToMod={?$book.bid?}"><img border=0 alt="{?t?}Добавить издание{?/t?}" src="{?$sitepath?}images/icons/right.gif"></a>
    <a href="{?$sitepath?}lib_get.php?bid={?$book.bid?}" target=_blank><img border=0 alt="{?t?}Открыть издание{?/t?}" src="{?$sitepath?}images/icons/save.gif"></a>
    {?/if?}
    </td>
</tr>
{?/foreach?}
{?else?}
<tr>
    <td colspan=6>{?t?}ничего не найдено{?/t?}</td>
</tr>
{?/if?}
</table>

<p>
{?$pages?}
<p>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
{?/php?}

</BODY>
</HTML>