<?php
if('development' != APPLICATION_ENV || Zend_Registry::get('config')->debug == 0):?>

<?php if ($this->errorType == 403): ?>
<h2><?php echo _('Недостаточно прав');?></h2>
<p><?php echo _('У Вас недостаточно прав для просмотра данной страницы. Это могло произойти по следующим причинам:');?></p>
<ul>
<li><?php echo _('Вы долгое время не совершали никаких действий в системе и время сессии закончилось;');?>
<li><?php echo _('Вы перешли по прямой ссылке (например, сохраненной в закладках) и еще не авторизовались;');?>
</ul>
<p><?php echo _('Вы можете авторизоваться заново и продолжить работу с системой.');?></p>
<input type="button" value="<?php echo _('Продолжить');?>" onclick="top.location.href='<?php echo Zend_Registry::get('config')->url->base;?>'">
<?php elseif ($this->errorType == 404): ?>
<h2><?php echo _('Страница не найдена');?></h2>
<p><?php echo _('Запрашиваемая Вами страница не найдена. Это могло произойти по следующим причинам:');?></p>
<ul>
<li><?php echo _('Вы перешли по ссылке на учебный материал, который был физически удалён на сервере;');?>
<li><?php echo _('неверно настроены права доступа к файлам на файловой системе сервера;');?>
<li><?php echo _('неверно установлены ссылки в содержимом учебного модуля (информационного ресурса), либо он вообще не рассчитан на работу с данным типом файловой системы;');?>
</ul>
<p><?php echo _('Вы можете сообщить об этой проблеме системному администратору и продолжить работу с системой.');?></p>
<input type="button" value="<?php echo _('Продолжить');?>" onclick="top.location.href='<?php echo Zend_Registry::get('config')->url->base;?>'">
<?php elseif ($this->errorType == 500): ?>
<h2><?php echo _('Нештатная ситуация');?></h2>
<p><?php echo _('В ходе работы программы произошла нештатная ситуация. ');?></p>
<?php if (Zend_Registry::get('serviceContainer')->getService('User')->isRoleExists(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), HM_Role_RoleModelAbstract::ROLE_ADMIN)): ?>
<p><?php echo sprintf(_('Вы можете отправить %s о случившемся в службу технической поддержки и продолжить работу с системой.'), '<a href="' . $this->url(array('module' => 'file', 'controller' => 'get', 'action' => 'log')) . '" target="_blank">' . _('отчет') . '</a>');?></p>
<?php endif; ?>
<input type="button" value="<?php echo _('Продолжить');?>" onclick="top.location.href='<?php echo Zend_Registry::get('config')->url->base;?>'">
<?php endif; ?>

<!--div style="text-align: center;">
<img src="<?php $this->baseUrl(''); ?>/images/errors/<?php echo $this->errorType; ?>.png" alt="<?php echo _('Ошибка'); ?>" style="padding: 100px;" />
</div-->
<?php endif;?>


<? if ('development' == APPLICATION_ENV || Zend_Registry::get('config')->debug->on): ?>

<h3><?=_('Информация об ошибке')?>:</h3>
<p><b><?=_('Текст ошибки')?>:</b> <?= $this->exception->getMessage() ?>
</p>

<h3><?=_('Подробнее')?>:</h3>
<pre><?= $this->exception->getTraceAsString() ?>
  </pre>

<h3><?=_('Параметры запроса')?>:</h3>
<pre><? var_dump($this->request->getParams()) ?>
  </pre>
<? endif ?>