<?php echo $this->form?>
<?php $this->inlineScript()->captureStart();?>
$(function(){
	
	issetDouble($('#external_id').val(), '<?=$this->subject_id;?>');
	
	function issetDouble(external_id, subject_id){
		$('#external_id_error').remove();
		$('#submit').prop( "disabled", false );
		
		$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'ajax', 'action' => 'validate-external-id'))) ) ?>, {
			type: 'POST',
			global: false,
			dataType:'json',
			data: {            
				external_id: external_id,
				subject_id: subject_id,
			}
		}).done(function (data) {		
			_.defer(function () {  				
				if(data){				
					if(data.error){				
						$('#external_id').after('<div id="external_id_error" style="color:red;">'+data.error+'</div>');
						$('#submit').prop( "disabled", true );
						return false;
					} 
				}
				return true;
			});
		}).fail(function () {
			var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#external_id');
			jQuery.ui.errorbox.clear($message);
			$message.errorbox({level: 'error'});
		}).always(function () {
			
		});		
	}
    $('#external_id').change(function(){		
		var external_id = $(this).val();
		var subject_id = '<?=$this->subject_id;?>';
		issetDouble(external_id, subject_id);		
	});
	
	
	
	var oldDate = {
        beginDate: $("#begin").val(),
        endDate: $("#end").val()
    }
    $("#subjects").bind('submit',function(e){
        if($("input[name='period']:checked").val()!=0) return;
        var begin = $('#begin').val(),
            end     = $('#end').val();
        if(oldDate.beginDate!=begin||oldDate.endDate!=end){
            if(!confirm("<?php echo _('При изменении времени обучения автоматически изменятся все даты занятий, которые вышли за окончание курса. Продолжить?')?>")){
                $('#begin').val(oldDate.beginDate);
                $('#end').val(oldDate.endDate);
                return false;
            }
        }
    })

    function updateInputs() {
        if ($('#auto_mark').attr('checked')) {
            val = ($('#scale_id').val() == <?php echo HM_Scale_ScaleModel::TYPE_CONTINUOUS?>)
            $('#formula_id').attr('disabled', !val);
            $('#threshold').attr('disabled', val);    
        } else {
            $('#formula_id').attr('disabled', true);
            $('#threshold').attr('disabled', true);
        }
    }
    
    $('#auto_mark').change(function(){
        updateInputs();
    });
    
    $('#scale_id').change(function(){
        $('#auto_mark').attr('checked', false);
        $('#formula_id').attr('disabled', true);
        $('#threshold').attr('disabled', true);
    }); 
    
    updateInputs();
    
})
<?php $this->inlineScript()->captureEnd();?>