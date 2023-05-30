{?if $BLANK?}
<table width="100%" align="center" cellspacing="0">
    <tr>
        <td>
{?/if?}

{?if !$BLANK && !$LESSON_ID_URL_PART ?}
{?$BREADCRUMBS?}
<br>
{?/if?}








    <div class="lesson_forum_wrap">
    {?if $messages?}
        {?foreach name="messages" from=$messages item=message?}
        {?assign var=tempMID value=$message.mid?}
        {?assign var=tempCurrentUserId value=$lessonInfo.currentUserId?}
        {?if $message.parent && $message.level?}

            {?section name="loop" loop=$message.level?}
            <!--_START_CHILD_-->
            {?/section?}
        {?/if?}
        <div class="lf_item{?if $lessonInfo && $lessonInfo.marked.$tempMID?} marked_message{?/if?}">
            <div class="lf_autor">
                <span class="nowrap">
                    <a title="Карточка" href="{?$SITEPATH?}user/list/view/user_id/{?$message.mid?}" rel="pcard" target="lightbox" class="lightbox pcard-link">
                        <img class="ui-els-icon " title="Карточка" src="/images/content-modules/grid/card.gif">
                    </a>
                </span>	            
                <span class="author">
                    {?if $message.email?}
                        <a href="mailto:{?$message.email?}">
                    {?/if?}
                    {?$message.author?}
                    {?if $message.email?}</a>{?/if?}
                </span>
                <span class="date">({?$message.date|trim?})</span>
                {?if $MODERATE || ($MID==$message.mid)?}
                    {?if !$message.is_topic ?}
                        <a class="lf_delete" href="{?$SITEPATH?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/{?$LESSON_ID_URL_PART?}?thread={?$message.thread?}&amp;action=delete_message&amp;id={?$message.id?}{?if $BLANK?}&amp;view=blank{?/if?}" onclick="if (confirm('Удалить сообщение?')) return true; else return false;" title="Удалить сообщение" alt="Удалить сообщение"></a>
                    {?/if?}
                {?/if?}                        
                {?if $lessonInfo &&
                     $lessonInfo.vedomost &&
                     $lessonInfo.teacher != $tempMID?}
                    {?if $lessonInfo.marked.$tempMID?}
                        <div class="evaluation_of_exercise">{?t?}Оценка за занятие{?/t?}: <b>{?$lessonInfo.marked.$tempMID?}</b></div>
                    {?else?}
                        {?if $lessonInfo.teacher == $lessonInfo.currentUserId?} 
                            <form class="evaluation_of_exercise" action='' method='POST' onSubmit="if (confirm('{?t?}Вы действительно желаете выставить оценку и прекратить работу данного слушателя с форумом?{?/t?}')) return true; else return false;">
                                <div>
                                    {?t?}Оценка за занятие{?/t?}:
                                    <input type='hidden' name='user_id' value='{?$tempMID?}'/>
                                    <input type='text' size=1 name='user_mark'/>
                                    <input type='submit' name='setusermark' value='OK'>
                                </div>
                            </form>
                        {?/if?}
                    {?/if?}
                {?/if?}
            </div>
            {?$message.message?}
        </div>
        {?if $msg == $message.id && !$IS_RESULT ?}
            <form name="create_message" action="{?$sitepath?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/{?$LESSON_ID_URL_PART?}" method="POST">
                <input name="action" type="hidden" value="create_message">
                <input name="data[thread][int]" type="hidden" value="{?$thread?}">
                <input name="data[parent][int]" type="hidden" value="{?$msg?}">
                {?include file="forum_form.tpl"?}
            </form>
        {?/if?}    

        {?if $message.parent && $message.level?}
            {?section name="loop" loop=$message.level?}
                <!--_END_CHILD_-->
            {?/section?}
        {?/if?}

        {?/foreach?}
    {?else?}
        {?t?}не найдено{?/t?}
    {?/if?}
    </div>
    {?if !$IS_RESULT &&
         (!$lessonInfo ||
            ($lessonInfo &&
            ($lessonInfo.teacher == $lessonInfo.currentUserId ||
            ($lessonInfo.teacher != $lessonInfo.currentUserId && !$lessonInfo.marked.$tempCurrentUserId))))
         ?}
                <form name="create_message" action="{?$sitepath?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/{?$LESSON_ID_URL_PART?}" method="POST">
                    <input name="action" type="hidden" value="create_message">
                    <input name="data[thread][int]" type="hidden" value="{?$thread?}">
                    {?include file="forum_form.tpl" answer="true"?}
                </form>
            <br/>
    {?/if?}   

{?if $MODERATE && $thread && !$BLANK && 1==2?}
<form name="move_thread" action="" method="POST" onSubmit="if (confirm('{?t?}Переместить тему?{?/t?}')) return true; else return false;">
<input name="action" type="hidden" value="move_thread">
<input name="data[thread][int]" type="hidden" value="{?$thread?}">
	<table width=100% class=main cellspacing=0>
		<tr>
			<th colspan=2>{?t?}Переместить тему{?/t?}</th>
		</tr>
		<tr>
			<td>
			<div style="float: left;">В &nbsp;
			<select size="1" name="data[category][int]">
			{?if $categories?}
				{?foreach from=$categories item=item?}
    			<option value="{?$item.id?}"> {?$item.name?}</option>
				{?/foreach?}
			{?/if?}
			</select> &nbsp;
			</div>
            <div class='button ok'><a href='javascript:void(0);' onclick="return eLS.utils.form.submit(this);">{?t?}Переместить{?/t?}</a></div><input type='submit' class="submit" value='{?t?}переместить{?/t?}' style='display: none;'/><div class='clear-both'></div>
			</td>
		</tr>
	</table>
</form>
{?/if?}

{?if $BLANK?}
</div>
{?/if?}