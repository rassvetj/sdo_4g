<style>
	.report-journal-subject-item {
		padding-bottom: 25px;
		font-size: 15px;
	}

	.report-journal-table {
		margin-top: 5px;
		font-size: 12px;
	}
	
	.report-journal-table thead td {
		text-align: center;    
		background-color: #effaff;
	}
	.report-journal-table tbody td {
		vertical-align: middle;
		text-align: center;
	}
	
	.report-journal-table, .report-journal-table td {
		border: 1px solid black;
		border-collapse: collapse;
		padding: 5px;
	}
	.report-journal-lesson-item {
		padding-bottom: 15px;
	}
</style>
<p>
	<a target="_blank" href="<?=$this->url(array(	'module'      => 'report', 
													'controller'  => 'journal', 
													'action'      => 'export-to-archive', 
													'subject_id'  => $this->subjectId,
													'group_id'    => $this->groupId,
													'programm_id' => $this->programmId													
													))?>">
		<?=_('Выгрузить одним файлом')?>
	</a>
</p>
<br />

<?php foreach($this->subjects as $subject):?>
	<?php $this->subject = $subject; ?>
	<?=$this->render('journal/partials/_subject.tpl')?>
<?php endforeach;?>