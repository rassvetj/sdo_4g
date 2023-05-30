<?php 	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);?>
<?php // moved to ./happy-end.tpl?>
<?php $cardId = $this->id('card-inline'); ?>
<div class="pcard pcard_inline" id="<?php echo $this->escape($cardId) ?>">
    <?php echo $this->partial('list/card.tpl', null, array('subject' => $this->subject, 'graduated' => $this->graduated));?>
</div>
<?php if ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL): ?>
<?php

    $container = Zend_Registry::get('serviceContainer');
    if (  in_array($container->getService('User')->getCurrentUserRole(),
                   array(HM_Role_RoleModelAbstract::ROLE_TEACHER,
                         HM_Role_RoleModelAbstract::ROLE_DEAN))):
        $confirmMsg = array(
            HM_Subject_SubjectModel::STATE_ACTUAL      => _('Вы уверены, что хотите начать обучение на курсе? В этот момент будут отправлены уведомления всем участникам курса и доступ к материалам курса для них будет открыт; относительные даты занятий будут отсчитываться от этого момента.'),
            HM_Subject_SubjectModel::STATE_CLOSED => _('Вы уверены, что хотите закончить обучение на курсе? При этом все слушатели курса будут автоматически переведены в прошедшие обучение. Дальнейшее зачисление слушателей на курс станет невозможным.')
        );
        $actionUrl = $this->url(array(
                                      'module'     => 'subject',
                                      'controller' => 'index',
                                      'action'     => 'change-state',
                                      'subject_id' => $this->subject->subid,
                                      'state'=> ''
                                ),null,true);
        $this->inlineScript()->captureStart();
?>
$(document).ready(function () {

var confs = <?php echo Zend_Json::encode($confirmMsg) ?>;
var $subjectsetstate = $(<?php echo Zend_Json::encode("#$cardId"); ?>).find('select[name="subjectsetstate_new_mode"]')
  , $tparent = $subjectsetstate.parent()
  , currentValue = $subjectsetstate.val();

if ($subjectsetstate.length) {
    $subjectsetstate
        .selectmenu({
            style: 'dropdown',
            menuWidth: 170,
            width: 170,
            positionOptions: { collision: 'none' }
        }).change(function () {
            var _this = this
              , _val  = $(_this).val();
            if (elsHelpers.confirm != null) {
                elsHelpers.confirm(confs[_val], <?php echo Zend_Json::encode(_("Смена состояния курса")) ?>).done(function () {
                    window.location = <?php echo Zend_Json::encode($actionUrl); ?> + (currentValue = _val);
                }).always(function () {
                    $(_this).val(currentValue)
                        .selectmenu('value', currentValue);
                });
            } else {
                if (confirm(confs[_val])) {
                    window.location = <?php echo Zend_Json::encode($actionUrl); ?> + (currentValue = _val);
                }
                $(_this).val(currentValue)
                    .selectmenu('value', currentValue);
            }
        });
}

});
<?php
        $this->inlineScript()->captureEnd();
    endif;
?>
<? endif; // end period restriction?>
<?php if (strlen(strip_tags(trim($this->subject->description)))) :?>
<br>
<br>
<h2><?php echo _('Описание курса');?></h2>
<hr>
<div class="text-content">
<?php if ($lang == 'eng') {echo $this->subject->description_translation;}
else {echo $this->subject->description;}?>
</div>
<? endif; ?>