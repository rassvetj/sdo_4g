$(function(){

	$('#activityBlock #activity-select-group').change(function(){
		result = $.ajax({
			url:		'infoblock/activity/get-users',
			type:		'POST',
			data:		{
				group: $(this).val(),
				format: 'json'
			},
			dataType: 	'json',
			success: 	function(data) {
				if (data.users) {
					var width = $('#activity-select-user').width();
					$('#activity-select-user > *').remove();
					$('#activity-select-user').width(width); // в IE меняется ширина select'а после append - ???
					$.each(data.users, function(key, value) {
					     $('#activity-select-user').append($("<option></option>").attr("value",key).text(value));
					});
				}
			}
		});
	});

	$('#activityBlock select').change(function(){
		result = $.ajax({
			url:		'infoblock/activity/get-data/format/xml',
			type:		'POST',
			data:		{
				key:	$(this).attr('name'),
				value:	$(this).val()
			},
			dataType: 	'html',
			success: 	function(data) {
				if (data) {
					activityChart = document.getElementById('activity-chart');
					activityChart.setData(data);
				}
			}
		});
	});
});