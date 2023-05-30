<?php
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/courses_table.css');
?>
<?php echo $this->headSwitcher(array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'switcher' => 'programm'));?>
<div class="clearfix"></div>
<?php if (count($this->programms)):?>
<?php
    $service = Zend_Registry::get('serviceContainer');
    foreach ($this->programms as $programm) {
        $progress = $service->getService('Programm')->getUserProgress($programm['programm_id'], $this->user->MID);
        echo '<div class="programm">
                <div class="arrow collapsed"></div>
                <div class="title">' . $programm['name'] . '</div>
                <div class="progress">' . $progress . '</div>
                <div class="clearfix"></div>';

        echo '<div class="subjects close"> <div class="description">' . $programm['description'] . '</div>';

        $events = $service->getService('Programm')->getEvents($programm['programm_id']);
        if ($events) {

            $subjectsIds = array();
            foreach ($events as $event) {
                $subjectsIds[] =  $event->item_id;
            }

            if(count($subjectsIds)){
                $serviceClaimant = $service->getService('Claimant');
                $selectClaimants = $serviceClaimant->getSelect();
                $selectClaimants->from(array('c' => 'claimants'),
                    array(
                        'subid' => 'c.CID',
                    )
                )->where('CID IN (?)', $subjectsIds
                )->where(' MID = ?', $this->user->MID
                )->where(' status = ?', HM_Role_ClaimantModel::STATUS_ACCEPTED);
                $claimantSubjects = $selectClaimants->query()->fetchAll();

                $claimantSubjectSIDs = array();
                foreach ($claimantSubjects as $claimantSubject)
                {
                    $subId = $claimantSubject['subid'];
                    $claimantSubjectSIDs[$subId] = $subId;
                }
            }

            foreach ($events as $event) {
                if ($event->type == HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT) {
                    $subject = $service->getService('Subject')->getOne($service->getService('Subject')->findDependence(array('Student', 'Lesson'), $event->item_id));
                    $subject->teachers = $service->getService('Subject')->getTeachers($event->item_id);
                    if ($subject) {
                        if(isset($claimantSubjectSIDs[$subject->subid]))  $subject->isUnsubscribleSubject = true;
                        echo $this->subjectPreview($subject, $this->marks, $this->graduatedList, $this->studentCourseData[$subject->subid], array('isElective' => $event->isElective,'switcher' => 'programm'));
                    }
                }
            }

            echo '</div>';
        }
        echo '</div>';
   }
?>

<?php  else:  ?>
<div class="clearfix"></div>
<div><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>



<style>
    .close {
        display: none;
    }
    .programm .title {
        font-size: 22px;
        font-weight: bold;
        text-decoration: underline;
        color: #5B94BC;
        float: left;
        width: 560px;
    }
    .programm .electiv {
        margin-bottom: 20px;;
    }
    .programm .progress {
        font-size: 18px;
		margin-top: 10px;
        font-weight: bold;
        border:none;
		color: #888;
		width: auto;
    }
    .subjects .description {
        margin: 10px 0 5px;
    }
    .programm {
        border: 1px solid #D6DCE0;
        border-left: 20px solid #D6DCE0;
        padding: 15px;
        width: auto;
        position: relative;
		margin: 15px 0;
    }
    .collapsed {
        background-image: url("../../../../images/icons/expand-down.png");
    }
    .expanded {
        background-image: url("../../../../images/icons/expand-up.png");
    }
    .programm .arrow {
        background-position: 7px 22px;
        background-repeat: no-repeat;
        display: block;
        height: 55px;
        left: -20px;
        position: absolute;
        top: 1px;
        width: 18px;	
    }
</style>
<script>
    $('.arrow').click(function(){
        var el = $(this).parent().children('.subjects'),
            arrow = $(this);
        if (el.hasClass('open')) {
            el.hide();
            el.removeClass('open');
            el.addClass('close');
        } else {
            el.show();
            el.removeClass('close');
            el.addClass('open');
        }
        if (arrow.hasClass('collapsed')) {
            arrow.removeClass('collapsed');
            arrow.addClass('expanded');
        } else {
            arrow.removeClass('expanded');
            arrow.addClass('collapsed');
        }
    })
	$('.subjects:first').show();;
	$('.subjects:first').addClass('open');
	$('.subjects:first').parent().children('.arrow:first').addClass('expanded');
</script>
