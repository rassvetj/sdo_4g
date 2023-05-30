<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/kbase.css')); ?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/material-icons.css')); ?>
<div class="kbase_left">
    <div class="search-form-simple">
        <?php echo $this->partial('_search-form-simple.tpl', array('query' => $this->query));?>
    </div>
    <div class="search-form-advanced">
        <?php echo $this->form;?>
    </div>
</div>
<div class="clearfix"></div>
<?php if($this->error == false): ?>
<?php
    $page = $this->paginator->getCurrentPageNumber()-1;
    $itemPerPage = $this->paginator->getDefaultItemCountPerPage();
    $i =0;
?>
<ol class="search-results" start="<?php echo $page * $itemPerPage + 1; // @todo: кажется оно depricated?>">
<?php
    foreach($this->paginator as $key => $item) {
        // здесь было много лишнего кода
        echo $this->searchItem($item['obj'], $page * $itemPerPage + (++$i), $this->words, array_keys($this->params) + array('page'));
    }
?>
</ol>
<?php echo $this->listMassActions(array(
    'pagination' => array($this->paginator, 'Sliding', 'search/controls-advanced.tpl', array('params' => $this->params)),
    'export' => array('formats' => array('excel'), 'params' => $this->params),        
));?>
<?php else: ?>
<div><?php echo $this->error;?></div>
<?php endif;?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('.search_form_adv a').click(function(){
            jQuery('.search-form-simple').css('display', 'none');
            jQuery('.search-form-advanced').css('display', 'block');
            return false;
        });
    });
<?php $this->inlineScript()->captureEnd(); ?>