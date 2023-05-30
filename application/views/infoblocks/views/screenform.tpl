<?php
$this->headLink()
     ->appendStylesheet( $this->baseUrl('css/content-modules/portlets.css') );
    $attribs = $this->htmlAttribs( $this->htmlAttribsPrepare($this->attribs, array('class' => array(
        'ui-widget',
        'ui-portlet'
    ))) );
?>
<div <?php echo trim($attribs);?>><div class='ui-portlet-wrapper'>
    <div class='ui-portlet-titlebar ui-widget-header'><div class='ui-portlet-titlebar-wrapper'><div class='bg'></div>
        <h3><?php echo _($this->title); ?></h3>
    </div></div>
    <div class='ui-portlet-body'><div class='ui-portlet-body-wrapper'>
        <div class='ui-portlet-body-wrapper ui-portlet-scrollable-area'><div class='ui-portlet-content ui-widget-content'>
            <?= $this->content ?>
        </div></div>
    </div></div>
</div></div>