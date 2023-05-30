<!--- content --->
<div class="kbase_main clearfix">
	<div class="kbase_left">
    	<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER))): ?>
            <?php echo $this->Actions('resource', array(
                array('title' => _('Создать информационный ресурс'), 'url' => $this->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'new'))),
                array('title' => _('Создать учебный модуль'), 'url' => $this->url(array('module' => 'course', 'controller' => 'list', 'action' => 'new'))),
            ));?>
    	<?php endif;?>

		<?php echo $this->partial('_search-form-simple.tpl');?>
		<div class="kbase_tags">
            <ul class="tag-cloud">
            <?php foreach($this->tags as $tag) :?>
                <li><a href="<?php echo $this->url(array('module'=> 'resource','controller' => 'search', 'action' => 'tag', 'tag' => $tag->body))?>" class="tag<?php if($tag->num > 0)  print ' tag'.$tag->num; ?>" rel="tag"><?php echo $tag->body?></a></li>
            <?php endforeach;?>
            </ul>
		</div>
		 <div class="kbase_left_block_wrap">
			<div class="kbase_left_block">
				<p><?php echo _('Статистика базы знаний');?></p>
				<span><?php echo _('Всего информационных ресурсов');?>: <b><?php echo $this->statIRCount;?></b></span>
				<span><?php echo _('Общее количество пользователей');?>: <b><?php echo $this->statUCount;?></b></span>
				<span><?php echo _('Новых ресурсов за последний месяц');?>: <b><?php echo $this->statMIRCount;?></b></span>
			</div>
		</div>
		<!-- <div class="kbase_left_block_wrap">
			<div class="kbase_left_block">
				<p><?php echo _('Последние просмотренные информационные ресурсы');?></p>
				<span>Электронный курс "<a href="/resource/catalog">Управление конфликтами</a>"</span>
				<span>Электронный курс "<a href="/resource/catalog">Управление проектами</a>"</span>
				<span>Электронный курс "<a href="/resource/catalog">Управление рисками</a>"</span>
			</div>
		</div> -->
		<div class="kbase_left_block_wrap">
			<div class="kbase_left_block">
				<p><?php echo _('Последние добавления в базу знаний');?></p>
				<?php if($this->lastAdd):
				        foreach($this->lastAdd as $irItem):
				?>
					<span>
                        <?php
                        echo $irItem['type'];
                        if ( $irItem['url'] ):
                        ?>
                            "<a href="<?php echo $this->url($irItem['url']);?>"><?php echo _($irItem['title']);?></a>"
                        <?php
                        else:?>
                            "<?php echo _($irItem['title']);?>"
                        <?php
                        endif;
                        ?>
                    </span>
				<?php
				        endforeach; 
				    endif;?>
			</div>
		</div> 
	</div>
	<?php if ($this->classifiers):?>
		<div class="kbase_right">
			<?php 
			$isFirstItem = TRUE;
			foreach( $this->classifiers as $k=>$item): 
			    $text = '<p>' . _($item['title']) . '</p>';
			    $text .= (!empty($item['image']))?('<img style="max-width: 68px; margin-right: 5px; max-height: 68px;" src="'.$item['image'].'">'):('');
				if ( count($item['items']) ) {			        
					foreach( $item['items'] as $rubric){
			            
						$text .= '<a href="' .$this->url(array('module'=>'resource','controller' => 'catalog','action' => 'index', 'type' => $k, 'classifier_id' => $rubric->classifier_id)) . '">' . _($rubric->name) . '</a> ';
			        }
			    } else {
			        $text .= _('Нет рубрик в классификаторе');
			    }
			    
			    if($isFirstItem):
			    ?>
			    <div class="kbase_right_list">
					<div class="kbase_list_l">
					<?php echo $text;?>
					</div>
					<div class="kbase_list_r"></div>
				</div>
				<?php 
				else:
				?>
				<div style="clear:both;"></div>
				<div class="kbase_right_block">
				<?php echo $text;?>
				</div>
				<?php 
				endif; 
			$isFirstItem = FALSE;
			endforeach;?>
		</div>
	<?php endif;?>
</div>