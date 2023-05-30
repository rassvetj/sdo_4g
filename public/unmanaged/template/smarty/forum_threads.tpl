<style type="text/css">
.forum-threads .forum-author,
.forum-threads .forum-author a,
.forum-threads .forum-messages-count,
.forum-threads .forum-last-update,
.forum-threads .forum-last-update span,
.forum-threads .forum-actions {
	white-space: nowrap;
}
.forum-topic img {
	display: block;
	width: 16px;
	height: 16px;
	position: absolute;	
	zoom: 1;
}
.ie6 .forum-topic img {
	display: inline;
	width: auto;
	height: auto;
	position: static;
}
.forum-topic {
	position: relative;
	zoom: 1;
}
.forum-topic > img + a {
	display: inline-block;
	margin-left: 23px;
}
.ie7 .forum-topic img {
	position: static;
	float: left;
}
.ie7 .forum-topic a {
	margin-left: 7px;
	float: left;
}
</style>

<link rel="stylesheet" type="text/css" href="{?$sitepath?}css/content-modules/grid.css" media="screen">
<script type="text/javascript" src="{?$sitepath?}js/lib/jquery/jquery.collapsorz_1.1.min.js"></script>
<script type="text/javascript" src="{?$sitepath?}js/content-modules/grid.js"></script>

{?$BREADCRUMBS?}
<div class="dropdown-actions dropdown-actions-forum dropdown-actions-empty">
	<span class="clicker"></span>
	<a href="{?$sitepath?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/?action=create_thread&category={?$category?}">{?t?}создать тему{?/t?}</a>
</div>

{?assign var=owner value='no'?}
{?if $threads?}
{?foreach from=$threads item=thread?}
{?if $MID==$thread.mid?}
{?assign var=owner value='yes'?}
{?/if?}
{?/foreach?}
{?/if?}

<div id="forum-threads" class="els-grid forum-threads"><table cellspacing="0">
	<thead>
		<tr>
			<th class="forum-topic">{?t?}Темы{?/t?}</th>
			<th class="grid-narrow forum-author">{?t?}Автор{?/t?}</th>
			<th class="grid-narrow forum-messages-count">{?t?}Сообщений{?/t?}</th>
			<th class="grid-narrow forum-last-update">{?t?}Обновление{?/t?}</th>
			{?if $MODERATE || $owner == 'yes'?}
			<th class="grid-narrow grid-actions forum-actions">{?t?}Действия{?/t?}</th>
			{?/if?}
		</tr>
	</thead>
	<tbody>
		{?if $threads?}
		{?foreach from=$threads item=thread name=topics?}
		<tr {?if $smarty.foreach.topics.iteration mod 2 == 0 ?}class="even"{?else?}class="alt odd"{?/if?} id="autogenerated-forum-row-id-{?$smarty.foreach.topics.iteration?}">
			<td class="forum-topic"><img src="{?$sitepath?}{?$ICONS[$thread.icon]?}"><a href="{?$SITEPATH?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/{?$LESSON_ID_URL_PART?}?thread={?$thread.thread?}">{?$thread.name|strip_tags?}</a></td>
			<td class="forum-author">{?if $thread.email?}<a href="mailto:{?$thread.email?}">{?/if?}{?$thread.author?}{?if $thread.email?}</a>{?/if?}</td>
			<td class="forum-messages-count">{?$thread.answers?}</td>
			<td class="forum-last-update"><span>{?$thread.date?}</span></td>
			{?if $MODERATE || ($MID==$thread.mid) || $owner == 'yes' ?}
			<td class="grid-actions forum-actions">
				{?if $MODERATE || ($MID==$thread.mid) ?}
				<menu class="grid-row-actions">
					<ul class="dropdown">
						<li><a href="{?$SITEPATH?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/?category={?$category?}&action=edit_thread&id={?$thread.thread?}"><img src="{?$SITEPATH?}images/icons/edit.gif" title="Редактировать" class="ui-els-icon"><span>Редактировать</span></a></li>
						<li><a href="{?$SITEPATH?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/?action=delete_thread&amp;id={?$thread.thread?}"><img src="{?$SITEPATH?}images/icons/delete.gif" title="Удалить" onClick="if (confirm('{?t?}Вы действительно хотите удалить?{?/t?}')) return true; return false;" class="ui-els-icon"><span onClick="if (confirm('{?t?}Вы действительно хотите удалить?{?/t?}')) return true; return false;" >Удалить</span></a></li>
					</ul>
				</menu>
				{?/if?}
			</td>
			{?/if?}
		</tr>
		{?/foreach?}
		{?else?}
		<tr class="els-grid-no-actions">
			<td class="no-result" colspan={?if $MODERATE || ($MID==$thread.mid) || $owner == 'yes' ?}5{?else?}4{?/if?}>{?t?}Отсутствуют данные для отображения{?/t?}</td>
		</tr>
		{?/if?}
	</tbody>
</table></div>
