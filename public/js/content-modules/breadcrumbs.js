$(function(){
	$(document).delegate('.breadcrumbs .separator', 'click', function (event) {
		var $cTarget = $(event.currentTarget)
			, $menu
			, id = $cTarget.data('ddid');
		if(id==undefined){
			$menu = $cTarget.next('.dropdown-actions-menu')
			if($menu.length==0) return
			id = _.uniqueId('dropdown-actions-menu-');
			$cTarget.data('ddid', id);
			$menu.attr('id', id).hide();
			$(".breadcrumbs .separator").removeClass("active_separate");
			$(".breadcrumbs .dropdown-actions-menu").hide();
			$menu.data('ddid', id);
			$menu.appendTo('body');
		};
		$menu = $('#' + id);
		$menu.toggle();	
		if($menu.is(':visible')){
			$menu.data('origin', 'main').position({
				my: 'left top',
				at: 'left bottom',
				of: $cTarget,
				offset: '0px 2px',
				collision:"none"
			});
		}
		$cTarget.toggleClass("active_separate");
	})
	$(document).click(function (event) {
	    var $target = $(event.target);
	    if (!$target.closest('.breadcrumbs, .dropdown-actions').length) {
	        $(".dropdown-actions-menu").hide();
	        $(".breadcrumbs .separator").removeClass("active_separate");
	    }
	});	
})