<?php $card = $this->courseCard(
    $this->course,
    array(
        //'Title' => _('Название'),
        'Description' => _('Краткое описание'),
        'getStatus()' => _('Статус'),
        //'getDevelopers()' => _('Разработчики'),
        //'getPlanDate()' => _('Плановая дата окончания разработки'),
        'longtime' => _('Продолжительность обучения (в часах)'),
        'getAuthor()' => _('Создал')
    ),
    array(
        'title' => _('Карточка учебного модуля')
    ));
?>
<?php if ($this->isAjaxRequest): ?>
<img src="<?php echo $this->baseUrl('images/events/4g/105x/course.png');?>" align="left"/>
<?php echo $card;?>
<?php else: ?>
<div class="pcard pcard_inline">
    <img src="<?php echo $this->baseUrl('images/events/4g/105x/course.png');?>" align="left"/>
    <?php echo $card;?>
</div>
<?php endif; ?>