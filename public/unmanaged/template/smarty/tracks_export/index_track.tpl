<script type="text/javascript" language="JavaScript">
<!--
var levels = new Array();
//-->
</script>
{?if $data->is_specialities_exists && $data->levels?}
{?foreach name=data from=$data->levels key=key item=level?}
{?if $key != $smarty.const.COURSE_FREE?}
<div class="courses-list">
	<h1>{?$key?}-{?t?}й семестр{?/t?}</h1>
	<ul>
		{?foreach name="courses" from=$level item=course?}
		<li id="course_{?$course->id?}_{?$key?}" style="display: none;">
			{?if $course->structureHtml?}
			<a href="term{?$key?}/index_{?$course->id?}.html">{?$course->attributes.title?}</a>
			{?else?}
			{?$course->attributes.title?}
			{?/if?}
		</li>
		{?/foreach?}
</div>
<script type="text/javascript" language="JavaScript">
<!--
	levels[levels.length] = Number('{?$key?}');
//-->
</script>
{?/if?}
{?/foreach?}
{?/if?}
{?if $data->levels.free?}
<div class="courses-list">
	{?if $data->is_specialities_exists?}
	<h1>{?t?}Курсы по выбору{?/t?}</h1>
	{?/if?}
	<ul>
		{?foreach from=$data->levels.free item=course_free?}
		<li id="course_free_{?$course_free->id?}" style="display: none;"><a href="termfree/index_{?$course_free->id?}.html">{?$course_free->attributes.title?}</a></li>
		{?/foreach?}
	</ul>
</div>
{?/if?}

{?if $data->permissions?}
<script type="text/javascript" src="./common/user.js"></script>
<script type="text/javascript">
	var permissions = new Array();
	{?foreach from=$data->permissions key=user item=courses_free?}
	{?if $courses_free?}
	permissions[{?$user?}] = [{?$courses_free?}]
	{?/if?}
	{?/foreach?}

	function show_free_courses() {
		var j;
		if (Object.isUndefined(window.user)) {
			$P(document).observe('dom:loaded', function() {
				eLS.utils.showMessageBox('{?t?}Пользователь не определен{?/t?}');
			});
			return false
		}
		if (!permissions[user]) { return; }
		for (var i = 0; i < permissions[user].length; i++) {
			var obj = $P('course_free_' + permissions[user][i]);
			if (obj) {
				obj.show();
			}

			if (levels && levels.length) {
				for(var j=0; j < levels.length; j++) {
					var obj = $P('course_' + permissions[user][i] + '_' + levels[j]);
					if (obj) {
						obj.show();
					}
				}
			}
		}
	}
	show_free_courses();
</script>
{?/if?}
