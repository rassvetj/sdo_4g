<?php
if (count($this->userList)):
    $names = $this->usersName;
    echo _('Нижеуказанные пользователи уже проходили обучение на выбранных тренингах.');
?>
<ul>
<?php
    foreach($this->userList as $conflictItems):
        foreach($conflictItems as $conflictUser):
?>
    <li>
        <?php echo $names[$conflictUser['MID']]?> (
        <?php echo _('Тренинг'), ': ', $conflictUser['training']; ?>
        <?php if (isset($conflictUser['session'])) { echo _('Сессия'), ': ', $conflictUser['session'];} ?>
        <?php echo _('Дата окончания обучения'), ': ', $conflictUser['endDate']; ?> )
    </li>
<?php
        endforeach;
    endforeach;
?>
</ul>
<?php
endif;
?>
<br/>
<?php echo $this->form; ?>

<?php
$this->inlineScript()->captureStart();
?>
    $('#all_submit').bind('click',function(){
        $('#<?php echo $this->postMassField;?>').val($('#all_users').val());
    });
     $('#filter_submit').bind('click',function(){
        $('#<?php echo $this->postMassField;?>').val($('#filtered_users').val());
    });
<?php
$this->inlineScript()->captureEnd();
?>