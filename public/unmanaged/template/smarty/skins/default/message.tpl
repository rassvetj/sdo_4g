<div id="controller-message" title="{?$smarty.const.APPLICATION_TITLE|escape?}">
	<p>{?$this->content|strip|trim?}</p>
</div>
<script type="text/javascript">
	jQuery(function ($) {
		var buttons = {};
		var dialogOptions = {
			closeOnEscape: false,
			open: function (event, ui)  {
				$(".ui-dialog-titlebar-close", $(this).closest('.ui-dialog')).hide()
			}
		};
		{?if !$this->cancel_url && !$this->url ?}
		dialogOptions["closeOnEscape"] = true;
		dialogOptions["open"] = null;
		{?/if?}
		{?if $this->url ?}
		buttons["{?t?}Ok{?/t?}"] = function (event, ui) {
			var okUrl = '{?$this->cancel_url|replace:"'":"\\'"?}';
			event.preventDefault();
			if (/^javascript/i.test(okUrl)) {
				var handler = {?if $this->onclick?}{?$this->onclick?}{?else?}function () {}{?/if?};
				try {
					handler(event);
				} catch (error) {
					try {
						console.log(error);
					} catch (e) {}
				}
			} else {
				document.location.href = "{?$this->url?}";
			}
		}
		{?/if?}
		{?if $this->cancel_url ?}
		buttons["{?t?}Отмена{?/t?}"] = function (event, ui) {
			var cancelUrl = '{?$this->cancel_url|replace:"'":"\\'"?}';
			if (!/^javascript/i.test(cancelUrl)) {
				document.location.href = "{?$this->cancel_url?}";
				event.preventDefault();
			}
		}
		{?/if?}
		$("#controller-message").dialog(_.extend({
			modal: true,
			buttons: buttons,
			draggable: false,
			dialogClass: "pcard",
			resizable: true,
			width: 450
		}, dialogOptions));
	});
</script>