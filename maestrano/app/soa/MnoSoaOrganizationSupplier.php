<?php

class MnoSoaOrganizationSupplier extends MnoSoaOrganization {
    protected $_local_entity_name = "SUPPLIERS";
    
    // DONE
    protected function pullEntity() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->type = 'v';
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
}
