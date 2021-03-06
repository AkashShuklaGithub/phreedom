<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright (c) 2008, 2009, 2010, 2011, 2012 PhreeSoft, LLC       |
// | http://www.PhreeSoft.com                                        |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
// Path: /index.php
//
error_reporting(E_ALL & ~E_NOTICE);
if ($_POST['module'])    $module = $_POST['module'];
elseif ($_GET['module']) $module = $_GET['module'];
else                     $module = 'phreedom';
if ($_POST['page'])      $page = $_POST['page'];
elseif ($_GET['page'])   $page = $_GET['page'];
else                     $page = 'main';
require_once('includes/application_top.php');
if (!$user_validated) {
  $_SESSION['pb_cat']    = $_GET['module'];
  $_SESSION['pb_module'] = $_GET['page'];
  $_SESSION['pb_jID']    = $_GET['jID'];
  $_SESSION['pb_type']   = $_GET['type'];
  $module = 'phreedom';
  $page   = 'main';
  if ($_GET['action'] <> 'validate') $_GET['action'] = 'login';
  
  // Hook:Maestrano
  // Redirect to SSO login
  $maestrano = MaestranoService::getInstance();
  if ($maestrano->isSsoEnabled()) {
    header("Location: " . $maestrano->getSsoInitUrl());
    exit;
  }
} else {
  unset($_SESSION['pb_cat']);
  unset($_SESSION['pb_module']);
  unset($_SESSION['pb_jID']);
  unset($_SESSION['pb_type']);
  
  // Hook:Maestrano
  // Check Maestrano session is still valid
  $maestrano = MaestranoService::getInstance();
  if ($maestrano->isSsoEnabled()) {
    if (!$maestrano->getSsoSession()->isValid()) {
      header("Location: " . $maestrano->getSsoInitUrl());
      exit;
    }
  }
}
if ($page == 'ajax') {
  $pre_process_path = DIR_FS_MODULES . $module . 'custom/ajax/' . $_GET['op'] . '.php';
  if (file_exists($pre_process_path)) { require($pre_process_path); die; }
  $pre_process_path = DIR_FS_MODULES . $module . '/ajax/' . $_GET['op'] . '.php';
  if (file_exists($pre_process_path)) { require($pre_process_path); die; }
  die; // go no further
}
$custom_html      = false;
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
$pre_process_path = DIR_FS_MODULES . $module . '/pages/' . $page . '/pre_process.php';
if (file_exists($pre_process_path)) { define('DIR_FS_WORKING', DIR_FS_MODULES . $module . '/'); }
  else die('No pre_process file, looking for the file: ' . $pre_process_path);
require($pre_process_path); 
if (file_exists(DIR_FS_WORKING . 'custom/pages/' . $page . '/' . $include_template)) {
  $template_path = DIR_FS_WORKING . 'custom/pages/' . $page . '/' . $include_template;
} else {
  $template_path = DIR_FS_WORKING . 'pages/' . $page . '/' . $include_template;
}

require('includes/template_index.php');
require('includes/application_bottom.php');

?>