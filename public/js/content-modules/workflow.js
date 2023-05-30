$(function(){
	$(".els-grid").delegate(".grid-workflow","click",function(e){
		$(".workflow").remove();
		var param = "index="+$(this).closest("tr").attr("id")
		$.ajax({
			type: "POST",
			url: "/6884/workflow.php",
			data: param,
			error: function( msg ) {
				console.log( "ERROR: " + msg );
			},
			success:function(msg){
				(function(e){
					var curPos = $(e.currentTarget).position();					
					$('body')
						.append(msg)
					$('.workflow').css({
						left: curPos.left+50+$(e.currentTarget).width(),
						top: curPos.top+100+($('.workflow').height()/2)
					});
				})(e)
			}
		})
	})
	$(window).delegate(".workflow","click",function(e){
		var currentTarget = $(e.target);
		if(currentTarget.is(".wih_title")){
			var desc = currentTarget
							.closest(".workflow_item_head")
							.next(".workflow_item_description")
			if(desc.is(":visible")){
				desc.slideToggle()
				return
			}
			$(".workflow_item_description:visible").slideToggle()
			desc.slideToggle()
			return				
		}else{
			if($(currentTarget).closest(".workflow_item_description:visible").length<1){
				$(".workflow_item_description:visible").slideToggle()
			}
			if(currentTarget.is(".close")||$(currentTarget).closest(".workflow").length<1){
				$(".workflow").remove();
			}			
		}
	})	
})