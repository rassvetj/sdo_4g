<br>
<form name="{?$action?}" action="{?$sitepath?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}{?if $thread_info?}/?id={?$thread_info.thread?}{?/if?}" method="POST">
<input name="action" type="hidden" value="{?$action?}">
<input name="data[category][int]" type="hidden" value="{?$category?}">
{?include file="forum_form.tpl"?}
</form>