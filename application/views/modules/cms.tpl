<?php echo $this->doctype(Zend_View_Helper_Doctype::XHTML1_TRANSITIONAL); ?>
<html>
 <head>
  <?php echo $this->headTitle('Webquote Vendors'); ?>
  <?php echo $this->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8'); ?>
  <?php echo $this->headLink()->appendStylesheet($this->baseUrl . 'css/style.css'); ?>
  <?php $this->headScript()->prependFile('/js/library/jquery-1.3.2.min.js'); ?>
</head>
<body>
    <div id="mockup">
        <?php echo $this->action('menu', 'auth', 'vendors'); ?>
        <?php echo $this->layout()->content;?>
    </div>
<?php echo $this->headScript(); ?>
</body>
</html>
