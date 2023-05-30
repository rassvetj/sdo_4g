function createColumnsEditor (settings) {
	"use strict";
	var $columns
	  , $grips = $([])
	  , grip
	  , $overlay
	  , $dashboard;

	function updateTitlebar (collection) {
		return $(collection).each(function () {
			if (!$('.ui-portlet-titlebar-remove', this).length && !$(this).data('undeletable')) {
				$(this).find('.ui-portlet-titlebar-wrapper')
					.append("<a class='ui-portlet-titlebar-remove' href='#' role='button' title='"+ settings.l10n.del +"'><span class='ui-icon ui-icon-closethick'>remove</span></a>").end();
			}
		});
	}

	grip = (function () {
		var dashboardWidth
		  , dashboardOffset
		  , minColumnWidth;
		function check () {
			dashboardOffset || $(window).trigger('resize.dashboard');
		}
		function getGripOffset ($columns, gripIdx) {
			var offset = 0;
			for (var i = gripIdx; i >= 0; --i)
				offset += $columns.eq(i).data('widthPx');
			return offset;
		}
		function tododo (array, property, sum, min) {
			var i = 0
			  , clone = [].concat(array)
			  , sorted
			  , sign = 1;
			sum = _.reduce(array, function (memo, item) { return memo - item[property]; }, sum);
			sorted = _.sortBy(array, function (item) { return -item[property]; });
			sum && (sign = sum / Math.abs(sum));
			while (sum != 0) {
				if (clone[sorted[i].index][property] > min) {
					clone[sorted[i].index][property] += sign;
					sum -= sign;
				}
				i = (i + 1) % array.length;
			}
			if (property == 'widthPx') {
				_.last(clone).widthPx--;
			}
			return clone;
		}
		function calculateMetrics ($columns, $grips) {
			// this function will update data of columns & grips
			// calculateColumnWidths
			var widths;
			// TODO использовать абсолютное позиционирование!!!
			
			check();
			widths = _.map($columns.get(), function (column, idx) {
				var width = $(column).data('width');
				
				width || (width = 100 / $columns.length);
				((settings.columns.minWidth + settings.columns.widthDelta) > width) && (width = settings.columns.minWidth);
				
				return {
					index: idx,
					width: Math.round(width)
				};
			});
			widths = _.map(tododo(widths, 'width', 100, settings.columns.minWidth), function (item) {
				return (item.widthPx = Math.floor(dashboardWidth*0.01*item.width), item);
			});
			widths = tododo(widths, 'widthPx', dashboardWidth, minColumnWidth);
			$columns.each(function (i) {
				$(this).data(widths[i]);
			});
			
			// calculateGripPositions
			$grips.each(function (idx) {
				$(this).data('offset', getGripOffset($columns, idx));
			});
		}
		function containment (gripIdx, $columns) {
			check();
			return [
				  dashboardOffset.left + getGripOffset($columns, gripIdx - 1) + minColumnWidth
				, 0
				, dashboardOffset.left + getGripOffset($columns, gripIdx + 1) - minColumnWidth
				, 0
			];
		}
		function showOverlay ($dashboard) {
			if (!$dashboard.children('.overlay').length) {
				$('<div class="overlay"></div>')
					.hide().css('opacity', 0.5)
					.appendTo($dashboard);
			}
			$dashboard.children('.overlay')
				.width($dashboard.innerWidth())
				.height($dashboard.outerHeight(true))
				.fadeIn('fast');
		}
		function hideOverlay ($dashboard) {
			$dashboard.children('.overlay')
				.fadeOut('fast');
		}
		function getExtremeColumns ($columns) {
			var max = { idx: -1, width: 0 }
			  , min = { idx: -1, width: 200 };
			$columns.each(function (idx) {
				var width = $(this).data('width');
				if (width > max.width)
					max = { idx: idx, width: width }
				if (min.width > width)
					min = { idx: idx, width: width }
			});
			return {
				narrow: $columns.eq(min.idx),
				wide:   $columns.eq(max.idx)
			}
		}
		function classify ($dashboard) {
			// todo remove global vars
			$columns = $dashboard.children('.column-wrapper')
				.not('.ui-sortable-helper')
				.removeClass('last first')
				.first().addClass('first').end()
				.last().addClass('last').end();
		}
		var uploadToServer = _.debounce(uploadToServerInstant, 1000);
		function uploadToServerInstant ($dashboard, $columns) {
			$.post(settings.url.upload + settings.role, {
				portlets: JSON.stringify(_($dashboard.portlets('toArray')).map(function (a) { return _.compact(a); })),
				columns:  JSON.stringify(_($columns.get()).map(function (item) { return $(item).data('width'); }))
			});
		}
		function buildInfoblockSelector (data) {
			var $dashboard = $('.user-dashboard');

			if (!settings.role) { return; }
			
			var buildInfoblocksSelect = function (blocks) {
				var $select = $('<select><option value="">'+ settings.l10n.select +'</option></select>')
				_(blocks).each(function (item) {
					var $optgroup = $('<optgroup></optgroup>')
						.data('name', item.name)
						.attr('label', item.title)
						.appendTo($select);
					item.block && (_.isUndefined(item.block['title']) ? "" : (item.block = [item.block]), 1) && _(item.block).each(function (item, idx) {
						// XXX hack
						if (!/^[\d]+$/.test(''+idx)) return; 
						$('#' + item.name).data('description', item.description);
						$('<option></option>')
							.attr('value', item.name)
							.attr('data-title', item.title)
							.attr('data-description', item.description)
							.html(item.title + '//' + item.description)
							.appendTo($optgroup);
					});
				});
				return $select;
			}
			var filterSelect = function ($select, $dashboard) {
				var $filtered = $select.clone(true);
				$dashboard.find('.ui-portlet').each(function () {
					var classname = $(this).data('infoblock')
					  , id = this.id;
					id        && $filtered.find('option[value="'+ id +'"]').remove();
					classname && $filtered.find('option[value="'+ classname +'"]').remove();
				});
				return $filtered.addClass('infoblock-select');
			}
			var appendInfoblock = function (option, $select, $dashboard, skipUpload) {
				var $screenForm
				  , params;
				
				params = {
					icons: null,
					width: 250,
					maxHeight: 300,
					format: function (text) {
						text = text.split('//');
						return text.length == 2 ? '<h3>'+ text[0] +'</h3><p>'+ text[1] +'</p>' : '<h3>'+ text[0] +'</h3>';
					},
					change: function (event, opt) {
						if (!opt.value) return;
						appendInfoblock(opt.option, $select, $dashboard);
					}
				};
				
				$('.infoblock-select').selectmenu('destroy');
				if (option && option.value) {
					// todo find column
					// rebuild sortable - not needed :)
					// todo hardcode and dirty hack for news!!!
					var infoblockName = option.value;
					if (/^news_[0-9]+$/.test(infoblockName)) {
						infoblockName = 'news';
					}
					$screenForm = updateTitlebar($(settings.empty))
						.attr('id', option.value)
						.attr('data-infoblock', infoblockName)
						.find('.ui-portlet-titlebar h3')
						.html($(option).data('title')).end()
						.find('.ui-portlet-content')
						.html($(option).data('description')).end()
						.prependTo($dashboard.find('.column:first'));
					
					$.get(settings.url.content.replace('_role_', settings.role).replace('_name_', option.value)).done(function (data) {
						var $newInfoblock = updateTitlebar($(data));
						if (/^(screenForm|news)$/.test($newInfoblock.data('infoblock'))) {
							$newInfoblock.attr('id', $newInfoblock.data('infoblock'));
						}
						if ($newInfoblock.is('.ui-portlet')) {
							$screenForm.replaceWith($newInfoblock);
						} else {
							$screenForm.find('.ui-portlet-content').html($newInfoblock);
						}
					}).fail(function (data) {
						//alert('ooops');
					});
				}
				$('.infoblock-select').replaceWith(
					filterSelect($select, $dashboard)
				);
				$('.infoblock-select').selectmenu(params);
				skipUpload || uploadToServer($dashboard, $columns);
			}
			var removeInfoblock = function ($portlet, $select, $dashboard) {
				$portlet.remove();
				appendInfoblock(null, $select, $dashboard);
			}

			$dashboard.delegate('a.ui-portlet-titlebar-remove', 'click', function (event) {
				event.preventDefault();
			});

			var $select = buildInfoblocksSelect(data.blocks);
			$dashboard.delegate('a.ui-portlet-titlebar-remove', 'click', function (event) {
				removeInfoblock($(event.target).closest('.ui-portlet'), $select, $dashboard);
			});
			updateTitlebar($dashboard.find('.ui-portlet'));
			appendInfoblock(null, $select, $dashboard, true);
			$('.portlets-editor-bar').show('blind');
			settings.admin && $grips.show();
		}

		$(window).bind('resize.dashboard', function () {
			// todo 1px ie bug 
			dashboardWidth  = $dashboard.innerWidth();
			dashboardOffset = $dashboard.offset();
			minColumnWidth  = dashboardWidth * settings.columns.minWidth * 0.01;
		});
		return {
			updateVisual: function (animate) {
				calculateMetrics($columns, $grips);

				$columns.stop(true);
				$grips.each(function (i) {
					$(this)
						.css('left', $(this).data('offset'))
						.draggable('option', 'containment', containment($grips.index(this), $columns));
				});
				if (animate === true) {
					$grips.draggable('disable');
					$grips.hide();
					$columns.each(function (idx) {
						// todo columns may drop off at the bottom
						$(this).animate({ width: $(this).data('widthPx') }, function () {
							$grips.fadeIn('fast');
							$grips.draggable('enable');
						});
					});
				} else {
					$columns.width(function (idx) {
						return $(this).data('widthPx');
					});
				}
			},
			createGrips: function () {
				$grips && $grips.remove();
				_($columns.length - 1).times(function () {
					$dashboard.append('<div class="column-resize-grip" style="position: absolute;"><div></div></div>');
				});
				$grips = $dashboard.find('.column-resize-grip');
				
				calculateMetrics($columns, $grips);
				
				$grips.each( function () {
					var $grip = $(this)
					  , width = $grip.width()
					  , idx = $grips.index(this)
					  , p = parseFloat;

					$grip.bind('remove', function () {
						$grip.draggable('destroy');
						$grip = null;
					});
					$grip.draggable({
						axis: 'x',
						handle: '> div',
						cursor: 'col-resize',
						start: function () { showOverlay($dashboard); },
						stop: function (even, ui) {
							var leftHandNeighboursOffset = 0
							  , leftHandColumnsWidth
							  , withOfAdjacentColumns
							  , withOfColumn;
							
							// in percent
							withOfAdjacentColumns = $columns.eq(idx).data('width') + $columns.eq(idx+1).data('width');
							
							// in pixels
							idx && ( leftHandNeighboursOffset = $grips.eq(idx-1).data('offset') );
							// in percent
							leftHandColumnsWidth = ((p($grip.css('left')) - leftHandNeighboursOffset) * 100) / dashboardWidth;
							
							$columns
								.eq(idx).data('width', leftHandColumnsWidth).end()
								.eq(idx+1).data('width', withOfAdjacentColumns - leftHandColumnsWidth);

							grip.updateVisual(false);
							hideOverlay($dashboard);
							
							uploadToServer($dashboard, $columns);
						}
					});
				} );
			},
			createPortlets: function () {
				$dashboard.portlets({
					items: '> .ui-portlet',
					handle: '> .ui-portlet-wrapper > .ui-portlet-titlebar',
					helper: function (event, el) {
						var title = el.find('.ui-portlet-titlebar h3').html();
						return $(settings.empty)
							.css('margin-bottom', '1em')
							.find('.ui-portlet-titlebar h3')
							.html(title).end()
							.find('.ui-portlet-content')
							.html(el.data('description') || '').end();
					},
					start: function (event, ui) {
						ui.item.show().css('opacity', 0.5);
						ui.placeholder.hide();
					},
					change: function (event, ui) {
						var prev = ui.placeholder.prev('.ui-portlet')
						  , next = ui.placeholder.next('.ui-portlet')
						  , item = ui.item.get(0);
						if (prev.get(0) == item || next.get(0) == item) {
							ui.placeholder.hide();
						} else {
							ui.placeholder.show();
						}
					},
					stop: function (event, ui) {
						ui.item.css('opacity', 1);
					},
					update: function (event, ui) {
						uploadToServer($dashboard, $columns);
					},
					forceHelperSize: true,
					forcePlaceholderSize: true,
					tolerance: 'pointer',
					placeholder: 'ui-portlet ui-sortable-placeholder',
					appendTo: 'body'
				});
			},
			createSortableColumns: function () {
				$columns
					.prepend('<div class="drag-me-gently"><div></div></div>');

				$dashboard.sortable({
					handle: '.drag-me-gently > div',
					revert: true,
					axis: 'x',
					//tolerance: 'pointer',
					cursor: 'w-resize',
					start: function (event, ui) {
						//$grips.fadeOut('slow');
						$grips.hide();
					},
					stop: function () {
						//$grips.fadeIn('slow');
						$grips.appendTo($dashboard);
						$grips.show();
						classify($dashboard);
						grip.update();
					},
					change: function (event, ui) {
						classify($dashboard);
					}
				});
			},
			addColumn: function () {
				if ($columns.length >= settings.columns.max) { return; }

				$dashboard.portlets('destroy');
				$('<div class="column-wrapper"><div class="column"></div></div>')
					.width(0)
					.data('width', settings.columns.minWidth)
					.appendTo($dashboard);
				classify($dashboard);
				grip.createPortlets();
				grip.createGrips();
				grip.updateVisual(false);
				// добавление пустой колонки не должно вызывать загрузку на сервер
				//uploadToServer($dashboard, $columns);
			},
			removeColumn: function () {
				var $narrow
				  , $wide
				  , $portlets;
				if ($columns.length == 1) { return; }
				$dashboard.portlets('destroy');
				$narrow = getExtremeColumns($columns).narrow.detach();
				classify($dashboard);

				$wide = getExtremeColumns($columns).wide;
				$narrow.find('.ui-portlet').appendTo($wide.find('.column'));
				$narrow.remove();
				grip.createPortlets();
				grip.createGrips();
				grip.updateVisual(false);
				uploadToServer($dashboard, $columns);
			},
			init: function (data) {
				var $dashboardColumnsReminder = $('<div class="dashboard-columns-reminder"><div class="d-first"></div><div class="d-second"></div><div class="d-third"></div></div>')
					.appendTo('.user-dashboard')
					.show()
					.children('div');
				$dashboard = $('<div class="user-dashboard columns ui-portlets-edit-mode"></div>');
				$('.user-dashboard').find('.column').each(function (idx) {
					var $portlets = $(this).find('.ui-portlet')
					  , $copy = $('<div class="column-wrapper"><div class="column"></div></div>').appendTo($dashboard);
					$portlets.each(function () {
						var $portlet = $(this);
						if (!/^(screenForm|news)$/.test($portlet.data('infoblock'))) {
							$portlet.attr('id', $portlet.data('infoblock'));
						}
					});
					
					$copy.data('width', $dashboardColumnsReminder.eq(idx).width());
					$portlets.appendTo($copy.children('.column:first'));
				});
				classify($dashboard);
				
				$('.user-dashboard').replaceWith($dashboard);
				
				//grip.createSortableColumns();
				grip.createPortlets();
				settings.admin && settings.isEdit && grip.createGrips();
				settings.admin && settings.isEdit && $grips.hide();
				grip.updateVisual();
				
				$(window).bind('resize.dashboard-grips', function () {
					grip.updateVisual();
				});
				buildInfoblockSelector(data);
			}
		};
	})();
	
	if (!_.isNumber(settings.columns.max) || _.isNaN(settings.columns.max) || settings.columns.max <= 0) {
		settings.columns.max = Math.floor(100 / settings.columns.minWidth);
	}

	$.getJSON(settings.url.list + settings.role).done(function (data) {
		grip.init(data);
	});

	if (settings.admin) {		
		$('.role-select select').selectmenu({
			icons: null,
			width: 250,
			maxHeight: 300
		});	
		$('.portlets-editor-bar').delegate('a.add-column, a.remove-column', 'click', function (event) {
			var $target = $(event.currentTarget);
			if ($target.is('.add-column')) {
				grip.addColumn();
			} else {
				grip.removeColumn();
			}
			event.preventDefault();
		});
	} else {
		$('.portlets-editor-bar').find('a.add-column, a.remove-column').hide();
	}
	
	var $clearDeferred;
	if (settings.url.self && settings.url.clear) {
		$('.portlets-editor-bar').delegate('.clear-settings', 'click', function (event) {
			event.preventDefault();
			if ( !$clearDeferred || $clearDeferred.isRejected() || ($clearDeferred.state() === 'resolved') ) {
				$clearDeferred = $.post(settings.url.clear).then(function () {
					$('.portlets-editor-bar').slideUp();
					window.location.href = settings.url.self;
				}, function () { 
					$('.portlets-editor-bar').slideUp();
					window.location.href = settings.url.self;
				});
			}
		});
	} else {
		$('.portlets-editor-bar .clear-settings').hide();
	}
}
