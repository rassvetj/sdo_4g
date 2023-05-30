$(function(){

	$('#activitydevBlock select').change(function(){
		result = $.ajax({
			url:		'infoblock/activitydev/get-data/format/xml',
			type:		'POST',
			data:		{
				key:	$(this).attr('name'),
				value:	$(this).val()
			},
			dataType: 	'html',
			success: 	function(data) {
				if (data) {
					activitydevChart = document.getElementById('activitydev-chart');
					activitydevChart.setData(data);
				}
			}
		});
	});
});