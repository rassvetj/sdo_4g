var initPortletsEditor = (function ($) {

var settings;

function updateCssClasses () {
	$('.draggable-area-2columns .column > .ui-portlet')
		.removeClass('ui-portlet-last-in-column');
	$('.draggable-area-2columns .column').each(function () {
		 $('> .ui-portlet:visible', this).last().addClass('ui-portlet-last-in-column');
	});
}
function updateTitlebar ($obj) {
	if (!$('.ui-portlet-titlebar-remove', $obj).length) {
		 $obj.find('.ui-portlet-titlebar-wrapper').append("<a class='ui-portlet-titlebar-remove' href='#' role='button' title='"+ settings.l10n.del +"'><span class='ui-icon ui-icon-closethick'>remove</span></a>").end();
	}
	return $obj;
}
function rebuildDraggable() {
	$('#infoblocks-list li')
		.draggable('destroy')
		.filter(function () {
			return $('> ul', this).length == 0;
		})
		.draggable({
			revert: 'invalid',
			revertDuration: 300,
			helper: function (event) {
				var $helper
				  , $target = $(event.target);

				$helper = $(settings.draggableHtmlStub)
					.attr('id', $target.attr('id'))
					.attr('data-category', $target.parent().closest('li').attr('id'))
					.attr('data-description', $target.data('description') || '')
					.attr('data-title', $target.html())
					.find('div.ui-portlet-titlebar:first h3')
					.html($target.html())
					.end()
					.find('div.ui-portlet-content:first')
					.html($target.data('description') || '')
					.end()
					.wrap('<div>');

				$helper
					.data({
						'from-source': true,
						'html': $helper.parent().html(),
						'reverted': true
					})
					.css('width', 200);

				return $helper;
			},
			appendTo: 'body',
			zIndex: $('.portlets-editor').portlets('option', 'zIndex'), // the same as in portlets sortable
			connectToSortable: '.portlets-editor .draggable-area-2columns .column',
			drag: function (event, ui) {
				$(ui.helper).css('height', '');
			},
			stop: function (event, ui) {
				// strange, i can't explain
				// why this data is preserved
				// only on invalid drag
				if (!$(ui.helper).data('reverted')) {
					$(event.target).draggable('disable').remove();
				}
			}
		});
}
function removePortletSortable ($item) {
	$item = $($item.remove().get(0));

	if (!$('#' + $item.data('category') + ' > ul').length) {
		$('#' + $item.data('category')).append('<ul></ul>');
	}

	$('<li>' + $item.data('title') + '</li>')
		.attr('data-description', $item.data('description') || '')
		.attr('id', $item.attr('id'))
		.appendTo('#' + $item.data('category') + ' > ul');
}
function uploadToServer () {
	if (settings.uploadUrl) {
		$.post(settings.uploadUrl, {
			portlets: $('.portlets-editor').portlets('serialize'),
			role: $('#role').val()
		});
	}
}

return function (o) {
	if (settings) { return; }
	settings = o;

	$('.portlets-editor .ui-portlet').each(function () {
		updateTitlebar($(this));
	});

	updateCssClasses();

	$('#portlets-delete-all').click(function (event) {
		event.preventDefault();
		$('.draggable-area-2columns .ui-portlet').each(function () {
			removePortletSortable($(this));
		});
		uploadToServer();
		rebuildDraggable();
	});
	$(document).delegate('.ui-portlet-titlebar-remove', 'click', function (event) {
		removePortletSortable($(event.target).closest('.ui-portlet'));

		uploadToServer();
		rebuildDraggable();
	});

	$('.portlets-editor').portlets({
		columns: '.draggable-area-2columns .column',
		items: '> .ui-portlet',
		start: function (event, ui) {
			var fromSource = !!$(ui.helper).data('from-source');

			// transfer all data to item, because helper
			// will be destroyed before onStop event
			$(ui.item).data({
				'els-orig-width': $(ui.item).width(),
				'from-source': fromSource,
				'html': $(ui.helper).data('html')
			});

			$(ui.helper).css('height', '');
			$(ui.placeholder).css('height', fromSource
				? $(ui.helper).height()
				: $(ui.item).height()
			);
		},
		sort: function (event, ui) {
			if (ui.placeholder && ui.placeholder.length) {
				$(ui.helper).css('width', $(ui.placeholder).width());
			} else {
				$(ui.helper).css('width', $(ui.item).data('els-orig-width'));
			}

			_.defer(updateCssClasses);
		},
		stop: function (event, ui) {
			var fromSource = $(ui.item).data('from-source')
				 , sourceHtml = $(ui.item).data('html')
				 , $sourceHtml;
			$(ui.item).removeData();

			if (fromSource) {
				$sourceHtml = updateTitlebar($(sourceHtml));
				$(ui.item).replaceWith($sourceHtml);
			}

			updateCssClasses();

			uploadToServer();
		},
		handle: '> .ui-portlet-wrapper > .ui-portlet-titlebar',
		helper: 'clone',
		placeholder: 'ui-portlet ui-sortable-placeholder',
		revert: false,
		forcePlaceholderSize: true,
		appendTo: 'body'
	});

	$('#infoblocks-list').disableSelection();

	// draggable target, the source of all infoblocks
	rebuildDraggable();
};

})(jQuery);