function initMarksheet (settings) {

var l10n = settings.l10n
, $marksheet
, $marksheetScoreCellsNum
, $marksheetInputsNum
, marksNum
, diff
, $personCheckboxes
, $scoreCheckboxes;

function initializeCellSelection ($marksheet, $cells) {
	$(document).delegate('.number_number, .article-controls', 'mouseup', function (event) {
		var $target = $(event.currentTarget);
		if (!event.ctrlKey) {
			$cells.not($target).removeClass('ui-selected');
		}
		$target.toggleClass('ui-selected');
	});
}
function switchGrayRed($arMarks,event){
	if(typeof $arMarks.length !='undefined'){
		$.each($arMarks,function(elem){
			$item = $($arMarks[elem]);
			if ($item.attr('type') != 'text') {
				return;
			}
			if($item.val().length>0 && $item.val()!=l10n.no) {
				$item.closest('.number_number')
					.removeClass('score_gray')
					.addClass('score_red');
				var $el = $('<span/>').text($item.val()).css({
					visbility: 'hidden',
					display: 'inline'
				});
				$item.parent().append($el);
				$item.css('width', $el.width());
				$el.remove();
			}	else	{
				$item.closest('.number_number')
					.removeClass('score_red')
					.addClass('score_gray');
				$item.css({width:'30px'});
			}
		});
	}
}
$(function () {
	$marksheetScoreCellsNum = $('div.number_number');
	$marksheetInputsNum = $('div.number_number input[type="text"], .form-score-ternary input[type="hidden"]').placeholder();
	marksNum = $marksheetInputsNum.serializeArray();
	// get initial marks
	marksNum = _.reduce(marksNum, function (memo, item) {
		memo[item.name] = item.value;
		return memo;
	}, {});
	switchGrayRed($marksheetInputsNum);
	initializeCellSelection($(".lesson_min"), $marksheetScoreCellsNum);
		
	$(".number_number, .form-score-ternary").closest(".lesson_table").find('.tComment').css('display','block');	
	$('.lesson_min, .progress_table').bind('keyup click',function(e){
		switchGrayRed($(this).find('input[type="text"]'),e);
	})
	$('.lesson_min, .progress_table').hover(		
		function(e){
			$(this).addClass('lesson_hover');
		},
		function(e){
			$(this).removeClass('lesson_hover');
		}		
	)
	var tCommentFocus = null;
	$(document).delegate(".tComment",'hover',function(){
		if(tCommentFocus!=true){
			$(this).toggleClass('tCommentFocus');
		}			
	})	
	$(".tComment").bind('focus',function(){
		tCommentFocus=true;
		$(this).addClass('tCommentFocus');
	})
	$(".tComment").bind('blur',function(){
		var data={};
		data[$(this).attr('name')]='';
		data['comment']=$.trim($(this).val());
		$(this).removeClass('tCommentFocus');
		$.post(settings.url.comments, data);
		tCommentFocus=null;
	})		
});

yepnope({
	test: Modernizr.input.pattern,
	nope: '/js/lib/polyfills/h5f.js',
	complete: function () { _.defer(function () {
		$(function () {
			window.H5F && (H5F.placeholder = function () {});
			window.H5F && H5F.setup($("#marksheetteacher").get(0), { invalidClass: "invalid" });

            var diff = null,
                sendDiff = _.debounce(function() {
                    if (!diff) {
                        return;
                    }
                    marksNum = _.extend(marksNum, diff);
                    $.post(settings.url.score, diff);
                    diff = null;
                }, 500);

			// marksheet score submission
			$(document).delegate('.lesson_min, .progress_table', 'keyup click', function (e) {

                var $this = $(this).find('input[name]'),
                    mark,
                    isValid = !!this.checkValidity && this.checkValidity(),
                    inputName = $this.attr('name'),
                    inputValue = $this.val();

                if (marksNum[inputName] !== inputValue) {
                    if (!diff) {
                        diff = {};
                    }
                    diff[inputName] = inputValue;
                    sendDiff();
                }
            });
		});
	}); 
	}
});
}