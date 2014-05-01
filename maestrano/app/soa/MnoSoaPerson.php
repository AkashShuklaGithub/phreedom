<?php

/**
 * Mno Organization Class
 */
class MnoSoaPerson extends MnoSoaBasePerson
{
    protected $_local_entity_name = "CONTACTS";
    protected $_override_local_identifier = "";
    protected $_local_organizations = array();
    
    // DONE
    protected function pushId() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start");
	$id = $this->getLocalEntityIdentifier();
	
	if (!empty($id)) {
	    $mno_id = $this->getMnoIdByLocalId($id);

	    if ($this->isValidIdentifier($mno_id)) {
                $this->_log->debug(__FUNCTION__ . " this->getMnoIdByLocalId(id) = " . json_encode($mno_id));
		$this->_id = $mno_id->_id;
	    }
	}
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end");
    }
    
    // DONE
    protected function pullId() 
    {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start");
	if (!empty($this->_id)) {
            $local_id = $this->getOverrideLocalIdentifier();
            if (empty($local_id)) {
                $local_id = $this->getLocalIdByMnoId($this->_id);
            }
            $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
	    
	    if ($this->isValidIdentifier($local_id)) {
                $this->_local_entity = new contacts();
                $this->_local_entity->id = $local_id->_id;
                $this->_local_entity->getContact();
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_EXISTING_ID");
		return constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
	    } else if ($this->isDeletedIdentifier($local_id)) {
                $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " is STATUS_DELETED_ID");
                return constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
            } else {
                $this->_local_entity = new contacts();
                $current_timestamp = round(microtime(true) * 1000) + rand(0,999);
                $this->_local_entity->type='i';
                $this->_local_entity->short_name = "AUTO".$current_timestamp;
                $this->_local_entity->fields = (object) array();
		return constant('MnoSoaBaseEntity::STATUS_NEW_ID');
	    }
	}
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " return STATUS_ERROR");
        return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }
    
    protected function pushName() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_name->familyName = $this->push_set_or_delete_value($this->_local_entity->contact_last);
        $this->_name->givenNames = $this->push_set_or_delete_value($this->_local_entity->contact_first);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    
    
    protected function pullName() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->contact_last = $this->pull_set_or_delete_value($this->_name->familyName);
        $this->_local_entity->contact_first = $this->pull_set_or_delete_value($this->_name->givenNames);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushBirthDate() {
        // DO NOTHING
    }
    
    protected function pullBirthDate() {
        // DO NOTHING
    }
    
    protected function pushGender() {
	// DO NOTHING
    }
    
    protected function pullGender() {
	// DO NOTHING
    }
    
    protected function pushAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        // MAILING ADDRESS -> POSTAL ADDRESS
        $mailing_address = $this->_local_entity->address_book['m'][0];
        $mailing_street_address = trim($mailing_address->address1 . " " . $mailing_address->address2);
        $this->_address->work->postalAddress->streetAddress = $this->push_set_or_delete_value($mailing_street_address);
        $this->_address->work->postalAddress->locality = $this->push_set_or_delete_value($mailing_address->city_town);
        $this->_address->work->postalAddress->region = $this->push_set_or_delete_value($mailing_address->state_province);
        $this->_address->work->postalAddress->postalCode = $this->push_set_or_delete_value($mailing_address->postal_code);
        $iso2_country_code = gen_get_country_iso_2_from_3($mailing_address->country_code);
        $this->_address->work->postalAddress->country = strtoupper($this->push_set_or_delete_value($iso2_country_code));
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullAddresses() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
	// POSTAL ADDRESS -> MAILING ADDRESS
        $street_address = $this->pull_set_or_delete_value($this->_address->work->postalAddress->streetAddress);
        
        if (strlen($street_address) > 31) {
            $ww = wordwrap($street_address, 31, "\n", true);
            $pieces = explode(" ", $ww);
            $this->_local_entity->address_book['m'][0]->address1 = $pieces[0];
            $this->_local_entity->address_book['m'][0]->address2 = $pieces[1];
        } else {
            $this->_local_entity->address_book['m'][0]->address1 = $street_address;
        }
        
        $this->_local_entity->address_book['m'][0]->city_town = $this->pull_set_or_delete_value($this->_address->work->postalAddress->locality);
        $this->_local_entity->address_book['m'][0]->state_province = $this->pull_set_or_delete_value($this->_address->work->postalAddress->region);
        $this->_local_entity->address_book['m'][0]->postal_code = $this->pull_set_or_delete_value($this->_address->work->postalAddress->postalCode);
        $iso3_country_code = gen_get_country_iso_3_from_2($this->pull_set_or_delete_value($this->_address->work->postalAddress->country));
        $this->_local_entity->address_book['m'][0]->country_code = $this->pull_set_or_delete_value($iso3_country_code);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_email->emailAddress = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->email);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullEmails() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_book['m'][0]->email = $this->pull_set_or_delete_value($this->_email->emailAddress);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    
    protected function pushTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_telephone->work->voice = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone1);
        $this->_telephone->work->voice2 = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone2);
        $this->_telephone->work->fax = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone3);
        $this->_telephone->home->mobile = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->telephone4);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullTelephones() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_book['m'][0]->telephone1 = $this->pull_set_or_delete_value($this->_telephone->work->voice);
        $this->_local_entity->address_book['m'][0]->telephone2 = $this->pull_set_or_delete_value($this->_telephone->work->voice2);
        $this->_local_entity->address_book['m'][0]->telephone3 = $this->pull_set_or_delete_value($this->_telephone->work->fax);
        $this->_local_entity->address_book['m'][0]->telephone4 = $this->pull_set_or_delete_value($this->_telephone->home->mobile);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushWebsites() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
	$this->_website->url = $this->push_set_or_delete_value($this->_local_entity->address_book['m'][0]->website);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullWebsites() {
	$this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_book['m'][0]->website = $this->pull_set_or_delete_value($this->_website->url);
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pushEntity() {
        // DO NOTHING
    }
    
    protected function pullEntity() {
        // DO NOTHING
    }
    
    protected function pushRole() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        
        $local_org_id = $this->_local_entity->dept_rep_id;
        
        if (empty($local_org_id)) return;
        
        $org_record = new contacts();
        $org_record->id = $local_org_id;
        $org_record->getContact();
        
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " org_record=" . json_encode($org_record));
        $org_type = $org_record->type;
        
        switch ($org_type) {
            case "v":
                $mno_org_id = $this->getMnoIdByLocalIdName($local_org_id, "SUPPLIERS");
                break;
            case "c":
                $mno_org_id = $this->getMnoIdByLocalIdName($local_org_id, "CUSTOMERS");
                break;
            default:
                return;
        }
        
        if ($this->isValidIdentifier($mno_org_id)) {    
	    $this->_log->debug("is valid identifier");
	    $this->_role->organization->id = $mno_org_id->_id;
	} else if ($this->isDeletedIdentifier($mno_org_id)) {
	    $this->_log->debug(__FUNCTION__ . " deleted identifier");
	    // do not update
	    return;
	} else {
	    if ($org_type == 'c') {
		$organization = new MnoSoaOrganizationCustomer($this->_db, $this->_log);
		$status = $organization->send($org_record);

		if ($status) {
		    $mno_org_id = $this->getMnoIdByLocalIdName($local_org_id, "CUSTOMERS");

		    if ($this->isValidIdentifier($mno_org_id)) {
			$this->_role->organization->id = $mno_org_id->_id;
		    }
		}
	    } else if ($org_type == 'v') {
		$organization = new MnoSoaOrganizationSupplier($this->_db, $this->_log);
		$status = $organization->send($org_record);

		if ($status) {
		    $mno_org_id = $this->getMnoIdByLocalIdName($local_org_id, "SUPPLIERS");

		    if ($this->isValidIdentifier($mno_org_id)) {
			$this->_role->organization->id = $mno_org_id->_id;
		    }
		}
	    }
	}
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function pullRole() {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        if (empty($this->_role->organization->id)) {
            // EXCEPTION - PERSON (CLIENT CONTACT) MUST BE RELATED TO AN ORGANIZATION (CLIENT)
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " MNO_000: Message not persisted - person must be related to an organization (MNOID=" . $this->_id . ")" );
            throw new Exception("MNO_000: Message not persisted - person must be related to an organization (MNOID=" . $this->_id . ")");
        } else {
            // CONSTRUCT NOTIFICATION
            $notification->entity = "ORGANIZATIONS";
            $notification->id = $this->_role->organization->id;
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " notification=".json_encode($notification));
            // GET ORGANIZATION
            process_notification($notification);
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " after process notification");
            
            $local_entities = $this->getLocalIdsByMnoIdName($notification->id, $notification->entity);          
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " local_entities=".json_encode($local_entities));
            
            if (empty($local_entities)) {
                throw new Exception("MNO_000: Message not persisted - person must be related to a customer or supplier organization (MNOID=" . $this->_id . ")");
            }
            
            $customer_id = null;
            $supplier_id = null;
            
            foreach ($local_entities as $local_id) {
                if ($local_id->_entity == "CUSTOMERS" && $this->isValidIdentifier($local_id)) {
                    $customer_id = $local_id->_id;
                    array_push($this->_local_organizations, $customer_id);
                } else if ($local_id->_entity == "SUPPLIERS" && $this->isValidIdentifier($local_id)) {
                    $supplier_id = $local_id->_id;
                    array_push($this->_local_organizations, $supplier_id);
                }
            }
            
            if (empty($customer_id) && empty($supplier_id)) {
                throw new Exception("MNO_000: Message not persisted - person must be related to a customer or supplier organization (MNOID=" . $this->_id . ")");
            }
        }
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function saveLocalEntity($push_to_maestrano, $status) {
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " start ");
        $this->_local_entity->address_types = array($this->_local_entity->type . "m");
        
        if (isset($this->_local_entity->inactive) && empty($this->_local_entity->inactive)) {
            unset($this->_local_entity->inactive);
        }
        
        $mappings = $this->getLocalOrgPersonMapping();
        
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " mappings=" . json_encode($mappings));
        
        $short_name = $this->_local_entity->short_name;
        
        foreach($mappings as $map) {
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " map=" . json_encode($map));
            $this->_local_entity->id = $map->person_id;
            $this->_local_entity->dept_rep_id = $map->org_id;
            if (!empty($map->address_id)) {
                $this->_local_entity->address_book['m'][0]->address_id = $map->address_id;
            } else {
                if (isset($this->_local_entity->address_book['m'][0]->address_id)) {
                    unset($this->_local_entity->address_book['m'][0]->address_id);
                }
            }
            if (!empty($map->person_id)) {
                $this->_local_entity->address_book['m'][0]->ref_id = $map->person_id;
            } else {
                if (isset($this->_local_entity->address_book['m'][0]->ref_id)) {
                    unset($this->_local_entity->address_book['m'][0]->ref_id);
                }
            }
            $this->_local_entity->address[$this->_local_entity->type . 'm'] = (array) $this->_local_entity->address_book['m'][0];
            if (isset($this->_local_entity->i_id)) {
                unset($this->_local_entity->i_id);
            }
            $this->_local_entity->short_name = $short_name . $map->org_id;
            $this->_local_entity->save_contact();
            $this->_local_entity->i_id = $this->_local_entity->id;
            $this->_local_entity->save_addres(true);
            $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " local_entity=" . json_encode($this->_local_entity));
            
            $is_new_id = empty($map->person_id);
            $local_entity_id = $this->getLocalEntityIdentifier();
            $mno_entity_id = $this->_id;
            
            if ($is_new_id && !empty($local_entity_id) && !empty($mno_entity_id)) {
                $this->addIdMapEntry($local_entity_id, $mno_entity_id);
            }
            
        }
        $this->_log->debug(__CLASS__ . '.' . __FUNCTION__ . " end ");
    }
    
    protected function getLocalOrgPersonMapping() {
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
        $org_list = '("' . implode('", "', $this->_local_organizations) . '")';
        
        $query =   "SELECT org_person.org_id as org_id, org_person.person_id as person_id, address.address_id as address_id
                    FROM
                    (
                        SELECT orgs.id as org_id, persons.id as person_id
                        FROM ".TABLE_CONTACTS." orgs
                        LEFT JOIN (
                            SELECT id, dept_rep_id
                            FROM ".TABLE_CONTACTS." cont, mno_id_map map
                            WHERE   cont.dept_rep_id IN " . $org_list . " AND 
                                    map.app_entity_id=cont.id AND 
                                    map.mno_entity_guid='" . $this->_id . "' AND 
                                    map.mno_entity_name='PERSONS'
                        ) persons
                        ON orgs.id = persons.dept_rep_id
                        WHERE orgs.id IN " . $org_list . "
                    ) org_person 
                    LEFT JOIN ".TABLE_ADDRESS_BOOK." address
                    ON org_person.person_id=address.ref_id";
        
        
	$this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " query = " . $query);
        
        $row = $this->_db->Execute($query);
        
        $mappings = array();
        
        while (!$row->EOF) {
            $map = (object) array (
                    "org_id" => $row->fields['org_id'],
                    "person_id" => $row->fields['person_id'],
                    "address_id" => $row->fields['address_id']
            );
            array_push($mappings, $map);

            $row->MoveNext();
        }
        
	return $mappings;
    }
    
    public function getLocalEntityIdentifier() {
        return $this->_local_entity->id;
    }
    
    public function setOverrideLocalIdentifier($id) {
        $this->_override_local_identifier = $id;
    }
    
    protected function getOverrideLocalIdentifier() {
        return $this->_override_local_identifier;
    }
}

?>