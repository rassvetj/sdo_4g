<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/content-modules/columns.css') ); ?>
<?php if (!isset($this->attribs)) $this->attribs = array(); ?>
<?php
    foreach ($this->attribs as $key => $value) {
        unset($value['class']);
        $attribs[$key] = $this->htmlAttribs( $value );
    }
?>
<?php $px = ($this->type === 'px') ? true : false; ?>
<?php if (count($this->columns) === 3):?>
<div class="<?php echo $this->classes; ?> <?php if (!$px): ?>hgll-pc-3-columns<?php endif; ?> hgll-columns columns"><div class="hgll-colmask hgll-colwrap-outer"><div class="hgll-colwrap-middle"><div class="hgll-colwrap-inner">
	<div class="hgll-col1 column" <?php echo $attribs[0] ?>>
		<?/*
		<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) : ?>					
			<?=Zend_Registry::get('serviceContainer')->getService('EntryEvent')->getInnerBlock(1);?>
		<?php endif; ?>
		*/?>
	
		<?php echo $this->columns[0]; ?>
	</div>
    <div class="hgll-col2 column" <?php echo $attribs[1] ?>><?php echo $this->columns[1]; ?></div>
    <div class="hgll-col3 column" <?php echo $attribs[2] ?>><?php echo $this->columns[2]; ?>	
	<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)) : ?>					
		<a href="https://docs.google.com/forms/d/e/1FAIpQLScR-YSiyVWtDa_qDuS0HV3E9PyTl91vloRhhwT1aYjvgOgp8g/viewform" target="_blank"><img style="width:100%" src="<?=$this->serverUrl('/images/ad/Rh5LAZgK3Hc.jpg');?>"></a>
		<br />						
	<?php endif; ?>
	</div>	
</div></div></div></div>
<?php elseif (count($this->columns) === 2 || empty($this->columns)):?>
<div class="<?php echo $this->classes; ?> <?php if (!$px): ?>hgll-pc-2-columns<?php endif; ?> hgll-columns columns"><div class="hgll-colmask hgll-colwrap-outer"><div class="hgll-colwrap-inner">
    <?php
    if ($px): ?><div class="hgll-col1wrap"><?php
    endif; ?><div class="hgll-col1 column" <?php echo $attribs[0] ?>><?php echo $this->columns[0]; ?></div><?php
    if ($px): ?></div><?php endif; ?>
    <div class="hgll-col2 column" <?php echo $attribs[1] ?>><?php echo $this->columns[1]; ?></div>
</div></div></div>
<?php else:?>
<div class="<?php echo $this->classes; ?> <?php if (!$px): ?>hgll-pc-1-column<?php endif; ?> hgll-columns columns"><div class="hgll-colmask hgll-colwrap-outer">
    <div class="hgll-col1 column" <?php echo $attribs[0] ?>><?php
    foreach($this->columns as $value) {
        echo $value;
    }
    ?></div>
</div></div>
<?php endif;?>