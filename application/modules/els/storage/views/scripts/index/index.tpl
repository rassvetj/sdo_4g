<?php echo $this->elFinder('main', array(
    'connectorUrl' => $this->url(array(
        'module' => 'storage',
        'controller' => 'index',
        'action' => 'elfinder',
        'subject' => $this->subjectName,
        'subject_id' => $this->subjectId
    )),
    'lang' => Zend_Registry::get('config')->wysiwyg->params->lang
));?>
