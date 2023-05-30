<chart>
	<series>
	<?php foreach ($this->series as $key => $value):?>
		<value xid="<?php echo $key?>"><?php echo $value?></value>
	<?php endforeach;?>
	</series>
	<graphs>
		<graph gid="1">
		<?php foreach ($this->data as $key => $value):?>
			<value xid="<?php echo $key?>" color="<?php echo $this->colors[$key]?>"><?php echo $value;?></value>
		<?php endforeach;?>
		</graph>
	</graphs>
</chart>