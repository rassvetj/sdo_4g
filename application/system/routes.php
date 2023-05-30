<?php
$router = new Zend_Controller_Router_Rewrite();
$router->addRoute(
    'static', new Zend_Controller_Router_Route('static/:url', array('module' => 'default', 'controller' => 'static', 'action' => 'index'))
);