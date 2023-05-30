<?php if (count($this->students)):?>
    <?php foreach($this->students as $student):?>
    <option value="<?php echo $student->MID?>"> <?php echo $this->escape($student->getName())?></option>
    <?php endforeach;?>
<?php endif;?>