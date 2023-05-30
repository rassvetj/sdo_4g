<?php
# файловый кэш быстрее, чем Memcached при условии:
# достаточно свободной ОЗУ, т.к.:
# 1. ОС держит в ОЗУ или tmpfs последние и частоиспользуемые файлы кэша
# 2. Доступ к файлам осущесвляется напрямую, в рамках ФС, в то время как Memcached - доступ к времшему серверу с доп. вычислениями и запуском доп.модулей сети.
# 3. В случае исчерпания ОЗУ ФС может выгрузить файловый кэш на диск, тем самым освободив ОЗУ для других процессов.
class HM_Cache_CacheService extends HM_Service_Abstract
{
	private $_cache = NULL;
	
	const TYPE_MEMCACHED = 'Memcached';
	const TYPE_FILE		 = 'File';
	
	
	# TODO - Если не доступен тот или иной тип кэша, переключиться на другой, запасной Метод test есть в Memcached
	public function __construct() {
		parent::__construct();		
		$config			= Zend_Registry::get('config');
        $this->_cache	= Zend_Cache::factory('Core', self::TYPE_MEMCACHED, $config->cache->frontend->toArray(), $config->cache->backend->toArray());		
	}
	
		
	public function remove($id)
	{
		if(!$this->_cache){ return false; }		 
		return $this->_cache->remove($id);
	}
	
	
	public function save($data, $id, $tags = array(), $specificLifetime = false)
	{
		return $this->_cache->save($data, $id, $tags, $specificLifetime);
	}
	
	
	public function load($id, $doNotTestCacheValidity = false)
	{
		return $this->_cache->load($id, $doNotTestCacheValidity);		
	}

}
