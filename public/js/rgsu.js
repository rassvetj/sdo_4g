$( document ).ready(function() {
    var els = $('.ball-area');	
	
	if(els.length < 1){ return; }
	
	var ids = [];
	$.each(els, function(key, value){
		ids.push($(this).data('id'));
	});
	
	/*
	jQuery.ajax({
		type	: 'POST',
		url		: '/subject/info/get-marks/',
		dataType: 'json',
		data: {'ids':ids},			
		success: function (result) {
			if (typeof result !== "undefined") {
				$.each(result, function(subject_id, row){
					var el = $('#ball-area-'+subject_id);
					if(row.isFail == 1)	{ el.find('.score-block').addClass('not-pass');		}
					else				{ el.find('.score-block').removeClass('not-pass');	}					
					
					if(row.total > 0)	{ el.find('.number_number').removeClass('score_gray').addClass('score_red'); }
					else				{ el.find('.number_number').removeClass('score_red').addClass('score_gray'); }
					
					el.find('.message-block').html('');
					
					if (typeof row.reasonFail !== "undefined") {
						var reasons = '';
						$.each(row.reasonFail, function(key, reason){							
							reasons += reason.message;
							return false;
						});	
						el.find('.message-block').html(reasons);						
					}					
					el.find('.number_number span').html(row.total);
					el.find('.score-block').removeClass('hidden');
					el.find('.message-block').removeClass('hidden');
				});
			}
		}
	});
	*/
});

$( document ).ready(function() {
  $('#gridAction_grid').on('change', function() {
    
	let elCourses   = $('#courseId');
	let elUnCourses = $('#unCourseId');
	let elActive    = null;
	
	if(!elCourses.is(':hidden'))  { elActive = elCourses;   }
	if(!elUnCourses.is(':hidden')){ elActive = elUnCourses; }
	
	destroySubjectList(elCourses);
	destroySubjectList(elUnCourses);
	
	if(elActive != null){ initSubjectList(elActive); }
  });
  
  $('img.multiple_toggle').on('click', function() {
	let elCourses   = $('#courseId');
	let elUnCourses = $('#unCourseId');
	let elActive    = null;
	
	if(!elCourses.is(':hidden'))  { elActive = elCourses;   }
	if(!elUnCourses.is(':hidden')){ elActive = elUnCourses; }
	
	if(elActive != null){ modifySubjectList(elActive); }
  });
  
  
});

function destroySubjectList(el)
{
	if(el.hasClass('select2-hidden-accessible')){ el.select2('destroy'); }
	el.val(null).trigger('change');
}

function modifySubjectList(el)
{	
	let attr   = el.attr('multiple');
	let config = {};
	
	if(typeof attr !== typeof undefined && attr !== false){
		config.multiple = false;
	} else {
		config.multiple = true;
	}
	initSubjectList(el, config);
}

function initSubjectList(el, config = {})
{
	let multiple = config.multiple || false;
	
	destroySubjectList(el);
	
	el.select2({
		width: '400px',
		multiple: multiple,		
		ajax: {
			url: '/subject/ajax/get-subject-list/',
			dataType: 'json',
			delay: 1000,
			data: function (params) {
				var query = {
					search: params.term
				}
				return query;
			},
			processResults: function (data, params) {			  
			  return {
				results: data.items
			  };
			},
			cache: true
		},
		minimumInputLength: 3		
	});
}


