<?php
    $showCourseContainer = isset($this->tree);
    // has_tree SHOULD be has_own_tree, because it's value means: if couse
    // has own navigation - set it to true
    if (isset($this->isDegeneratedTree)) {
        $showCourseTree = $showCourseContainer && $this->isDegeneratedTree === false;
    } else {
        $showCourseTree = $showCourseContainer && count($this->tree) != 0 && !$this->courseObject->has_tree;
    }
    $showCourseInPopup = $showCourseContainer && $this->courseObject->new_window;
?>
<?php 
if (isset($this->courses) && count($this->courses) == 0):?>
    <?php $this->placeholder('tmp')->set(''); ?>
    <?php $this->placeholder('tmp')->captureStart(); ?>
        <?php echo _('Занятие не связано с учебным модулем.');?>
        <?php //if (Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_TEACHER):?>
        <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)):?>
            <?php echo sprintf(_('Перейти на страницу').' <a href="%s">'._('Выбор используемых учебных модулей').'</a>', $this->escape( $this->baseUrl($this->url( array('module' => 'subject', 'action' => 'courses', 'controller' => 'index', 'subject_id' => $this->subjectId) )) ))?>
        <?php endif;?>
    <?php $this->placeholder('tmp')->captureEnd(); ?>
    <?php echo $this->notifications(array(array(
        'message' => (string)$this->placeholder('tmp'), 'type' => HM_Notification_NotificationModel::TYPE_NOTICE,
        'hasMarkup' => true
    )), array('html' => true)); ?>
<?php elseif(isset($this->courses) && count($this->courses) > 0):?>
    <ol class="subject-course-list">
        <?php foreach($this->courses as $course):?>
        <li class="subject-course"><a href="<?php echo $this->escape( $this->baseUrl($this->url(array('course_id' => $course->CID))) );?>"><?php echo $this->escape($course->Title)?></a></li>
        <?php endforeach;?>
    </ol>
<?php endif;?>
<?php
// TODO:
//       gradient-me && gradient-me-again must be added by script from theme
//       *-button-backup also
//       common.js must push iframe with indexbox to the top
?>
<?php if ($showCourseContainer):?>
<?php if (count($this->tree) === 1) { $this->current = $this->tree[0]['key']; } ?>
<?php
$itemUrlParams = array('module' => 'course', 'controller' => 'item', 'action' => 'view');
$idSuffix = "";
if (isset($this->lessonId) && $this->lessonId !== false) {
    $itemUrlParams['lesson_id'] = $this->lessonId;
    $idSuffix = "_{$this->lessonId}";
}
$itemUrl = $this->url( $itemUrlParams );
$currentUrl = (false !== $this->current)
    ? "{$itemUrl}/item_id/{$this->current}"
    : "about:blank";

$courseWindowId = $this->id("course_window{$idSuffix}_{$this->current}");
$openInPopupId = $this->id("course_open_button{$idSuffix}_{$this->current}");

$cssClasses = array("course-presentation");
if (count($this->tree) === 0  && !isset($this->allowEmptyTree)) array_push($cssClasses, "course-presentation-empty");
if ($showCourseTree) array_push($cssClasses, "course-presentation-with-index");
else                 array_push($cssClasses, "course-presentation-without-index");
if ($showCourseInPopup) array_push($cssClasses, "course-presentation-use-popup");
if ($this->itemCurrent) $title = 'title="'.$this->escape($this->itemCurrent->title).'"';
?>

<!-- BEGIN: course presentation box -->
<div class="<?php echo implode(" ", $cssClasses); ?>">
    <div class="course-iframe-box">
        <?php if (count($this->tree) === 0  && !isset($this->allowEmptyTree)): ?>
        <div class="course-text-box"><?php echo _("Структура учебного модуля пуста"); ?></div>
        <?php elseif ($showCourseInPopup): ?>
        <div class="course-text-box subject-course">
            <!-- TODO отнапильничать фразу, т.к. кнопка "play" опциональна, то её нужно составить аккуратно -->
            <span class="course-popup-excuse-text"><?php echo _("Учебный модуль должен быть открыт в новом окне"); ?></span>
            <?php if (!$showCourseTree): ?>
                <a class="<?php echo $this->escape($openInPopupId); ?>" href="<?php echo $this->escape($currentUrl); ?>"
                    target="<?php echo $courseWindowId; ?>" <?php echo $title; ?>></a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <iframe src="<?php echo $this->escape($currentUrl); ?>" name="item" id="course-iframe" frameborder="0">
            <?php echo _("Отсутствует поддержка iframe!"); ?>
        </iframe>
        <?php endif; ?>
    </div>

    <?php if ($showCourseTree): ?>
    <!-- BEGIN: course index -->
    <div class="course-index ui-helper-hidden">
        <div class="course-index-header"><?php echo _("Оглавление"); ?></div>
        <div class="course-index-box"><div class="course-index-box-wrapper"><?php
            $activate = "function (dtnode) {
                var popupId = '".($showCourseInPopup ? $courseWindowId : '')."'
                  , link = jQuery('.".$openInPopupId."');
                if (!dtnode.data.isFolder) {
                    if (popupId) {
                        if (link.length) {
                            link.attr('title', dtnode.data.title || '')
                                .attr('href', '". $itemUrl ."/item_id/' + dtnode.data.key);
                        }
                        window.elsHelpers.popup('". $itemUrl ."/item_id/' + dtnode.data.key, popupId);
                    } else {
                        jQuery('#course-iframe').attr('src', '". $itemUrl ."/item_id/' + dtnode.data.key);
                    }
                }
            }";
            $queryExpand = "function (flag, dtnode) {
                if (!flag) {
                    $.post('".$this->url(array('module' => 'course', 'controller' => 'index', 'action' => 'deletetreechild') )."', { key: dtnode.data.key});
                }
            }";
            $res =  $this->htmlTree($this->tree, 'htmlTree');
            echo $this->uiDynaTree(
                'tree',
                $res,
                array(
                    'remoteUrl' => $this->url(array(
                        'module' => 'course',
                        'controller' => 'index',
                        'action' => 'gettreechild'
                    )),
                    'onQueryExpand' => $queryExpand,
                    'title' => $this->courseObject->Title,
                    'clickFolderMode' => 2,
                    'onActivate' => $activate,
                    // block user interaction while loading child nodes
                    'onClick' => 'function (dtnode, event) { if (dtnode.isLoading) { return false; } }',
                    'onKeydown' => 'function (dtnode, event) { if (dtnode.isLoading && _.indexOf([37, 39, 187, 189], event.which) !== -1) { return false; } }'
                )
            );
        ?></div></div>
    </div>
    <?php endif; // $showCourseTree ?>
    <!-- END: course index -->
    <div class="course-navigation">
        <div class="gradient-me"></div>
        <div class="gradient-me-again"></div>
        <div class="gradient-hr"></div>
        <div class="wrapper">
            <?php if ($showCourseTree): ?>
            <div class="nav-button-backup course-button-backup"></div>
            <a class="nav-button course-button course-button-bottom" id="course-button"><span class="ns-spacer"></span><span><?php echo _("Оглавление") ?></span></a>
            <?php endif; // $showCourseTree ?>
        </div>
    </div>
</div>

<?php if ($showCourseTree): ?>
<?php $this->inlineScript()->captureStart(); ?>
jQuery(document).bind('dynatreecreate', function (event) {
    var $target = $(event.target)
        , dTree
        , active
        , current = <?php echo Zend_Json::encode($this->current); ?>;
    if ($target.is('#tree')) {
        dTree = $target.dynatree("getTree");
        active = dTree.getNodeByKey(current);
        if (!dTree.getActiveNode() && current && active) {
            active.activateSilently();
        }
    }
});
jQuery(function ($) {
    function animateIdxToggle ($button, $courseIndex) {
        var $ifr = $courseIndex.prev('iframe');
        if (!$ifr.length) {
            $courseIndex.before('<iframe scrolling="no" class="bgiframe" frameborder="0" tabindex="-1" src="javascript:\'\';">');
            /* TODO: сделать так, что-бы iframe закрывал только то, что под меню */
            $ifr = $courseIndex.prev('iframe');
            $ifr.css({
                position: 'absolute',
                display: 'block',
                zIndex: 0,
                width: '100%',
                height: '100%',
                background: 'white',
                top: 0,
                left: 0
            }).hide();
            $ifr.get(0).style.opacity = 0.8;
            try {
                _.delay(function () {
                    $($ifr.get(0).contentWindow.document).click(function (event) {
                        $(document).trigger('click');
                    });
                }, 100);
            } catch (error) {}
        }
        var idxIsGrowing = !$courseIndex.is(':visible');
        var queueSize = $courseIndex.queue('menufx').length;
        if ($ifr.is(':animated') || $courseIndex.is(':animated')) { return; }
        $button.toggleClass("course-button-bottom");
        if (idxIsGrowing) {
            $courseIndex.queue('menufx', function (next) {
                $ifr.slideToggle(10, function () { next(); });
            });
        }
        if (idxIsGrowing) {
            $button.addClass("course-button-active");
        } else {
            $button.removeClass("course-button-active");
        }
        $courseIndex.queue('menufx', function (next) {
            $courseIndex.animate({
                height: "toggle", marginBottom: "toggle", marginTop: "toggle",
                paddingBottom: "toggle", paddingTop: "toggle"
            }, {
                duration: 100,
                step: function (now, fx) {
                    //$button.css('top', -1 * ($courseIndex.height()) - 1);
                },
                complete: function () {
                    if (!idxIsGrowing) {
                        //$button.css('top', '');
                    }
                    next();
                }
            });
        });
        if (!idxIsGrowing) {
            $courseIndex.queue('menufx', function (next) {
                $ifr.slideToggle(10,function () { next(); });
            });
        }
        if (queueSize === 0) {
            $courseIndex.dequeue('menufx');
        }
    }
    $(document).click(function (event) {
        var $target = $(event.target)
          , isLeafDynatreeNode = $target.is('a.dynatree-title') && !$target.parent().is('.dynatree-has-children');
        if (!$('.course-index').is(':visible')) { return; }
        // FIXME: HACK - event.target.parentNode
        if ( (event.target.nodeType == 9 || !!event.target.parentNode) && (isLeafDynatreeNode || !$target.closest('.course-index').length) ) {
            animateIdxToggle($('#course-button'), $('.course-index'));
        }
    });
    $('#course-button').click(function () {
        animateIdxToggle($(this), $('.course-index'));
    });
});
$(function(){
    $(".course-navigation").disableSelection()
        .find('#course-button').trigger("click");
})
<?php $this->inlineScript()->captureEnd(); ?>
<?php endif; // $showCourseTree ?>

<!-- END: course presentation box -->
<?php endif; // $showCourseContainer ?>