<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="<?php echo $this->baseUrl('css/content-modules/marksheet.css')?>" type="text/css">
        <link rel="stylesheet" href="<?php echo $this->baseUrl('css/common.css')?>" type="text/css">
        <!--<link rel="stylesheet" href="<?php echo $this->baseUrl('themes/redmond/css/theme.css')?>" type="text/css">
    --></head>
    <body>
	
	
	<?php if (!count($this->persons)):?>
	    <!-- TODO: Это вынести в отдельный файл при стайлинге -->
	    <div style="padding:10px;text-align:center;color:brown;font-size:14px;"><?php echo _('Отсутствуют данные для отображения')?></div>
	<?php else:?>
	<?php $totalSchedules = count($this->schedules); ?>
	<?php $totalPersons = count($this->persons); ?>
	
	<table id="marksheet" class="main-grid" cellspacing="0" data-schedules="<?php echo $totalSchedules;?>" data-persons="<?php echo $totalPersons;?>">
	    <colgroup><col><col><col span="<?php echo $totalSchedules + 1;?>"></colgroup>
	    <thead>
	        <tr class="marksheet-labels">
	            <!-- TODO: Убрать в нужное место кнопулю комментирования -->
	            <td class="first-cell" colspan="2"></td>
	            <?php foreach($this->schedules as $key => $schedule):?>
	            <td class="lesson-cell score-cell">
	                <?php echo $this->escape($schedule->title)?>
	            </td>
	            <?php endforeach;?>
	            <td class="score-cell total-cell last-cell"><span class="total-score-label">Итог</span></td>
	        </tr>
	        <tr class="marksheet-head">
	            <th class="marksheet-rowcheckbox first-cell"></th>
	            <th class="fio-cell"><?php echo _('ФИО');?></th>
	            <?php foreach($this->schedules as $key => $schedule):?>
	            <td class="lesson-cell score-cell"></td>
	            <?php endforeach;?>
	            <td class="total-cell last-cell score-cell"></td>
	        </tr>
	    </thead>
	    <tbody>
	        <?php
	        $temp1 = 1;
	        foreach($this->persons as $key => $person):?>
	        <tr class="<?php echo ($temp1 % 2 == 0) ? "even" : "odd"; if ($temp1 == 1) { echo " first-row"; } else if ($temp1 == $totalPersons) { echo " last-row"; } ?>">
	            <td class="marksheet-rowcheckbox first-cell"></td>
	            <td class="fio-cell"><?php echo $this->escape($person->getName());?></td>
	            <?php
	            $temp = 1;
	            foreach($this->schedules as $schedule):?>
	            <td class="score-cell lesson-cell<?php if(!isset($this->scores[$key.'_'.$schedule->SHEID])):?> no-score<?endif;?>"><div>
	                <?php if (isset($this->scores[$key.'_'.$schedule->SHEID]) && ($this->scores[$key.'_'.$schedule->SHEID]->V_STATUS >-1)):?>
	                <?php echo $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS;?>
	                <?php endif;?>
	            </div></td>
	            <?php $temp++;
	            endforeach;?>
	            <td class="score-cell total-cell last-cell"><div><?php if($this->scores[$key."_total"]['mark'] > -1) echo $this->scores[$key."_total"]['mark'];?></div></td>
	            <?php $temp++;?>
	        </tr>
	        <?php $temp1++;
	        endforeach;?>
	        <tr class="last-row ui-helper-hidden">
	            <td class="first-cell" colspan="2"></td>
	            <td class="slider-cell" colspan="<?php echo $totalSchedules; ?>"></td>
	            <td class="last-cell"></td>
	        </tr>
	    </tbody>
	</table>
	
	<script type="text/javascript">
	<!--
	window.print();
	window.onload = function cleanPage(){ document.getElementById('ZFDebug_debug').innerHTML = '';};
	//-->
	</script>
	<?php endif;?>
	
	
</body>
</html>