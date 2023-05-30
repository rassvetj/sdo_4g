<?php if (!$this->gridAjaxRequest):?>
    <div style="float: right; "> <input type="button" class="" value="<? echo _('Обновить');?>" onClick="if(typeof($('#tabs').tabs('option', 'selected')) == 'number') { $('#tabs').tabs('load', $('#tabs').tabs('option', 'selected')); }else{ window.location.reload(); }"/> </div>
    <div style="padding-top: 9px;">
	<?php
    if (!$this->disableMessages &&
        Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:message:send:instant-send')        
    ) {
    	echo $this->actions('message',
    	    array(
    	        array('title' => _('Создать сообщение'),
    	              'url'   => $this->url(
    						                array('module'     => 'message',
    						                      'controller' => 'send',
    						                      'action'     => 'instant-send'
    						                )
    	                         )
    	        )
    	    )
    	);
    }
	?></div>
<?php endif;?>
<?php echo $this->grid ?>