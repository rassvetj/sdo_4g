$(function(){
	$('#claimsBlock .chart-select-period').change(function(){
		period = $(this).val();
		result = $.ajax({
			url:		'infoblock/claims/get-data/format/xml',
			type:		'POST',
			data:		{period: period},
			dataType: 	'html',
			success: 	function(data) {
				if (data) {
					claimsChart = document.getElementById('claims-chart');
					claimsChart.setData(data);
					updateMeta(data);
				}
			}
		});
	}); 
}); 

//	 @todo: use this method onLoad (not only update)
function updateMeta(meta){
    xml = $.parseXML(meta);
    xml = $(xml);
    if (total = xml.find('total')) {
	    $('#claims-placeholder-total').html(total.text());	
    }
    if (undone = xml.find('undone')) {
	    $('#claims-placeholder-undone').html(undone.text());	
    }
}