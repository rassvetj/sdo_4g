<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/course-index.css')); ?>
<?php $this->headScript()->appendFile( $this->serverUrl('/js/lib/jquery/jquery.masonry.min.js') ); ?>

<?php
if ($this->from == 'indexcourses'){
    echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'materials', 'action' => 'index', 'switcher' => 'materialresource_courses'), 'materialresource', array('index', 'materialresource'));
} else {
    echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'materials', 'action' => 'index', 'switcher' => 'materialresource'), 'materialresource', array('index_courses', 'materialresource_courses'));}
?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:materials:edit-section')):?>
    <?php echo $this->Actions('materials', array(
        array('title' => _('Создать группу материалов'), 'url' => $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'edit-section'))),
        array('title' => _('Создать информационный ресурс'), 'url' => $this->url(array('module' => 'resource', 'controller' => 'list', 'action' => 'new'))),
        array('title' => _('Создать учебный модуль'), 'url' => $this->url(array('module' => 'course', 'controller' => 'list', 'action' => 'new'))),
    ));?>
<?php endif;?>

<?php $containerIds = array(); ?>
<?php $subjectPageId = $this->id('sp'); ?>
<?php
/* TODO: можно-ли эту проверку использовать для включения режима редактирования? */
$isEditAllowed = Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:materials:edit-section');
?>
<div id="<?= $subjectPageId ?>" class="subject-page <?php if ($isEditAllowed): ?>edit-mode<?php endif; ?>">
    <?php foreach ($this->sections as $section): ?>
    <form action="<?= $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'order-section', 'section_id' => $section->section_id)); ?>" method="POST">
    <div class="container-wrapper"><div class="container">
        <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:materials:edit-section')):?>
        <h3 class="<?php if (!strlen($section->name)): ?>no-title<?php endif; ?>">
            <span><?= (strlen($section->name) ? $section->name : _("Нет названия") )?></span>
            <a href="<?= $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'edit-section', 'section_id' => $section->section_id));?>"><img src="<?= $this->serverUrl('/images/blog/controls-edit.png'); ?>"></a>
            <?php if ((!count($section->lessons)) && (count($this->sections) > 1) && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:subject:materials:delete-section')): ?>
            <a href="<?= $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'delete-section', 'section_id' => $section->section_id));?>"><img src="<?= $this->serverUrl('/images/blog/controls-delete.png'); ?>"></a>
            <?php endif; ?>
        </h3>
        <?php elseif (strlen($section->name)): ?>
        <h3><span><?= $section->name;?></span></h3>
        <?php endif; ?>
    
        <div class="items" id="<?= $containerIds[] = $this->id('c'); ?>">
            <?php foreach ($section->lessons as $lesson): ?>
            <?php echo $this->materialPreview($lesson);?>
            <?php endforeach; ?>
        </div>
    </div></div>
    </form>
    <?php endforeach; ?>
</div>

<?php $this->inlineScript()->captureStart(); ?>
(function () {

var selector = <?= Zend_Json::encode('#'.implode(', #', $containerIds)) ?>;
var subjPage = <?= Zend_Json::encode('#'.$subjectPageId) ?>

function enableMassonry () {
    $(selector).masonry({
        itemSelector : '.material-preview',
        columnWidth : 290,
        isFitWidth: true,
        isAnimated: true,
        animationOptions: {
            duration: 400
        }
    });
}
function disableMassonry () {
    $(selector).masonry('destroy');
}
function saveOrder ($form) {
    var data = $form.serializeArray()
      , action = $form.attr('action')
      , method = $form.attr('method');

    method = /^(GET|PUT|POST|DELETE|HEAD|OPTIONS)$/i.test(method || '') ? method.toUpperCase() : 'GET';

    $.ajax(action || '', {
        type: method,
        data: data
    });
}
function enableEditMode () {
    $(subjPage).disableSelectionLight().addClass('edit-mode');
    $(selector).sortable({
        connectWith: selector,
        containment: '#main',
        cursor: 'move',
        forceHelperSize: true,
        forcePlaceholderSize: true,
        placeholder: 'ui-state-highlight placeholder',
        //tolerance: 'pointer',
        handle: '> .grip',
        revert: 300,
        update: function () {
            saveOrder($(this).closest('form'));
        }
    });
}
function disableEditMode () {
    $(subjPage).enableSelection().removeClass('edit-mode');
    $(selector).sortable('destroy');
}

$(function () {
    if ($(subjPage).hasClass('edit-mode')) {
        enableEditMode();
    } else {
        enableMassonry();
    }
});
$(document).delegate('#edit-mode-enable', 'click', function (event) {
    event.preventDefault();
    disableMassonry();
    enableEditMode();
});
$(document).delegate('#edit-mode-disable', 'click', function (event) {
    event.preventDefault();
    disableEditMode();
    enableMassonry();
});

})();
<?php $this->inlineScript()->captureEnd(); ?>
