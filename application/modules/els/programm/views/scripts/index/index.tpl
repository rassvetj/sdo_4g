<?php
    if (isset($this->message)) {
        echo '<div id="error-box" style="margin-bottom: 15px;" class="error-box">
            <div id="error-message-0" class="ui-widget ui-els-flash-message" title="">
                <div class="ui-state-error ui-corner-all">
                    <span class="ui-icon ui-icon-alert"></span>
                    <div class="ui-message-here">'.$this->message.'</div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>';
    }
?>
<?php $pageId = $this->id('pa'); ?>
<?php $this->headLink()
    ->appendStylesheet( $this->baseUrl('css/content-modules/extended-page.css') )
    ->appendStylesheet( $this->baseUrl('css/content-modules/program-editor.css') ); ?>

<?php $this->placeholder('columns')->captureStart('SET'); ?>

<h3 class="title"><?= _("Все учебные курсы") ?></h3>
<style>
    .electiv{
        margin-right: 20px!important;
    }
</style>

<?php if (count($this->subjects) || count($this->sessions)):?>
<div class="page-context-accordion" id="<?= $pageId; ?>-accordion">
    <?php if (count($this->subjects)):?>
    <h3 class="ui-accordion-header"><a href="#"><span class="header"><?php echo _('Учебные курсы')?></span></a></h3>
    <div class="ui-accordion-content"><div class="ui-accordion-content-wrapper">
        <ul id="<?= $pageId ?>-source">
            <?php foreach($this->subjects as $subject):?>
            <li <?php if (count($this->events) && $this->events->exists('subid', $subject->subid)):?>class="ui-state-disabled"<?php endif;?>>
                <?php
                    $freemode =  in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER));
                ?>
                <span><?php echo $subject->name; ?><b class="isElective" style="color: red"></b></span>
                <input type="hidden" name="course_id[]" value="<?php echo $subject->subid?>">
                <input type="hidden" name="idElective[]" value="0">
                <input type="hidden" name="freemode[]" value="<?php echo ($freemode ? 1:0); ?>">
                <span class="ui-icon ui-icon-pin-s change-mode-course" style="right: 30px;"></span>
                <span class="ui-icon ui-icon-trash remove"></span>
            </li>
            <?php endforeach;?>
        </ul>
    </div></div>
    <?php endif;?>
    <?php if (count($this->sessions)):?>
    <h3 class="ui-accordion-header"><a href="#"><span class="header"><?php echo _('Учебные сессии')?></span></a></h3>
    <div class="ui-accordion-content"><div class="ui-accordion-content-wrapper">
        <ul id="<?= $pageId ?>-source">
            <?php foreach($this->sessions as $subject):?>
            <?php $freemode =  in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER)); ?>
            <li <?php if (count($this->events) && $this->events->exists('subid', $subject->subid)):?>class="ui-state-disabled"<?php endif;?>>
                <span><?php echo $subject->name?><b class="isElective" style="color: red"></b></span>
                <input type="hidden" name="course_id[]" value="<?php echo $subject->subid?>">
                <input type="hidden" name="idElective[]" value="0">
                <input type="hidden" name="freemode[]" value="<?php echo ($freemode ? 1:0); ?>">
                <span class="ui-icon ui-icon-pin-s change-mode-course" style="right: 30px;"></span>
                <span class="ui-icon ui-icon-trash remove"></span>
            </li>
            <?php endforeach;?>
        </ul>
    </div></div>
    <?php endif;?>
</div>
</br>
<?php endif;?>

<?php $this->placeholder('columns')->captureEnd(); ?>
<?php $this->placeholder('columns')->captureStart(); ?>
<h3 class="title"><?= _("Курсы, включенные в программу") ?></h3>
<form method="POST" action="<?php echo $this->serverUrl($this->url(array('module' => 'programm', 'controller' => 'index', 'action' => 'assign', 'programm_id' => $this->programmId)))?>" id="<?= $pageId ?>-sorted">
    <ul class="programs-editor-sorted">
        <?php if (count($this->events)):?>

            <?php foreach($this->events as $subject):?>
                <li>
                    <?php
                        $freemode = in_array($subject->reg_type, array(HM_Subject_SubjectModel::REGTYPE_FREE, HM_Subject_SubjectModel::REGTYPE_MODER));
                        foreach ($this->collection as $item) {
                            if ($item->item_id == $subject->subid) {
                                $subject->isElective = $item->isElective;
                            }
                        }
                    ?>
                    <span><?php echo $subject->name?><b class="isElective" style="color: red"><?php echo ($subject->isElective==1 ? '' : '*'); ?></b></span>
                    <input type="hidden" name="course_id[]" value="<?php echo $subject->subid?>">
                    <input type="hidden" name="idElective[]" value="<?php echo (int)$subject->isElective; ?>">
                    <input type="hidden" name="freemode[]" value="<?php echo ($freemode ? '1':'0'); ?>">
                    <span class="ui-icon <?php echo ($subject->isElective==1 ? 'ui-icon-pin-w' : 'ui-icon-pin-s');?> change-mode-course" style="right: 30px;" title="<?php echo ($subject->isElective==1) ? _('Сделать обязательным') : _('Сделать элективным');?>"></span>
                    <span class="ui-icon ui-icon-trash remove" title="<?php echo _('Исключить из программы');?>"></span>
                </li>
            <?php endforeach;?>
        <?php endif;?>
    </ul>
	<div class="footnotes"><hr>
    <p><font color="red">* </font><span><?php echo _('Обязательный элемент программы.');?></span></p>
	</div>
    <button class="toPost"><?php echo _('Сохранить'); ?></button>
</form>


<?php $this->placeholder('columns')->captureEnd(); ?>

<?php echo $this->partial('_columns.tpl', array(
    'columns' => $this->placeholder('columns')->getArrayCopy(),
    'classes' => 'programs-editor'
)); ?>

<?php $this->headScript()->captureStart(); ?>
(function () {

function trySave ($form) {
    $.ajax($form.prop('action'), {
        type: /^(GET|PUT|POST|DELETE|HEAD|OPTIONS)$/i.test($form.prop('method'))
            ? $form.prop('method').toUpperCase()
            : 'GET',
        data: $form.serializeArray()
    })
}

$(document).ready(function () {

    var $source = $("#" + <?= Zend_Json::encode($pageId) ?> + '-source');

    $("#" + <?= Zend_Json::encode($pageId) ?> + '-accordion').accordion({
        autoHeight: false
    });
    $("#" + <?= Zend_Json::encode($pageId) ?> + '-source > li').draggable({
        appendTo: 'body',
        cursor: 'move',
        cursorAt: {top: 0, left: 0},
        helper: function () {
            return $('<div>').css({
                width:  $(this).outerHeight(),
                height: $(this).outerHeight(),
                marginTop: -1 * ($(this).outerHeight() / 2),
                marginLeft: -1 * ($(this).outerHeight() / 2)
            }).addClass('programs-editor-draggable-helper');
        },
        containment: '#main .els-body:first',
        stop: function () {},
        connectToSortable: '#' + <?= Zend_Json::encode($pageId) ?> + '-sorted > ul'
    });
    $("body").on("click", ".toPost", function (){
        if (typeof $(this).closest('form').attr('id') != 'undefined') {
            trySave($(this).closest('form'));
        }
        return false;
    });
    $("body").on("click", ".change-mode-course", function (){
        var input = $(this).parent().children('input[name="idElective[]"]'),
            freemode = $(this).parent().children('input[name="freemode[]"]'),
            text = $(this).parent().children('span').children('b');

        if (freemode.val() == 0) {
            alert('<?php echo _('Данныый курс не предполагает возможность подачи заявок, поэтому его нельзя сделать элективным.');?>');
            return false;
        }
        if (input.val() == 1) {
            if(!confirm("<?php echo _('Вы действительно желаете сделать этот курс обязательным? После сохранения программы данный курс будет автоматически назначен всем слушателям, проходящим обучение по данной программе.');?>")) {
               return false;
            }
            $(this).removeClass('ui-icon-pin-w');
            $(this).addClass('ui-icon-pin-s');
            text.text('*');
            input.val(0);
        } else {
            $(this).removeClass('ui-icon-pin-s');
            $(this).addClass('ui-icon-pin-w');
            text.text('');
            input.val(1);
        }

        if (typeof $(this).closest('form').attr('id') != 'undefined') {
            //trySave($(this).closest('form'));
        } else {
            return false;
        }
    });
    $('#' + <?= Zend_Json::encode($pageId) ?> + '-sorted > ul').sortable({
        appendTo: 'body',
        cursor: 'move',
        cursorAt: {top: 0, left: 0},
        forcePlaceholderSize: true,
        placeholder: 'ui-state-highlight',
        axis: 'y',
        tolerance: 'pointer',
        receive: function (event, ui) {
            ui.item.addClass('ui-state-disabled');
        },
        remove: function () {
            //trySave($(this).closest('form'));
        },
        update: function (event, e) {
            //trySave($(this).closest('form'));
            e.item.children('span').children('b').text('*');
        },
        cursorAt: {top: 0, left: 0},
        helper: function (event, element) {
            return $('<div>').css({
                width:  element.outerWidth(),
                height: element.outerHeight(),
                left:   element.offset().left,
                marginTop: -1 * (element.outerHeight() / 2)
            }).addClass('programs-editor-draggable-helper');
        },
        containment: '#main .els-body:first'
    }).delegate('li .remove', 'click', function (event) {

        if(!confirm("<?php echo _('Вы действительно желаете исключить этот курс из программы? При этом пользователи, уже зачисленные курс, не будут отчислены.');?>")) {
            return false;
        }

        var $li = $(event.target).closest('li')
          , $input = $li.find('input[type="hidden"]')
          , val = $input.val()
          , name = $input.prop('name')
          , $items
          , position
          , height
          , accordionContent
          , scrollTop
          , $ul = $li.closest('ul')
          , $form = $ul.closest('form');
        $items = $source.find('li input[type="hidden"]').filter(function () {
            return $(this).val() === val && $(this).prop('name') === name;
        }).closest('li');

        position = $items.first().show().position();
        height = $items.first().outerHeight();
        $items.first().hide();

        accordionContent = $items.first().closest('.ui-accordion-content');

        scrollTop = accordionContent.scrollTop();
        if (position) { // временный хак с if (position); надо разобраться почему не определен position
            if (position.top < 0) {
            scrollTop = scrollTop + position.top - 10;
        } else if (position.top > (accordionContent.height() - height)) {
            scrollTop = scrollTop + position.top - accordionContent.height() + height + 10;
            }
        }

        if (accordionContent.scrollTop() != scrollTop) {
            accordionContent.animate({
                scrollTop: scrollTop
            }, function () {});
            $items.delay(400);
        }
        $items.removeClass('ui-state-disabled')
            .hide()
            .slideToggle('fast', function () {
                $(this).effect('highlight');
            });
        $li.slideToggle('fast', function () {
            $li.remove();
            $ul.sortable('refresh');
            //trySave($form);
        });
    });
});

})();

<?php $this->headScript()->captureEnd(); ?>
