(function ($, window, doc, undef) {

var cloxItems = []
    , $doc = $(doc);

function updateCloxItems () {
	var dt = new Date()
	    , min = dt.getMinutes()
	    , hrs = dt.getHours()
	    , str;
	str = (hrs < 10 ? '0' + hrs : hrs) + ':' + (min < 10 ? '0' + min : min);
	_.each(cloxItems, function (item) {
		try {
			item.innerHTML = str;
		} catch (error) {}
	});
}

$.fn.clox = function () {
	cloxItems = cloxItems.concat(this.get());
	updateCloxItems();
	if (!$.fn.clox.interval) {
		$.fn.clox.interval = setInterval(updateCloxItems, 11000);
	}
};

function supportsVml () {
	if (typeof supportsVml.supported == "undefined") {
		var a = doc.body.appendChild(doc.createElement('div'));
		a.innerHTML = '<g_vml_:shape id="vml_flag1" adj="1" />';
		var b = a.firstChild;
		b.style.behavior = "url(#default#VML)";
		supportsVml.supported = b ? typeof b.adj == "object": true;
		a.parentNode.removeChild(a);
	}
	return supportsVml.supported;
}

function addNamespacesVML (doc) {
	// create xmlns
	if (!doc.namespaces['g_vml_']) {
		doc.namespaces.add('g_vml_', 'urn:schemas-microsoft-com:vml',
			'#default#VML');
	}
	if (!doc.namespaces['g_o_']) {
		doc.namespaces.add('g_o_', 'urn:schemas-microsoft-com:office:office',
			'#default#VML');
	}
}

try {
	if (doc.namespaces) {
		addNamespacesVML(doc);
	}
} catch (error) {}

$(function () {
	// main menu
	(function () {
		var to
		    , postponeTimeout
		    , lastMouseleaveTime = 0
		    , effectShowDuration = 400
		    , effectHideDuration = 300
		    , postponeTolerance = 200
		    , hideDelay = 500
		    , animationSpeed = 150;
		function getItems ($tab) {
			return _.reduce($tab.siblings('li').andSelf().get(), function (memo, item) {
				if ($(item).data('submenu-id')) {
					memo.push({ li: item, href: '#' + $(item).data('submenu-id') });
				} else {
					memo.push({ li: item, href: '' });
				}

				return memo;
			}, []);
		}
		function updateMenu (tabs, $tab, $panel, $selectedPanel) {
			var $tabs = $(_(tabs).chain().pluck('href').without('').value().join(', '));
			$tabs
				.filter('.is-expading')
				.removeClass('is-expading')
				.each(function () {
					var $panel = $(this)
					   , panelOuterHeight = $panel.outerHeight()
					   , animatedHeight = $panel.is(':animated') ? parseInt($panel.css('top'), 10) : 0;

					animatedHeight += panelOuterHeight;
					// should not be used
					animatedHeight = animatedHeight > panelOuterHeight ? panelOuterHeight : animatedHeight;
					

					$panel.stop().effect('slide', {
						distance: animatedHeight, mode: 'hide', direction: 'up'
					}, parseInt(animatedHeight / animationSpeed * effectHideDuration));
				});
			$(_(tabs).pluck('li')).removeClass('ui-state-active');

			// add active state to tab
			if ($tab) {
				$tab.addClass('ui-state-active');
			}

			// show panel
			if ($panel || $selectedPanel) {
				if ($panel.length) {
					$panel.removeClass('ui-helper-hidden');
				} else {
					$selectedPanel.removeClass('ui-helper-hidden');
				}
			}
		}
		function mainMenuEventCoreHandler (event) {
			var $this = $(this)
			    , isTab = $this.is('li')
			    , id
			    , $tab
			    , items
			    , hasId;

			id = isTab
				? '#' + ($this.data('submenu-id') || '')
				: '#' + ($this.attr('id') || '');
			$tab = isTab
				? $this
				: $('#main .tab-bar ul > li[data-submenu-id="'+ id.substr(1) +'"]');
			hasId = (id !== '#' && /^#/.test(id));

			if (to) {
				clearTimeout(to);
				to = null;
			}

			if (event.type == 'mouseenter') {
				items = _.reduce(getItems($tab), function (memo, item) {
					if (item.href !== id) {
						memo.push(item);
					}
					return memo;
				}, []);
				updateMenu(items, $tab, hasId ? $(id) : null);
			}

			if (event.type == 'mouseleave') {
				to = setTimeout(function () {
					updateMenu(getItems($tab));
				}, hideDelay);
			}
		}
		function mainMenuEventUIHandler (event) {
			var $this = $(this)
			    , id
			    , $panel
			    , $closest
			    , isAnimated
			    , isExpanding
			    , offsetLeft
			    , $offsetParent
			    , isRight
			    , css;
			
			id = '#' + ($this.data('submenu-id') || '');
			
			if (id === '#' || !/^#/.test(id)) { return; }
			
			$panel = $(id);
			$closest = $panel.closest('.ui-effects-wrapper');
			
			if (!$closest.length && !$panel.parent().is('body')) {
				$panel.hide()
					.appendTo('body')
					.show();
			}
			
			isAnimated = $panel.is(':animated');
			isExpanding = $panel.is('.is-expading');
			$offsetParent = $this.closest('.tab-bar');
			offsetLeft = $this.find('span').offset().left ;
			isRight = (offsetLeft + $panel.outerWidth()) >= $(window).width();
			
			css = {
				right: isRight ? ( $(window).width() - offsetLeft - $this.find('span').outerWidth() ) : '',
				left: isRight ? '' : offsetLeft,
				top: $offsetParent.offset().top + $offsetParent.outerHeight() - 1
			};
			
			if ($.fn.bgiframe && !$panel.data('bgiframed')) {
				$panel.data('bgiframed', true);
				$panel.bgiframe();
			}
			
			$panel
				.removeClass('submenu-left submenu-right')
				.addClass('is-expading')
				.addClass(isRight ? 'submenu-right' : 'submenu-left');
			
			if ($panel.outerWidth() <= $this.find('span').outerWidth()) {
				$panel.css('width', $this.find('span').outerWidth());
			} else {
				$panel.css('width', $panel.width());
			}

			if ($closest.length) {
				$closest
					.css(css)
					.appendTo('body');
			} else {
				if (!isAnimated) {
					$panel.css(css);
				}
				$panel.appendTo('body');
			}
			if (isAnimated && !isExpanding) {
				$panel.stop();
			}

			if (!isExpanding) {
				(function (css) {
					var panelOuterHeight = $panel.outerHeight()
					    , animatedHeight = isAnimated ? parseInt($panel.css('top'), 10) * -1 : panelOuterHeight;
					// should not be used
					animatedHeight = animatedHeight > panelOuterHeight ? panelOuterHeight : animatedHeight;
					$panel.effect('slide', {
						distance: animatedHeight, mode: 'show', direction: 'up',
						complete: function () {
							$(this).css(css).css('position', 'absolute');
						}
					}, parseInt(animatedHeight / animationSpeed * effectShowDuration));
				})(css);
			}
		}
		function mainMenuEventHandler () {
			mainMenuEventCoreHandler.apply(this, arguments);
			mainMenuEventUIHandler.apply(this, arguments);
		}
		// postpone sending mouseenter event to tabs if mouseleave from another happened before
		$('body').delegate('.tab-bar ul > li', 'mouseenter mouseleave', function (event) {
			var now = (new Date()).getTime();
			if (event.type == 'mouseenter') {
				if ((now - lastMouseleaveTime) > postponeTolerance) {
					$(this).data('mouseenterhappened', true);
					mainMenuEventHandler.apply(this, arguments);
				} else {
					postponeTimeout = _.delay((function (_this, event, args) { return function () {
						$(_this).data('mouseenterhappened', true);
						mainMenuEventHandler.apply(_this, [event].concat(args));
					}; })(this, _.extend({}, event), _.rest(arguments)), postponeTolerance);
				}
			} else {
				lastMouseleaveTime = now;
				if (postponeTimeout) {
					clearTimeout(postponeTimeout);
				}
				postponeTimeout = 0;
				if ($(this).data('mouseenterhappened') === true) {
					$(this).data('mouseenterhappened', false);
					mainMenuEventCoreHandler.apply(this, arguments);
				}
			}
		});
		$('body').delegate('.submenu', 'mouseenter mouseleave', mainMenuEventCoreHandler);
		$('#main .tab-bar ul > li.ui-tabs-selected:first')
			.siblings('li.ui-tabs-selected')
			.removeClass('ui-tabs-selected');
	})();

	// cloxzz
	$('#main .tab-bar .clocks')
		.remove();
	(function (formId) {
		var $form = $('#' + formId)
		  , $loginLink = $('#header .login');

		$(document).bind('click', function (event) {
			if (!$(event.target).closest('#' + formId).length && !$(event.target).closest('#header .login').length) {
				$form.hide();
			}
		});

		$loginLink.click(function (event) {
			if (!$form.length) {
				$form = $('<div><div class="form-content"/><div class="ajax-spinner-local"/></div>')
					.attr('id', formId)
					.addClass('login-inline-form')
					.hide()
					.appendTo('body')
			}
			$form.toggle();
			if ($form.is(':visible')) {
				$(window).trigger('resize.login-inline-form');
				if (!$form.data('has-form')) {
					$form.addClass('ui-state-loading')
						.removeClass('ui-state-error')
						.find('.form-content')
						.html('')
					$.ajax(elsHelpers.url('root') + 'infoblock/index/view/role/guest/mode/view/name/Authorization', { global: false, dataType: 'html' })
						.done(function (data) {
							var $data = $('<div>').append(data)
							  , docLinks;

							docLinks = _.reduce(_.toArray(doc.getElementsByTagName('link')), function (memo, link) {
								var href = link.getAttribute('href');
								if (href) { memo[href] = true; }
								return memo;
							}, {});
							_.each($data.find('link').get(), function (link) {
								var href = link.getAttribute('href');
								if (href && docLinks[href] === true) {
									link.parentNode.removeChild(link);
								}
							});

							$form.find('.form-content').empty().append($data.children());
							$form.removeClass('ui-state-loading')
								.data('has-form', true);
						}).fail(function () {
							$form.removeClass('ui-state-loading')
								.addClass('ui-state-error');
						});
				}
			}
			event.preventDefault();
		});
	})(_.uniqueId('login-inline-form'));

	// role select
	$doc.delegate('.navigation-select, .language-select', 'change', function (event) {
		doc.location.href = event.target.value;
	});

	$('.language-select')
		.appendTo($('.language-select').parent());
	$('.navigation-select')
		.insertBefore($('.navigation-select').parent().children('.name'));

	$('select.navigation-select')
		.selectmenu({
			style: 'dropdown',
			menuWidth: 170,
			width: 170,
			positionOptions: {
				collision: 'none'
			}
		});
	$('select.language-select')
		.selectmenu({
			style: 'dropdown',
			format: function (text) {
				return (text || '').replace(/^([a-z]{2,3})/, '<acronym>$1</acronym>');
			},
			maxHeight: 200,
			width: 135,
			menuWidth: 135
		});
		
	// confirmable links
	$(document).click(function (event, confirmed) {
		var $target = $(event.target)
		  , $dialog
		  , confirmData = $target.data('confirm')
		  , dialogButtons = {};
		if ($target.is('a') && confirmData && !confirmed) {
			event.preventDefault();
			$('.modal-confirm-dialog:ui-dialog').dialog('destroy');
			$('.modal-confirm-dialog').remove();
			$dialog = $('<div class="modal-confirm-dialog" title="' + confirmData['title'] + '">' + confirmData['text'] + '</div>');
			dialogButtons[confirmData['ok']] = function () {
				$(this).dialog("close");
				document.location.href = $target.attr('href');
			}
			dialogButtons[confirmData['cancel']] = function () {
				$(this).dialog("close");
			}
			$dialog.appendTo('body').dialog({
				resizable: false,
				height: 140,
				modal: true,
				buttons: dialogButtons
			});
		}
	});

	// extended page play button overlay
	(function () {
		var $contentHere = $('.content-container > .content-here')
		  , $coursesList = $contentHere.find('.subject-course')
		  , $firstA;

		if (!$coursesList.length) {
			return;
		}
		if ($coursesList.length == 1) {
			$firstA = $coursesList.find('a:first');
			$('<div><div class="course-play-overlay"></div><div class="course-play-overlay-wrapper"></div></div>')
				.children('div.course-play-overlay-wrapper')
				.append($firstA.clone(true).addClass('play-button'))
				.end()
				.appendTo($contentHere.find('.course-iframe-box').length ? $contentHere.find('.course-iframe-box') : $contentHere);
		}
	})();

	// extended page expand/collapse
	(function () {
		var scroll
		  , $contentContainers
		  , $extendedPagesWithAccordion;

		$('.content-container.content-container-expandable').each(function () {
			var $this = $(this)
			    , css
			    , $backupBox;
			css = {
				width: $this.width(),
				height: $this.height()
			};
			$backupBox = $($this.get(0).cloneNode(false))
				.attr('id', _.uniqueId('content-container-backup-'))
				.css('height', css.height);
			$this
				.data('backup-id', $backupBox.attr('id'))
				.css(_.extend(css, $this.offset(), {
					position: 'absolute'
				}))
				.after($backupBox)
				.appendTo('body')
				.addClass('content-container-with-course-presentation');
		});
		$contentContainers = $('.content-container-with-course-presentation');

		$extendedPagesWithAccordion = $('div.hgll-col2').closest('div.extended-page');
		$extendedPagesWithAccordion.each(function (idx) {
			$('#main').append(
				$('<div class="accordion-expander container-ear">')
					.css('top', $(this).offset().top - $('#main').offset().top)
					.data('extended-page-idx', idx)
			);
		});
		$(doc).delegate('.accordion-expander', 'click', function (event, immediate) {
			var $target = $(event.target)
			  , idx = $target.data('extended-page-idx')
			  , $column1 = $extendedPagesWithAccordion.eq(idx).find('div.hgll-col1:first')
			  , $column2 = $extendedPagesWithAccordion.eq(idx).find('div.hgll-col2:first')
			  , $columnsWrapper = $extendedPagesWithAccordion.eq(idx).find('div.hgll-colwrap-inner:first')
			  , animateStyle
			  , duration = immediate ? 0 : 'fast';

			if ($column1.is(':animated') || $column2.is(':animated')) {
				return;
			}

			if (!$target.data('animate-style')) {
				$target.data('animate-style', {
					'column1': {
						'from': {
							'margin-right': parseInt($column1.css('margin-left'), 10)
							              + parseInt($column1.css('margin-right'), 10),
							'margin-left': 0
						},
						'to': {
							'margin-right': 0,
							'margin-left': 0
						}
					},
					'column2': {
						'from': {
							'left': 0
						},
						'to': {
							'left': parseInt($column2.css('left'), 10)
						}
					}
				});
			}
			animateStyle = $target.data('animate-style');

			if ($target.data('hiding')) {
				$target.data('hiding', false);
				elsHelpers.store.init().then(function () {
					this.set('accordion-column-state', 'expanded');
				});
				$columnsWrapper.css('margin-left', 0);
				$extendedPagesWithAccordion.eq(idx)
					.removeClass('extended-page-narrow hgll-pc-1-column');

				$column1
					.css(animateStyle.column1.to)
					.animate(animateStyle.column1.from, duration);

				$column2
					.css(animateStyle.column2.to)
					.animate(animateStyle.column2.from, {
						step: unthrottledResizeFunction,
						duration: duration,
						complete: function () {
							_.invoke([$column1, $column2, $columnsWrapper], 'attr', 'style', '');
							$target.removeClass('accordion-expander-collapsed');
							unthrottledResizeFunction();
							$(window).trigger('resize');
						}
					});
			} else {
				$target.data('hiding', true);
				elsHelpers.store.init().then(function () {
					this.set('accordion-column-state', 'collapsed');
				});
				$columnsWrapper.css('margin-left', 0);

				$column1
					.css(animateStyle.column1.from)
					.animate(animateStyle.column1.to, duration);

				$column2
					.css(animateStyle.column2.from)
					.animate(animateStyle.column2.to, {
						step: unthrottledResizeFunction,
						duration: duration,
						complete: function () {
							_.invoke([$column1, $column2, $columnsWrapper], 'attr', 'style', '');
							$extendedPagesWithAccordion.eq(idx)
								.addClass('extended-page-narrow hgll-pc-1-column');
							$target.addClass('accordion-expander-collapsed');
							unthrottledResizeFunction();
							$(window).trigger('resize');
						}
					});
			}
		});
		
		elsHelpers.store.init().then(function () {
			if (this.get('accordion-column-state') == 'collapsed') {
				$('.accordion-expander').trigger('click', ['immediate']);
			}
		});

		function unthrottledResizeFunction () {
			$contentContainers.each(function () {
				var $this = $(this)
				  , $backupBox;
				if ($this.is('.content-container-expanding') || $this.is('.content-container-expanded')) {
					return;
				}
				$backupBox = $('#' + $this.data('backup-id'));
				$('.course-iframe-box', this).css('height', '');
				$this.css(_.extend({
					width: $backupBox.width(),
					height: $backupBox.height()
				}, $backupBox.offset()));
			});
		}

		function fsResizeFunction () {
			$contentContainers.each(function () {
				var $this = $(this)
				  , $iframeBox
				  , height;
				if (!$this.is('.content-container-expanded')) { return; }
				$iframeBox = $('.course-iframe-box', this);
				if (!$iframeBox.length) { return; }
				height = $this.height();
				$iframeBox.css('height', height - ($iframeBox.offset().top - $this.offset().top));
			});
		}

		if ($contentContainers.length) {
			jQuery.resize.throttleWindow = false;
			$([doc, window])
				.bind('resize.content-containers', _.throttle(unthrottledResizeFunction, 100))
				.bind('resize.content-containers-iframe-box', _.throttle(fsResizeFunction, 100));
			$(window).bind('scroll load', function () {
				$contentContainers.each(function () {
					var $this = $(this);
					if (!$this.is('.content-container-expanded')) { return; }
					$this.css('top', $(window).scrollTop());
				});
			});
			$(window).resize();
		}

		function updateContentContainerState ($contentContainer, expand) {
			/*var $contentSizeButton = $contentContainer.find('.content-size:first');*/

			if (!expand) {
				$(doc.documentElement).removeClass('no-overflow');
				_.defer(function () {
					$(doc.documentElement).scrollTop(scroll.top);
					$(doc.documentElement).scrollLeft(scroll.left);
				});
				$contentContainer
					.removeClass('content-container-expanded');
				unthrottledResizeFunction();
			} else {
				scroll = {
					top: $(window).scrollTop(),
					left: $(window).scrollLeft()
				};
				$contentContainer
					.animate(_.extend({
						width: $(window).width(),
						height: $(window).height()
					}, scroll), 1, function () {
						$(doc.documentElement).addClass('no-overflow');
						$(this)
							.css({
								width: '',
								height: '',
								top: $(window).scrollTop(),
								left: $(window).scrollLeft()
							})
							.removeClass('content-container-expanding')
							.addClass('content-container-expanded');
						fsResizeFunction();
					})
					.addClass('content-container-expanding');
			}
		}

		scroll = { top: 0, left: 0 };
		$contentContainers.each(function (index) {
			$(this).find('.content-size:first').attr('title', '');
			if (index == 0 && /fullscreen/.test(window.location.hash)) {
				$(this).addClass('content-container-expanded');
			}
			updateContentContainerState($(this), $(this).hasClass('content-container-expanded'));
		});
		$('body').delegate('.content-container .content-size', 'click', function () {
			var $contentContainer = $(this).closest('.content-container');
			updateContentContainerState($contentContainer, !$contentContainer.hasClass('content-container-expanded'));
		});
	})();

	// some fun
	yepnope({
		load: ['/js/lib/jquery/jquery.hotkeys.min.js'],
		complete: function () { _.defer(function () { if (jQuery.hotkeys) {
			// helpfull staff
			/*(function (reattach) {
				$doc.bind('keydown', 'alt+ctrl+shift+w', reattach);
				setInterval(reattach, 5000);
			})(function () {
				// reattach stylesheets
				var links = $('link[rel="stylesheet"]');

				_.each(links.get(), function (link) {
					var href = link.getAttribute('href')
					  , a = document.createElement('a');

					a.setAttribute('href', href);
					a.search = _.map((a.search || '').split('&'), function (piece) {
						if (/^_reload=/.test(piece)) {
							return '_reload=' + (new Date()).getTime();
						} else {
							return piece;
						}
					}).join('&');

					link.setAttribute('href', a.href);
				});
			});*/
			
		
			(function () {
				var i = []
				  , direction = 1
				  , $contentContainers = $('.content-container-with-course-presentation');

				function animateCutie () {
					$contentContainers.each(function (index) {
						var $contentContainer = $(this)
						  , interval;

						i[index] || (i[index] = 0);

						interval = setInterval(function () {
							i[index] = (i[index] + direction * 1) % 8;
							$contentContainer.css('background-position', '' + i[index] + 'px ' + i[index] + 'px');
						}, 50);
						setTimeout(function () {
							clearInterval(interval);
						}, 110 + parseInt(500 * Math.random()));
					});
					setTimeout(function () {
						direction = Math.random() > 0.5 ? 1 : -1;
						animateCutie();
					}, 2000 + parseInt(5000 * Math.random()));
				}
				$doc.one('keydown', null, 'alt+ctrl+shift+q', function () {
					animateCutie();
				});
			})();
			$doc.one('keydown', null, 'alt+ctrl+shift+p', function () {
				yepnope({
					test: Modernizr.canvas && Modernizr.localstorage && Modernizr.audio && !!(Modernizr.audio.ogg || Modernizr.audio.mp3),
					yep: ['theme!/css/fun/pacman.css', 'theme!/js/pacman.js'],
					complete: function () { _.defer(function () { if (window.PACMAN) {
						$('<div id="pacman">')
							.css({
								width: 342,
								height: 450,
								position: 'absolute',
								top: ($(window).height() / 2 - 450/2),
								left: ($(window).width() / 2 - 342/2),
								zIndex: 1000
							})
							.appendTo('body');

						_.delay(function () {
							PACMAN.init($('#pacman').get(0), elsHelpers.url('theme'));
							$doc.one('keydown', 'esc', function () {
								PACMAN.destroy($('#pacman').get(0));
								$('#pacman').remove();
							});
						}, 1);
					} }); }
				});
			});
		} }); }
	});

	$('#container > #footer').prepend('<div class="bb-p"><div class="first"></div><div class="second"></div><div class="third"></div><div class="fourth"></div><div class="fifth"></div></div>');
	
	$('.ui-portlet').each(function () {
		$(this).addClass('ui-portlet-color-' + Math.round(Math.random() * 3 ));
	});

    $(document).on('mouseover focus', 'button, input[type="button"], input[type="submit"]', function () {
		$(this).is(':ui-button') || $(this).button($(this).data('ui-options') || {});
	}).trigger('mouseover');
});

// accordion on extended page
$doc.bind('accordioncreate', function (event) {
	var $target = $(event.target);
	if ($target.is('#page-context-accordion') && $target.find('> .ui-accordion-header > a > .gradient-me').length === 0) {
		// this should update accordion
		$target
			.accordion('option', {
				changestart: function (event, ui) {
					_.defer(function () {
						$(ui.newContent).css('width', '100%');
					});
				},
				change: function (event, ui) {
					$(ui.newContent).css('overflow', 'auto');
				}
			})
			.find('> .ui-accordion-header')
			.children('a')
			.prepend('<div class="gradient-me"><div class="gradient-me-again"><hr /></div></div>')
			.end();

		if (!Modernizr.borderradius && supportsVml()) {
			$target.children('.ui-accordion-header').children('.ui-icon')
				.each(function () {
					if (!Modernizr.borderradius && supportsVml()) {
						this.insertAdjacentHTML('afterBegin', [
							'<g_vml_:oval',
							'		style="width: '+ ($(this).width() - 1) +'px; height: '+ ($(this).height() - 1) +'px; position: absolute; top: 0; left: 0; z-index: -1;"',
							'		filled="t"',
							'		onclick="return false;"',
							'		stroked="f">',
							'	<g_vml_:stroke color="#ffffff" />',
							'	<g_vml_:fill color="#ffffff" />',
							'</g_vml_:oval>',
							'<span class="ui-icon"></span>'
						].join(''));
					}
				});
		}
	}
});

$doc.bind('progressbarcreate', function (event) {
	var $target = $(event.target);
	if ($target.find('.gradient-me').length === 0) {
		$target.find('.ui-progressbar-value').andSelf()
			.prepend('<div class="gradient-me"><div class="gradient-me-again"></div></div>');
	}
});
$doc.bind('dialogcreate', function (event) {
	var $target = $(event.target)
		 , $outerDialog = $target.closest('.ui-dialog');
	if ($outerDialog.length && $outerDialog.find('.ui-dialog-gradient').length === 0) {
		$outerDialog.find('.ui-dialog-titlebar').append('<div class="ui-dialog-gradient"><div></div></div>');
	}
	if ($target.closest('.ui-dialog-content-wrapper').length === 0) {
		$target.wrap('<div class="ui-dialog-content-wrapper"></div>');
	}
});
$doc.bind('tabscreate', function (event) {
	var $target = $(event.target)
	  , $li = $target.find('> .ui-tabs-nav > li');
	if ($li.find('.gradient-me').length === 0) {
		if ($li.children('> span').length) {
			$li.find('> a > span')
				.wrap('<span></span>')
				.parent()
				.prepend('<div class="gradient-me"></div>');
		} else {
			$li.find('> a')
				.each(function () {
					var $this = $(this);
					$this.html('<span><div class="gradient-me"></div><span>'+ $this.html() +'</span></span>');
				});
		}
	}
});

$(function () {
	if ($('#page-title').length && $('#page-title').text() === "Page header place here!") {
		$('#page-title').html('<span>' + ($('#page-title').text() || '').split('').join('</span><span>') + '</span>');
		$('#page-title span').each(function () {
			var a = ['baseline', 'bottom', 'middle', 'text-bottom', 'text-top', 'top'];
			var rand = Math.floor(a.length * Math.random());
			$(this).css('vertical-align', a[rand]);
		});
		
	}
});

yepnope({
	test: !Modernizr.input.placeholder,
	yep:  '/js/lib/polyfills/placeholder.js',
	callback: function () {$(function () {
		_.defer(function() { $('input[placeholder], textarea[placeholder]').placeholder(); });
	}); }
});

// control min-height of els-body
$(function () {
	var $elsBody = $('#main > .els-body:first')
	  , $footer = $('#footer')
	  , innerMargins;

	$elsBody.length && $footer.length && $([window, document]).bind('load resize.min-height', function () {
		var offsetTop = $elsBody.offset().top
		  , footerHeight = $footer.outerHeight(true);
		
		if (innerMargins == null) {
			var overflow = $elsBody.css('overflow');
			$elsBody.css('overflow', 'hidden');
			innerMargins = offsetTop - $elsBody.offset().top;
			if (overflow) {
				$elsBody.css('overflow', overflow);
			}
		}
		
		$elsBody.css('min-height', $(window).height() - (offsetTop + footerHeight) - 1 - innerMargins);
	}).trigger('resize.min-height');
});

// Change logo picture when lang switches
$(function () {
	var lng = $('.ui-selectmenu-status').text();
    var href = window.location.href.split('/');
    imgdir =  href[0]+'//' + href[2]+'/' + 'themes/rgsu/images';	
    logostyle = "url("+imgdir+"/logo_eng.jpg) no-repeat top left, url("+imgdir+"/logo_right.jpg) no-repeat top left, #fff";

	if(lng == 'eng English') 
		$('#header #logo').css({'background' : logostyle});	
});


})(jQuery, this, this.document);

$(function () {
	yepnope('theme!/css/gradients.css');
});

yepnope({
	test: /(:?^|\s)ie6(:?\s|$)/i.test(document.documentElement.className),
	yep: 'theme!/js/ie6.js'
});


$( document ).ready(function() {
    var isMobile = false;
	// device detection
	if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) ||
		/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))
	){
		isMobile = true;
	}
	
	if(isMobile){
		var el = $('.tab-bar>div>ul>li');
		
		el.click(function(event){
			var submenu_id = $(this).data('submenu-id');
			if (typeof submenu_id === "undefined") {
				return true;
			}			
			event.preventDefault();
			return false;
		});
	}
});