(function () {

var updateUsersSystemCounter = _.debounce(_updateUsersSystemCounter, 100);

function _updateUsersSystemCounter (from, to) {
	$.post(usersSystemCounterUrl, { from: from, to: to }, function (data) {
		$( '#usersSystemCounterBlock .ui-portlet-content #usersSystemCounter_guests' ).text(data.guests);
		$( '#usersSystemCounterBlock .ui-portlet-content #usersSystemCounter_users' ).text(data.users);
	}, "json");
}

$(function() {
	$( "#usersSystemCounterBlock .ui-portlet-content #from, #usersSystemCounterBlock .ui-portlet-content #to" ).datepicker({
		showOn: "button",
		buttonImage: "/images/infoblocks/usersSystemCounter/datepicker.gif",
		buttonImageOnly: true,
		defaultDate: "+1w",
		changeMonth: false,
		numberOfMonths: 1,
		dateFormat: 'dd.mm.yy',
		onSelect: function ( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate"
			  , selectedDate = $(this).datepicker('getDate')
			  , $opposite = $('#usersSystemCounterBlock .ui-portlet-content #' + (option == 'minDate' ? 'to' : 'from'))
			  , oppositeDate = $opposite.datepicker('option', option);

			if (!_.isEqual(selectedDate, oppositeDate)) {
				$opposite.datepicker('option', option, selectedDate);
			}

			updateUsersSystemCounter($( '#usersSystemCounterBlock .ui-portlet-content #from' ).val(), $( '#usersSystemCounterBlock .ui-portlet-content #to' ).val());
		}
	});
});

})();
