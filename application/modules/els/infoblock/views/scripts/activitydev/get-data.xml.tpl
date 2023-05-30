<chart>
	<series>
	<?foreach ($this->series as $key => $value):?>
		<value xid="<? echo $key?>"><? echo $value?></value>
	<?endforeach;?>
	</series>
	<graphs>
		<graph gid="activitydev-<? echo $this->type; ?>">
		<?foreach ($this->graphs as $key => $value):?>
			<value xid="<? echo $key?>"><? echo $value;?></value>
		<?endforeach;?>
		</graph>
	</graphs>
</chart>