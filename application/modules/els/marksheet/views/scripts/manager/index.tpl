<?php if (!$this->gridAjaxRequest):?>
<div class="_grid_gridswitcher">		
	<div class="ending _u_selected">
		<?= _('Возврат из "прошедших обучение"') ?>
	</div>
	<a href="<?=$this->url(array('module' => 'marksheet', 'controller' => 'list', 'action' => 'index'));?>">
		<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
			<?= _('Список ведомостей') ?>
		</div>
	</a>		
</div>
<div style="clear:both"></div>	

Информация:
<ol>
	<li>В данном разделе выполняется повторное назначение студентов на сессию после завершения курса</li>
	<li>Кол-во строк в таблице = кол-ву сформированных файлов</li>
	<li>Группа - группы студентов, по которым сформирована данная ведомось</li>
	<li>Кол слуш - совпадает с кол-м студентов в сформированной ведомости. Может не совпадать в случае, если изменится кол-во доступных тьютору студентов.</li>
	<li>После назначения студентов на сессию файл ведомости сохраняется на сервере</li>
	<li>Тип "ИН" - ведомость была сформирована как индивидуальное направление. Назначается только один студент.</li>
	<li>Тип "На группу" - ведомость была сформирована на указанную группу. Назначаеются все студенты группы, доступные автору ведомости в данный момент</li>
	<li>Тип "На сессию" - ведомость была сформирована без указания группы. Назначатся все студенты, доступные автору ведомости в данный момент.</li> 
	<li>Если была сформирована ведомость на группу_1 или ИН, а затем сформировали по всем "оставшимся" на сессию (без отборов), при возврате "оставшихся" также назначатся и студенты группы_1 и ИН.
		Варианта 2:
		<ul>
			<li>вернуть всех студентов, а затем для каждого заново сформировать ведомости и ИН</li>
			<li>вручную изменить тип с "на сессию" на "на группу", указав нужную группу, затем вернуть ее студентов</li>
		</ul>	 
	</li>
	<li>Все старые ведомости будут с типом "на сессию", т.к. до внедрения данного интерфейса группа или студент (для ИН), на которую формировалась ведомость не фиксировалась</li>
</ol>
<br />
<?php endif;?>

<?php echo $this->grid?>