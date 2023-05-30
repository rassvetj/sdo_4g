<?php echo $this->grid; ?>
<script type="text/javascript">
    
    $(function(){
        $(".notify-switcher").buttonset();
        $(".last-cell").hide();
        $(".filters_tr").hide();
        
        $(".notify-switcher .radio").on('change', function(ev){
            var p = $(this).parent();
            var evType = p.data('evtype');
            var notifyType = p.data('notifytype');
            var isActive = parseInt($(this).val());
            console.log(isActive);
            $.ajax({
                url:'/es/notifies/update/',
                type:'get',
                data:'notify_type_id='+notifyType+'&event_type_id='+evType+'&is_active='+isActive,
                success:function(data){
//                    console.log(data);
                },
                error: function() {
                    
                }
            });
        });
        
    });
    
</script>
<script>
	$( document ).ready(function() {		
		$('table label.ui-corner-right').addClass('user-right-active');	
		$('table label.ui-corner-left').addClass('user-left-active');		
		$('table label .ui-button-text').addClass('user-button-text');			
	});
</script>