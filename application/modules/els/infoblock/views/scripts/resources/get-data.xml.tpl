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
		<graph gid="resources">
		<?foreach ($this->data as $key => $value):?>
			<value xid="<? echo $key?>" color="<? echo $this->colors[$key];?>"><? echo $value;?></value>
		<?endforeach;?>
		</graph>
	</graphs>
</chart>