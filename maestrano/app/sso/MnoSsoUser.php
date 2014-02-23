<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Assign new attributes
    $this->connection = $opts['db_connection'];
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    // First get the full user object
    $sql = "select admin_id, admin_name, inactive, display_name, admin_email, admin_pass, account_id, admin_prefs, admin_security from users WHERE admin_id = {$this->connection->prepare_input($this->local_id)}";
    $result = $this->connection->Execute($sql);
    
    if ($result) {
      $_SESSION['admin_id']       = $result->fields['admin_id'];
      $_SESSION['display_name']   = $result->fields['display_name'];
      $_SESSION['admin_email']    = $result->fields['admin_email'];
	    $_SESSION['admin_prefs']    = unserialize($result->fields['admin_prefs']);
	    $_SESSION['company']        = 'phreedom';
      $_SESSION['language']       = null;
	    $_SESSION['account_id']     = $result->fields['account_id'];
      $_SESSION['admin_security'] = gen_parse_permissions($result->fields['admin_security']);
        
      return true;
    } else {
      return false;
    }
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      // First set $conn variable (need global variable?)
      $user = $this->buildLocalUser();
      
      // Create user
      db_perform('users', $user);
      $lid = db_insert_id();
    }
    
    return $lid;
  }
  
  /**
   * Build a local user for creation
   *
   * @return a hash containing user attributes
   */
  protected function buildLocalUser()
  {
  	$user_data = array(
  	  'admin_name'     => db_prepare_input($this->uid),
  	  'is_role'        => '0',
  	  'inactive'       => '0',
  	  'display_name'   => db_prepare_input("$this->name $this->surname"),
  	  'admin_email'    => db_prepare_input($this->email),
  	  'account_id'     => 0,
  	  //'admin_prefs'    => null,
  	  'admin_security' => $this->getSecurityPermissions(),
      'admin_pass'     => pw_encrypt_password($this->generatePassword())
  	);
    
    return $user_data;
  }
  
  /**
   * Return the list of permissions for the user.
   * Two sets are currently defined: base user and admin 
   *
   * @return a hash containing user attributes
   */
  protected function getSecurityPermissions()
  {
    $admin_default_sec = "26:4,26:4,49:4,51:4,51:4,76:4,76:4,15:4,15:4,16:4,151:4,151:4,152:4,153:4,156:4,88:4,89:4,102:4,111:4,103:4,101:4,112:4,105:4,104:4,107:4,108:4,29:4,35:4,28:4,32:4,30:4,34:4,31:4,40:4,54:4,59:4,53:4,58:4,55:4,61:4,57:4,60:4,126:4,130:4,4:4,129:4,19:4,6:4,7:4,11:4,20:4,2:4,18:4,1:4,5:4,3:4,3:4,3:4,3:4,3:4,3:4,3:4,13:4";
    
    $user_default_sec = "26:0,49:0,16:0,88:0,29:0,35:0,28:0,32:0,30:0,34:0,31:0,40:0,51:0,89:0,54:0,59:0,53:0,58:0,55:0,61:0,57:0,60:0,151:0,152:0,153:0,102:0,111:0,103:0,101:0,112:0,105:0,104:0,107:0,108:0,126:0,130:0,4:0,129:0,19:0,76:0,2:0,18:0,3:0,13:0,6:0,7:0,11:0,1:0,5:0";
      
    $security = $user_default_sec; // User
  
    if ($this->app_owner) {
      $security = $admin_default_sec; // Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $security = $admin_default_sec;
        } else {
          $security = $user_default_sec;
        }
      }
    }
    
    return $security;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $result = $this->connection->Execute_return_error("SELECT admin_id FROM users WHERE mno_uid = '{$this->connection->prepare_input($this->uid)}' LIMIT 1");
    $result = $result->fields;
    
    if ($result && $result['admin_id']) {
      return $result['admin_id'];
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {
    $result = $this->connection->Execute_return_error("SELECT admin_id FROM users WHERE admin_email = '{$this->connection->prepare_input($this->email)}' LIMIT 1");
    $result = $result->fields;
    
    if ($result && $result['admin_id']) {
      return $result['admin_id'];
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     if($this->local_id) {
       
       $upd = $this->connection->Execute_return_error("UPDATE users 
         SET admin_name = '{$this->connection->prepare_input($this->uid)}',
         admin_email = '{$this->connection->prepare_input($this->email)}',
         display_name = '{$this->connection->prepare_input("$this->name $this->surname")}'
         WHERE admin_id = {$this->connection->prepare_input($this->local_id)}");
       
       return $upd;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      $upd = $this->connection->Execute_return_error("UPDATE users 
        SET mno_uid = '{$this->connection->prepare_input($this->uid)}'
        WHERE admin_id = {$this->connection->prepare_input($this->local_id)}");
      
      return $upd;
    }
    
    return false;
  }
}