<?php
//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}
require_once MAESTRANO_ROOT . '/app/init/base.php';

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('APP_DIR', realpath(MAESTRANO_ROOT . '/../'));
chdir(APP_DIR);

// Set company in session to force phreedom
// to configure the database object
session_start();
$_SESSION['company'] = 'phreedom';

require_once APP_DIR . '/includes/configure.php';
require_once APP_DIR . '/includes/application_top.php';
require_once APP_DIR . '/modules/contacts/classes/contacts.php';

if (!defined('TABLE_ADDRESS_BOOK')) {
    define('TABLE_ADDRESS_BOOK', DB_PREFIX . 'address_book');
}

if (!defined('TABLE_CONTACTS')) {
    define('TABLE_CONTACTS', DB_PREFIX . 'contacts');
}

if (!defined('ACT_C_TYPE_NAME')) {
    define('ACT_C_TYPE_NAME','Customers');
}

if (!defined('ACT_V_TYPE_NAME')) {
    define('ACT_V_TYPE_NAME','Suppliers');
}

if (!defined('ACT_I_TYPE_NAME')) {
    define('ACT_I_TYPE_NAME','Contacts');
}

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction
// e.g:
$opts = array();
// TODO - Define DB connection here
$opts['db_connection'] = $db;
