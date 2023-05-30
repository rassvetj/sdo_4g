<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->actions(
        'apps',
        array(
            array(
                'title' => _('Добавить приложение'),
                'url' => $this->url(array('action' => 'new'))
            )
        )
    );?>
<?php endif;?>
<?php echo $this->grid?>