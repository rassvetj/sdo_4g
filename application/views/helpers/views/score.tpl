<div class="<?php if (!$this->markseetMode):?>form-score-numeric<?php else:?>form-score-marksheet<?php endif?>">
<?php if(intval($this->score)>0):?>
<div class="<?php echo (intval($this->score) > 0 ? 'score_red' : 'score_gray') ?> number_number">
    <span>
        <?php
            if($this->forStudent && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)){
        ?>
                <input
                    tabindex="<?php echo $this->tabindex;?>"
                    id="<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>"
                    name="score[<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>]"
                    type="text"
                    placeholder="<?php echo _("Нет") ?>"
                    value="<?php echo (intval($this->score) > 0 ? $this->score : '') ?>"
                    pattern="<?php echo "^[1-9]{1}\d?$|^0$|^100$";?>"
                >
                <?php if(strlen($this->comments)):?>
                <div class="score-comments" title="<?php echo $this->escape($this->comments);?>"></div>
                <?php endif;?>
                
        <?php } else{ ?>
            <?php echo (intval($this->score) > 0 ? $this->score : _('Нет')) ?>
        <?php } ?>
    </span>
</div>        
<?php else:?>
<div class="score_gray number_number">
    <span align="center">
            <?php
            if($this->forStudent && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)){
            ?>
                <input
                    tabindex="8001"
                    id="<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>"
                    name="score[<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>]"
                    type="text"
                    placeholder="<? echo _("Нет") ?>"
                    pattern="<?php echo "^[1-9]{1}\d?$|^0$|^100$";?>"
                >
            <?php } else{ ?>
                Нет
            <?php
            }
            ?>
    </span>
    <?php
            /*
            if($this->forStudent && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)){
    ?>
    <div class="article-controls">
        <a href="#" class="edit"></a>
    </div>
    <?php
    }
            */
    ?>
</div> 
<?php endif?>                    
</div>
