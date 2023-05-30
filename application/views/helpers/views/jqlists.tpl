<?php
$this->headScript()
     ->appendFile( $this->baseUrl('js/lib/jquery/jquery.multiselects.packed.js') );
?>

<?php
$this->inlineScript()->captureStart();
?>
// Определить функция для срабатывания по клику на Фильтровать, 
// для заполнения левого списка значениями, на $this->filter['ajaxurl'] необходимо передавать
// так же и значения правого поля 
jQuery(function($) {
	$("#select_<?php echo $this->list1['name']?>").multiSelect("#select_<?php echo $this->list2['name']?>", {trigger: "#options_<?php echo $this->list1['name']?>"});
	$("#select_<?php echo $this->list2['name']?>").multiSelect("#select_<?php echo $this->list1['name']?>", {trigger: "#options_<?php echo $this->list2['name']?>"});
});
<?php
$this->inlineScript()->captureEnd();
?>
<table>
	<?php 
	
	if($this->filter['option']==true){?>
	<tr colspan=3>
	 &nbsp;&nbsp;<input type="button" value="Все">&nbsp;&nbsp;<input type="text" value="*">&nbsp;&nbsp;<input type="button" value="Фильтровать">
	</tr>
	<?php }?>
    <tr>
        <td>
            <select style="width: 300px;" name="<?php echo $this->list1['name']?>[]" id="select_<?php echo $this->list1['name']?>" multiple="multiple">
               <?php foreach($this->list1['options'] as $key=>$val){?>
               <option value="<?php echo $key;?>"><?php echo $val;?></option>
               <?php }?>
            </select>
        </td>
        
        <td>
            <a id="options_<?php echo $this->list1['name']?>" href="javascript: return false;"><img src="<?php echo $this->serverUrl('/images/arrow_right.gif')?>"></a><br/>
            <a id="options_<?php echo $this->list2['name']?>"  href="javascript: return false;"><img src="<?php echo $this->serverUrl('/images/arrow_left.gif')?>"></a>
            
        </td>
        <td>
            <select style="width: 300px;" name="<?php echo $this->list2['name']?>[]" id="select_<?php echo $this->list2['name']?>" multiple="multiple">
               <?php foreach($this->list2['options'] as $key=>$val){?>
               <option value="<?php echo $key;?>"><?php echo $val;?></option>
               <?php }?>
            </select>
        </td>
    </tr>
</table>