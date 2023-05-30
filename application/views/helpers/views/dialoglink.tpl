<?php $id = $this->id()?>
<a href="javascript:void(0);" onClick="$('#dialoglink_<?php echo $id?>').dialog({title: '<?php echo $this->title?>', modal: true <?php echo $this->dialogOptions?><?php echo $this->dialogButtons?>})"><?php echo $this->linkText?></a>
<div id="dialoglink_<?php echo $id?>" style="display: none"><?php echo $this->content?></div>
