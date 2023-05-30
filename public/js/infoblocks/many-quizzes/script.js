(function($){

// Creating the sweetPages jQuery plugin:
$.fn.sweetPages = function(opts){
	
	// If no options were passed, create an empty opts object
	if(!opts) opts = {};
	
	var resultsPerPage = opts.perPage || 3;
	var currentPage = 1;
	
	
	// The plugin works best for unordered lists, althugh ols would do just as well:
	var ul = this;
	var li = ul.find('li');
	
	li.each(function(){
		// Calculating the height of each li element, and storing it with the data method:
		var el = $(this);
		el.data('height',el.outerHeight(true));
	});
	
	// Calculating the total number of pages:
	var pagesNumber = Math.ceil(li.length/resultsPerPage);
	
	// If the pages are less than two, do nothing:
	if(pagesNumber<2) return this;

	// Creating the controls div:
	var swControls = $('<div class="swControls">');
	
	for(var i=0;i<pagesNumber;i++)
	{
		// Slice a portion of the lis, and wrap it in a swPage div:
		li.slice(i*resultsPerPage,(i+1)*resultsPerPage).wrapAll('<div class="swPage" />');
		
		// Adding a link to the swControls div:
		//swControls.append('<a href="" class="swShowPage">'+(i+1)+'</a>');
	}
	//→←
	swControls.append('<button class="swShowPage" data-direction="left"><span>←</span></button>');
	swControls.append('<span class="swShowPage"><span id="currentPage">' + currentPage + '</span>/' + pagesNumber + '</span>');
	swControls.append('<button class="swShowPage" data-direction="right"><span>→</span></button>');

	ul.append(swControls);
	
	var maxHeight = 0;
	var totalWidth = 0;
	
	var swPage = ul.find('.swPage');
	swPage.each(function(){
		
		// Looping through all the newly created pages:
		
		var elem = $(this);

		var tmpHeight = 0;
		elem.find('li').each(function(){tmpHeight+=$(this).data('height');});

		if(tmpHeight>maxHeight)
			maxHeight = tmpHeight;

		totalWidth+=elem.outerWidth();
		
		elem.css('float','left').width(ul.width());
	});
	
	swPage.wrapAll('<div class="swSlider" />');
	
	// Setting the height of the ul to the height of the tallest page:
	ul.height(maxHeight);
	
	var swSlider = ul.find('.swSlider');
	swSlider.append('<div class="clear" />').width(totalWidth);

	var hyperLinks = ul.find('button.swShowPage');
	
	hyperLinks.click(function(e){
		// If one of the control links is clicked, slide the swSlider div 
		// (which contains all the pages) and mark it as active:
		$(this).addClass('active').siblings().removeClass('active');
		
		if($(this).data('direction') == 'left'){
			currentPage--;
		}else{
			currentPage++;
		}
		if(currentPage <= 0){
			currentPage = pagesNumber;
		}
		if(currentPage > pagesNumber){
			currentPage = 1;
		}
		$('.swShowPage #currentPage').text(currentPage);
		
		
		swSlider.stop().animate({'margin-left':-(currentPage-1)*ul.width()},'slow');
		e.preventDefault();
	});
	
	// Mark the first link as active the first time this code runs:
	hyperLinks.eq(0).addClass('active');
	
	// Center the control div:
	swControls.css({
		'left':'50%',
		'margin-left': swControls.width()/2 - 80
	});
	
	return this;
	
}})(jQuery);

$(function(){

	$('.many-quizzes-answers-form').submit(function(){
		
		var form = $(this);
		data = form.find("input").serializeArray();
		
		var quizId = form.find("input[name=quiz_id]").val();
		var quisIds = JSON.parse($.cookie("many-quizzes-quiz-id"));
		if (!quisIds || !quisIds.length) quisIds = new Array();
		if (quizId) quisIds.push(quizId);
		$.cookie("many-quizzes-quiz-id", JSON.stringify(quisIds));
		
		var questionId = form.find("input[name=question_id]").val();;
		var questionIds = JSON.parse($.cookie("many-quizzes-quiz-id"));
		if (!questionIds || !questionIds.length) questionIds = new Array();
		if (questionId) questionIds.push(questionId);
		$.cookie("many-quizzes-question-id", JSON.stringify(questionIds));
		
	    result = $.ajax({
	         url:		$('#quizUrl').val(),
	         type:		'POST',
	         data:		data,
			 dataType: 	'json',
	         success: 	function(data) {
				if (data.result) {
					form.find('.many-quizzes-results-allow').css('visibility', 'visible');
					form.find('input[type=submit]').attr('disabled', 'true');
				} else {
					alert(data.msg);
				}
	         }
	    });
	    return false;
	});

	$('.many-quizzes-results-allow a').click(function(){
		
		kod = $(this).data('kod');
		
		
		$('.kod_' + kod + ' #many-quizzes-chart-container-' + kod).css('display', 'block');
		$('.kod_' + kod + ' .many-quizzes-results-allow').css('display', 'none');
		$('.kod_' + kod + ' .many-quizzes-answers').css('display', 'none');
	});

	$('#manyQuizzesBlock input:checkbox:not([safari])').checkbox();
	$('#manyQuizzesBlock input[safari]:checkbox').checkbox({cls:'jquery-safari-checkbox'});
	$('#manyQuizzesBlock input:radio').checkbox({cls:'jquery-radio-checkbox'});
	$('.many-quizzes-answers').css('visibility', 'visible');
	
	$('.holder').sweetPages({perPage:1});
	
	// The default behaviour of the plugin is to insert the 
	// page links in the ul, but we need them in the main container:

	var controls = $('.swControls').detach();
	controls.appendTo('.holder');
	
	
	

});