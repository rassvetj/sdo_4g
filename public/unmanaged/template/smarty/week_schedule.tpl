{?foreach from=$week_schedule item=day_schedule?}
<div class="schedule-item">
{?if $is_edited && $add_permission?}
        <div style='padding-bottom: 0px; zoom: 1; margin-bottom: -2.5em; z-index: 100; position: relative;'>
            <div style='float: left;'><img src='{?$sitepath?}images/icons/small_star.gif'>&nbsp;</div>
            <div><a href='{?$sitepath?}schedule.php4?c=add&tweek={?$day_schedule.tweek?}' style='text-decoration: none;'>{?t?}создать занятие{?/t?}</a></div>
        </div>
     {?/if?}
   <table width=100% class="main schedule" cellspacing=0>
		<col width="45%" />
		<col width="20%" />
		<col width="35%" />
		<caption>
			<span class="day-name">{?$day_schedule.day_name?}</span>,
			{?assign var='dateItems' value='.'|explode:$day_schedule.date?}
			{?foreach from=$dateItems item=dayItem name='dayItemsIteration'?}<span class="date">{?$dayItem?}{?if !$smarty.foreach.dayItemsIteration.last ?}.{?/if?}{?/foreach?}{?foreach from=$dateItems item=dayItem?}</span>{?/foreach?}
		</caption>
		<tr>
			<th>{?t?}название{?/t?}</th>
			<th>{?t?}время{?/t?}</th>
			<th>{?t?}заметки{?/t?}</th>
		</tr>
		{?foreach from=$day_schedule.studies key=key item=study?}
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" border="0" class="schedule-info">
					<tr>
						<td valign="middle">
							<img src="{?$smarty.server.document_root?}images/events/{?$study.icon?}" class="icon">
						</td>
						<td valign="top">
							<p class="course">{?$study.course_name?}</p>
							<p class="subject">
							    {?if (strlen(trim($study.connectId)))?}
                                <a href="cp.php?id={?$study.sheid?}" target="_blank" title="{?t?}Запустить данное занятие{?/t?}">{?$study.name?}</a>
							    {?else?}							
								<a href="schedule.php4?c=go&mode_frames=1&sheid={?$study.sheid?}" title="{?t?}Запустить данное занятие{?/t?}">{?$study.name?}</a>
								{?/if?}
								{?if $study.penalty?}
								<div class="penalty">
									<span class="penalty-text">{?t?}Штраф за несвоевременную сдачу{?/t?}</span>
									<span class="penalty-pc"{?$study.penalty?}%</span>
								</div>
								{?/if?}
							</p>
							{?if $study.teacher_mid?}
							<p class="teacher">{?t?}Преподаватель{?/t?}: <a href='#' onclick="javascript: window.open('userinfo.php?mid={?$study.teacher_mid?}', 'teach_{?$study.teacher_mid?}', 'width=430,height=180');">{?$study.teacher?}</p>
							{?/if?}
						</td>
					</tr>
				</table>
			</td>
			<td align="center">{?if $study.period neq ""?}{?$study.period?}{?elseif ($study.time.begin eq '00:00') && ($study.time.end eq '23:59')?}{?t?}весь день{?/t?}{?else?}{?$study.time.begin?} - {?$study.time.end?}{?/if?}</td>
			<td>
				<p class="description">{?$study.description?}</p>
				{?if $is_edited eq 1 && $study.edit_permission?}
				<p align="right" style="white-space: nowrap;">
					<!-- a href='schedule_plan.php?sheid={?$study.sheid?}&mode=plan' title='{?t?}Распечатать план занятий{?/t?}' onclick="wopen('','printwin')" target="printwin"></a -->
					<a href='schedule.php4?c=modify&sheid={?$study.sheid?}'" title='{?t?}Редактировать занятие{?/t?}'><img src='{?$smarty.server.document_root?}images/icons/edit.gif' border=0></a>
					<a href="schedule.php4?c=delete&sheid={?$study.sheid?}&tweek={?$day_schedule.tweek?}" onClick='return confirm("{?t?}Удалить это занятие?{?/t?}")' title='{?t?}Удалить это занятие{?/t?}'><img src='{?$smarty.server.document_root?}images/icons/delete.gif' border="0"></a>
				</p>
				{?/if?}
			</td>
		</tr>
		{?foreachelse?}
		<tr>
			<td colspan="3"><div class="no-schedules">{?t?}на этот день не назначено ни одного занятия{?/t?}</div></td>
		</tr>
		{?/foreach?}
	</table>
</div>
  {?/foreach?}