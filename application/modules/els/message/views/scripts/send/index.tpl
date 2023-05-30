<table width="100%" cellpadding="0" cellspacing="0" border="0" class="main">
<tr><th><?php echo _('Список адресатов')?></th></tr>
<?php if (count($this->users)):?>
    <?php foreach($this->users as $user):?>
        <tr><td><?php echo sprintf("%s", $user->getName())?></td></tr>
    <?php endforeach;?>
<?php endif;?>
</table>
<?php echo $this->form?>

