// =============================================================================
//
// Скрипт, реализующий работу переключалки [xv] в marksheet
// Для шаблонов: /application/views/helpers/views/score/3/*
//
// =============================================================================
$(document).delegate('.form-score-ternary .els-icon', 'click', function (event) {

	var $parent = $(this).closest('.hm-score-ternary-icon');
    
    if ($parent.hasClass('hm-score-ternary-icon-readonly')) {
    	return;
    }
	
    var $others = $(this).parent().find('.els-icon.cross, .els-icon.check').not(this),
        $input = $(this).closest('.form-score-ternary').find('input:first'),
        values = window.hm.dict.scaleValues.TERNARY;
    
    if ($(this).hasClass('cross')) {
        $(this).toggleClass('cross-checked');
    } else if ($(this).hasClass('check')) {
        $(this).toggleClass('check-checked');
    }
    if ($(this).hasClass('cross-checked')) {
    	// выключено
        $others.removeClass('check-checked');
        $input.val(values.OFF);
    } else if ($(this).hasClass('check-checked')) {
    	// включено
        $others.removeClass('cross-checked');
        $input.val(values.ON);
    } else {
    	// не выбрано
        $input.val(values.NA);
    }
});
//=============================================================================
//
// Скрипт, реализующий работу переключалки [v] в marksheet
// Для шаблонов: /application/views/helpers/views/score/2/*
//
// =============================================================================
$(function() {
	$('.hm_score_numeric').bind('click keyup blur', function(event) {
		
		var $this = $(this),
			targetId = $this.data('target'),
			value = $this.val();
		
		$('#' + targetId).val(value !== '' ? value : -1);
		
	});
});

//=============================================================================
//
// Скрипт, реализующий работу текстового ввода ячейки marksheet
// Для шаблонов: /application/views/helpers/views/score/2/*
//
//=============================================================================
$(document).delegate('.hm-score-binary-icon', 'click', function(e) {
	
	// галка только для чтения, выходим
	if ($(this).hasClass('hm-score-binary-icon-readonly')) {
		return;
	}
	
	var $input = $(this).parent().find('input:first'),
		BINARY = window.hm.dict.scaleValues.BINARY;
	
	$(this).toggleClass('check-checked');

	if ($input.val() == BINARY.NA) {
		$input.val(BINARY.ON);
	} else {
		$input.val(BINARY.NA);
	}
});