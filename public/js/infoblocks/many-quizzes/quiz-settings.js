$(function(){
	$('#quizsettings #quiz_id').change(function(){
		var quiz_id = $(this).val();
		if (quiz_id) {
			result = $.ajax({
				url:		$('#getQuestionsUrl').val(),
				type:		'POST',
				data:		{
					quiz_id: quiz_id,
					format: 'json'
				},
				dataType: 	'json',
				success: 	function(data) {
					$('#quizsettings #question_id > *').remove();
					if (data.questions) {
						$.each(data.questions, function(key, value) {
						     $('#quizsettings #question_id').append($("<option></option>").attr("value",key).text(value));
						});
					}
				}
			});
		}
	}); 
});  