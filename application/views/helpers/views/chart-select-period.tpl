<select class="chart-select-period" name="period">
<?foreach ($this->periods as $key => $value):?>
<option value="<?php echo $key?>" <? echo ($this->periodDefault == $key) ? 'selected' : ''; ?>><?php echo $value?></option>
<?endforeach;?>
</select>