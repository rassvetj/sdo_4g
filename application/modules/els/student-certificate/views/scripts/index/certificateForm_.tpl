<?= $this->form?>

<div class="ajax-spinner-local"></div>

<?php $this->inlineScript()->captureStart()?>
	(function () {
		var block_1 = 'block_1';

		$(document.body).delegate('#' + block_1 + ' form', 'submit', _.debounce(function (event) {
			$('#' + block_1)
				.prop('disabled', true)
				.find('input').prop('disabled', true);

			var $portletContent = $(this).closest('.ui-portlet-content');
			if ($portletContent.length) {
				$portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
			}
			
			sendForm2(block_1, <?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'send'))) ) ?>);
		}, 50));
	
		$(document.body).delegate('#' + block_1 + ' form', 'submit', function(event) {
			event.preventDefault();
		});

	})();
<?php $this->inlineScript()->captureEnd()?>

