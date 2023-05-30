<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/score.css'); ?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css'); ?>
<style>
	#els-extended-group {
		overflow: auto;
		max-height: 540px;
	}
	
	#els-extended-group table tr.active {	
		background-color: rgb(223, 231, 243)!important;
	}
</style>
<script>
    $(document).ready(function(){
        var filter = $('#els-extended-group-filter');
        var group = $('#els-extended-group');
        var content = $('#els-extended-content');
        var defaultContent = content.html();

        var lastGroupSelect = $.cookie('lastGroupSelect');

        filter.on('change', function(){
            var filterVal = $(this).val();
            $.cookie('lastGroupSelect',filterVal);

            $('input[name="group_id"]').each(function(){
                var $tr = $(this).parents('tr:first');
                content.html(defaultContent);
                group.find('.active').removeClass('active');
                if (filterVal == 'show_all') {
                    $tr.show();
                } else {
                    if ($(this).val().indexOf(','+filterVal+',', 0) >= 0) {
                        $tr.show();
                    } else {
                        $tr.hide();
                    }
                }
            });
        });

        if (lastGroupSelect = $.cookie('lastGroupSelect')) {
            filter.val(lastGroupSelect).trigger('change');
        } else {
            var firstVal = filter.children('option:first').val();
            filter.val(firstVal).trigger('change');
            $.cookie('lastGroupSelect',0);
        }

        $('.els-extended-user-interview').on('click', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            group.find('.active').removeClass('active');
            $(this).parents('tr:first').addClass('active');
            content.html('<p><?=_('Загрузка...')?></p>').load(url);
        });
    });
</script>

<div class="els-extended-users els-scloll">
    <select id="els-extended-group-filter">
        <?php foreach($this->groups as $key => $value) {
            if ($value['new_count'] > 0) {
                echo '<option style="color: red;" value="'.$key.'">'.$value['name'].' (New +'.$value['new_count'].')'.'</option>';
            } else {
                echo '<option style="color: black;" value="'.$key.'">'.$value['name'].'</option>';
            }
        }
        ?>
    </select>

    <div id="els-extended-group">
        <table width="100%">
            <?php foreach($this->users as $user) : ?>
                <tr style="display: none;">
                    <td>
                        <input type="hidden" name="group_id" value="<?php echo ','.implode(',',$user['groups']).','; ?>">
                        <?php echo $user['card']; ?>
                    </td>
                    <td>
                        <a class="els-extended-user-interview" href="<?php echo $user['url']; ?>"><?php echo $user['fio']; ?></a>&nbsp;<?php if ($user['is_new']) echo '<b><sup style="font-size: 0.8em; color: red;">New</sup></b>'; ?>
                        <br/>
                        <b class="els-extended-user-variant"><?php echo $user['interview_title']; ?></b>
                    </td>                   
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
<div id="els-extended-content" style="">
    <div class="els-extended-default">
        <p><?=_('Нет данных для отображения.')?></p><br>
        <p><?=_('Необходимо выбрать пользователя в меню слева.')?>
    </div>
</div>