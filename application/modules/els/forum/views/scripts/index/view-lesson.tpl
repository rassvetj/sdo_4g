<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/forum2.css')); ?>
<?php $pageMsg = $this->placeholder('page_messages'); ?>
<div class="forum forum-index">

    <?php if(!empty($this->forum->section)):
        $section  = $this->forum->section;
    ?>
    <?php $sectionIndex = 0; ?>

    <div class="topics-list-container first<?php if (empty($section->title)): ?> topics-list-wo-title<?php endif; ?>">
        <?php if(!empty($section)): ?>
        <?= $this->partial('themes.tpl', array(
        'forum'     => $this->forum,
        'sections'  => $section,
        'moderator' => $this->forum->moderator,
        'isSubscriber' => $this->isSubscriber
        )) ?>
        <?php endif; ?>
    </div>
    <?php endif ?>
    <?php if(!empty($this->formTheme)): ?>
    <div class="topic-createeditor visuallyhidden">
        <div class="error-box"></div>
        <?= $this->formTheme ?>
    </div>
    <?php endif ?>
</div>

<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function(){
    $('.forum-index .topic-expand-comments').get(0).click();
});

$(document.body).undelegate('.forum2');

$(document.body).delegate('.forum-index .topic-comment-ajaxload, .forum-index .topic-ajaxload', 'click.forum2', function (event) {
var $this = $(this)
, $text
, $li
, xhr
, pingXhr
, prefix = $this.is('.topic-ajaxload') ? 'topic-' : 'topic-comment-';

event.preventDefault();

$li = $this.closest('li');
$text = $li.find('> .'+ prefix +'text:first');
if (!$text.length) {
$text = $('<div class="'+ prefix +'text"><div class="'+ prefix +'text-content"></div></div>')
.insertAfter($li.find('> .'+ prefix +'header:first'));
}
$li.toggleClass(prefix +'text-visible');

xhr = $li.data('ajax');
pingXhr = $li.data('pingAjax');
if ($li.is('.'+ prefix +'text-visible') && (!$li.is('.'+ prefix +'text-loading') || xhr == null) && (!$li.is('.'+ prefix +'text-loaded') || $li.is('.'+ prefix +'text-loaderror'))) {
if (xhr != null) {
xhr.abort();
}
$li.addClass(prefix +'text-loading')
.removeClass(prefix +'text-loaderror '+ prefix +'text-loaded');
$text.addClass('ajax-spinner-local');
xhr = $.ajax($this.attr('href'), {
global: false,
dataType: 'html'
}).always(function () {
$li.removeClass(prefix +'text-loading');
$text.removeClass('ajax-spinner-local');
}).fail(function (xhr, textStatus) {
if (textStatus != "abort") {
$li.addClass(prefix +'text-loaderror');
}
}).done(function (data) {
$li.addClass(prefix +'text-loaded');
$text.find('> .'+ prefix +'text-content').html(data);
});
$li.data('ajax', xhr);
} else if ($li.is('.topic-comment-text-visible') && pingXhr == null && $this.data('pingUrl')) {
pingXhr = $.ajax($this.data('pingUrl'), {
global: false,
dataType: 'text'
});
$li.data('pingAjax', pingXhr);
}
});
$(document.body).delegate('.forum-index .topic-expand-comments', 'click.forum2', function (event) {
var $this = $(this)
, $li = $this.closest('li')
, xhr
, $overlay;

event.preventDefault();

xhr = $li.data('commentsAjax');
$overlay = $li.find('> .topic-loading-overlay');
if (!$overlay.length) {
$li.append('<div class="topic-loading-overlay"></a>');
}
if ((!$li.is('.topic-comments-loading') || xhr == null) && (!$li.is('.topic-comments-loaded') || $li.is('.topic-comments-loaderror'))) {
if (xhr != null) {
xhr.abort();
}
$li.addClass('topic-comments-loading')
.removeClass('topic-comments-loaderror topic-comments-loaded');
xhr = $.ajax($this.attr('href'), {
global: false,
dataType: 'html'
}).always(function () {
$li.removeClass('topic-comments-loading');
}).fail(function (xhr, textStatus) {
if (textStatus != "abort") {
$li.addClass('topic-comments-loaderror');
if (xhr.status == 404 || xhr.status == 500) {
$li.append(xhr.responseText).hide(750);
} else {
$.ui.errorbox.clear();
$('<div>' + <?= Zend_Json::encode($pageMsg->errMsgsLoading); ?> + '</div>').errorbox({ level: 'error' });
}
}
}).done(function (data) {
var $comments = $li.find('> ul.topic-comments')
, $footer = $li.find('> .topic-footer');
$li.toggleClass('topic-comments-visible');
$li.addClass('topic-comments-loaded');
if ($comments.length) {
$comments.replaceWith(data);
} else {
$footer.before(data);
}
});
$li.data('commentsAjax', xhr);
} else if ($li.is('.topic-comments-loaded')) {
$li.toggleClass('topic-comments-visible');
}
});
$(document.body).delegate('.forum-index .topic-pin-marker a', 'click.forum2', function (event) {
var $this = $(this)
, $li = $this.closest('li')
, xhr;

event.preventDefault();

xhr = $li.data('pinAjax');
if (xhr == null || xhr.isResolved() || xhr.isRejected()) {
$li.toggleClass('topic-pinned');
$li.data('pinAjax', $.get($this.attr('href')));
}
});
// Answer link & label
$(document.body).delegate('.forum-index label.for-tinymce', 'click.forum2', function (event) {
var $textarea;

$textarea = $(this).closest('.forum-index')
.find('.topic-createeditor:first textarea:first');
if ($textarea.length) {
tinymce.execCommand('mceFocus', false, $textarea.prop('id'));
}
});
$(document.body).delegate('.forum-index .topic-create-button', 'click.forum2', function (event) {
var $this = $(this)
, $topicList = $this.prev('.topics-list')
, $container = $this.closest('.forum-index')
, $editor
, $textarea
, $form;

event.preventDefault();

if (!$this.hasClass('topic-createeditor-active')) {
$container.find('.topic-createeditor-active')
.removeClass('topic-createeditor-active');
$this.addClass('topic-createeditor-active');
$editor   = $container.find('.topic-createeditor:first');
$textarea = $editor.find('textarea:first');
$form     = $editor.find('form:first');
$.ui.errorbox.clear($editor);
tinymce.execCommand('mceRemoveControl', false, $textarea.prop('id'));
$this.after($editor);
$form.attr('action', $this.attr('href'));
tinymce.execCommand('mceAddControl', false, $textarea.prop('id'));
$editor.removeClass('visuallyhidden');
/*tinymce.execCommand('mceFocus', false, $textarea.prop('id'));*/
$form.find('.topic-input input[type="text"]').first().focus();
}
});
$(document.body).delegate('.forum-index .topic-create-cancel', 'click.forum2', function (event) {
var $this = $(this)
, $container = $this.closest('.forum-index');

event.preventDefault();

$container.find('.topic-create-button').removeClass('topic-createeditor-active')
$.ui.errorbox.clear($container.find('.topic-createeditor:first'));
$container.find('.topic-createeditor:first')
.addClass('visuallyhidden')
.find('input[type="text"], textarea').val('');
});

$(document.body).delegate('.forum-index .topic-createeditor form', 'submit.forum2', function (event) {
var $form = $(this)
, action = $form.attr('action')
, $editor = $form.closest('.topic-createeditor')
, $forumRoot = $form.closest('.forum-index')
, $container = $forumRoot
, $topicList = $editor.prev('.topic-create-button').prev('.topics-list')
, $overlay
, xhr
, data
, editorHeight
, type;

event.preventDefault();

if (!$topicList.length) {
$topicList = $('<ul class="topics-list" />')
.insertBefore($editor.prev('.topic-create-button'));
}

type = /^(GET|POST)$/i.test($form.attr('method') || '') ?
$form.attr('method').toUpperCase()
: 'GET';
data = $form.serializeArray();

editorHeight = $editor.outerHeight();
$editor.find('input[type="text"], textarea').val('');

$overlay = $container.find('.topic-reply-posting-overlay');
if (!$overlay.length) {
$overlay = $('<div class="topic-reply-posting-overlay ajax-spinner-local">');
    }
    $overlay.insertAfter($editor)
    .css('height', editorHeight);

    $container.removeClass('topic-createeditor-active')
    .addClass('topic-reply-posting');
    $editor.addClass('visuallyhidden');

    if ($forumRoot.data('activeChildrenReplyXhr') == null) {
    $forumRoot.data('activeChildrenReplyXhr', 1);
    } else {
    $forumRoot.data('activeChildrenReplyXhr', $forumRoot.data('activeChildrenReplyXhr') + 1);
    }
    if ($forumRoot.data('activeChildrenReplyXhr') > 0) {
    $forumRoot.addClass('topic-chilren-reply-posting');
    }
    if ($container.data('replyXhr') != null) {
    $container.data('replyXhr').abort();
    }
    xhr = $.ajax(action, {
    global: false,
    dataType: 'html',
    type: type,
    data: data
    }).always(function () {
    $container.removeClass('topic-reply-posting');
    $editor.removeClass('visuallyhidden');
    $forumRoot.data('activeChildrenReplyXhr', $forumRoot.data('activeChildrenReplyXhr') - 1);
    if ($forumRoot.data('activeChildrenReplyXhr') === 0) {
    $forumRoot.removeClass('topic-chilren-reply-posting');
    }
    }).fail(function (xhr, textStatus) {
    if (textStatus != "abort") {
    _.each(data, function (item) {
    var element = this.elements[item.name];
    if (element) {
    $(element).val(item.value);
    }
    }, $form.get(0));
    if (xhr.status == 400) {
    $editor.append(xhr.responseText);
    } else {
    elsHelpers.alert(<?= Zend_Json::encode($pageMsg->errNewSectnion) ?>, <?= Zend_Json::encode(_("Ошибка при создании темы")) ?>);
    }
    }
    }).done(function (data) {
    var $data = $(data)
    , $ul = $topicList;

    if ($data.is('ul')) {
    $data = $data.children('li:first');
    }
    if (!$data.is('li')) {
    throw new Error('Custom error: Invalid reply markup');
    } else {
    $('.forum-index .topic-create-cancel').trigger('click.forum2');
    }

    $data.appendTo($ul);
    });
    $container.data('replyXhr', xhr);
    });

    $(document.body).delegate('.forum-index .topics-list-container .topic-delete', 'click.forum2', function (event) {
    var $target = $(this);

    event.preventDefault();

    elsHelpers.confirm(<?= Zend_Json::encode(_('Вы действительно хотите удалить раздел?')) ?>, <?= Zend_Json::encode(_('Удаление раздела')) ?>).done(function () {
    $.get($target.attr('href')).always(function () {
    $.ui.errorbox.clear($target.parent());
    }).done(function () {
    $('<div>').insertAfter($target)
        .text(<?= Zend_Json::encode(_('Раздел был удален')) ?>).errorbox();
        $('<a>link</a>')
        .insertAfter($target)
        .addClass('visuallyhidden')
        .attr('onclick', 'window.location.href = <?= Zend_Json::encode($this->forum->url()) ?>')
        .trigger('mouseover')
        .trigger('click');
        }).fail(function () {
        $('<div>').insertAfter($target)
            .text(<?= Zend_Json::encode(_('Ошибка при удалении раздела')) ?>).errorbox();
            });
            });
            })
<?php if ($section[0]->lesson_id):?>
    $(document.body).delegate('.forum-index .topic-footer #subscribeSection', 'click.forum2', function (event) {
      if($(this).is(':checked')){
            url =  '<?=$this->url(array('module' => 'forum', 'controller' => 'index', 'action' => 'subscribe', 'route' => 'default'));?>';
       } else {
            url =  '<?=$this->url(array('module' => 'forum', 'controller' => 'index', 'action' => 'unsubscribe', 'route' => 'default'));?>';
       }
        $.ajax({
            url: url,
            dataType: 'json',
            success: function(result){
                alert(result.msg);
            }
        });
    });
<?php endif; ?>
<?php $this->inlineScript()->captureEnd(); ?>