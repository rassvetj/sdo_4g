<form id="marksheet-form-filters" method="POST">
	<div class="filter_wrap <?=!count($this->persons) ? 'filter_wrap_nodata' : ''?>">
		<div class="dateFilterGroup classFWrap">
			<div class="filter_desc"><?=_('Фильтр по группе/подгруппе:')?></div>
			<div class="filterContent">
				<div class="constructorVis">
					<span class="field-cell field-filters field-filters-group disabled-field-filters">
						<span class="field-icon"></span>
						<span class="field-filters-value filterSelect">
							<?=$this->formSelect('groupname', $this->current_groupname, null, $this->groupname);?>
						</span>
					</span>
				</div>
			</div>
		</div>
		<div class="filterSubmit classFWrap">
			<button class="dateFilter">Фильтровать</button>
		</div>
		
		<div style="margin-top: 16px; height: 40px; float: left; padding: 5px; display: inline-block;  margin-bottom: -16px;">
			<div class="_grid_gridswitcher">			
				<div>Отбор по дням: </div>
				<?php if(!empty($this->prevDay)):?>
					<a href="<?=$this->baseUrl($this->url(array('module' => 'journal', 'controller' => 'laboratory', 'action' => 'extended', 'subject_id' => $this->subject_id, 'lesson_id' => $this->lesson_id, 'day' => $this->firstDay)));?>">
						<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
							&laquo; первый
						</div>
					</a>
					<a href="<?=$this->baseUrl($this->url(array('module' => 'journal', 'controller' => 'laboratory', 'action' => 'extended', 'subject_id' => $this->subject_id, 'lesson_id' => $this->lesson_id, 'day' => $this->prevDay)));?>">
						<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
							&larr; предыдущие
						</div>
					</a>
				<?php endif;?>
				
				<?php if($this->isShowingAll):?>
					<a href="<?=$this->baseUrl($this->url(array('module' => 'journal', 'controller' => 'laboratory', 'action' => 'extended', 'subject_id' => $this->subject_id, 'lesson_id' => $this->lesson_id, 'day' => '')));?>">
						<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
							Отфильтровать по первому незаполненному дню
						</div>
					</a>
				<?php else: ?>			
					<a href="<?=$this->baseUrl($this->url(array('module' => 'journal', 'controller' => 'laboratory', 'action' => 'extended', 'subject_id' => $this->subject_id, 'lesson_id' => $this->lesson_id, 'day' => 'all')));?>">
						<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
							все
						</div>	
					</a>
				<?php endif;?>
				
				<?php if(!empty($this->nextDay)):?>
					<a href="<?=$this->baseUrl($this->url(array('module' => 'journal', 'controller' => 'laboratory', 'action' => 'extended', 'subject_id' => $this->subject_id, 'lesson_id' => $this->lesson_id, 'day' => $this->nextDay)));?>">
						<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
							следующие &rarr;
						</div>
					</a>
					<a href="<?=$this->baseUrl($this->url(array('module' => 'journal', 'controller' => 'laboratory', 'action' => 'extended', 'subject_id' => $this->subject_id, 'lesson_id' => $this->lesson_id, 'day' => $this->lastDay)));?>">
						<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
							последний &raquo;
						</div>
					</a>			
				<?php endif;?>
			</div>
		</div>
	</div>
</form>