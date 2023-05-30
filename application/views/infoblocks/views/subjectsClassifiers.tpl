<?php $this->headLink()->appendStylesheet( $this->baseUrl('css/infoblocks/subjectsClassifiers/style.css') ); ?>
<?php foreach($this->classifiers as $classifierType => $classifiers) : ?>
<div class="subject-classifiers-content">
    <div class="t_title">
        <div class="t_h1">
    		<a href="<?php echo $this->baseUrl($this->url(array(
    												'module' => 'subject',
    												'controller' => 'catalog',
    												'action' => 'index',
    												'type' => $classifierType
    		)))?>"><?php echo $this->classifiersTypes[$classifierType];?></a>
        </div>
    </div>
<div class="course">
	<?php for ($i=0; $i < count($classifiers); $i++):?>
		<div class="subject-classifiers-rowdata">
			<div class="courseWName">
				<?php echo $this->freshness($classifiers[$i]['freshness'], _('Обновляемость содержимого курсов категории'));?>
				<a href="<?php echo $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'catalog', 'action' => 'index', 'type' => $classifiers[$i]['type'], 'item' => $classifiers[$i]['key'])))?>"><?php echo trim($classifiers[$i]['title'])?></a>
				<span class="right"><?php echo Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount((int)$classifiers[$i]['count'])?></span>
			</div>
		</div>
	<?php endfor;?>
</div>
</div>
<?php endforeach; ?>
<?php if($this->notClassified):?>
            <div class="subject-classifiers-rowdata not_classific">
                <div class="bullet"></div>
                <div class="courseWName">
                    <a href="<?php echo $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'catalog', 'action' => 'index')))?>"><?php echo _('Не классифицированы');?></a>
                </div>
                <div class="right_not_classific"><?php echo Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount((int)$this->notClassified);?></div>
            </div>
<?php endif;?>
<?php if(!count($this->classifiers) && !$this->notClassified): ?>
		<div align="center"><?php echo _('В каталоге нет курсов со свободной регистрацией')?></div>
<?php endif;?>
