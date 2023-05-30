<?php

require_once $GLOBALS['wwf'].'/lib/sharepoint/Config.class.php';
if (defined(SHAREPOINT_ENABLE)) {
require_once $GLOBALS['wwf'].'/lib/sharepoint/NTLMSoapClient.class.php';
require_once $GLOBALS['wwf'].'/lib/sharepoint/NTLMStream.class.php';
require_once $GLOBALS['wwf'].'/lib/sharepoint/Xml.class.php';
require_once $GLOBALS['wwf'].'/lib/sharepoint/Lists.class.php';
}
?>