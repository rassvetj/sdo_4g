<?php if (!$this->gridAjaxRequest):?>	

		<div style="    margin-top: 16px; height: 40px; float: left; padding: 5px; display: inline-block;  margin-bottom: -16px;">
			
			<div class="_grid_gridswitcher" data-userway-font-size="11">
				<div class="ending _u_selected"><?=_('Мои заявки')?></div>
				
				<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'certificate', 'action' => 'index')));?>">
					<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
						<?=_('Заказать справку/документ')?>
					</div>
				</a>
				
				<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'ask-question', 'action' => 'index')));?>">
					<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
						<?=_('Задать вопрос')?>
					</div>
				</a>
				
				<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'list', 'action' => 'send-document')));?>">
					<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
						<?=_('Отправить документ')?>
					</div>
				</a>
				
			</div>
		</div>

	<div class=".area-grid">
		<?php echo $this->grid?>
	</div>
<?php else : ?>	
	<?php echo $this->grid?>	
<?php endif;?>