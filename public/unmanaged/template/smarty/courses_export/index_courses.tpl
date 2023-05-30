<script type="text/javascript">
<!--
	checkDate = function(timestamp, url) {
		var d = new Date();
		d.setUTCHours(0,0,0,0);

		if ((timestamp*1000) >= d.getTime() - 60*60*1000 && (timestamp*1000) <= d.getTime() + 60*60*1000) {
			return true;
		}
		if (eLS.isDOMLoaded) {
			eLS.utils.showMessageBox('{?t?}Задание назначено на другое время!{?/t?}');
		} else {
			$P(document).observe('dom:loaded', function() {
				eLS.utils.showMessageBox('{?t?}Задание назначено на другое время!{?/t?}');
			});
		}
		return false;
	}
//-->
</script>


<div class="week-navigation auto-size-fit">
	<div>
		{?if $data.prev?}
		<span class="previous-week-date"><a title="{?t?}к предыдущей неделе{?/t?}" href="index_{?$data.prev?}.html">{?$data.prevDay?}</a></span>
		{?/if?}
		{?if $data.prev && $data.next?}
		<span style="font-size: 200%;">&ndash;</span>
		{?/if?}
		{?if $data.next?}
		<span class="next-week-date"><a title="{?t?}к следующей неделе{?/t?}" href="index_{?$data.next?}.html">{?$data.nextDay?}</a></span>
		{?/if?}
	</div>
	<div class="current-week"><span><a href="index.html">{?t?}к текущей неделе{?/t?}</a></span></div>
</div>

{?if $data.schedules?}
{?foreach from=$data.schedules item=day_schedule?}
{?assign var='dateItems' value='.'|explode:$day_schedule.date?}
{?assign var='dateid' value='-'|implode:$dateItems?}
<div class="schedule-item">
	<table width=100% class="main schedule" cellspacing=0>
		<col width="45%" /><col width="20%" /><col width="35%" />
		<caption>
			<span class="day-name">{?$day_schedule.day_name?}</span>,
			{?foreach from=$dateItems item=dayItem name='dayItemsIteration'?}<span class="date">{?$dayItem?}{?if !$smarty.foreach.dayItemsIteration.last ?}.{?/if?}{?/foreach?}{?foreach from=$dateItems item=dayItem?}</span>{?/foreach?}
		</caption>
		<tr>
			<th>{?t?}название{?/t?}</th>
			<th>{?t?}время{?/t?}</th>
			<th>{?t?}заметки{?/t?}</th>
		</tr>
		{?foreach from=$day_schedule.studies key=key item=study?}
		<tr id="schedule_{?$study.sheid?}_{?$sheids[$study.sheid]|intval?}" date-id="day_schedule_{?$dateid?}" style="display: none;">
			{?if $sheids[$study.sheid]++?}{?/if?}
			<td>
				<table cellpadding="0" cellspacing="0" border="0" class="schedule-info">
					<tr>
						<td valign="middle">
							<img src="./common/images/events/{?$study.icon?}" class="icon">
						</td>
						<td valign="top">
							<p class="course">{?$study.course_name?}</p>
							<p class="subject">
								<!-- TODO: как получить oid на основе этих данных? -->
								<!-- TODO: может-ли быть несколько разных cid в одном оглавлении? -->
								<a
									title="{?t?}Запустить данное занятие{?/t?}"
									onclick="if (checkDate({?$day_schedule.tweekUTC?})) return true; else return false;"
									href="termfree/index_{?$study.cid?}.html?type={?if $study.test_id?}test{?else?}module{?/if?}&id={?if $study.test_id?}{?$study.test_id?}{?else?}{?$study.module_id?}{?/if?}&sheid={?$study.sheid?}">{?$study.name?}</a>
								{?if $study.penalty?}
								<div class="penalty">
									<span class="penalty-text">{?t?}Штраф за несвоевременную сдачу{?/t?}</span>
									<span class="penalty-pc"{?$study.penalty?}%</span>
								</div>
								{?/if?}
								{?*
									{?$study.course_name?}<br>
									{?$study.teacher?}
								*?}
							</p>
							{?if $study.teacher_mid?}
							<p class="teacher">{?t?}Преподаватель{?/t?}: {?$study.teacher?}</p>
							{?/if?}
						</td>
					</tr>
				</table>
			</td>
			<td align="center">{?if $study.period neq ""?}{?$study.period?}{?elseif ($study.time.begin eq '00:00') && ($study.time.end eq '23:59')?}{?t?}весь день{?/t?}{?else?}{?$study.time.begin?} - {?$study.time.end?}{?/if?}</td>
			<td>
				<p class="description">{?$study.description?}</p>
			</td>
		</tr>
		{?/foreach?}
		<tr id="day_schedule_{?$dateid?}_null">
			<td colspan="3"><div class="no-schedules">{?t?}на этот день не назначено ни одного занятия{?/t?}</div></td>
		</tr>
	</table>
</div>
{?/foreach?}

{?if $data.schedules_permissions?}
<script type="text/javascript" src="./common/user.js"></script>
<script type="text/javascript">
	var permissions = new Array();
	{?foreach from=$data.schedules_permissions key=user item=schedules?}
	{?if $schedules?}
	permissions[{?$user?}] = [{?$schedules?}];
	{?/if?}
	{?/foreach?}

	function show_schedules() {
		var j;
		if (Object.isUndefined(window.user)) {
			$P(document).observe('dom:loaded', function() {
				eLS.utils.showMessageBox('{?t?}Пользователь не определен{?/t?}');
			});
			return false
		}
		if (!permissions[user]) { return; }
		for (var i = 0; i < permissions[user].length; i++) {
			var j = 0;
			while(obj = $P('schedule_' + permissions[user][i] + '_' + (j++))) {
				var nil = $P(obj.readAttribute('date-id') + '_null');
				if (nil) { nil.hide(); }
				obj.show();
			}
		}
	}
	show_schedules();
</script>
{?/if?}

{?/if?}