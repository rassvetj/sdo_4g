<table width=100% class=main cellspacing=0 id="track_levels">
<tr>
<th>{?$track.name?}</th>
</tr>
<tr>
<td>
<ul>
{?foreach from=$levels key=number item=level?}
	<li>
		<div class="actions">
		<a href="tracks.php?c=edit_courses_level&trid={?$track.trid?}&level={?$number?}" target="_blank"><img src="images/icons/edit.gif" border="0" alt="{?t?}Редактировать состав курсов специальности{?/t?}"></a>
		</div>
		<span>{?$number?} {?t?}семестр{?/t?}</span>
		<div class="line"></div>
	</li>
	<ul>
	{?foreach from=$level key=cid item=title?}
		<li>{?$title?}</li>
	{?foreachelse?}
		<li>{?t?}нет курсов на этом семестре специальности{?/t?}</li>
	{?/foreach?}
	</ul>
{?/foreach?}
</ul>
</td>
</tr>
</table>
</div>
