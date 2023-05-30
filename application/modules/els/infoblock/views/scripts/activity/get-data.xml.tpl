<chart>
	<meta>
		<?php if (is_array($this->meta) && count($this->meta)):?>
		<?foreach ($this->meta as $key => $value):?>
		<<?php echo $key?>><![CDATA[<? echo $value?>]]></<?php echo $key?>>
		<?endforeach;?>
		<?php endif;?>
	</meta>
	<series>
	<?foreach ($this->series as $key => $value):?>
		<value xid="<? echo $key?>"><? echo $value?></value>
	<?endforeach;?>
	</series>
	<graphs>
		<graph gid="activity-<? echo $this->type; ?><? echo $this->single;?>">
		<?foreach ($this->graphs as $key => $value):?>
			<value xid="<? echo $key?>"><? echo $value;?></value>
		<?endforeach;?>
		</graph>
	</graphs>
</chart>