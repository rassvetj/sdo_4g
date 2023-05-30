(function () {

var updateUsersSystemCounter = _.debounce(_updateUsersSystemCounter, 100);

function _updateUsersSystemCounter (from, to) {
	$.post(usersUsersCounterUrl, { from: from, to: to }, function(data) {
		$( '.usersUsersCounter #usersUsersCounter_count' ).text(data.count);
		$( '.usersUsersCounter #usersUsersCounter_time' ).text(data.time);
	}, "json");
}

$(function() {
	$( ".usersUsersCounter #from, .usersUsersCounter #to" ).datepicker({
		showOn: "button",
		buttonImage: "/images/infoblocks/usersSystemCounter/datepicker.gif",
		buttonImageOnly: true,
		defaultDate: "+1w",
		changeMonth: false,
		numberOfMonths: 1,
		dateFormat: 'dd.mm.yy',
		onSelect: function( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate"
			  , selectedDate = $(this).datepicker('getDate')
			  , $opposite = $('.usersUsersCounter #' + (option == 'minDate' ? 'to' : 'from'))
			  , oppositeDate = $opposite.datepicker('option', option);

			if (!_.isEqual(selectedDate, oppositeDate)) {
				$opposite.datepicker('option', option, selectedDate);
			}

			updateUsersSystemCounter($( '.usersUsersCounter #from' ).val(), $( '.usersUsersCounter #to' ).val());
		}
	});
});

})();