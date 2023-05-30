<div class="workflow">
    <span class="close"></span>
    <div class="workflow_header">
        <h3><?= $this->model->getName();?></h3>
    </div>
    <div class="workflow_list">
        <?php foreach($this->model->getProcess()->getStates() as $key => $state): ?>
        <div class="workflow_item <?php echo $state->getClass(); ?>">
            <div class="workflow_item_head clearfix">
                <div class="wih_icon"></div>
                <div class="wih_title">
                    <span><?php echo $this->statesTypes[get_class($state)]; ?></span>
                    <?php /* <div class="wih_time">12.03.2012</div> */ ?>
                </div>
            </div>
            <div class="workflow_item_description clearfix">
                <?php if($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED || $state->getStatus() == HM_State_Abstract::STATE_STATUS_PASSED){ ?>
                <div class="wid_deadline">
                    <div class="wid_d_full"><?php /*
                        <div class="wid_d_full_se">
                             Начало:<br>
                             11.11.2011<br>
                            <span>17:24</span>
                        </div>
                        <div class="wid_d_full_se">
                             Окончание:<br>
                             11.11.2011<br>
                            <span>17:24</span>
                        </div>
                        <div class="wid_d_clear">
                        </div>
                        <div class="wid_d_full_desc complete">
                            <div>
                                Выполнено в срок
                            </div>
                            <div class="wid_d_full_desc_time">
                                 31.11.2011 в <span>17:23</span>
                            </div>
                        </div> */ ?>
                        <?php $class = ($state->getStatus() == HM_State_Abstract::STATE_STATUS_FAILED) ? 'not' : 'complete'; ?>
                        <div class="wid_d_full_desc <?php echo $class; ?>">
                            <div><?= _('Результат') ?></div>
                            <div class="wid_d_full_desc_time"><?php echo $state->getResultMessage();?></div>
                        </div>
                        <?php /*
                        <div class="wid_d_full_desc not">
                            <div>Не выполнено</div>
                        </div> */ ?>
                    </div>
                </div>
                <?php } ?>
                <div class="wid_text">
                    <?php $description = trim($state->getDescription()); ?>
                    <?php if(!empty($description)): ?>
                        <?php echo($state->getDescription()); ?>
                    <?php endif; ?>
                    <?php if($state->getStatus() != HM_State_Abstract::STATE_STATUS_WAITING): // Выводим описание и возможные действия ?>
                    <?php if(!empty($description)): ?><hr><?php endif; ?>
                    <div class="wid_control_link">
                        <?php $workflowHelper = $this->getHelper("Workflow");
                        echo($workflowHelper->renderStatesList($state->getActions()));?>

                        <?php if($state->getStatus() == HM_State_Abstract::STATE_STATUS_PASSED){
                            echo $state->getCompleteMessage();
                        } ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach;?>
    </div>
</div>    