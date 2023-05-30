{?if $step==4?}
{?php?}
$GLOBALS['controller']->setView('DocumentPrint');
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}
<link rel="stylesheet" href="{?$sitepath?}styles/report.css" type="text/css">
{?/if?}

{?if $step!=4?}
{?php?}
$GLOBALS['controller']->captureFromOb(TRASH);
{?/php?}
{?/if?}
<div class="rep-print">
<p align=center><b>{?$reportName?}</b></p>

{?if $step!=4?}
{?php?}
$GLOBALS['controller']->captureStop(TRASH);
{?/php?}
{?/if?}

<table width=100% class=main cellspacing=0>
<tr><td>{?t?}Дата создания отчета:{?/t?} </td><td> {?$smarty.now|date_format:"%d.%m.%Y"?}</td></tr>
<tr><td>{?t?}Автор отчета:{?/t?} </td><td> {?$s.user.lname?} {?$s.user.fname?}</td></tr>

{?if $subjectArea?}
{?foreach from=$subjectArea item=data key=name?}
<tr><td>{?$name?}: </td><td> {?$data?}</td></tr>
{?/foreach?}
{?/if?}

{?if $additionalResults?}
{?foreach from=$additionalResults item=data key=name?}
<tr><td>{?$name?}: </td><td> {?$data?}</td></tr>
{?/foreach?}
{?/if?}
</table>
<p>
</div>