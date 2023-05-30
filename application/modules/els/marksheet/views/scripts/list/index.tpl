<?php if (!$this->gridAjaxRequest):?>
<style>
	.user-not-found{
		color:#ff9800;
	}
	
	.user-not-assign.total, .user-not-assign a {
		color:red;
	}
	
	.user-not-assign-demo {
		background-color: red;
	}
	
	.user-not-found-demo {
		background-color: #ff9800;
	}
	
	.btn-assign-demo {
		color: #9d9976;
		text-decoration: underline;
	}
	
	.col-name-demo{
		color: #1171b4;
		//font-weight: bold;
	}
	
	.marksheet-info{
		font-size: 12px;
	}
</style>
<div class="_grid_gridswitcher">		
	<a href="<?=$this->url(array('module' => 'marksheet', 'controller' => 'manager', 'action' => 'index'));?>">
		<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
			<?= _('Возврат из "прошедших обучение"') ?>
		</div>
	</a>
	<div class="ending _u_selected">
		<?= _('Список ведомостей') ?>
	</div>
</div>
<div style="clear:both"></div>	
<div class="marksheet-info">
	Информация:
	<ol>
		<li>Данные обновляются каждую ночь.</li>
		<li>Проведенные ведомости не выгружаются.</li>
		<li><span class="col-name-demo">Список студентов</span> &mdash; только студенты из ведомости. На сессию может быть назначено больше студентов. Они не выводятся в этом списке.</li>
		<li><span class="user-not-assign-demo">&nbsp;&nbsp;&nbsp;&nbsp;</span> &mdash; студенты, не назначенные на сессию.</li>
		<li><span class="user-not-found-demo" >&nbsp;&nbsp;&nbsp;&nbsp;</span> &mdash; студенты, не найденные на сайте. Выводится код студента.</li>
		<li>
			Если заголовок списка студентов окрашен в <span class="user-not-assign-demo">&nbsp;&nbsp;&nbsp;&nbsp;</span> или <span class="user-not-found-demo" >&nbsp;&nbsp;&nbsp;&nbsp;</span>,
			значит в нем присутствуют соответствующие студенты.
		</li>
		<li>Действие <span class="btn-assign-demo">
			Назначить</span> &mdash; назначает всех неназначенных студентов без учета даты начала обучения. Доступно только для записей с <span class="user-not-assign-demo">&nbsp;&nbsp;&nbsp;&nbsp;</span>.
		</li>
		
	</ol>
</div>
<br />
<?php endif;?>

<?php echo $this->grid?>