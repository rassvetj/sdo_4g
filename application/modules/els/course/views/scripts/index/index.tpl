<?php echo $this->partial('_course-hairy-box.tpl', array(
    'tree'              => $this->tree,
    'courseObject'      => $this->courseObject,
    'courses'           => $this->courses,
    'subjectId'         => $this->subjectId,
    'current'           => $this->current,
    'lessonId'          => $this->lessonId,
    'allowEmptyTree'    => $this->allowEmptyTree,
    'itemCurrent'       => $this->itemCurrent,
    'isDegeneratedTree' => $this->isDegeneratedTree
)); ?>