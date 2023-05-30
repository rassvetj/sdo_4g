<?php 
$isStudent = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(HM_Role_RoleModelAbstract::ROLE_STUDENT));
?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/forum2.css')); ?>
<?php $pageMsg = $this->placeholder('page_messages'); ?>
<?php $this->placeholder('ratings')->captureStart() ?>
    <option>&nbsp;</option>
    <?php foreach($this->ratings as $key => $value): ?>
    <option value="<?= $key ?>"><?= $value ?></option>
    <?php endforeach ?>
<?php $this->placeholder('ratings')->captureEnd() ?>
<?php $section = $this->forum->section; ?>

<?php
    if ($section->lesson_id):
?>
    <a href="<?= $this->url(array('module'=>'forum', 'controller' => 'index', 'action' => 'view-lesson', 'subject' => 'subject', 'subject_id' => $section->subject_id, 'lesson_id' => $section->lesson_id, 'route' => 'default')) ?>">← <?php echo _('Назад');?></a>
    <?php
    else:
    ?>
    <a href="<?= $this->forum->url() ?>">← <?php echo _('Назад');?></a>
<?php
    endif;
?>
<?= $this->headSwitcher($this->switcherParams) ?>
<div id="<?=$section->section_id ?>" class="forum forum-topic-view">
    <div class="topic<?php if ($section->flags->closed): ?> topic-closed<?php endif; ?>">
        <div class="topic-header">
            <div class="topic-author-and-pubdate">
                <?php
                $userImg = Zend_Registry::get('serviceContainer')->getService('User')->getImageSrc($section->user_id);
                $userImg = ($userImg)? '/' . Zend_Registry::get('config')->src->upload->photo . $userImg : '/images/content-modules/nophoto-small.gif';
                ?>
                <?php if($isStudent) :?>
					<span class="pcard-link topic-author-userpic lightbox">
					<?='<img src="' . $this->serverUrl($userImg) . '"><div class="topic-new-marker">'. $pageMsg->newTheme .'</div>'; ?>
					</span>
				<?php else : ?>
					<?php echo $this->cardLink($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $section->user_id), 'default', true), '<img src="' . $this->serverUrl($userImg) . '"><div class="topic-new-marker">'. $pageMsg->newTheme .'</div>', 'html', array('pcard', 'topic-author-userpic')); ?>
                <?php endif; ?>
				<a href="<?= $this->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => $section->user_id), 'default', true) ?>" data-user-id="<?= $section->user_id ?>" class="topic-author-username"><?= $section->user_name ?></a>
                <time datetime="<?= $section->created ?>"><?= $section->createdDateTime() ?></time>
            </div>
            <h3 class="topic-title"><?=$this->escape($section->title) ?></h3>
        </div>
        <div class="topic-text"><div class="topic-text-content"><?= $section->text ?></div></div>
        <div class="topic-stats"><span class="ns-aligner"></span><span class="topic-stats-text">
            <?php if($this->forum->config->messages->structure->only_new): ?>
                <span class="active"><?= $pageMsg->messagesNew ?></span>|
                <a href="<?= $section->url(array('mode' => 'all')) ?>"><?= $pageMsg->messagesAll ?></a>
            <?php else: ?>
                <a href="<?= $section->url(array('mode' => 'new:list')) ?>"><?= $pageMsg->messagesNew ?></a>|
                <span class="active"><?= $pageMsg->messagesAll ?></span>
            <?php endif ?>
        </span></div>
        <div class="topic-footer">
            <?php if (!$section->flags->closed): ?>
            <a href="#<?= $section->section_id . '-reply' ?>" class="topic-reply"><?= $pageMsg->newComment ?></a>
            <?php endif; ?>
            <?php /* if ($this->forum->moderator): ?>
            <form action="" method="POST"><label for="<?=$section->section_id ?>-scoreme"><?= $pageMsg->score ?></label> <select id="<?=$section->section_id ?>-scoreme" name="score">
            </select></form>
            <?php $this->inlineScript()->captureStart(); ?>$(document).ready(function () {
                $('#<?=$section->section_id ?>-scoreme').selectmenu();
            });<?php $this->inlineScript()->captureEnd(); ?>
            <?php endif; */ ?>
            <?php if ($this->forum->moderator && $section->flags->closed/* && $this->forum->flags->subsections*/): ?>
            <a href="<?= $section->url(array('close' => '0')) ?>" class="topic-open ui-button" style="color:white;"><?= _('Открыть тему'); ?></a>
            <?php elseif ($this->forum->moderator/* && $this->forum->flags->subsections*/): ?>
            <a href="<?= $section->url(array('close' => '1')) ?>" class="topic-close ui-button"><?= _('Закрыть тему'); ?></a>
            <?php endif; // $this->forum->moderator && $section->flags->closed/* && $this->forum->flags->subsections*/ ?>
            <?php if ($this->forum->moderator/* && $this->forum->flags->subsections*/): ?>
            <a href="<?= $section->url(array('sdelete' => '1')) ?>" class="topic-delete ui-button" style="color:white;"><?= _('Удалить тему'); ?></a>
            <?php endif; // $this->forum->moderator && $this->forum->flags->subsections ?>
        </div>
    </div>
    <?php if(!empty($section->messages)): ?>
    <?=$this->partial('messages-theme.tpl', array(
        'section'        => $section,
        'messages'       => $section->messages,
        'forum'          => $this->forum,
        'root'           => true,
        'currentUserId'  => $this->currentUserId,
        'moderatorsList' => $this->moderatorsList
    )) ?>
    <?php endif ?>
    <?php if (!$section->flags->closed): ?>
    <div class="topic-comment-replyeditor visuallyhidden">
        <div class="error-box"></div>
        <?= $this->formAnswer ?>
    </div>
    <div id="<?=$section->section_id . '-reply' ?>" class="topic-replyeditor">
        <div class="error-box"></div>
        <?= $this->formMessage ?>
    </div>
    <?php endif; ?>
</div>

<?php $this->inlineScript()->captureStart(); ?>
$(document.body).undelegate('.forum2-view');

$(document.body).delegate('.forum-topic-view .topic-comment-footer > .topic-comment-reply', 'click.forum2-view', function (event) {
    var $this = $(this)
      , $li = $this.closest('li')
      , $footer = $li.children('.topic-comment-footer')
      , $container = $this.closest('.forum-topic-view')
      , $editor
      , $textarea
      , $form;

    event.preventDefault();

    if (!$li.hasClass('topic-comment-replyeditor-active')) {
        $container.find('.topic-comment-replyeditor-active')
            .removeClass('topic-comment-replyeditor-active');
        $li.addClass('topic-comment-replyeditor-active');
        $editor   = $container.find('.topic-comment-replyeditor');
        $textarea = $editor.find('textarea');
        $form     = $editor.find('form:first');
        $.ui.errorbox.clear($editor);
        tinymce.execCommand('mceRemoveControl', false, $textarea.prop('id'));
        $footer.after($editor);
        $form.attr('action', $this.attr('href'));
        tinymce.execCommand('mceAddControl', false, $textarea.prop('id'));
        //tinymce.get($textarea.prop('id')).onKeyUp();
        $editor.removeClass('visuallyhidden');
        tinymce.execCommand('mceFocus', false, $textarea.prop('id'));
    }
});
$(document.body).delegate('.forum-topic-view .topic-comment-replycancel', 'click.forum2-view', function (event) {
    var $this = $(this)
      , $li = $this.closest('li')
      , $container = $this.closest('.forum-topic-view');

    event.preventDefault();

    $li.removeClass('topic-comment-replyeditor-active');
    $.ui.errorbox.clear($container.find('.topic-comment-replyeditor'));
    $container.find('.topic-comment-replyeditor')
        .addClass('visuallyhidden')
        .find('input[type="text"], textarea').val('');
});
// Answer link & label
$(document.body).delegate('.forum-topic-view .topic-footer .topic-reply, .forum-topic-view label.for-tinymce', 'click.forum2-view', function (event) {
    var $textarea;

    if ($(this).hasClass('topic-reply')) {
        $textarea = $(this).closest('.forum-topic-view')
            .find('.topic-replyeditor:first textarea:first');
    } else {
        $textarea = $(this).closest('.topic-replyeditor, .topic-comment-replyeditor')
            .find('textarea:first');
    }
    if ($textarea.length) {
        tinymce.execCommand('mceFocus', false, $textarea.prop('id'));
    }
});
$(document.body).delegate('.forum-topic-view .topic-stats a.active', 'click.forum2-view', function (event) {
    event.preventDefault();
});
$(document.body).delegate('.forum-topic-view .topic-comment-replyeditor form, .forum-topic-view .topic-replyeditor form', 'submit.forum2-view', function (event) {
    var $form = $(this)
      , action = $form.attr('action')
      , $editor = $form.closest('.topic-comment-replyeditor, .topic-replyeditor')
      , $forumRoot = $form.closest('.forum-topic-view')
      , $container
      , $children
      , $overlay
      , xhr
      , data
      , editorHeight
      , type;

    event.preventDefault();

    type = /^(GET|POST)$/i.test($form.attr('method') || '') ?
        $form.attr('method').toUpperCase()
        : 'GET';
    data = $form.serializeArray();

    $container = $editor.hasClass('topic-comment-replyeditor')
        ? $editor.closest('li')
        : $editor.closest('.forum-topic-view');
    $children = $container.children('ul.topic-comments, ul.topic-comment-childs');

    editorHeight = $editor.outerHeight();
    $editor.find('input[type="text"], textarea').val('');

    $overlay = $container.children('.topic-reply-posting-overlay');
    if (!$overlay.length) {
        $overlay = $('<div class="topic-reply-posting-overlay ajax-spinner-local">')
            .insertAfter($editor);
    }
    $overlay.css('height', editorHeight);

    $container.removeClass('topic-comment-replyeditor-active')
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
        if (!$editor.hasClass('topic-comment-replyeditor') || xhr.status == 400) {
            $editor.removeClass('visuallyhidden');
        }
        if ($editor.hasClass('topic-comment-replyeditor') && xhr.status == 400) {
            $container.addClass('topic-comment-replyeditor-active');
        }
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
                elsHelpers.alert(<?= Zend_Json::encode(_("Не удалось отправить сообщение, повторите попытку позже.")) ?>, <?= Zend_Json::encode(_("Ошибка при отправке сообщения")) ?>);
            }
        }
    }).done(function (data) {
        var $data = $(data)
          , $ul;

        if ($data.is('ul')) {
            $data = $data.children('li:first');
        }
        if (!$data.is('li')) {
            throw new Error('Custom error: Invalid reply markup');
        }

        $ul = $container.children('ul.topic-comments, ul.topic-comment-childs');
        if (!$ul.length) {
            $ul = $('<ul />');
            if ($editor.hasClass('topic-comment-replyeditor')) {
                $ul.addClass('topic-comment-childs');
                $ul.appendTo($container);
            } else {
                $ul.addClass('topic-comments');
                $ul.insertBefore($editor);
            }
        }

        if ($editor.hasClass('topic-comment-replyeditor')) {
            $data.prependTo($ul);
        } else {
            $data.appendTo($ul);
        }
        if ($forumRoot.data('highlightedUserId') != null) {
            $data.addClass('topic-comment-highlighted');
        }
        $('.error-box').remove();
    });
    $container.data('replyXhr', xhr);
});
$(document.body).delegate('.forum-topic-view .topic-comment-footer select, .forum-topic-view .topic-footer select', 'change.forum2-view', function (event) {
    $(this).closest('form').submit();
});
$(document.body).delegate('.forum-topic-view .topic-comment-footer form, .forum-topic-view .topic-footer form', 'submit.forum2-view', function (event) {
    var $form = $(this)
      , action = $form.attr('action')
      , $container = $form.closest('li')
      , xhr
      , data
      , type;

    event.preventDefault();

    type = /^(GET|POST)$/i.test($form.attr('method') || '') ?
        $form.attr('method').toUpperCase()
        : 'GET';
    data = $form.serializeArray();

    if ($container.data('scoreXhr') != null) {
        $container.data('scoreXhr').abort();
    }
    xhr = $.ajax(action, {
        //global: false,
        //dataType: 'html',
        type: type,
        data: data
    }).always(function () {
    }).fail(function (xhr, textStatus) {
        if (textStatus != "abort") {}
    }).done(function (data) {
    });
    $container.data('scoreXhr', xhr);
});
$(document.body).delegate('.forum-topic-view .topic-comment-author-username, .forum-topic-view .topic-author-username', 'click.forum2-view', function (event) {
    var $user = $(this)
      , userId = $user.data('userId')
      , $forumRoot = $user.closest('.forum-topic-view');

    event.preventDefault();

    if (userId != null) {
        $forumRoot.find('.topic-comments li.topic-comment-highlighted')
            .removeClass('topic-comment-highlighted');
        if (userId != $forumRoot.data('highlightedUserId')) {
            $forumRoot.find('.topic-comments li.user-'+ userId +'-x')
                .addClass('topic-comment-highlighted');
            $forumRoot.data('highlightedUserId', userId);
        } else {
            $forumRoot.data('highlightedUserId', null);
        }
    }
});
$(document.body).delegate('.forum-topic-view .topic-comment-delete', 'click.forum2-view', function (event) {
    var $button = $(this);

    event.preventDefault();
    if (!$button.hasClass('ui-state-disabled')) {
        $button.addClass('ui-state-disabled');
        elsHelpers.confirm(<?= Zend_Json::encode(_("Вы уверены, что хотите удалить сообщение?")) ?>, <?= Zend_Json::encode(_("Удаление сообщения")) ?>, {
            ok:     <?= Zend_Json::encode(_("удалить")) ?>,
            cancel: <?= Zend_Json::encode(_("оставить")) ?>
        }).fail(function () {
            $button.removeClass('ui-state-disabled');
        }).done(function () {
            $.ajax($button.attr('href'), {
                global: false,
                dataType: 'html'
            }).done(function (text) {
                var $li = $button.closest('li')
                  , $data = $(text);

                if ($data.is('ul')) {
                    $data = $data.children('li:first');
                }

                if ($data.is('li')) {
                    $li.children().not('ul.topic-comment-childs, .topic-comment-replyeditor').remove();
                    $li.addClass('topic-comment-deleted')
                        .prepend($data.children());
                } else {
                    $.ui.errorbox.clear($li);
                    $('<div>').appendTo($li)
                        .text(<?= Zend_Json::encode(_('Ошибка при удалении сообщения')) ?>).errorbox({ level: 'error' });
                }
            }).fail(function () {
                $button.removeClass('ui-state-disabled');
            });
        });
    }
});

$(document.body).delegate('.forum-topic-view .topic-comment-footer select[name="rating"]', 'change.forum2-view', function (event) {
    var $select = $(this)
      , val = jQuery.trim($select.val() || '')
      , $li = $select.closest('li');
    
    if (val) {
        $li.find('> .topic-comment-header .topic-comment-score-value').text(
            $($select.prop('options')[$select.prop('selectedIndex')]).text()
        );
        $li.addClass('topic-comment-has-score');
    } else {
        $li.removeClass('topic-comment-has-score');
    }
});

$(document.body).delegate('.forum-topic-view .topic-delete', 'click.forum2-view', function (event) {
    var $target = $(this);

    event.preventDefault();

    elsHelpers.confirm(<?= Zend_Json::encode(_('Вы действительно хотите удалить тему?')) ?>, <?= Zend_Json::encode(_('Удаление темы')) ?>).done(function () {
        $.get($target.attr('href')).always(function () {
            $.ui.errorbox.clear($target.parent());
        }).done(function () {
            $('<div>').insertAfter($target)
                .text(<?= Zend_Json::encode(_('Тема была удалена')) ?>).errorbox();
            $('<a>link</a>')
                .insertAfter($target)
                .addClass('visuallyhidden')
                .attr('onclick', 'window.location.href = <?= Zend_Json::encode($this->forum->url()) ?>')
                .trigger('mouseover')
                .trigger('click');
        }).fail(function () {
            $('<div>').insertAfter($target)
                .text(<?= Zend_Json::encode(_('Ошибка при удалении темы')) ?>).errorbox();
        });
    });
})

// Enter key handler
function comment_editor_keyup_handler (editor, e) {
    return;
    if (e.keyCode == 13) { // Enter
        var node = editor.selection.getNode();

        // TODO проверить, что это именно цитата поста (т.е. должен быть атрибут указывающий на автора)
        if (editor.formatter.match('blockquote', null, node)) {
            // just break citation block
            if (node.tagName.toUpperCase() == 'P') {
                editor.execCommand('mceBlockQuote');
                console.log('hmmm');
            } else {
                var html = $(node).html()
                  , prev = $(node).prev().get(0);

                //$(node).html(html.replace(/^<br>/, ''));
                if (prev) {
                    var p = editor.dom.create('p');
                    editor.dom.insertAfter(p, prev);
                    editor.selection.select(p);
                    editor.execCommand('mceRepaint');
                }
            }
        }
    }
}
<?php $this->inlineScript()->captureEnd(); ?>