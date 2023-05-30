<?php if (is_array($this->activities) && count($this->activities)):?>
	<ul class="navigation activities">
		<li>
			<ul>
                <?php foreach($this->activities as $activity):?>
                    <li class="activity-<?php echo $activity['id'];?>"><a href="<?php if(is_array($activity['url'])){ $activity['url']['subject'] = $this->subject; $activity['url']['subject_id'] = $this->subject_id; echo $this->url($activity['url'],false,true);} else{ echo $activity['url'];}?>"><?php echo $activity['name']?></a></li>
               	<?php endforeach;?>
            </ul>
        </li>
    </ul>
<?php endif;?>
<?php if ($this->isModerator): ?>
<ul>
	<li>
        <ul>
        	<li>
        	    <a href="<?php echo $this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'edit-services', 'subject_id' => $this->subject_id));?>"><?php echo _('Настроить сервисы взаимодействия');?></a>
            </li>
        </ul>
    </li>
</ul>
<?php endif;?>