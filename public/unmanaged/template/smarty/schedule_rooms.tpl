{?popup_init src='js/overlib.js'?}

<table width=100% class=main cellspacing=0>
<tr><td align=right>
<table width="0"><tr>
<td><a title="{?t?}Предыдущая неделя{?/t?}" href="{?$sitepath?}schedule_rooms.php?tweek={?$tweeklast?}&cid={?$cid?}"><img src="{?$sitepath?}images/icons/left.gif" border="0" /></a></td>
<td>{?$tweeklast|date_format:"%d.%m.%Y"?}</td>
<td>-</td>
<td>{?$tweeknext|date_format:"%d.%m.%Y"?}</td>
<td><a title="{?t?}Следующая неделя{?/t?}" href="{?$sitepath?}schedule_rooms.php?tweek={?$tweeknext?}&cid={?$cid?}"><img src="{?$sitepath?}images/icons/right.gif" border="0" /></a></td>
</tr></table>
</td></tr>
</table>
<br>
<table border="0" cellspacing="0" cellpadding="0" height="100%" width="100%">
 <tr valign="top">
  <td valign="top" colspan="3" class="transparent">
{?foreach from=$week_schedule item=day_schedule?}
   <table width="100%" cellspacing="0" cellpadding="0"  valign="top">
    <tr>
     <td align="left" width="60%" class="th-before-corner">
     {?if $schedule_perm_edit && $schedule_perm_add?}
        <div style='padding-bottom: 0px;'>
            <div style='float: left;'><img src='{?$sitepath?}images/icons/small_star.gif'>&nbsp;</div>
            <div><a href='{?$sitepath?}schedule.php4?c=add&tweek={?$day_schedule.tweek?}' style='text-decoration: none;'>{?t?}создать занятие{?/t?}</a></div>
        </div>
     {?/if?}
     <img src="{?$skin_path?}/images/spacer.gif" width="1" height="1" alt="">
     </td>
     <td class="th3" align="right" background="{?$skin_path?}/images/corner03.gif" width="25px" border="0" height="15px">
        <img src="{?$skin_path?}/images/spacer.gif" width="25" height="1" alt="">
     </td>
     <td  align="right" class="th3" nowrap ><b>{?$day_schedule.day_name?} &nbsp;  <span class="date">{?$day_schedule.date?}</span></b>&nbsp;</td>
    </tr>
   </table>
   <table width="100%" cellspacing="1" cellpadding="0"  valign="top">
	   <tr><td>
       <table width=100% class=main cellspacing=0>
           <tr><th width={?$width?}%>{?t?}Сетка занятий{?/t?}</th>
           {?if $rooms?}
           {?foreach from=$rooms item=room?}
               <th width={?$width?}%>{?$room.name?}</th>
           {?/foreach?}
           {?/if?}
           </tr>
           {?if $periods?}
           {?foreach from=$periods item=period?}
           <tr>
               <td align=center>{?$period.name?}</td>
	           {?if $rooms?}
	           {?foreach from=$rooms item=room?}
               <!--td valign=top ondblclick="wopen('{?$sitepath?}schedule.php4?c=add&tweek={?$day_schedule.tweek?}&room={?$room.rid?}&period={?$period.lid?}&CID={?$cid?}','')" onmouseover="style.cursor='pointer'" title="{?t?}Двойной клик по ячейке добавит занятие{?/t?}"-->
               <td valign=top ondblclick="window.location.href = '{?$sitepath?}schedule.php4?c=add&tweek={?$day_schedule.tweek?}&room={?$room.rid?}&period={?$period.lid?}&CID={?$cid?}'" onmouseover="style.cursor='pointer'" title="{?t?}Двойной клик по ячейке добавит занятие{?/t?}">
               {?assign var="i" value=`$room.rid`?}
               {?assign var="j" value=`$period.lid`?}
               {?if $day_schedule.studies.$i.$j.items?}
                   <table border=0>
                   <tr>
	               {?foreach from=$day_schedule.studies.$i.$j.items item=study?}
	                   <td {?if ($day_schedule.studies.$i.$j.count>1) && $i && $j?}{?/if?} nowrap valign=top>
	                   <a href="schedule.php4?c=go&mode_frames=1&sheid={?$study.sheid?}" title="{?t?}Запустить данное занятие{?/t?}">
	                   <img {?popup bgcolor="#BBBBBB" vauto="true" fgcolor="#FFFFFF" text=$study.text?} src="{?$sitepath?}images/events/{?if $study.icon?}{?$study.icon?}{?else?}material.gif{?/if?}" hspace="5" align="absmiddle" border=0>
                       {?if $schedule_perm_edit eq 1 && $study.edit_permission?}
                       <br>
                       <a href='schedule.php4?c=modify&sheid={?$study.sheid?}' title='{?t?}Редактировать занятие{?/t?}'>
                           <img src='{?$sitepath?}images/icons/edit.gif' border=0>
                       </a>
                       <a href="schedule.php4?c=delete&rp=rooms&sheid={?$study.sheid?}&tweek={?$day_schedule.tweek?}" onClick='return confirm("{?t?}Удалить занятие?{?/t?}")' title='{?t?}Удалить занятие{?/t?}'>
                           <img src='{?$sitepath?}images/icons/delete.gif' border=0>
                       </a>
                       {?/if?}
	                   </a>
	                   </td>
	               {?/foreach?}
	               </tr>
	               </table>
               {?/if?}
               </td>
               {?/foreach?}
               {?/if?}
           </tr>
           {?/foreach?}
           {?/if?}
       </table>
	   </td></tr>
   </table>
<br><br>
{?/foreach?}
  </td>
 </tr>
</table>
</body>
</html>