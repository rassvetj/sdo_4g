$(document).ready(function() {
	var $fieldsets = $('form fieldset legend').parent();

	$fieldsets.each(function (i) {
		if (i > 0 && $(this).find('.required').length == 0) {
			$(this).children().not('legend').hide();
		} else {
			$(this).children('legend:first')
				.addClass('separator-active');
		}
	});
	$fieldsets.children('legend')
		.each(function () {
			var $this = $(this);
			$this.html('<span class="label">' + $this.html() + '</span>');
			$this.prepend('<span class="separator">›</span> ');
		});

	$(document).on('click', 'form fieldset legend', function() {
		$(this).toggleClass('separator-active')
			.nextAll().toggle("fast");
	});
});
