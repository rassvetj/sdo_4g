<div class="hm-page-support">
    <div class="hm-page-support-header">
        <div class="hm-page-support-header-support">
            <span class="hm-page-support-header-title">
                <span class="hm-page-support-header-img"></span>
                <a href="#"><?php echo _('Техническая поддержка'); ?></a>
            </span>
        </div>
    </div>
    <div class="hm-page-support-content" id="hm-page-support-content">
        <?php echo _('Загрузка');?>
    </div>
</div>
<?php $this->inlineScript()->captureStart(); ?>
$( document ).ready(function() {

    $('.hm-page-support-header-title > a').on('click', function(e) {
        e.preventDefault();
       
        $( ".hm-page-support-content" ).animate({
            height: "toggle",
            width: "toggle",
        }, 400 );
        
        $('.hm-page-support').toggleClass('hm-page-support-opened');
    });

    $(document).on('click', '#cancel-request-form', function(e) {
        $( ".hm-page-support-content" ).animate({
            height: "toggle",
            width: "toggle",
        }, 400 );
        
        $('.hm-page-support').toggleClass('hm-page-support-opened');
    });
    
    $(document).on('click', '#submit-request-form', function(e) {
        jQuery.ajax({
            url: '<?php echo $this->url(array('module' => 'techsupport', 'controller' => 'ajax', 'action' => 'post-request'));?>',
            type: 'POST',
            dataType: 'html',
            data: jQuery('#request-form').serialize(), 
            success: function(response) {
                document.getElementById('request-result').innerHTML = response;
                $('.request-form').hide();
            },
            error: function(response) {
                document.getElementById('request-result').innerHTML = "Ошибка при отправке формы";
            }
            
        });
    });
    
    function loadData() {
        $.post(
            '<?php echo $this->url(array('module' => 'techsupport', 'controller' => 'ajax', 'action' => 'get-form'));?>',
            {},
            function(data) {
                $('#hm-page-support-content').html(data);
            }
        );
    }

    loadData();
});
<?php $this->inlineScript()->captureEnd(); ?>