<?php if (!$this->gridAjaxRequest):?>
    <?php // #4379 - в master'е не должно быть упоминаний SAP ?>
    <?php //echo $this->Actions('orders', array(array('title' => _('Импорт заявок из SAP'), 'url' => '')));?>
<?php endif;?>

<?php
$this->inlineScript()->captureStart();
?>
	var $select,
		ids=$("#postMassIds_grid").val();
	function sendAjax(param){
		$select.closest("form").find("input[type='submit']").attr("disabled",true);
		$.ajax({
			type: "POST",
			url: "/order/list/check-base/",
			dataType:'json',
			data: "ids="+param,
			success: function(data){
				processdata(data);
			}
		})
		return false;
	}
	function processdata(obj){
		if(obj.status=='data'){
			var opt = '<select name="concrete_subject" id="selectAjax">';
			for(var i in obj.subjects){
				if (obj.subjects.hasOwnProperty(i)) {
					opt+='<option value="'+i+'">'+obj.subjects[i]+'</option>';
				}
			}
			opt+='</select>';
			$(opt).insertAfter($select);
		}
		if(obj.status=='fail'){
			alert(obj.subjects);
            $("#gridAction_grid option").eq(0).attr("selected",true);
		}
        $select.closest("form").find("input[type='submit']").attr("disabled",false);
	}
	function checkIds(){
		if(!!(ids.length < $("#postMassIds_grid").val().length)){
			$("#selectAjax").remove();
			$("#gridAction_grid option").eq(0).attr("selected",true);
		}
	}
	$(document).on("change", "#gridAction_grid", function(e){
		$select = $(this);
		ids = $("#postMassIds_grid").val();
		var sVal = $(this).find("option:selected").val();
		if(sVal.match("/accept-by")){
			sendAjax(ids);
		}else{
			$("#selectAjax").remove();
		}
		e.stopPropagation();
	})
	$($("#gridAction_grid").closest("table")).delegate("input[type='checkbox']","change",function(e){
		checkIds();
	})
<?php
$this->inlineScript()->captureEnd();
?>

<?php echo $this->grid?>

<?= $this->partial('_workflow-script.tpl', array(
    'get_url'       => $this->url(array('action' => 'workflow')),
    'submit_url'    => $this->url(array('action' => 'update-state'))
)); ?>
