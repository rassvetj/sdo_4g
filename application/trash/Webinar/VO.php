<?php
class Webinar_VO 
{
	public function __construct($data = array())
	{
		if (is_array($data) && count($data)) {
			foreach($data as $name => $value) {
				if (property_exists(get_class($this), $name)) {
			        $this->$name = $value;
				}
			}
		}
	}
}