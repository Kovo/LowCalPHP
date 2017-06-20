<?php
global $LOWCAL_CONFIG_ARRAY;

$LOWCAL_CONFIG_ARRAY['LOGS_DIR'] = $LOWCAL_CONFIG_ARRAY['BASE_DIR'].'LOGS'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'] = $LOWCAL_CONFIG_ARRAY['BASE_DIR'].'Resources'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['VIEWS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'views'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['TRANSLATIONS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'translations'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['CSS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'css'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['JS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'js'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['IMAGES_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'images'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['PLUGINS_DIR'] = $LOWCAL_CONFIG_ARRAY['BASE_DIR'].'PzPHP'.DIRECTORY_SEPARATOR.'Plugin'.DIRECTORY_SEPARATOR;

$LOWCAL_CONFIG_ARRAY['OUTPUT_COMPRESSION'] = false;
$LOWCAL_CONFIG_ARRAY['OUTPUT_BUFFERING'] = false;

$LOWCAL_CONFIG_ARRAY['DOMAIN_PROTECTION'] = false;
$LOWCAL_CONFIG_ARRAY['DOMAIN_ALLOWED_DOMAINS'] = '';
$LOWCAL_CONFIG_ARRAY['DOMAIN_SOLUTION'] = array('type' => '', 'value' => '');

$LOWCAL_CONFIG_ARRAY['SECURITY_HASH_TABLE'] = array();
$LOWCAL_CONFIG_ARRAY['SECURITY_SALT'] = 'q!a<.r]_d#B^M#@^|>2x =<7r)t%M%y@X]8mK3b+9:e86.*6;|diL#&^|o$Ovu#K*Y>';
$LOWCAL_CONFIG_ARRAY['SECURITY_POISON_CONSTRAINTS'] = array();
$LOWCAL_CONFIG_ARRAY['SECURITY_REHASH_DEPTH'] = 1024;
$LOWCAL_CONFIG_ARRAY['SECURITY_CHECKSUM'] = '';

$LOWCAL_CONFIG_ARRAY['CACHE_MODE_SHARED_MEMORY'] = 1;
$LOWCAL_CONFIG_ARRAY['CACHE_MODE_MEMCACHED'] = 2;
$LOWCAL_CONFIG_ARRAY['CACHE_MODE_LOCALCACHE'] = 3;

$LOWCAL_CONFIG_ARRAY['DATABASE_MYSQLI'] = 1;
$LOWCAL_CONFIG_ARRAY['DATABASE_COUCHBASE'] = 2;
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO'] = 3;
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_CUBRID'] = 'cubrid';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_MSSQL'] = 'mssql';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_SYBASE'] = 'sybase';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_DBLIB'] = 'dblib';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_FIREBIRD'] = 'firebird';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_IBM'] = 'ibm';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_INFORMIX'] = 'informix';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_MYSQL'] = 'mysql';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_SQLSRV'] = 'sqlsrv';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_ORACLE'] = 'oci';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_ODBC'] = 'odbc';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_ODBC_IBMDB2'] = 'odbcibmdb2';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_ODBC_MSACCSS'] = 'odbcmsaccss';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_POSTGRESQL'] = 'pgsql';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_SQLITE'] = 'sqlite';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_SQLITE2'] = 'sqlite2';
$LOWCAL_CONFIG_ARRAY['DATABASE_PDO_4D'] = '4d';

$LOWCAL_CONFIG_ARRAY['SETTING_DB_CONNECT_RETRY_ATTEMPTS'] = 1;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'] = 2;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_DELAY_SECONDS'] = 0.3;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_DELAY_SECONDS'] = 0.5;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_RETRIES'] = 3;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_RETRIES'] = 6;

$LOWCAL_CONFIG_ARRAY['SETTING_CACHE_CONNECT_RETRY_ATTEMPTS'] = 1;
$LOWCAL_CONFIG_ARRAY['SETTING_CACHE_CONNECT_RETRY_DELAY_SECONDS'] = 2;
$LOWCAL_CONFIG_ARRAY['SETTING_CACHE_LOCK_EXPIRE_TIME_SECONDS'] = 15;