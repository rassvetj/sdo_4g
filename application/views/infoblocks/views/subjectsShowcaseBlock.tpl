<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/subject-showcase/subject-showcase.css') ); ?>
<?php $this->headScript()->appendFile( $this->serverUrl('/js/lib/jquery/jquery.masonry.min.js') ); ?>
<?php $itemId = $this->id('ss'); ?>
<div class="subject-showcase" id="<?= $itemId ?>">
    <!--div class="tooltip">подсказочки текст начерта<sup>й</sup> тут</div-->
	<div class="crumbs"><?php echo $this->breadcrumbs ?></div>
    <?php if(count($this->classifiers) > 0): ?>
    <ul class="categories">

        <?php foreach($this->classifiers as $classifier): ?>
        <li>
            <a href="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'course-showcase', 'action' => 'index', 'classifier_id' => $classifier->classifier_id, 'category_id' => $classifier->type_id)); ?>">
                <span class="icon">
                    <img src="<?php echo $classifier->getIcon();?>">
                </span><span class="title">
                    <span><?=$classifier->name; ?></span>
                </span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
    <?php if(count($this->subjects) > 0): ?>
    <ul class="courses">

        <?php foreach($this->subjects as $subject): ?>
        <li>
            <a href="<?php echo $this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'description', 'subject_id' => $subject->subid));?>" class="icon">
                <img src="<?php echo $subject->getIcon()?>">
            </a><span class="title">
                <a href="<?php echo $this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'description', 'subject_id' => $subject->subid));?>"><?= $subject->name; ?></a>
            </span>
            <?php if (Zend_Registry::get('serviceContainer')->getService('Option')->getOption('regDeny') !== '1'): ?>
            <span class="register"><a href="<?php echo $this->url(array('module' => 'user', 'controller' => 'reg', 'action' => 'subject', 'subid' => $subject->subid));?>"><?= _("Подать заявку") ?></a></span>
            <?php endif;?>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php endif; ?>
</div>

<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function () {
    $(<?= Zend_Json::encode("#$itemId > .categories") ?>).masonry({
        itemSelector : 'li',
        columnWidth : 220,
        isFitWidth: true,
        isAnimated: true,
        animationOptions: {
            duration: 400
        }
    });
});
$(document.body).undelegate('.subject-showcase');
$(document.body).delegate('.subject-showcase a:not(.courses a)', 'click.subject-showcase', function (event) {
    var $this = $(this)
      , $container = $this.closest('.subject-showcase')
      , xhr = $container.data('xhr')
      , $overlay = $container.children('.ajax-spinner-local');

    event.preventDefault();

    if (!$overlay.length) {
        $container.append('<div class="ajax-spinner-local"></div>');
    }
    if (xhr == null || xhr.isResolved() || xhr.isRejected()) {
        $container.addClass('ui-state-loading');
        xhr = $.get($this.attr('href')).always(function () {
            if (!$container.prev('.error-box').length) {
                $container.before('<div class="error-box"></div>');
            }
            $.ui.errorbox.clear($this);
            $container.removeClass('ui-state-loading');
        }).done(function (data) {
            var $data = $(data);
            if ($data.hasClass('subject-showcase') || $data.children('.subject-showcase').length) {
                $container.replaceWith($data);
            } else {
                $('<p>' + HM._('Ошибка при загрузке данных') + '</p>').insertAfter($this).errorbox({
                    level: 'error'
                });
            }
        }).fail(function () {
            $('<p>' + HM._('Ошибка при загрузке данных') + '</p>').insertAfter($this).errorbox({
                level: 'error'
            });
        });
        $this.data('xhr', xhr);
    }
});
<?php $this->inlineScript()->captureEnd(); ?>