<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 John Griffin                                      |
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU Lesser General Public           |
// | License as published by the Free Software Foundation; either         |
// | version 2.1 of the License, or (at your option) any later version.   |
// |                                                                      |
// | This library is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the Free Software  |
// | Foundation, Inc., 59 Temple Place, Suite 330,Boston,MA 02111-1307 USA|
// +----------------------------------------------------------------------+
// | Authors: John Griffin <jgriffin316@netscape.net>                     |
// +----------------------------------------------------------------------+
//
// $Id: Dialect_MySQL.php,v 1.2.2.2 2009/10/29 08:34:13 cvsup Exp $
//

// define tokens accepted by the SQL dialect.
$dialect = array(

'commands'=>array('alter','create','drop','select','delete','insert','update','use','set','type','auto_increment'),

'operators'=>array('=','<>','<','<=','>','>=','like','clike','slike','not','is','in','between'),

'types'=>array('character','char','varchar','nchar','bit','numeric','decimal','dec','integer','int','smallint','mediumint','bigint','tinyint','float','real','double','date','datetime', 'time','timestamp','year','interval','bool','boolean','set','enum','text','tinytext','mediumtext','longtext','blob','tinyblob','mediumblob','longblob'),

'conjunctions'=>array('by','as','on','into','from','where','with'),

'functions'=>array('avg','count','max','min','sum','nextval','currval','concat'),

'reserved'=>array('auto_increment', 'absolute','action','add','all','allocate','and','any','are','asc','ascending','assertion','at','authorization','begin','bigint', 'binary', 'bit_length','both','cascade','cascaded','case','cast','catalog','char_length','character_length','check','close','coalesce','collate','collation','column','commit','connect','connection','constraint','constraints','continue','convert','corresponding','cross','current','current_date','current_time','current_timestamp','current_user','cursor','database','day','deallocate','declare','default','deferrable','deferred','desc','descending','describe','descriptor','diagnostics','disconnect','distinct','domain','else','end','end-exec','escape','except','exception','exec','execute','exists','external','extract','false','fetch','first','for','foreign','found','full','get','global','go','goto','grant','group','having','hour','identity','immediate','indicator','initially','inner','input','insensitive','intersect','isolation','join','key','language','last','leading','left','level','limit','local','lower','match','minute','module','month','names','national','natural','next','no','null','nullif','octet_length','of','only','open','option','or','order','outer','output','overlaps','pad','partial','position','precision','prepare','preserve','primary','prior','privileges','procedure','public','read','references','relative','restrict','revoke','right','rollback','rows','schema','scroll','second','section','session','session_user','size','some','space','sql','sqlcode','sqlerror','sqlstate','substring','system_user','table','temporary','then','timezone_hour','timezone_minute','to','trailing','transaction','translate','translation','trim','true','union','unique','unknown','unsigned','upper','usage','user','using','value','values','varying','view','when','whenever','work','write','year','zone','eoc'),

'synonyms'=>array('decimal'=>'numeric','dec'=>'numeric','numeric'=>'numeric','float'=>'float','real'=>'real','double'=>'real','int'=>'int','integer'=>'int','tinyint'=>'int','smallint'=>'int','mediumint'=>'int','bigint'=>'int','interval'=>'interval','timestamp'=>'timestamp','bool'=>'bool','boolean'=>'bool','set'=>'set','enum'=>'enum','text'=>'text','longtext'=>'text','tinytext'=>'text','mediumtext'=>'text','longtext'=>'text','char'=>'char','character'=>'char','varchar'=>'varchar','ascending'=>'asc','asc'=>'asc','descending'=>'desc','desc'=>'desc','date'=>'date','time'=>'time','datetime'=>'datetime','blob'=>'blob','tinyblob'=>'blob','mediumblob'=>'blob','longblob'=>'blob')
);
?>
