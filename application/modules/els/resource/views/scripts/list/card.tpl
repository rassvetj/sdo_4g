<img src="<?php echo $this->baseUrl('images/events/4g/105x/resource.png');?>" align="left"/>
<?php echo $this->card(
    $this->resource,
    array(
//        'title' => _('Название'),
        'getType()' => _('Тип'),
        'description' => _('Краткое описание'),
    ),
    array(
        'title' => _('Карточка информационного ресурса')
    ));
?>