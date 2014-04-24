<?php

function mno_hook_push_organization($data, $type) 
{
    global $db;
    
    switch ($type) {
        case 'c': $type="CUSTOMERS"; $otherType="SUPPLIERS"; $class="MnoSoaOrganizationCustomer"; $otherEntityClass="MnoSoaOrganizationSupplier"; break;
        case 'v': $type="SUPPLIERS"; $otherType="CUSTOMERS"; $class="MnoSoaOrganizationSupplier"; $otherEntityClass="MnoSoaOrganizationCustomer"; break;
        default: return;
    }
    
    try {
        // Get Maestrano Service
        $maestrano = MaestranoService::getInstance();

        if (!$maestrano->isSoaEnabled() or !$maestrano->getSoaUrl()) return;
        
        $log = new MnoSoaBaseLogger();
        
        $mno_org=new $class($db, $log);
        $mno_org->send($data);
               
        $mno_response_id = $mno_org->getMnoResponseId();
        $mno_request_message = $mno_org->getMnoRequestMessageAsObject();
        $mno_request_message->id = $mno_response_id;
        
        if (empty($mno_response_id) || empty($mno_request_message)) return;
        
        $local_id = $mno_org->getLocalIdByMnoIdName($mno_response_id, "ORGANIZATIONS", $otherType);
        
        if (!$mno_org->isValidIdentifier($local_id)) return;
        
        $mno_org=new $otherEntityClass($db, $log);
        $mno_org->receive($mno_request_message);
    } catch (Exception $ex) {
        // skip
    }
}

function mno_hook_push_person($data, $type) {
    global $db;

    try {
        $maestrano = MaestranoService::getInstance();
        
        if (!$maestrano->isSoaEnabled() or !$maestrano->getSoaUrl()) return;
        
        $log = new MnoSoaBaseLogger();
        
        $mno_person=new MnoSoaPerson($db, $log);
        $mno_person->send($data);
        
        $mno_response_id = $mno_person->getMnoResponseId();
        $mno_request_message = $mno_person->getMnoRequestMessageAsObject();
        $mno_request_message->id = $mno_response_id;
        
        if (empty($mno_response_id) || empty($mno_request_message)) return;
        
        $local_entites = $mno_person->getLocalIdsByMnoIdName($mno_response_id, "PERSONS");
        
        $mno_person=new MnoSoaPerson($db, $log);
        $mno_person->receive($mno_request_message);        
    } catch (Exception $ex) {

    }
    
    error_log(__FILE__ . " " . __FUNCTION__ . " end");
}

function process_notification($notification) {
    global $db;
    error_log(__FILE__ . " " . __FUNCTION__ . " start");
    
    $notification_entity = strtoupper(trim($notification->entity));
    
    $log = new MnoSoaBaseLogger();
    
    $log->debug("Notification = ". json_encode($notification));
    
    switch ($notification_entity) {
        case "ORGANIZATIONS":        
            $mno_soa_base_organization = new MnoSoaBaseOrganization($db, $log);
            $mno_entity = $mno_soa_base_organization->getMnoEntity($notification);

            return process_notification_organization($mno_entity);
        case "PERSONS":
            $mno_person = new MnoSoaPerson($db, $log);		
            $mno_person->receiveNotification($notification);

            return $mno_person;
    }
    
    return false;
    $log->debug("Notification processed");
}
    
function process_notification_organization($mno_entity) {
    global $db;
    
    $log = new MnoSoaBaseLogger();
    $mno_organization = null;
    
    if (!empty($mno_entity->entity)) {
        if (!empty($mno_entity->entity->customer)) {
            $mno_organization = new MnoSoaOrganizationCustomer($db, $log);
            $mno_organization->receive($mno_entity);
        }
        if (!empty($mno_entity->entity->supplier)) {
            $mno_organization = new MnoSoaOrganizationSupplier($db, $log);
            $mno_organization->receive($mno_entity);
        }
    }
    
    return $mno_organization;
}

?>