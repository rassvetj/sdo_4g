<?php
$this->headScript()
     ->appendFile( $this->baseUrl('js/lib/jquery/jquery.cookie.js') );
$this->headLink()->appendStylesheet( $this->baseUrl('css/content-modules/extended-page.css') );
?>

<div <?php if ($this->courseContent === true): ?> class="extended-page-has-course-content"<?php endif; ?>>

<?php $this->placeholder('columns')->captureStart('SET'); ?>
<?php if($this->topContent):?>
<?php echo $this->topContent; ?>
<?php endif;?>
<div class="content-container<?php if ($this->courseContent === true): ?> content-container-expandable<?php endif; ?>">
    <div class="content-here">
        <?php echo $this->workspace?>
    </div>
    <?php if ($this->courseContent === true): ?>
    <a class="content-size" title="<?php echo _("Развернуть на весь экран"); ?>" data-titles="<?php echo $this->escape(Zend_Json::encode(array(
            "expand" => _("Развернуть на весь экран"),
            "collapse" => _("Свернуть")
        ))) ?>">
        <span class="content-size-expand" title="<?php echo _("Развернуть на весь экран"); ?>"><?php echo _("Развернуть на весь экран"); ?></span>
        <span class="content-size-collapse" title="<?php echo _("Свернуть"); ?>"><?php echo _("Свернуть"); ?></span>
        <span class="ui-icon"></span>
    </a>
    <?php endif; ?>
</div>
<?php
if (count($this->getTabLinks()) && $this->_withoutActivities === false):?>
    <?php $this->headScript()->appendFile($this->baseUrl('js/lib/jquery/jquery.form.js')); ?>
    <?php foreach($this->getTabLinks() as $title => $url):?>
        <?php $this->tabContainer()->addPane('tabs', $title, '', array('contentUrl' => $url));?>
    <?php endforeach;?>
    <? echo $this->tabContainer()->tabContainer(
        "tabs",
        array(
            "cache" => true,
            "spinner" => false,
            "selected" => -1
        ),
        array("class" => "extended-page-tabs ui-local-error-box"));
    ?>
<?php endif;?>
<?php $this->placeholder('columns')->captureEnd(); ?>

<?php if (!$this->withoutContextMenu): ?>
<?php $this->placeholder('columns')->captureStart(); ?>
<?php
    $this->accordionContainer()
         ->setElementHtmlTemplate('<h3 class="ui-accordion-header"><a href="#"><span class="header">%s</span></a></h3><div class="ui-accordion-content"><div class="ui-accordion-content-wrapper">%s</div></div>');
/*    if ($this->getContextNavigation()) {
    	$navigation = $this->navigation()->menu()->renderMenu($this->getContextNavigation(), array('maxDepth' => 1));
        $this->accordionContainer()
             ->addPane("page-context-accordion", $this->getPaneName(),
                 strip_tags($navigation, '<ul><li><a>')
             );
    }*/
   
    if (count($this->getInfoBlocks())) {
        foreach($this->getInfoBlocks() as $block) {
            $blockName = $block['name'];
            $blockOptions = $block['options'];
            if($this->_withoutActivities === true && $blockName == 'ActivitiesBlock'){
                continue;
            }
            $blockContent = $this->{$blockName}(null, null, $blockOptions);
            if (null !== $blockContent) {
                $this->accordionContainer()
                    ->addPane("page-context-accordion", (isset($blockOptions['title']) ? $blockOptions['title'] : _('Контекстный блок')), $blockContent);
            }
        }
    }

    echo $this->accordionContainer()
              ->accordionContainer("page-context-accordion", array('autoHeight' => FALSE), array("class" => "page-context-accordion"));
?>
<?php $this->placeholder('columns')->captureEnd(); ?>
<?php endif; ?>
<?php echo $this->partial('_columns.tpl', 'default', array(
    'columns' => $this->placeholder('columns')->getArrayCopy(),
    'classes' => 'extended-page'.($this->withoutContextMenu ? ' extended-page-narrow ' : ''),
    'type' => $this->withoutContextMenu ? 'pc' : 'px'
)); ?>

</div>

<?php $this->inlineScript()->captureStart()?>
(function ($, doc) {

$(doc).bind('tabscreate', function (event) {
    var $target = $(event.target);
    if ($target.is('#tabs')) {
        $('> .ui-tabs-nav', $target)
            .after('<div class="error-box">')
            .after('<div class="ajax-spinner-local">')
            .nextAll('.ajax-spinner-local:first')
            .hide();
	}
});
$(doc).bind('accordioncreate', function (event) {
	var $this = $(event.target)
	  , $panels
	  , $active;
	if ($this.is('#page-context-accordion')) {
		$panels = $this.children('.ui-accordion-content');
		$active = $panels.find('li.active:first').closest('.ui-accordion-content');
		if ($active.length) {
			$this.accordion('activate', $panels.index($active));
		} else {
			<?php if( Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR, HM_Role_RoleModelAbstract::ROLE_TEACHER)) ):?>
				$this.accordion('activate', 1);
			<?php endif;?>
		}
	}
});
function getCurrentTabPanel () {
	return $('#tabs > div.ui-tabs-panel:eq(' + ($('#tabs').tabs('option', 'selected') || 0) + ')');
}

function functionStringToOnClickAttribute (func) {
    var fstr = $.trim((func || '').toString() || '');
    return  $.trim(
        $.trim(
            fstr.replace(/^function\s*(onclick|anonymous|onmousedown|onmouseup)?\s*\([^)]*\)/i, '' )
        ).replace(/^{\s*(.*)\s*}$/m, '$1')
    );
}
function totalProhibiter (event) {
    event.stopImmediatePropagation();
    event.preventDefault();
    return false;
}
var commonAjaxOptionsForTabs = {
    dataType: 'html',
    global: false,
    beforeSend: function () {
        var ctp = getCurrentTabPanel()
            , ctpHeight = ctp.height()
            , ajaxSpinner = ctp.prevAll('.ajax-spinner-local:first');

        // TODO - absolute value, beeaaad
        if (40 > ctpHeight) {
            ctp.css({ height: 40 });
        }

        _.defer(function () {
            ajaxSpinner.show()
                .css({ width: ctp.outerWidth(), height: ctp.outerHeight() })
                .position({of: ctp});
        });

        ctp.bind('click mousedown mouseup keydown keyup keypress', totalProhibiter);
    },
    complete: function () {
        var ctp = getCurrentTabPanel()
            , ajaxSpinner = ctp.prevAll('.ajax-spinner-local:first');

        ctp
            .css('height', '')
            .unbind('click mousedown mouseup keydown keyup keypress', totalProhibiter);

        ajaxSpinner.hide();
    },
    success: function (msg) {
        getCurrentTabPanel().html(msg);
    },
    error: function () {
        // TODO error reaction!!!!!!!!!
    }
};

$(document).bind('tabscreate', function (event) {
    if ($(event.target).is('#tabs')) {
        $(event.target)
            .tabs('option', 'ajaxOptions', _.extend({}, commonAjaxOptionsForTabs, {
                success: function () {}
            }))
            // TODO: use storage to retrieve selected tab
            .tabs('select', 0);
    }
});

window.__Unmanaged = {
    navigateInCurrentTab: function (url) {
        var context = null;
        if ($(this).is('button, input[type="button"], input[type="submit"]')) {
            this.disabled = true;
            context = this;
        }
        $.ajax(_.extend({}, commonAjaxOptionsForTabs, {
            url: url,
            error: function () {
                if (context) { context.disabled = false; }
            }
        }));
    }
};

// navigate to tabs with target _self inside current tab
$(document).on('click', "a", function (event) {
    var $target = $(this)
      , origin = $(this).closest('*[data-origin]').attr('data-origin')
      , target
      , url;

    if (!$(origin ? '#'+origin : $target).closest('#tabs').length) {
		return;
    }
    if (event.isDefaultPrevented()) {
		return;
    }

    target = $.trim($target.attr('target') || '')
    url = $.trim($target.attr('href') || '')

    if (url && /^#.*?$/.test(url)) {
        return;
    }

    // TODO допилить список расширений файлов

    if (/(\.\w+)$/i.test(url)) {
        event.preventDefault();
        window.open(url);
        return;
    }

    if (!url || !target || target === '_self') {
        event.preventDefault();
    }
    if (!url || /^javascript:/i.test(url)) {
        return;
    }

    if (!target || target === '_self') {
        $.ajax(_.extend({}, commonAjaxOptionsForTabs, { url: url }));
    }
});

$(document)
    // Preprocess onclick events in buttons to eliminate (document|window).location.href :)
    .on('focus mouseover mousedown', '#tabs form button, #tabs form input[type="submit"]', function (event) {
        var $target = $(this)
            , re = /(document|window)\.location\.href\s?=\s?("[^"']*"|'[^"']*'|[^;\n]*)/ig
            , onclick
            , matches;

        if ($target.data('onclick-processed') === true) {
            return;
        }
        $target.data('onclick-processed', true);

        onclick = functionStringToOnClickAttribute(this.onclick);
        if (onclick) {
            matches = onclick.match(re);
            $target.data('can-navigate-outside', !!matches && !!matches.length);
            _.each(matches || [], function (match) {
                var url = match.replace(/^(document|window)\.location\.href\s?=\s?/i, '');
                onclick = onclick.replace(match, "__Unmanaged.navigateInCurrentTab.call(this, "+ url +")");
            });
            this.onclick = new Function(onclick);
        }
    })
    // don't trigger onSubmit with <button> or <input type="submit"> if it
    // can navigate outside with document.location.href
    .on('click', '#tabs form button, #tabs form input[type="submit"]', function (event) {
        if ($(this).is('button') || $(this).data('can-navigate-outside') === true) {
            event.preventDefault();
        }
    });

$(document).on('submit', '#tabs form', function (event) {
    if (event.isDefaultPrevented()) {
        return;
    }
    event.preventDefault();
    $(this).ajaxSubmit(commonAjaxOptionsForTabs);
});

})(this.jQuery, document);
<?php $this->inlineScript()->captureEnd()?>