<!-- TITLE PAGE -->
{?php?}
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<p align=center>
{?$smarty.const.APPLICATION_TITLE?}
</p>
<p align=center><font size=+3><b>{?$info.name?}</b><br>{?t?}Справочное руководство{?/t?}</font></p>
{?t?}Подготовлено:{?/t?} <i>{?$smarty.now|date_format:"%d.%m.%Y"?}</i><br>
{?t?}Подготовил:{?/t?} <i>{?$created_by->attributes.LastName|escape?} {?$created_by->attributes.FirstName|escape?}</i><br>
<!-- CONTENT PAGE -->
<hr/>
<p align=center><font size=+2>{?t?}Содержание{?/t?}</font>
{?if $info?}
<p align=left>
{?foreach from=$info.perms item=i key=k?}
  <tr>
    <td {?if is_array($i.actions) && count($i.actions)?}rowspan=2{?/if?} valign=top>

    {?$i.i?} <a href="#a{?$i.i|replace:".":"a"?}">{?$i.name|capitalize?}</a><br>

    </td>


  </tr>

{?/foreach?}
</p>
{?/if?}
<hr/>
<!-- ABSTRACT PAGE -->
<p align=center><font size=+2>{?t?}Аннотация{?/t?}</font>

<p align=left>
{?t?}Настоящее руководство предназначено для обеспечения работы пользователей, имеющих статус{?/t?} "{?$info.name?}".

<p>{?t?}В настоящем руководстве приведены сведения о назначении различных разделов, доступных пользвателю, условиях функционирования, указаны основные действия пользователя.{?/t?}


</p>
<hr>
<!-- INTRO PAGE -->
<p align=center><font size=+2>{?t?}Введение{?/t?}</font>

<p align=left>
{?t?}Настоящее руководство предназначено для обеспечения работы пользователей, имеющих статус{?/t?} "{?$info.name?}".

<p>{?t?}В настоящем руководстве приведены сведения о назначении различных разделов, доступных пользвателю, условиях функционирования, указаны основные действия пользователя.{?/t?}


</p>
<hr/>
</p>
<hr>
<!-- GLOSSARY PAGE -->
<p align=center><font size=+2>{?t?}Основные термины и определения{?/t?}</font>
#include "glossary.htm"
<li>"{?t?}Статус{?/t?}" - {?t?}совокупность разрешенных действий пользователя в системе.{?/t?}
<li>"{?t?}Обучаемый{?/t?}"
<li>"{?$info.name?}" - {?t?}статус пользвователя в систем определяемый профилем{?/t?}
<li>"{?t?}Профиль{?/t?}" - {?t?}перечень доступных данных и функций пользователя{?/t?}
<li>"{?t?}Роль{?/t?}"

<li>"{?t?}Учебный курс{?/t?}"
<li>"{?t?}Расписание{?/t?}"
<li>"{?t?}Результаты обучения{?/t?}"
<li>"{?t?}Задание{?/t?}"
<li>"{?t?}Библиотека{?/t?}"
<li>"{?t?}Общение{?/t?}"


</p>
<hr/>
<!-- ALL PAGES -->
<hr/>
<table border=0 width=100% border=1 cellspacing=0 cellpadding=4>
{?if $info?}
{?foreach from=$info.perms item=i key=k?}
  <tr>
    <td {?if is_array($i.actions) && count($i.actions)?}rowspan=2{?/if?} valign=top>

    <b><a name="a{?$i.i|replace:".":"a"?}">{?$i.i?} {?$i.name|capitalize?}</a></b>

    </td>
    <td valign=top>
    {?if is_array($i.actions) && count($i.actions)?}
        <b>{?t?}Действия:{?/t?}</b><br>
        {?foreach from=$i.actions item=action?}
            <li>{?$action?}
        {?/foreach?}
    {?else?}
    {?if is_array($i.helpfiles) && count($i.helpfiles)?}
    <b>{?t?}Описание:{?/t?}</b>
    {?foreach from=$i.helpfiles item=hf?}
        <p>{?include file=$hf?}</p>
        <hr>
    {?/foreach?}
    {?/if?}
    {?/if?}
    </td>
    <td {?if is_array($i.actions) && count($i.actions)?}rowspan=2{?/if?} valign=top nowrap>
    {?if $i.url?}<br><a target=_blank href="{?$i.url?}">страница >></a>{?/if?}
    </td>
  </tr>
{?if is_array($i.actions) && count($i.actions)?}
  <tr>
    <td>
    {?if is_array($i.helpfiles) && count($i.helpfiles)?}
    {?foreach from=$i.helpfiles item=hf?}
        <p>{?include file=$hf?}</p>
    {?/foreach?}
    {?/if?}
    </td>
  </tr>
{?/if?}
{?/foreach?}
{?/if?}
</table>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}