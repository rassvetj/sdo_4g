<?php  if(!$this->isAjaxRequest)
           echo $this->actions('htmlpage-list',
                            array(
                                array(
                                    'title' => _('создать инфоблок'),
                                    'url' => $this->url(array('module' => 'info',
                                                              'controller' => 'list', 
                                                              'action' => 'new'), 
                                                        null, 
                                                        true))));?>
<?php echo $this->grid;?>
