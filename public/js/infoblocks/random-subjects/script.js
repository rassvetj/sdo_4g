//var flag = 1;

$(function(){
	$('#subject-next-submit').click(function(){
		// мегахак! функция запускалась дважды за клик, теперь отрабатывает через раз :)
		//if (flag == 1) {
		//	flag = 2;
		//	return false;
		//}
		//flag = 1;
		
		data = $("#subject-next-form :input").serializeArray();
	    result = $.ajax({
	         url:		$('#subject-next-url').val(),
	         type:		'POST',
	         data:		data,
			 dataType: 	'json',
	         success: 	function(data) {
				if (data.result) {
					$('#random-subjects-title').html(data.title);
					$('#random-subjects-description').html(data.description);
					$('#random-subjects-url').html(data.url);
					$('#random-subjects-form input[type=image]').attr('disabled', 'true');
				}
	         }
	    });
	    return false;
	});

});