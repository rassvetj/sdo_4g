<?php 

    //markup was broked and we must delete all tags exclude ul, a, li
    $menu = strip_tags($this->navigation()->extendedMenu()->renderMenu($this->menu, array('maxDepth' => 1)), '<ul><li><a>');
    // Remove empty blocks
    $preg = preg_split('#<li>\s*</li>#iUm', $menu);

    echo implode("\n", $preg);
