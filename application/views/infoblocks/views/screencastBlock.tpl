<?php echo _('Выберите ролик')?>:&nbsp;<select id="screencast-select" name="screencast">
<?foreach ($this->screencasts as $key => $value):?>
<option value="<?php echo $key?>" <? echo ($this->screencast == $key) ? 'selected' : ''; ?>><?php echo $value; ?></option>
<?endforeach;?>
</select><br><br>
<div class="container">
</div>