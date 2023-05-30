<div class="bcr_row tpad_<?php echo $this->item->level?>">
    <div class="bcr_r_title"><?php echo $this->item->title;?></div>
    <div class="bcr_row_data">
	    <div class="bcr_r_num brn_<?php echo $this->classes[$this->result->status];?>"><?php echo $this->result->score;?></div>
	    <?php if($this->item->module > 0){?>
	        <div class="bcr_r_visual">
	            <div class="bcr_diagram">
	                <div title="<?php echo HM_Scorm_Track_Data_DataModel::getStatus($this->result->status);?>" class="fill bcr_diagram_<?php echo $this->classes[$this->result->status];?>" style="<?php if(in_array($this->result->status, array(HM_Scorm_Track_Data_DataModel::STATUS_COMPLETED, HM_Scorm_Track_Data_DataModel::STATUS_FAILED, HM_Scorm_Track_Data_DataModel::STATUS_PASSED))):?>width: <?php echo $this->result->percentProgress;?>%; <?php endif;?>">&nbsp;</div>
	            </div>
	        </div>
	    <?php }?>
	    <div class="bcr_r_success">
	        <span class="rsuccess rs_<?php echo $this->classes[$this->result->status];?>"></span>
	    </div>
    </div>
</div>