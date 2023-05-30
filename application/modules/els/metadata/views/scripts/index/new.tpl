<?php echo $this->partial(
                'index/edit.tpl',
                array(
                    'action' => $this->action,
                    'group' => $this->group,
                    'roles' => $this->roles
                )
)?>