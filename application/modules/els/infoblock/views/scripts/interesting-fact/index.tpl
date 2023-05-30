<?php
echo $this->Actions('interestinFacts', 
    array( 
        array(
            'url' => $this->url(
                array(
                	'module' => 'infoblock', 
                	'controller' => 'interesting-fact', 
                	'action' => 'new'
                )
            ), 
            'title' => _('Добавить факт')
       )
    )
);

echo $this->grid;