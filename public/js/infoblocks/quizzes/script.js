$(function(){

	$('#quizzes-answers-form').submit(function(){
		data = $("#quizzes-answers-form :input").serializeArray();
		$.cookie("quizzes-quiz-id", $("#quizzes-answers-form input[name=quiz_id]").val());
		$.cookie("quizzes-question-id", $("#quizzes-answers-form input[name=question_id]").val());
	    result = $.ajax({
	         url:		$('#quizUrl').val(),
	         type:		'POST',
	         data:		data,
			 dataType: 	'json',
	         success: 	function(data) {
				if (data.result) {
					$('#quizzes-results-allow').css('visibility', 'visible');
					$('#quizzes-answers-form input[type=submit]').attr('disabled', 'true');
				} else {
					alert(data.msg);
				}
	         }
	    });
	    return false;
	});

	$('#quizzes-results-allow a').click(function(){
		$('#quizzes-chart-container').css('display', 'block');
		$('#quizzes-results-allow').css('display', 'none');
		$('#quizzes-answers').css('display', 'none');
	});

	$('#quizzesBlock input:checkbox:not([safari])').checkbox();
	$('#quizzesBlock input[safari]:checkbox').checkbox({cls:'jquery-safari-checkbox'});
	$('#quizzesBlock input:radio').checkbox({cls:'jquery-radio-checkbox'});
	$('#quizzes-answers').css('visibility', 'visible');

});