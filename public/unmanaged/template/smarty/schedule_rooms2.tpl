{?popup_init src='js/overlib.js'?}

<table width=100% class=main cellspacing=0>
<tr><td align=right>
<table width="0"><tr>
<td><a title="{?t?}Предыдущая неделя{?/t?}" href="{?$sitepath?}schedule_rooms2.php?tweek={?$tweeklast?}&cid={?$cid?}"><img src="{?$sitepath?}images/icons/left.gif" border="0" /></a></td>
<td>{?$tweeklast|date_format:"%d.%m.%Y"?}</td>
<td> {?$tweek?} </td>
<td>{?$tweeknext|date_format:"%d.%m.%Y"?}</td>
<td><a title="{?t?}Следующая неделя{?/t?}" href="{?$sitepath?}schedule_rooms2.php?tweek={?$tweeknext?}&cid={?$cid?}"><img src="{?$sitepath?}images/icons/right.gif" border="0" /></a></td>
</tr></table>
</td></tr>
</table>
<br>
<table border="0" cellspacing="0" cellpadding="0" height="100%" width="100%">
 <tr valign="top">
  <td valign="top" colspan="3" class="transparent">
{?foreach from=$rooms item=room?}
   <table width="100%" cellspacing="0" cellpadding="0"  valign="top">
    <tr>
     <td align="right" width="60%" class="th-before-corner"><img src="{?$skin_path?}/images/spacer.gif" width="1" height="1" alt=""></td>
     <td class="th3" align="right" background="{?$skin_path?}/images/corner03.gif" width="25px" border="0" height="15px">
        <img src="{?$skin_path?}/images/spacer.gif" width="25" height="1" alt="">
     </td>
     <td  align="right" class="th3" nowrap ><b>{?$room.name?}</b>&nbsp;</td>
    </tr>
   </table>
   <table width="100%" cellspacing="1" cellpadding="0"  valign="top">
	   <tr><td>
       <table width=100% class=main cellspacing=0>
           <tr><th width={?$width?}%></th>
           {?foreach from=$week_schedule item=day_schedule?}
               <th width={?$width?}% style="text-align: center">{?$day_schedule.day_name?}<br>{?$day_schedule.tweek|date_format:"%d.%m"?}</th>
           {?/foreach?}
           </tr>
           {?if $periods?}
           {?foreach from=$periods item=period?}
           <tr>
               <td align=center>{?$period.name?}</td>
               {?foreach from=$week_schedule item=day_schedule?}
               <td ondblclick="wopen('schedule.php4?c=add&tweek={?$day_schedule.tweek?}&room={?$room.rid?}&period={?$period.lid?}','')" onmouseover="style.cursor='pointer'" title="{?t?}Двойной клик по ячейке добавит занятие{?/t?}">
               {?assign var="i" value=`$room.rid`?}
               {?assign var="j" value=`$period.lid`?}
               {?if $day_schedule.studies.$i.$j.items?}
                   <table border=0>
                   <tr>
	               {?foreach from=$day_schedule.studies.$i.$j.items item=study?}
	                   <td {?if ($day_schedule.studies.$i.$j.count>1) && $i && $j?}style="background-color: #CCCCCC"{?/if?}>
	                   <a href="schedule.php4?c=go&mode_frames=1&sheid={?$study.sheid?}" title="{?t?}Запустить данное занятие{?/t?}">
	                   <img {?popup bgcolor="#BBBBBB" vauto="true" fgcolor="#FFFFFF" text=$study.text?} src="{?$sitepath?}images/events/{?if $study.icon?}{?$study.icon?}{?else?}material.gif{?/if?}" hspace="5" align="absmiddle" border=0>
                       {?if $schedule_perm_edit eq 1 && $study.edit_permission?}
                       <br>
                       <a href='schedule.php4?c=modify&sheid={?$study.sheid?}' title='{?t?}Редактировать занятие{?/t?}' onclick="wopen('','schedit')" target="schedit">
                           <img src='{?$sitepath?}images/icons/edit.gif' border=0>
                       </a>
                       <a href="schedule.php4?c=delete&rp=rooms2&sheid={?$study.sheid?}&tweek={?$day_schedule.tweek?}" onClick='return confirm("{?t?}Удалить занятие?{?/t?}")' title='{?t?}Удалить это занятие{?/t?}'>
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
           </tr>
           {?/foreach?}
           {?/if?}
       </table>
	   </td></tr>
   </table>
{?if $schedule_perm_edit && $schedule_perm_add?}
 <table width="100%" cellspacing="0" cellpadding="0"  valign="top" >
    <tr>
     <td  align="right" nowrap colspan='3' class="transparent">
      <b><a href='schedule.php4?c=add&room={?$room.rid?}&tweek={?$day_schedule.tweek?}&parent=rooms2' onclick="wopen('','scheadd_{?$day_schedule.tweek?}', 800, 600)" target="scheadd_{?$day_schedule.tweek?}">{?t?}добавить  занятие{?/t?}</a></b>&nbsp;
     </td>
    </tr>
    <tr>
     <td colspan='3' class="transparent">&nbsp;
     </td>
    </tr>
   </table>
{?else?}
<br><br>
{?/if?}
{?/foreach?}
  </td>
 </tr>
</table>
</body>
</html>