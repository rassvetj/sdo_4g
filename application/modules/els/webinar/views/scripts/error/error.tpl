  <h2><?= $this->message ?></h2>

  <? if ('development' == APPLICATION_ENV): ?>

  <h3><?=_('Информация об ошибке')?>:</h3>
  <p>
      <b><?=_('Текст ошибки')?>:</b> <?= $this->exception->getMessage() ?>
  </p>

  <h3><?=_('Подробнее')?>:</h3>
  <pre><?= $this->exception->getTraceAsString() ?>
  </pre>

  <h3><?=_('Параметры запроса')?>:</h3>
  <pre><? var_dump($this->request->getParams()) ?>
  </pre>
  <? endif ?>