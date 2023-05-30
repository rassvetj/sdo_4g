<?php echo $this->form?>
<?php
$this->inlineScript()->captureStart();
?>
$(function(){
	/* взаимоисключение классификаторов */
	var inputs 		= ["#link_types-3","#link_types-4"],
		prcCheckbox	= function(obj,arInput){					
			var current	= "#"+$(obj).attr("id"),
				disab	= _.without(arInput, current),
				attr	= [{"disabled":false},{"checked":false,"disabled":true}],
				act		= ($(obj).is(":checked"))?1:0;
			$(String(disab)).attr(attr[act]);
		};
		
	if($("#classifiersTypes").length==0) return false;

	var isCheck = $.map(inputs,function(chck){ 
		if($(chck).is(":checked")){					
			return chck;
		};
	});
	if(isCheck){
		prcCheckbox($(isCheck[0]),inputs);
	}
	$("#classifiersTypes").delegate(inputs.join(','),"change",function(){
		prcCheckbox(this,inputs);
	})
	
	/* Предупреждение при снятии области применения */
	$('input[name="link_types[]"]').on("change", notice)
	
	function notice(){
		if(!$(this).is(':checked')){
			elsHelpers.alert('При исключении классификатора из обрасти учета, все связи рубрик с субъектами будут удалены!', 'Предупреждение!');
			$('input[name="link_types[]"]').off("change", notice);
		}
	}
	
})


$(document).ready(function(){

})
<?php
$this->inlineScript()->captureEnd();
?>