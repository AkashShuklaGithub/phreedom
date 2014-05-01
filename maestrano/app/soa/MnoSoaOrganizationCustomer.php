<?php

class MnoSoaOrganizationCustomer extends MnoSoaOrganization {
    protected $_local_entity_name = "CUSTOMERS";
    
    // DONE
    protected function pullEntity() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->type = 'c';
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
}
