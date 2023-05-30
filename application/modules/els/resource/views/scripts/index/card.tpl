<?php $card = $this->card(
    $this->resource,
    array(
        'title' => _('Название'),
        'getType()' => _('Тип'),
		//'getTypeByClassifier()' => _('Тип'),
        'description' => _('Краткое описание'),
        'getCreateBy()' =>_('Создал'),
    ),
    array(
        'title' => _('Карточка информационного ресурса')
    ));
?>
<?php if ($this->isAjaxRequest): ?>
<img src="<?php echo $this->baseUrl('images/events/4g/105x/resource.png');?>" align="left"/>
<?php echo $card;?>
<?php else: ?>
<div class="pcard pcard_inline">
        <img src="<?php echo $this->baseUrl('images/events/4g/105x/resource.png');?>" align="left"/>
        <?php echo $card;?>
</div>
<?php endif; ?>