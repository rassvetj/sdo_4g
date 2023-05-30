function initMarksheet (settings) {

var l10n = settings.l10n
  , $marksheet
  , $marksheetScoreCells
  , $marksheetInputs
  , marks
  , diff
  , $personCheckboxes
  , $scoreCheckboxes;

// marksheet slider
function slideColumns(columns, value, totalVisibleColumns, current_el) {
	var els      = $('.hm-marksheet-scrollbar-scroller');
	
	var current_el_id = current_el.attr('id');	
	var left 	 	  = $('#'+current_el_id+' .hm-marksheet-scrollbar-scroller').css('left');	
	els.css('left', left);
	
    for (var i = 0; i < columns.length; ++i) {        
		if (i < value || i >= (totalVisibleColumns + value))
            columns[i].addClass('ui-helper-hidden');
        else
            columns[i].removeClass('ui-helper-hidden');
    }
}

function onMarksheetLayoutUpdate(columns, columnWidth) {
	
	renderSlider('marksheet-slider-top', columns, columnWidth);
	renderSlider('marksheet-slider', columns, columnWidth);
	/*
    var totalVisibleColumns = 12
      , debouncedSlideColumns = _.debounce(slideColumns, 5); // calculate here
    
    $('#marksheet-slider').slider('destroy');
    if (totalVisibleColumns >= columns.length) {
        $('#marksheet-slider').closest('tr')
            .addClass('ui-helper-hidden');
        $('#marksheet-slider').closest('td').attr('colspan', columns.length);
    } else {
        $('#marksheet-slider').closest('tr')
            .removeClass('ui-helper-hidden');
        $('#marksheet-slider').closest('td')
            .attr('colspan', totalVisibleColumns);
    }
    slideColumns(columns, 0, totalVisibleColumns);
    
    $('#marksheet-slider').css('width', 'auto');
    
    var unvisibleCount = columns.length - totalVisibleColumns,
    	scrollSize = totalVisibleColumns * 100 / columns.length;
    
    if (scrollSize < 100) {
    	HM.create('hm.module.course.ui.marksheet.ScrollBar', {
    		renderTo: '#marksheet-slider',
    		scrollSize: scrollSize,
    		listeners: {
    			scroll: function (e, value) {
    	            debouncedSlideColumns(columns, Math.round(value * unvisibleCount), totalVisibleColumns);
    	        }
    		}
    	});
    } else {
    	$('#marksheet-slider').hide();
    }
	*/
}

function renderSlider(slider_id, columns, columnWidth){	
	var totalVisibleColumns = 12
      , debouncedSlideColumns = _.debounce(slideColumns, 5); // calculate here
    
	var el = $('#'+slider_id);
	
    el.slider('destroy');
    if (totalVisibleColumns >= columns.length) {
        el.closest('tr')
            .addClass('ui-helper-hidden');
        el.closest('td').attr('colspan', columns.length);
    } else {
        el.closest('tr')
            .removeClass('ui-helper-hidden');
        el.closest('td')
            .attr('colspan', totalVisibleColumns);
    }
    slideColumns(columns, 0, totalVisibleColumns, el);
    
    el.css('width', 'auto');
    
    var unvisibleCount = columns.length - totalVisibleColumns,
    	scrollSize = totalVisibleColumns * 100 / columns.length;
    
    if (scrollSize < 100) {
    	HM.create('hm.module.course.ui.marksheet.ScrollBar', {
    		renderTo: '#'+slider_id,
    		scrollSize: scrollSize,
    		listeners: {
    			scroll: function (e, value) {
    	            debouncedSlideColumns(columns, Math.round(value * unvisibleCount), totalVisibleColumns, el);
    	        }
    		}
    	});
    } else {
    	el.hide();
    }
	
}



$(function () {
    var columnWidth
      , columns = []
      , $allRows
      , $randomRow = $('thead').find('tr:eq(0)').find('td.lesson-cell');

    columnWidth = $randomRow.eq(0).outerWidth();

    for (var i = 0, length = $randomRow.length; i < length; ++i) {
        $allRows || ($allRows = $('thead, tbody').find('tr'));
        columns.push($allRows.find('td.lesson-cell:eq('+ i +')'));
    }

    onMarksheetLayoutUpdate(columns, columnWidth);
    $(window).add(document).bind('resize.marksheet-layout', _.debounce(function () {
        //log('triggering relayout'); 
    }, 300));
});

function initializeCommentsDialog ($cells) {
    var buttons = {};
    buttons[l10n.save] = function () {
        var $currentCell = $cells.filter('.ui-selected'),
            $currentInput = $currentCell.find("input[name^='score[']"),
            scoreName = $currentInput.attr('name'),
            data = $currentInput
                   .add('#textComment')
                   .serialize()
          , value = $.trim($('#textComment').val());
        $('#textComment').val(value);
        $currentCell.each(function () {
                var $this = $(this)
                  , $comments = $this.find('.score-comments');
                if ($comments.length) {
                    if (!value) {
                        $comments.remove();
                    } else {
                        $comments.attr('title', $('#textComment').val());
                    }
                } else if (value) {
                    $this
                        .children('div:first')
                        .append('<div class="score-comments" title="'+ value +'"></div>');
                }
            });
        $.post(settings.url.comments, data);

        $(this).dialog('close');
    };
    // Dialog with textarea for comments
    $( "#marksheet-comment-dialog" ).dialog({
        autoOpen: false,
        resizeable: false,
        width: 300,
        modal: true,
        buttons: buttons
    });
    $('#commentButton').click(function () {
        var comments = _.map($cells.filter('.ui-selected').find('.score-comments').get(), function (comment) {
            return comment.getAttribute('title') || '';
        });
        comments = _.uniq(_.without(comments, '', null));
        $('#textComment').val(comments.join('\n'));
        $("#marksheet-comment-dialog").dialog('open');
    });
}
function initializeMarksheetActions ($personCheckboxes, $scoreCheckboxes) {
    var buttons = {};
    buttons[l10n.ok] = function () {
        $( this ).dialog( "close" );
    }
    // marksheet actions
    $('#StudentButton, #scheduleButton').click(function (event) {
        var $this = $(this)
          , data = $this.is('#StudentButton')
                   ? $personCheckboxes.serialize()
                   : $scoreCheckboxes.serialize()
          , url = $this.is('#StudentButton')
                  ? $('#studentMassAction').val()
                  : $('#scheduleMassAction').val()
          , message
          , dialogId
          , $dialog
          , buttonsConfirm = {};

        buttonsConfirm[l10n.yes] = function () {
            $.ajax({
                type: 'POST',
                data: data,
                url: $this.is('#StudentButton') ? $('#studentMassAction').val() : $('#scheduleMassAction').val(),
                complete: function () {
                    document.location.reload();
                }
            });
            $( this ).dialog( "close" );
        }
        buttonsConfirm[l10n.no] = function () {
            $( this ).dialog( "close" );
        }

        if (!data || url == 'none' || !url) {
            dialogId = this.id + (data ? '-nourl' : '') + '-dialog';
            $dialog = $('#'+dialogId);
            if (!$dialog.length) {
                message = $this.is('#StudentButton')
                    ? data
                        ? l10n.noStudentActionSelected
                        : l10n.noStudentSelected
                    : data
                        ? l10n.noLessonActionSelected
                        : l10n.noLessonSelected;
                $dialog = $('<div id="'+ dialogId +'" title="'+ l10n.formError +'">'+ message +'</div>')
                    .hide()
                    .appendTo('body')
                    .dialog({
                        resizable: false,
                        autoOpen: false,
                        height: 140,
                        modal: true,
                        buttons: buttons
                    });
            }
            $dialog.dialog('open');
            return;
        }
        if (!$("#confirm-dialog").length) {
            $( '<div id="confirm-dialog" title="'+ l10n.confirm +'">'+ l10n.areUShure +'</div>' )
                .hide()
                .appendTo('body')
                .dialog({
                    resizable: false,
                    autoOpen: false,
                    height: 140,
                    modal: true
                });
        }
        $("#confirm-dialog")
            .dialog('option', 'buttons', buttonsConfirm)
            .dialog('open');
    });
}
function initializeCellSelection ($marksheet, $cells) {
    var updateCommentButton = function () {
        $('#commentButton').button();
        if ($cells.filter('.ui-selected').length) {
            $('#commentButton').button('enable');
        } else {
            $('#commentButton').button('disable');
        }
    };
    $(document).delegate('#marksheet tbody td.score-cell', 'mouseup', function (event) {
        var $target = $(event.currentTarget);
        if ($target.is('.no-score')) { return; }
        if (!event.ctrlKey) {
            $cells.not($target).removeClass('ui-selected');
        }
        $target.toggleClass('ui-selected');
        updateCommentButton();
    });
    $marksheet.mouseup(function (event) {
        //if (!$(event.target).closest('td.score-cell').length && !$(event.target).is('#commentButton') && !$(event.target).closest('#commentButton').length) {
        //    $cells.removeClass('ui-selected');
        //    updateCommentButton();
        //}
    });
}

$(function () {
    $marksheet = $('#marksheet');
    $marksheetScoreCells = $marksheet.find('tbody td.score-cell');
    $marksheetInputs = $marksheetScoreCells.find("input[name^='score[']");
    marks = $marksheetInputs.serializeArray();
    $personCheckboxes = $marksheet.find('tbody td.marksheet-rowcheckbox').children('input[type="checkbox"]');
    $scoreCheckboxes  = $marksheet.find('tr.marksheet-head > td.score-cell').find('input[type="checkbox"]');

    // get initial marks
    marks = _.reduce(marks, function (memo, item) {
        memo[item.name] = item.value;
        return memo;
    }, {});

    initializeCommentsDialog($marksheetScoreCells);
    initializeMarksheetActions($personCheckboxes, $scoreCheckboxes);
    initializeCellSelection($marksheet, $marksheetScoreCells);
});

yepnope({
    test: Modernizr.input.pattern,
    nope: '/js/lib/polyfills/h5f.js',
    complete: function () { _.defer(function () {
        $(function () {
            window.H5F && H5F.setup($("#marksheet-form").get(0), { invalidClass: "invalid" });

            var diff = null,
                sendDiff = _.debounce(function() {
                    if (!diff) {
                        return;
                    }
                    marks = _.extend(marks, diff);
                    $.post(settings.url.score, diff);
                    diff = null;
                }, 500);

            // marksheet score submission
            $(document).delegate('#marksheet', 'keyup click', function (event) {

                $marksheetInputs.each(function (index) {

                    var $this = $marksheetInputs.eq(index),
                        mark,
                        isValid = !!this.checkValidity && this.checkValidity(),
                        inputName = $this.attr('name'),
                        inputValue = $this.val();

                    if (isValid && marks[inputName] !== inputValue) {

                        if (!diff) {
                            diff = {};
                        }

                        diff[inputName] = inputValue;
                    }
                });

                if (diff) {
                    sendDiff();
                }
            });
        });
    }); }
});
	$(function(){
		var checkAll = $("th.first-cell").find(":checkbox")[0]
		checkAll.onchange=function(){
			iCheck=$(this).closest("#marksheet").find("td:first-child :checkbox")
			if(this.checked==true){
				iCheck.attr("checked","checked")
			}else{
				iCheck.removeAttr("checked")
			}
		}
        if($(".ie7 .cell430").length>0){
            $(".cell430").append("<div class='test' style='width:385px;'></div>");
        }
	})
}
$(function(){
    if($("html").is(".ie7")){
        var arOpt = $(".filterSelect select option");
        $.each(arOpt,function(i){
            $(arOpt[i]).attr("title",$(arOpt[i]).text());
        })
    }    
})
