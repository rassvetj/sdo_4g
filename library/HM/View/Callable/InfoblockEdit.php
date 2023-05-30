<?php
class HM_View_Callable_InfoblockEdit extends HM_View_Callable_Escape {
     public function call($path, &$current, &$all, array $meta = array())
     {
         $data = parent::call($path, $current, $all, $meta);
         if (isset($data['title']) && is_array($current)) {
             foreach ($current as $key => $value) {
                 if ($key === 'title') {
                     continue;
                 }
                 if ($key === 'description') {
                     $data['attrs']['data-' . $key] = $value;
                 } else {
                     $data['attrs'][$key] = $value;
                 }
             }
         }
         return $data;
     }
}
