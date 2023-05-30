<div class="accordion-container">
	<div class="accordion-header">
		<a href="#" class="btn-accordion">Обозначения</a>
	</div>
	<div class="accordion-data">	
		<div class="byp-description">
			<ol>
				<li>Выводится первая группа студента</li>
				<li>Выводится программа группы студента</li>
				<li>Выводятся все сессии программы студента</li>
				<li><span style="font-size: 11px; color: red;"><?=_('устаревшая')?></span> - сессия, созданная до <?=HM_Subject_SubjectModel::getOldDateFormatted()?>. Дата начала обучения не учитывается</li>
			</ol>
		</div>	
	</div>	
</div>	
