<?php if (!empty($this->videos)){ ?>
   <?php echo _('Выберите ролик')?>:&nbsp;<select id="screencast-select" name="screencast" style="width:200px;">
        <option value="0">&nbsp</option>
        <?foreach ($this->videos as $key => $value):?>
        <option value="<?php echo $key?>" <? echo ($this->screencast == $key) ? 'selected' : ''; ?>><?php echo $value['name']; ?></option>
        <?endforeach;?>
    </select>
    <br><br>
       <div id="video-container" style='text-align:center;'>

</div>

   <script language="JavaScript">
         $("#screencast-select").on('change', function(event){
             result = $.ajax({
                url:		'video/list/get-video',
                type:		'POST',
                data:		{
                    screencast: $(this).val()
                },
                dataType: 	'html',
                success: 	function(data) {
                    if (data) {
                        $('#video-container').html(data);
                    }
                }
             });
         });
    </script>

<?php }else{ ?>
    <div align="center"><?php echo _('Отсутствуют данные для отображения')?></div>
<?php } ?>
<?php if ($this->showEditLink):?>
<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_DEAN, HM_Role_RoleModelAbstract::ROLE_ADMIN))):?>
<div class="bottom-links">
    <hr />
    <a href="<?php echo $this->baseUrl($this->url(array('module' => 'video', 'controller' => 'list', 'action' => 'index')))?>"><?php echo _('Редактировать видеоролики')?></a>
</div>
<?php endif;?>
<?php endif;?>