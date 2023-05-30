$(function(){

	$('#screencastBlock select').change(function(){
		if($(this).val()=="0") {
			$(".flv-container").remove()
			return false
		}
		result = $.ajax({
			url:		'infoblock/screencast/get-screencast',
			type:		'POST',
			data:		{
				screencast: $(this).val()
			},
			dataType: 	'html',
			success: 	function(data) {
				if (data) {
					//$('#screencastBlock div.container').html(data);
					
					loadFlv(data)
				}
			}
		});
	});
	

	var cHeight = parseInt($("#screencastBlock div.container").width()/1.3,10)
	$("#screencastBlock div.container").css({height:cHeight})

	$(document).on("click", ".flv-container", function(){flv_switch()})

	function loadFlv(data){
		if(!!!$(".flv-container").length)
			$('body').append('<div class="flv-container unexpand"><div class="flv_view_switch"></div></div>')
		$(".flv-container object").remove()
		$(".flv-container").append(data)
	
		$(".flv-container embed").attr("wmode","opaque")
		onLoadFlvContainer()
		$(window).bind("resize",function(){
			onLoadFlvContainer()
		})
	}
	function onLoadFlvContainer(){
		var containerOffset = $("#screencastBlock div.container").offset()
		var containerWidth = $("#screencastBlock div.container").width()
		var cHeight = parseInt(containerWidth/1.3,10)
		var containerHeight = cHeight	
		$("#screencastBlock div.container").height(cHeight)
		$(".flv-container object")
			.attr("height",containerHeight)
			.attr("width",containerWidth)	
		$(".flv-container embed")
			.attr("height",containerHeight)
			.attr("width",containerWidth)
		$(".flv-container").css({
		    top:containerOffset.top,
		    left:containerOffset.left,
		    width: containerWidth,
		    height: containerHeight
		})	
		$(".flv_view_switch").css({
		    top:"-33px",
		    left:containerWidth-32
		})	
		if($(".flv-container").is(".expand")){
			$(".flv-container")
				.removeClass("expand")
				.addClass("unexpand")
		}	
	}
	function flv_switch(){
		var flv_container = $(".flv-container")
	
		if(flv_container.is(".expand")){
			flv_container
				.removeClass("expand")
				.addClass("unexpand")
			onLoadFlvContainer()
		}else{		
			flv_container
				.removeClass("unexpand")
				.addClass("expand")
			
			var width =$(window).width()-100
			var height = $(window).height()
			$(".flv-container object")
				.attr("width",width)
				.attr("height",height)
			$(".flv-container embed")
				.attr("width",width)
				.attr("height",height)
			flv_container.css({
				width:width+100,
				height:$(document).height(),
				top:0,
				left:0
			})
			$(".flv_view_switch").css({
			    left:$(window).width()-33,
			    top:0
			})
			$("html,body").scrollTop(0)		
		}
	}

});