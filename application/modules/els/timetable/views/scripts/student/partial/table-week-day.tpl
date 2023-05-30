<?php $has_rows = false; ?>
<?php foreach($this->timetable as $item):?>
	<?php if($item->even_odd_id != $this->current_even_odd_id){ continue; }?>
	<?php if($item->week_day_id != $this->current_week_day_id){ continue; }?>
	<?php 
		$has_rows = true;	
		#$is_show_multiple_link = HM_Timetable_TimetableModel::isShowMultipleLink($item->discipline);
		$is_show_multiple_link = false;
	?>
	<tr>
		<td>
			<?=$item->time?>
		</td>
		<td>
			<?=$item->classroom?>
		</td>
		<td>
			<?=$item->discipline?>
			<?= empty($item->discipline_type) ? '' : ' (' . $item->discipline_type . ')'?>

			<?=empty($item->linkTrueConf) ? '' : ' <a href="' . $item->linkTrueConf . '" target="_blnk">On-line занятие в TrueConf</a>'?>

			<?php if($is_show_multiple_link):?>
				<?= empty($item->link)  ? '' : ' <a href="' . $item->link . '" target="_blnk">On-line занятие (Начальный)</a>'?>
				<?= empty($item->link2) ? '' : ' <a href="' . $item->link2 . '" target="_blnk">On-line занятие (Базовый)</a>'?>
				<?= empty($item->link3) ? '' : ' <a href="' . $item->link3 . '" target="_blnk">On-line занятие (Продвинутый)</a>'?>
			<?php else: ?>
				<?= empty($item->link) ? ''  : ' <a href="' . $item->link . '" target="_blnk">On-line занятие</a>'?>
				<?= empty($item->link2) ? '' : ' <a href="' . $item->link2 . '" target="_blnk">On-line занятие</a>'?>
				<?= empty($item->link3) ? '' : ' <a href="' . $item->link3 . '" target="_blnk">On-line занятие</a>'?>
			<?php endif;?>
		</td>
		<td>
			<?=$item->teacher?>
			<?= empty($item->rank) ? '' : '<br /><span class="user-rank">' . $item->rank . '</span>'?>
		</td>
	</tr>
<?php endforeach;?>
<?php if(!$has_rows): ?>
	<tr>
		<td colspan="4" style="text-align: center;"><?=_('Занятий нет')?></td>
	</tr>
<?php endif; ?>
