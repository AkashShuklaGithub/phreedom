<?php

/**
 * Maestrano map table functions
 *
 * @author root
 */

class MnoSoaDB extends MnoSoaBaseDB {
    /**
    * Update identifier map table
    * @param  	string 	local_id                Local entity identifier
    * @param    string  local_entity_name       Local entity name
    * @param	string	mno_id                  Maestrano entity identifier
    * @param	string	mno_entity_name         Maestrano entity name
    *
    * @return 	boolean Record inserted
    */
    
    public function addIdMapEntry($local_id, $local_entity_name, $mno_id, $mno_entity_name) 
    {   
        $this->_db = new queryFactory();
        $this->_db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
	// Fetch record
	$row = $this->_db->Execute("INSERT INTO mno_id_map (mno_entity_guid, mno_entity_name, app_entity_id, app_entity_name, db_timestamp) VALUES "
                                      . "('".$mno_id."', '".strtoupper($mno_entity_name)."', '".$local_id."', '".strtoupper($local_entity_name)."', UTC_TIMESTAMP)");
        
        return true;
    }
    
    /**
    * Get Maestrano GUID when provided with a local identifier
    * @param  	string 	local_id                Local entity identifier
    * @param    string  local_entity_name       Local entity name
    *
    * @return 	boolean Record found	
    */
    public function getMnoIdByLocalIdName($local_id, $local_entity_name)
    {
        $this->_db = new queryFactory();
        $this->_db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
        $mno_entity = null;
        
        $query = "SELECT mno_entity_guid, mno_entity_name, deleted_flag from mno_id_map where app_entity_id='".$local_id."' and app_entity_name='".strtoupper($local_entity_name)."'";

        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " query=".$query);
	// Fetch record
	$row = $this->_db->Execute($query);
        
	// Return id value
	if (!$row->EOF) {
            $mno_entity_guid = trim($row->fields['mno_entity_guid']);
            $mno_entity_name = trim($row->fields['mno_entity_name']);
            $deleted_flag = trim($row->fields['deleted_flag']);
            
            if (!empty($mno_entity_guid) && !empty($mno_entity_name)) {
                $mno_entity = (object) array (
                    "_id" => $mno_entity_guid,
                    "_entity" => $mno_entity_name,
                    "_deleted_flag" => $deleted_flag
                );
            }
	}
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " mno_entity=".json_encode($mno_entity));
        
	return $mno_entity;
    }
    
    public function getLocalIdByMnoIdName($mno_id, $mno_entity_name, $local_entity_name=null)
    {        
        $this->_db = new queryFactory();
        $this->_db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
	$local_entity = null;
        $query = "SELECT app_entity_id, app_entity_name, deleted_flag ".
                 "from mno_id_map where mno_entity_guid='".$mno_id."' and ".
                 "mno_entity_name='".strtoupper($mno_entity_name)."'";
     
        if (!empty($local_entity_name)) {
            $query .= " and app_entity_name='".strtoupper($local_entity_name)."'";
        }
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " query=".$query);
        
	// Fetch record
	$row = $this->_db->Execute($query);
        
	// Return id value
	if (!$row->EOF) {
            $app_entity_id = trim($row->fields['app_entity_id']);
            $app_entity_name = trim($row->fields['app_entity_name']);
            $deleted_flag = trim($row->fields['deleted_flag']);
            
            if (!empty($app_entity_id) && !empty($app_entity_name)) {
                $local_entity = (object) array (
                    "_id" => $app_entity_id,
                    "_entity" => $app_entity_name,
                    "_deleted_flag" => $deleted_flag
                );
            }
	}
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " local_entity=".json_encode($local_entity));
	
	return $local_entity;
    }
    
    public function getLocalIdsByMnoIdName($mno_id, $mno_entity_name)
    {
        $this->_db = new queryFactory();
        $this->_db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
	$local_entities = array();
        
	// Fetch record
	$query = "SELECT app_entity_id, app_entity_name, deleted_flag from mno_id_map where mno_entity_guid='"
                . $mno_id
                . "' and mno_entity_name='"
                . strtoupper($mno_entity_name) ."'";

        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " query = " . $query);
        
        $row = $this->_db->Execute($query);
        
        while (!$row->EOF) {
            // Return id value
            $app_entity_id = trim($row->fields['app_entity_id']);
            $app_entity_name = trim($row->fields['app_entity_name']);
            $deleted_flag = trim($row->fields['deleted_flag']);

            if (!empty($app_entity_id) && !empty($app_entity_name)) {
                $local_entity = (object) array (
                    "_id" => $app_entity_id,
                    "_entity" => $app_entity_name,
                    "_deleted_flag" => $deleted_flag
                );
                array_push($local_entities, $local_entity);
            }
            $row->MoveNext();
        }
        
	return $local_entities;
    }
    
    public function deleteIdMapEntry($local_id, $local_entity_name) 
    {
        $this->_db = new queryFactory();
        $this->_db->connect(DB_SERVER_HOST, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE);
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
        // Logically delete record
        $query = "UPDATE mno_id_map SET deleted_flag=1 WHERE app_entity_id='".$local_id."' and app_entity_name='".strtoupper($local_entity_name)."'";
        $row = $this->_db->Execute($query);
        
        $this->_log->debug("deleteIdMapEntry query = ".$query);
        
        return true;
    }
}

?>