<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/content-modules/workflow-bulbs.css') ); ?>
<div class="workflowBulbs grid-workflow" data-workflow_id="<?= $this->model->SID ?>">
    <?php foreach($this->model->getProcess()->getStates() as $key => $state): ?>
    <span class="bulb <?= $state->getClass(); ?>" title="<?= $this->escape($this->statesTypes[get_class($state)]) ?>"></span>
    <?php endforeach; ?>
</div>