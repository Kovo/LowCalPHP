<?php
global $LOWCAL_CONFIG_ARRAY;
/*
 *
 * Framework Specific configurations
 *
 */
/*
 * Directory Configurations
 */
$LOWCAL_CONFIG_ARRAY['LOGS_DIR'] = $LOWCAL_CONFIG_ARRAY['BASE_DIR'].'LOGS'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['LOWCAL_DIR'] = $LOWCAL_CONFIG_ARRAY['BASE_DIR'].'LowCal'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'] = $LOWCAL_CONFIG_ARRAY['LOWCAL_DIR'].'Resources'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['VIEWS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'views'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['MAIL_TEMPLATES_DIR'] = $LOWCAL_CONFIG_ARRAY['VIEWS_DIR'].'mail'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['TRANSLATIONS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'translations'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['CSS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'css'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['JS_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'js'.DIRECTORY_SEPARATOR;
$LOWCAL_CONFIG_ARRAY['IMAGES_DIR'] = $LOWCAL_CONFIG_ARRAY['RESOURCES_DIR'].'images'.DIRECTORY_SEPARATOR;

/*
 * View engine configurations
 */
$LOWCAL_CONFIG_ARRAY['VIEW_ENGINE_PHP'] = 1;

$LOWCAL_CONFIG_ARRAY['VIEW_ACTIVE_ENGINE'] = $LOWCAL_CONFIG_ARRAY['VIEW_ENGINE_PHP'];

/*
 * Client-site render configurations
 */
$LOWCAL_CONFIG_ARRAY['OUTPUT_COMPRESSION'] = false;
$LOWCAL_CONFIG_ARRAY['OUTPUT_BUFFERING'] = false;

/*
 * Domain protection configurations
 */
$LOWCAL_CONFIG_ARRAY['DOMAIN_PROTECTION'] = false;
$LOWCAL_CONFIG_ARRAY['DOMAIN_ALLOWED_DOMAINS'] = '';
$LOWCAL_CONFIG_ARRAY['DOMAIN_SOLUTION'] = array('type' => '', 'value' => '');

/*
 * Crypto configurations
 */
$LOWCAL_CONFIG_ARRAY['SECURITY_HASH_TABLE'] = array();
$LOWCAL_CONFIG_ARRAY['SECURITY_SALT'] = 'q!a<.r]_d#B^M#@^|>2x =<7r)t%M%y@X]8mK3b+9:e86.*6;|diL#&^|o$Ovu#K*Y>';
$LOWCAL_CONFIG_ARRAY['SECURITY_POISON_CONSTRAINTS'] = array();
$LOWCAL_CONFIG_ARRAY['SECURITY_REHASH_DEPTH'] = 1024;
$LOWCAL_CONFIG_ARRAY['SECURITY_CHECKSUM'] = '';

/*
 * Cache system configurations
 */
$LOWCAL_CONFIG_ARRAY['CACHE_TYPE_LOCAL'] = 1;
$LOWCAL_CONFIG_ARRAY['CACHE_TYPE_MEMCACHED'] = 2;
$LOWCAL_CONFIG_ARRAY['CACHE_TYPE_COUCHBASE'] = 3;

$LOWCAL_CONFIG_ARRAY['CACHE_SELECTED_TYPE'] = $LOWCAL_CONFIG_ARRAY['CACHE_TYPE_LOCAL'];

$LOWCAL_CONFIG_ARRAY['SETTING_CACHE_CONNECT_RETRY_ATTEMPTS'] = 1;
$LOWCAL_CONFIG_ARRAY['SETTING_CACHE_CONNECT_RETRY_DELAY_SECONDS'] = 2;
$LOWCAL_CONFIG_ARRAY['SETTING_CACHE_LOCK_EXPIRE_TIME_SECONDS'] = 15;

$LOWCAL_CONFIG_ARRAY['SETTING_CACHE_COUCHBASE_CONNECTION_CONFIGURATION_STRING'] = '';

/*
 * Database system configurations
 */
$LOWCAL_CONFIG_ARRAY['DATABASE_TYPE_MYSQLI'] = 1;
$LOWCAL_CONFIG_ARRAY['DATABASE_TYPE_COUCHBASE'] = 2;

$LOWCAL_CONFIG_ARRAY['DATABASE_SELECTED_TYPE'] = $LOWCAL_CONFIG_ARRAY['DATABASE_TYPE_MYSQLI'];

$LOWCAL_CONFIG_ARRAY['SETTING_DB_CONNECT_RETRY_ATTEMPTS'] = 1;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_CONNECT_RETRY_DELAY_SECONDS'] = 2;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_DELAY_SECONDS'] = 0.3;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_DELAY_SECONDS'] = 0.5;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_FIRST_INTERVAL_RETRIES'] = 3;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_WRITE_RETRY_SECOND_INTERVAL_RETRIES'] = 6;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_TIMEOUT_RETRIES'] = 2;
$LOWCAL_CONFIG_ARRAY['SETTING_DB_LOG_QUERIES'] = false;

$LOWCAL_CONFIG_ARRAY['SETTING_DB_COUCHBASE_CONNECTION_CONFIGURATION_STRING'] = '';

$LOWCAL_CONFIG_ARRAY['SETTING_DB_32BIT'] = false;

/*
 *
 * App Specific configurations
 *
 */
$LOWCAL_CONFIG_ARRAY['APP_NAME'] = 'LowCal';

/*
 * URLS
 */
$LOWCAL_CONFIG_ARRAY['APP_ROOT_URL'] = '';
$LOWCAL_CONFIG_ARRAY['APP_ROOT_URI'] = '';
$LOWCAL_CONFIG_ARRAY['APP_COOKIE_URL'] = '';
$LOWCAL_CONFIG_ARRAY['APP_CSS_URL'] = $LOWCAL_CONFIG_ARRAY['APP_ROOT_URL'].'Resources/static/css/';
$LOWCAL_CONFIG_ARRAY['APP_JS_URL'] = $LOWCAL_CONFIG_ARRAY['APP_ROOT_URL'].'Resources/static/js/';
$LOWCAL_CONFIG_ARRAY['APP_IMG_URL'] = $LOWCAL_CONFIG_ARRAY['APP_ROOT_URL'].'Resources/static/images/';
$LOWCAL_CONFIG_ARRAY['APP_FONT_URL'] = $LOWCAL_CONFIG_ARRAY['APP_ROOT_URL'].'Resources/static/fonts/';

/*
 * DB Access Info
 */
$LOWCAL_CONFIG_ARRAY['APP_DB_USER'] = '';
$LOWCAL_CONFIG_ARRAY['APP_DB_PASSWORD'] = '';
$LOWCAL_CONFIG_ARRAY['APP_DB_NAME'] = '';
$LOWCAL_CONFIG_ARRAY['APP_DB_HOST'] = '';
$LOWCAL_CONFIG_ARRAY['APP_DB_PORT'] = 3306;

/*
 * LDAP Access Info
 */
$LOWCAL_CONFIG_ARRAY['APP_LDAP_DN'] = '';
$LOWCAL_CONFIG_ARRAY['APP_LDAP_HOST'] = '';
$LOWCAL_CONFIG_ARRAY['APP_LDAP_PORT'] = '';

/*
 * MailInfo
 */
$LOWCAL_CONFIG_ARRAY['APP_MAIL_ENABLE_SMTP'] = false;
$LOWCAL_CONFIG_ARRAY['APP_MAIL_ENABLE_SMTP_DEBUG_LEVEL'] = 0;
$LOWCAL_CONFIG_ARRAY['APP_MAIL_SMTP_ENABLE_AUTH'] = false;
$LOWCAL_CONFIG_ARRAY['APP_MAIL_SMTP_HOST'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_SMTP_USERNAME'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_SMTP_PASSWORD'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_SMTP_PORT'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_SMTP_ENCRYPTION_METHOD'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_DEFAULT_FROM_EMAIL'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_DEFAULT_FROM_NAME'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_DEFAULT_REPLYTO_EMAIL'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_DEFAULT_REPLYTO_NAME'] = '';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_MTA'] = 'sendmail';
$LOWCAL_CONFIG_ARRAY['APP_MAIL_DEFAULT_CC_EMAILS'] = array();
$LOWCAL_CONFIG_ARRAY['APP_MAIL_DEFAULT_BCC_EMAILS'] = array();
