<?php
echo $this->card(
   $this->app,
   array(
       'title' => _('Название'),
       'callback_url' => _('Callback URL'),
       'api_key' => _('API Key'),
       'consumer_key' => _('Consumer Key'),
       'consumer_secret' => _('Consumer Secret')
   ),
   array(
       'title' => _('Карточка приложения')
   ));
?>