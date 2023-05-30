<?php if(!empty($this->marksheet->files)):?>
	<div style="float: right; padding: 10px;">
		<p><?=_('Архив ведомостей успеваемости:')?></p>
		<ol>
		<?php foreach($this->marksheet->files as $file_id => $file_name):?>
			<li><a href="
			<?=$this->url(array('module' => 'marksheet', 'controller' => 'get', 'action' => 'index', 'file_id' => $file_id), 'default', true);?>
			
			" target="_blank"><?=$file_name;?></a></li>
		<?php endforeach; ?>
		</ol>
	</div>
<?php endif; ?>