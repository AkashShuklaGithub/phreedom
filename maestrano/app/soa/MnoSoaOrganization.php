<?php

/**
 * Mno Organization Class
 */
class MnoSoaOrganization extends MnoSoaBaseOrganization
{    
    // DONE
    protected function pushId() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start");
	$id = $this->getLocalEntityIdentifier();
	$this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " localentityidentifier=".$id);
        
	if (!empty($id)) {
	    $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " this->_local_entity->id = " . json_encode($id));
	    $mno_id = $this->getMnoIdByLocalId($id);
            
	    if ($this->isValidIdentifier($mno_id)) {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " this->getMnoIdByLocalId(id) = " . json_encode($mno_id));
		$this->_id = $mno_id->_id;
	    }
	}
        
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end");
    }
    
    // DONE
    protected function pullId() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start " . $this->_id);
        
	if (!empty($this->_id)) {
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " local_entity_name=" . $this->_local_entity_name);
	    $local_id = $this->getLocalIdByMnoIdName($this->_id, "organizations", $this->_local_entity_name);
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
            
	    if ($this->isValidIdentifier($local_id)) {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_EXISTING_ID");
                $this->_local_entity = new contacts();
                $this->_local_entity->id = $local_id->_id;
                $this->_local_entity->getContact();
		return constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
	    } else if ($this->isDeletedIdentifier($local_id)) {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_DELETED_ID");
                return constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
            } else {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_NEW_ID");
                $this->_local_entity = new contacts();
                $current_timestamp = round(microtime(true) * 1000) + rand(0,999);
                $this->_local_entity->short_name = "AUTO".$current_timestamp;
                $this->_local_entity->fields = (object) array();
                $this->_local_entity->dept_rep_id = 0;
                $this->pullEntity();
		return constant('MnoSoaBaseEntity::STATUS_NEW_ID');
	    }
	}
        
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " return STATUS_ERROR");
        return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }
    
    // DONE
    protected function pushName() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_name = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->primary_name);
	$this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end " . $this->_name);
    }
    
    // DONE
    protected function pullName() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " name=" . $this->_name);
        $this->_local_entity->address_book['m'][0]->primary_name = $this->pull_set_or_delete_value($this->_name);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushIndustry() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullIndustry() {
	// DO NOTHING
    }
    
    // DONE
    protected function pushAnnualRevenue() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullAnnualRevenue() {
	// DO NOTHING
    }
    
    // DONE
    protected function pushCapital() {
        // DO NOTHING
    }
    
    // DONE
    protected function pullCapital() {
        // DO NOTHING
    }
    
    // DONE
    protected function pushNumberOfEmployees() {
	// DO NOTHING
    }
    
    // DONE
    protected function pullNumberOfEmployees() {
       // DO NOTHING
    }
    
    // DONE
    protected function pushAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        // MAIN MAILING ADDRESS -> POSTAL ADDRESS
        $mailing_address = $this->_local_entity->address_book['m'][0];
        $mailing_street_address = trim($mailing_address->address1 . " " . $mailing_address->address2);
        $this->_address->postalAddress->streetAddress = $this->push_set_or_delete_value($mailing_street_address);
        $this->_address->postalAddress->locality = $this->push_set_or_delete_value($mailing_address->city_town);
        $this->_address->postalAddress->region = $this->push_set_or_delete_value($mailing_address->state_province);
        $this->_address->postalAddress->postalCode = $this->push_set_or_delete_value($mailing_address->postal_code);
        $iso2_country_code = gen_get_country_iso_2_from_3($mailing_address->country_code);
        $this->_address->postalAddress->country = strtoupper($this->push_set_or_delete_value($iso2_country_code));
        // MAIN SHIPPING ADDRESS -> STREET ADDRESS
        $shipping_address = $this->_local_entity->address_book['s'][0];
        $shipping_street_address = trim($shipping_address->address1 . " " . $shipping_address->address2);
        if (!empty($shipping_address)) {
            $this->_address->streetAddress->streetAddress = $this->push_set_or_delete_value($shipping_street_address);
            $this->_address->streetAddress->locality = $this->push_set_or_delete_value($shipping_address->city_town);
            $this->_address->streetAddress->region = $this->push_set_or_delete_value($shipping_address->state_province);
            $this->_address->streetAddress->postalCode = $this->push_set_or_delete_value($shipping_address->postal_code);
            $iso2_country_code = gen_get_country_iso_2_from_3($shipping_address->country_code);
            $this->_address->streetAddress->country = strtoupper($this->push_set_or_delete_value($iso2_country_code));
        }
        // MAIN BILLING ADDRESS -> POSTAL ADDRESS 2
        $billing_address = $this->_local_entity->address_book['b'][0];
        $billing_street_address = trim($billing_address->address1 . " " . $billing_address->address2);
        if (!empty($billing_address)) {
            $this->_address->postalAddress2->streetAddress = $this->push_set_or_delete_value($billing_street_address);
            $this->_address->postalAddress2->locality = $this->push_set_or_delete_value($billing_address->city_town);
            $this->_address->postalAddress2->region = $this->push_set_or_delete_value($billing_address->state_province);
            $this->_address->postalAddress2->postalCode = $this->push_set_or_delete_value($billing_address->postal_code);
            $iso2_country_code = gen_get_country_iso_2_from_3($billing_address->country_code);
            $this->_address->postalAddress2->country = strtoupper($this->push_set_or_delete_value($iso2_country_code));
        }
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
	// POSTAL ADDRESS -> MAIN MAILING ADDRESS
        $street_address = $this->pull_set_or_delete_value($this->_address->postalAddress->streetAddress);
        
        if (strlen($street_address) > 31) {
            $ww = wordwrap($street_address, 255, "\n", true);
            $pieces = explode(" ", $ww);
            $this->_local_entity->address_book['m'][0]->address1 = $pieces[0];
            $this->_local_entity->address_book['m'][0]->address2 = $pieces[1];
        } else {
            $this->_local_entity->address_book['m'][0]->address1 = $street_address;
            $this->_local_entity->address_book['m'][0]->address2 = "";
        }
        
        if (empty($this->_local_entity->address_book['m'][0]->primary_name)) {
            $this->_local_entity->address_book['m'][0]->primary_name = 'Main';
        }
        
        $this->_local_entity->address_book['m'][0]->city_town = $this->pull_set_or_delete_value($this->_address->postalAddress->locality);
        $this->_local_entity->address_book['m'][0]->state_province = $this->pull_set_or_delete_value($this->_address->postalAddress->region);
        $this->_local_entity->address_book['m'][0]->postal_code = $this->pull_set_or_delete_value($this->_address->postalAddress->postalCode);
        $iso3_country_code = gen_get_country_iso_3_from_2($this->pull_set_or_delete_value($this->_address->postalAddress->country));
        $this->_local_entity->address_book['m'][0]->country_code = $this->pull_set_or_delete_value($iso3_country_code);

        // STREET ADDRESS -> MAIN SHIPPING ADDRESS
        $street_address = $this->pull_set_or_delete_value($this->_address->streetAddress->streetAddress);
        
        if (strlen($street_address) > 31) {
            $ww = wordwrap($street_address, 31, "\n", true);
            $pieces = explode(" ", $ww);
            $this->_local_entity->address_book['s'][0]->address1 = $pieces[0];
            $this->_local_entity->address_book['s'][0]->address2 = $pieces[1];
        } else {
            $this->_local_entity->address_book['s'][0]->address1 = $street_address;
            $this->_local_entity->address_book['s'][0]->address2 = "";
        }
        
        if (empty($this->_local_entity->address_book['s'][0]->primary_name)) {
            $this->_local_entity->address_book['s'][0]->primary_name = 'Main';
        }
        
        $this->_local_entity->address_book['s'][0]->city_town = $this->pull_set_or_delete_value($this->_address->streetAddress->locality);
        $this->_local_entity->address_book['s'][0]->state_province = $this->pull_set_or_delete_value($this->_address->streetAddress->region);
        $this->_local_entity->address_book['s'][0]->postal_code = $this->pull_set_or_delete_value($this->_address->streetAddress->postalCode);
        $iso3_country_code = gen_get_country_iso_3_from_2($this->pull_set_or_delete_value($this->_address->streetAddress->country));
        $this->_local_entity->address_book['s'][0]->country_code = $this->pull_set_or_delete_value($iso3_country_code);
        
        // POSTAL ADDRESS 2 -> MAIN BILLING ADDRESS
        $street_address = $this->pull_set_or_delete_value($this->_address->postalAddress2->streetAddress);
        
        if (strlen($street_address) > 31) {
            $ww = wordwrap($street_address, 255, "\n", true);
            $pieces = explode(" ", $ww);
            $this->_local_entity->address_book['b'][0]->address1 = $pieces[0];
            $this->_local_entity->address_book['b'][0]->address2 = $pieces[1];
        } else {
            $this->_local_entity->address_book['b'][0]->address1 = $street_address;
            $this->_local_entity->address_book['b'][0]->address2 = "";
        }
        
        if (empty($this->_local_entity->address_book['b'][0]->primary_name)) {
            $this->_local_entity->address_book['b'][0]->primary_name = 'Main';
        }
        
        $this->_local_entity->address_book['b'][0]->city_town = $this->pull_set_or_delete_value($this->_address->postalAddress2->locality);
        $this->_local_entity->address_book['b'][0]->state_province = $this->pull_set_or_delete_value($this->_address->postalAddress2->region);
        $this->_local_entity->address_book['b'][0]->postal_code = $this->pull_set_or_delete_value($this->_address->postalAddress2->postalCode);
        $iso3_country_code = gen_get_country_iso_3_from_2($this->pull_set_or_delete_value($this->_address->postalAddress2->country));
        $this->_local_entity->address_book['b'][0]->country_code = $this->pull_set_or_delete_value($iso3_country_code);

        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_email->emailAddress = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->email);
        $this->_email->emailAddress2 = $this->push_set_or_delete_value($this->_local_entity->address_book['s'][0]->email);
        $this->_email->emailAddress3 = $this->push_set_or_delete_value($this->_local_entity->address_book['b'][0]->email);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_book['m'][0]->email = $this->pull_set_or_delete_value($this->_email->emailAddress);
        $this->_local_entity->address_book['s'][0]->email = $this->pull_set_or_delete_value($this->_email->emailAddress2);
        $this->_local_entity->address_book['b'][0]->email = $this->pull_set_or_delete_value($this->_email->emailAddress3);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_telephone->voice = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone1);
        $this->_telephone->voice2 = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone2);
        $this->_telephone->fax = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone3);
        $this->_telephone->mobile = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone4);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_book['m'][0]->telephone1 = $this->pull_set_or_delete_value($this->_telephone->voice);
        $this->_local_entity->address_book['m'][0]->telephone2 = $this->pull_set_or_delete_value($this->_telephone->voice2);
        $this->_local_entity->address_book['m'][0]->telephone3 = $this->pull_set_or_delete_value($this->_telephone->fax);
        $this->_local_entity->address_book['m'][0]->telephone4 = $this->pull_set_or_delete_value($this->_telephone->mobile);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushWebsites() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_website->url = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->website);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pullWebsites() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_book['m'][0]->website = $this->pull_set_or_delete_value($this->_website->url);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    protected function pushEntity() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        switch ($this->_local_entity->type) {
            case 'v':
                $this->_entity->supplier = true;
                break;
            case 'c':
                $this->_entity->customer = true;
                break;
            default:
                break;
        }
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    // DONE
    public function getLocalEntityIdentifier() {
        return $this->_local_entity->id;
    }
    
    // DONE
    protected function saveLocalEntity($push_to_maestrano, $status) {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_types = array($this->_local_entity->type . "m", $this->_local_entity->type . "s", $this->_local_entity->type . "b");
        if (isset($this->_local_entity->inactive) && empty($this->_local_entity->inactive)) {
            unset($this->_local_entity->inactive);
        }
        $this->_local_entity->save_contact();
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " local_entity->type=" . $this->_local_entity->type);
        $this->_local_entity->address[$this->_local_entity->type . 'm'] = (array) $this->_local_entity->address_book['m'][0];
        $this->_local_entity->address[$this->_local_entity->type . 's'] = (array) $this->_local_entity->address_book['s'][0];
        $this->_local_entity->address[$this->_local_entity->type . 'b'] = (array) $this->_local_entity->address_book['b'][0];
        $this->_local_entity->save_addres();
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " local_entity=" . json_encode($this->_local_entity));
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }    
}

?>