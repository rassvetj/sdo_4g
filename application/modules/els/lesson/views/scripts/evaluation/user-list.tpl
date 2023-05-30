<?php
echo $this->grid;
if (!$this->isAjaxRequest) {
?>
<br><p><span style="color: red;">*</span> &mdash; В скобках указаны данные с учётом всех оценок</p>
<script>
    $(function(){
        var dialog = $('<div style="background-color: #ffffff;"></div>');
        dialog.dialog({
            autoOpen   : false,
            bgiframe   : true,
            height     : 600,
            width      : '80%',
            modal      : true,
            overlay    : {
                backgroundColor : "#000000",
                opacity         : 0.3
            },
            resizable : false,
            dialogClass: 'reset-color'
        });

        $(document).on('click', '.dialog', function(e){
            e.preventDefault();
            var $this = $(this);
            var href = $this.attr('href');
            var title = $this.attr('title');
            dialog.html('Загрузка...');
            dialog.load(href);
            dialog.dialog('option', 'title', title);
            dialog.dialog('open');
        });

    });
</script>
<?php
}
?>
