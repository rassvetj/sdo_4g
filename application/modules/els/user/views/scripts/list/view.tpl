<?php if ($this->data):?>
    <?php if ($this->data->getPhoto()):?>
    <div class="card_photo">
    <img src="<?php echo $this->baseUrl($this->data->getPhoto());?>" alt="<?php echo $this->escape($this->data->getName())?>" align="left"/>
    </div>
    <?php else:?>
    <div class="card_photo">
    <img src="<?php echo $this->baseUrl('images/people/nophoto.gif');?>" alt="<?php echo $this->escape($this->data->getName())?>" align="left"/>
    </div>
    <?php endif;?>
    <?php 
        $fields = isset($this->data->studyGroups)? $this->data->getCardFields()+array('studyGroups'=>_('Группы')) : $this->data->getCardFields();
        $fields = isset($this->data->currentCourseGroups)? $fields+array('currentCourseGroups'=>_('Подгруппы')) : $fields;
        echo $this->card(
            $this->data,
            $fields,
            array(
                'title' => _('Карточка пользователя')
            ));
    ?>
<?php else:?>
<?php echo _('Нет данных для отображения')?>
<?php endif;?>