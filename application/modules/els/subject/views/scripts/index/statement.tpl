<?php
    $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/marksheet.css');
$this->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/application/marksheet/index/index/script.js');
$this->headScript()->appendFile($this->baseUrl('/js/lib/jquery/datefilter.js'));
?>
<?php if (!count($this->persons)):?>
<!-- TODO: Это вынести в отдельный файл при стайлинге -->
<div style="padding:10px;text-align:center;color:brown;font-size:14px;"><?php echo _('Отсутствуют данные для отображения');?></div>
<?php else:?>
<?php $totalSchedules = count($this->schedules); ?>
<?php $totalPersons = count($this->persons); ?>

<form id="marksheet-form" method="POST" action="<?php echo $this->escape( $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score')) );?>">
<table id="marksheet" class="main-grid" cellspacing="0" data-schedules="<?php echo $totalSchedules;?>" data-persons="<?php echo $totalPersons;?>">
<colgroup><col><col><col span="<?php if ($this->finalForms) echo $totalSchedules + count($this->finalForms) ; else echo $totalSchedules + 1;?>"></colgroup>
<thead>
<tr class="marksheet-labels">
    <!-- TODO: Убрать в нужное место кнопулю комментирования -->
    <td class="first-cell cell430" colspan="2">
   </td>
    <?php foreach($this->schedules as $key => $schedule):?>
    <?php $subject_id = $schedule->CID;?>
    <td class="lesson-cell score-cell">
        <img src="<?php echo $schedule->getIcon()?>" alt="<?php echo $this->escape($schedule->title)?>" title="<?php echo $this->escape($schedule->title)?>">
    </td>
    <?php endforeach;?>

    <?php if ($this->finalForms):?>
    <?php if (count($this->finalForms)):?>
    <?php foreach($this->finalForms as $finalForm):?>
    <td class="score-cell total-cell"><span class="total-score-label"><?php echo $finalForm->name ?></span></td>
    <?php endforeach;?>
    <?php endif;?>
    <?php else:?>
    <td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Итог") ?></span></td>
    <?php endif;?>
</tr>
<tr class="marksheet-head">
    <th class="marksheet-rowcheckbox first-cell"><?php /*echo $this->formCheckbox('')*/?></th>
    <th class="fio-cell"><?php echo _('ФИО');?></th>
    <?php foreach($this->schedules as $key => $schedule):?>
    <td class="lesson-cell score-cell">&nbsp;</td>
    <?php endforeach;?>
    <?php if ($this->finalForms):?>
    <?php if (count($this->finalForms)):?>
    <?php foreach($this->finalForms as $finalForm):?>
    <td class="total-cell score-cell">&nbsp;</td>
    <?php endforeach;?>
    <?php endif;?>
    <?php else:?>
    <td class="total-cell last-cell score-cell">&nbsp;</td>
    <?php endif;?>
</tr>
</thead>
<tbody>
<?php
        $temp1 = 1;
        foreach($this->persons as $key => $person):?>
<tr class="<?php echo ($temp1 % 2 == 0) ? "even" : "odd"; if ($temp1 == 1) { echo " first-row"; } else if ($temp1 == $totalPersons) { echo " last-row"; } ?>">
<td class="marksheet-rowcheckbox first-cell">&nbsp;</td>
<td class="fio-cell cell430">
    <?php echo $this->cardLink($this->url(array('module' => 'user', 'controller' => 'list','action' => 'view', 'user_id' => $person->MID)),_('Карточка пользователя'));?>
    <?php if ($this->showUserUrl):?>
    <a href="<?php echo $this->url(array('module' => 'lesson', 'controller' => 'list','action' => 'my', 'user_id' => $person->MID));?>">
        <?php echo $this->escape($person->getName());?>
    </a>
    <?php else:?>
    <?php echo $this->escape($person->getName());?>
    <?php endif;?>
</td>
<?php
            $temp = 1;
            foreach($this->schedules as $schedule):?>
<td class="score-cell lesson-cell<?php if(!isset($this->scores[$key.'_'.$schedule->SHEID])):?> no-score<?endif;?>"><div>
    <?php if (isset($this->scores[$key.'_'.$schedule->SHEID])):?>
    <input
            type="text"
            tabindex="<?php echo $temp;?>00<?php echo $temp1;?>"
            id="<?php echo $key.'_'.$schedule->SHEID;?>"
            disabled="disabled"
            name="score[<?php echo $key?>_<?php echo $schedule->SHEID?>]"
            value="<?php echo $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS;?>"
            pattern="<?php if ($this->estimationScaleValues) echo "($this->estimationScaleValues)"; else echo "^[1-9]{1}\d?$|^0$|^100$";?>">
    <?php endif;?>
    <?php if(strlen($this->scores[$key.'_'.$schedule->SHEID]->comments)):?>
    <div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_'.$schedule->SHEID]->comments);?>"></div>
    <?php endif;?>
</div></td>
<?php $temp++;
            endforeach;?>

<?php if ($this->finalForms):?>
<?php if (count($this->finalForms)):?>
<?php foreach($this->finalForms as $finalForm):?>
<td class="score-cell total-cell"><div>
    <input
            tabindex="<?php echo $temp;?>00<?php echo $temp1;?>"
            id="<?php echo $key;?>_final_<?php echo $finalForm->final_reporting_form_id?>"
            name="score[<?php echo $key?>_final_<?php echo $finalForm->final_reporting_form_id?>]"
            disabled="disabled"
            type="text"
            value="<?php echo $this->scores[$key."_final_".$finalForm->final_reporting_form_id];?>"
    pattern="<?php if ($this->estimationScaleValues) echo "($this->estimationScaleValues)"; else echo _("(['100']{3})|([0-9]{0,2})");?>">
</div></td>
<?php $temp++;?>
<?php endforeach;?>
<?php endif;?>
<?php else:?>
<td class="score-cell total-cell last-cell"><div>
    <input
            tabindex="<?php echo $temp;?>00<?php echo $temp1;?>"
            id="<?php echo $key;?>_total"
            name="score[<?php echo $key?>_total]"
            disabled="disabled"
            type="text"
            value="<?php echo $this->scores[$key."_total"]['mark'];?>"
    pattern="<?php echo "^[1-9]{1}\d?$|^0$|^100$"; ?>"
</div></td>
<?php endif;?>

<?php $temp++;?>
</tr>
<?php $temp1++;
        endforeach;?>
<tr class="last-row ui-helper-hidden">
    <td class="first-cell" colspan="2"></td>
    <td class="slider-cell" colspan="<?php echo $totalSchedules; ?>"><div id="marksheet-slider"></div></td>
    <?php if ($this->finalForms):?>
    <?php if (count($this->finalForms)):?>
    <?php foreach($this->finalForms as $finalForm):?>
    <td></td>
    <?php endforeach;?>
    <?php endif;?>
    <?php else:?>
    <td class="last-cell"></td>
    <?php endif;?>

</tr>
</tbody>
<tfoot>
<tr>
    <td colspan="<?php if ($this->finalForms) echo $totalSchedules + 3 - 1 + count($this->finalForms); else echo $totalSchedules + 3; ?>">
        &nbsp;
    </td>
</tr>
</tfoot>
</table>
</form>

<div id="marksheet-comment-dialog" title="<?php echo _("Комментарии"); ?>">
<div class="textarea-wrapper"><textarea id="textComment" name="comment"></textarea></div>
</div>


<?php
$this->inlineScript()->captureStart();
?>
initMarksheet({
url: {
comments: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-comment'));?>",
score: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score'));?>"
},
l10n: {
save: "<?php echo _("Сохранить"); ?>",
noStudentActionSelected: "<?php echo _("Не выбрано ни одного действия со слушателем"); ?>",
noStudentSelected: "<?php echo _("Не выбрано ни одного слушателя"); ?>",
noLessonActionSelected: "<?php echo _("Не выбрано ни одного действия с занятием"); ?>",
noLessonSelected: "<?php echo _("Не выбрано ни одного занятия"); ?>",
formError: "<?php echo _("Ошибка формы") ?>",
ok: "<?php echo _("Хорошо"); ?>",
confirm: "<?php echo _("Подтверждение"); ?>",
areUShure: "<?php echo _("Вы уверены?"); ?>",
yes: "<?php echo _("Да"); ?>",
no: "<?php echo _("Нет"); ?>"
}
});
<?php
$this->inlineScript()->captureEnd();
?>

<?php endif;?>