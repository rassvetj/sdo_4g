<?php
class HM_View_Callable_HtmlTree extends HM_View_Callable_Escape {
    private function& getItemFromPath($path, &$all, $item = 'parent', array $meta = array())
    {
        if (empty($path)) {
            return $all;
        }
        if ($item === 'parent') {
            array_pop($path);
        } else if ($item === 'current') {
            // Do nothing
        } else if ($item === 'next') {
            if ($meta['type'] === 'title') {
                array_push($path, array_pop($path) + 1);
            }
        }
        $current = $all;
        foreach ($path as $value) {
            $current = $current[$value];
        }
        return $current;
    }

    private function array_type(&$obj)
    {
        return !is_array($obj)
            ? 'is-not-array'
            : (is_string(key($obj))
                ? 'assoc'
                : 'index');
    }
    
    private $known_keys = array(
        'expand'       => 'boolean',
        'select'       => 'boolean',
        'activate'     => 'boolean',
        'focus'        => 'boolean',
        'hideCheckbox' => 'boolean',
        'isLazy'       => 'boolean',
        'isFolder'     => 'boolean',
        'unselectable' => 'boolean',
        'url'          => 'string',
        'target'       => 'string',
        'key'          => 'string|integer',
        'tooltip'      => 'string',
        'addClass'     => 'string'
    );

    public function call($path, &$current, &$all, array $meta = array())
    {
        $attrs = array();
        $css_classes = array();
        $data = parent::call($path, $current, $all, $meta);
        $tmp_attrs = is_array($data['attrs'])
            ? $data['attrs']
            : array();
        unset($data['attrs']);

        // title will be set only if $meta['type'] ends with 'title'
        if (isset($data['title'])) {
            $title = $data['title'];
            unset($data['title']);
            $parent = $this->getItemFromPath($path, $all, 'parent', $meta);
            $possible_childs = $this->getItemFromPath($path, $all, 'next', $meta);

            if (is_array($current)) {
                foreach ($current as $key => $value) {
                    // skip title
                    if ($key === 'title') { continue; }
                    if (isset($this->known_keys[$key])) {
                        $typesSplit = explode('|', $this->known_keys[$key]);
                        foreach ($typesSplit as $keyType ) {
                            if ( ('integer' == $keyType && is_int($value))
                              || ('boolean' == $keyType && is_bool($value))
                              || ('string'  == $keyType && is_string($value)) ) {
                                $data[$key] = $value;
                                break;
                            }
                        }
                    } else {
                        $data[$key] = is_array($value) ? implode(' ', $value) : $value;
                    }
                }
            }

            if (is_array($possible_childs) && $this->array_type($possible_childs) === 'index') {
                if ($data['isFolder'] !== false) {
                    $css_classes[] = 'folder';
                    $data['isFolder'] = true;
                }
                if (empty($possible_childs) && $data['isLazy'] !== false) {
                    $css_classes[] = 'lazy';
                    $data['isLazy'] = true;
                }
                // don't make item lazy if it is no empty
                if (!empty($possible_childs) && $data['isLazy'] === true) {
                    $data['isLazy'] = false;
                }
            }
            // TODO lazy nodes can't be expanded onload :(
            // TODO if activate property is set on load onActivate is not triggerred :(
            // TODO the same with onSelect, onLazyRead :(
            // TODO if activate is set onload folder can be activated even if clickFolderMode == 2

            if (is_array($current) && isset($current['addClass'])) {
                $addClass = is_array($current['addClass'])
                    ? $current['addClass']
                    : @explode(' ', $current['addClass']);
                $css_classes = array_merge($css_classes, $addClass);
            }
            if (!isset($data['tooltip'])) {
                $filter = new Zend_Filter_StripTags();
                $data['tooltip'] = $filter->filter($title);
            }

            $css_classes = implode(' ', $css_classes);
            $data['addClass']  = $css_classes;

            $attrs['class'] = $css_classes;
            $attrs['title'] = $data['tooltip'];
            $attrs['data'] = Zend_Json::encode($data);

            // TODO: ???
            //if (isset($data['url'])) {
            //    $title = '<a href="' . $this->view->escape($data['url']) . '"' . (isset($data['target']) ? ' target="' . $this->view->escape($data['target']) . '"' : '') . '>' . $title . '</a>';
            //}
            $data['title'] = $title;
        }

        $data['attrs'] = array_merge($tmp_attrs, $attrs);

        return $data;
    }
}