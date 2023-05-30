<form action="<?php echo $this->url(array('action' => 'assign'))?>" method="POST">
<input type="hidden" name="speciality" value="<?php echo (int) $this->speciality->trid?>"/>
<input type="hidden" name="level" value="<?php echo $this->level?>"/>
<table width="100%" class="main" cellpadding="0" cellspacing="0" border="0">
<tr>
    <th>&nbsp;</th>
    <th><?php echo _('Группа')?></th>
    <th><?php echo _('Семестр')?></th>
    <th><?php echo _('Дата начала обучения')?></th>
</tr>
<?php if ($this->speciality && $this->speciality->getGroups()):?>
    <?php foreach($this->speciality->getGroups() as $group):?>
    <?php if ($this->level == "all" || $this->level == "s_".$group->level):?>
        <tr>
            <td><input type="checkbox" name="groups[]" value="<?php echo $group->gid?>"></td>
            <td><?php echo $this->escape($group->name)?></td>
            <td><?php echo (int) $group->level?></td>
            <td>
            <?php echo $group->date($group->updated)?>
            </td>
        </tr>
    <?php endif;?>
    <?php endforeach;?>
    <tr>
        <td colspan="4" align="right">
            <?php echo _('Выполнить действие')?>:
            <select name="direction">
                <option value=""> <?php echo _('Выберите действие')?></option>
                <option value="next"> <?php echo _('Перевести на следующий семестр')?></option>
                <option value="prev"> <?php echo _('Перевести на предыдущий семестр')?></option>
            </select>
            <?php echo okbutton()?>
        </td>
    </tr>
<?php else:?>
<tr>
    <td colspan="4" align="center"><?php echo _('Группы на специальности отсутствуют')?></td>
</tr>
<?php endif;?>
</table>
</form>