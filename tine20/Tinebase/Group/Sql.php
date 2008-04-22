<?php
/**
 * Tine 2.0
 * 
 * @package     Tinebase
 * @subpackage  Group
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @copyright   Copyright (c) 2008 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @version     $Id$
 */

/**
 * sql implementation of the groups interface
 * 
 * @package     Tinebase
 * @subpackage  Group
 */
class Tinebase_Group_Sql extends Tinebase_Group_Abstract
{
    /**
     * the groups table
     *
     * @var Tinebase_Db_Table
     */
    protected $groupsTable;
    
    /**
     * the groupmembers table
     *
     * @var Tinebase_Db_Table
     */
    protected $groupMembersTable;
    
    /**
     * the constructor
     *
     * don't use the constructor. use the singleton 
     */
    private function __construct() {
        $this->groupsTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'groups'));
        $this->groupMembersTable = new Tinebase_Db_Table(array('name' => SQL_TABLE_PREFIX . 'group_members'));
    }
    
    /**
     * don't clone. Use the singleton.
     *
     */
    private function __clone() {}

    /**
     * holdes the instance of the singleton
     *
     * @var Tinebase_Account_Sql
     */
    private static $_instance = NULL;
    
    /**
     * the singleton pattern
     *
     * @return Tinebase_Group_Sql
     */
    public static function getInstance() 
    {
        if (self::$_instance === NULL) {
            self::$_instance = new Tinebase_Group_Sql;
        }
        
        return self::$_instance;
    }    

    /**
     * return all groups an account is member of
     *
     * @param mixed $_accountId the account as integer or Tinebase_Account_Model_Account
     * @return array
     */
    public function getGroupMemberships($_accountId)
    {
        $accountId = Tinebase_Account_Model_Account::convertAccountIdToInt($_accountId);
        
        $memberships = array();
        
        $select = $this->groupMembersTable->select();
        $select->where('account_id = ?', $accountId);
        
        $rows = $this->groupMembersTable->fetchAll($select);
        
        foreach($rows as $membership) {
            $memberships[] = $membership->group_id;
        }

        return $memberships;
    }
    
    /**
     * get list of groupmembers 
     *
     * @param int $_groupId
     * @return array with account ids
     */
    public function getGroupMembers($_groupId)
    {
        $groupId = Tinebase_Group_Model_Group::convertGroupIdToInt($_groupId);
        
        $members = array();
        
        $select = $this->groupMembersTable->select();
        $select->where('group_id = ?', $groupId);
        
        $rows = $this->groupMembersTable->fetchAll($select);
        
        foreach($rows as $member) {
            $members[] = $member->account_id;
        }

        return $members;
    }
    
    /**
     * replace all current groupmembers with the new groupmembers list
     *
     * @param int $_groupId
     * @param array $_groupMembers
     * @return unknown
     */
    public function setGroupMembers($_groupId, $_groupMembers)
    {
    	// remove old members
        $where = Zend_Registry::get('dbAdapter')->quoteInto('group_id = ?', $_groupId);
        $this->groupMembersTable->delete($where);
    	
    	// add new members
    	foreach ( $_groupMembers as $accountId ) {
    		$this->addGroupMember($_groupId, $accountId);
    	}
    	
    }

    /**
     * add a new groupmember to a group
     *
     * @param int $_groupId
     * @param int $_accountId
     * @return void
     */
    public function addGroupMember($_groupId, $_accountId)
    {
        $groupId = Tinebase_Group_Model_Group::convertGroupIdToInt($_groupId);
        $accountId = Tinebase_Account_Model_Account::convertAccountIdToInt($_accountId);

        $data = array(
            'group_id'      => $groupId,
            'account_id'    => $accountId
        );
        
        try {
            $this->groupMembersTable->insert($data);
        } catch (Zend_Db_Statement_Exception $e) {
            // account is already member of this group
        }
    }
        
    /**
     * remove one groupmember from the group
     *
     * @param int $_groupId
     * @param int $_accountId
     * @return void
     */
    public function removeGroupMember($_groupId, $_accountId)
    {
        $groupId = Tinebase_Group_Model_Group::convertGroupIdToInt($_groupId);
        $accountId = Tinebase_Account_Model_Account::convertAccountIdToInt($_accountId);
    	
        $where = array(
            $this->groupMembersTable->getAdapter()->quoteInto('group_id = ?', $groupId),
            $this->groupMembersTable->getAdapter()->quoteInto('account_id = ?', $accountId),
        );
         
        $this->groupMembersTable->delete($where);
    }
    
    /**
     * create a new group
     *
     * @param string $_groupName
     * @return unknown
     */
    public function addGroup(Tinebase_Group_Model_Group $_group)
    {
        if(!$_group->isValid()) {
            throw(new Exception('invalid group object'));
        }
        
        $data = $_group->toArray();
        
        if(empty($data['id'])) {
            unset($data['id']);
        }
        
        $groupId = $this->groupsTable->insert($data);
        
        if(!isset($data['id'])) {
            $_group->id = $groupId;
        }
        
        return $_group;
    }
    
    /**
     * create a new group
     *
     * @param string $_groupName
     * @return unknown
     */
    public function updateGroup(Tinebase_Group_Model_Group $_group)
    {
        $groupId = Tinebase_Group_Model_Group::convertGroupIdToInt($_group);
        
        $data = array(
            'name'          => $_group->name,
            'description'   => $_group->description
        );
        $where = Zend_Registry::get('dbAdapter')->quoteInto('id = ?', $groupId);
        
        $this->groupsTable->update($data, $where);
        
        return $this->getGroupById($groupId);
    }
    
    /**
     * delete groups
     *
     * @param int|Tinebase_Group_Model_Group $_groupId
     * @return void
     */
    public function deleteGroups($_groupId)
    {
        $groupIds = array();
        
        if(is_array($_groupId) or $_groupId instanceof Tinebase_Record_RecordSet) {
            foreach($_groupId as $groupId) {
                $groupIds[] = Tinebase_Group_Model_Group::convertGroupIdToInt($groupId);
            }
        } else {
            $groupIds[] = Tinebase_Group_Model_Group::convertGroupIdToInt($_groupId);
        }        
        
        try {
            Zend_Registry::get('dbAdapter')->beginTransaction();

            $where = Zend_Registry::get('dbAdapter')->quoteInto('group_id IN (?)', $groupIds);
            $this->groupMembersTable->delete($where);
            
            $where = Zend_Registry::get('dbAdapter')->quoteInto('id IN (?)', $groupIds);
            $this->groupsTable->delete($where);
            
            Zend_Registry::get('dbAdapter')->commit();
        } catch (Exception $e) {
            Zend_Registry::get('dbAdapter')->rollBack();
            
            throw($e);
        }
    }
    
    /**
     * get list of groups
     *
     * @param string $_filter
     * @param string $_sort
     * @param string $_dir
     * @param int $_start
     * @param int $_limit
     * @return Tinebase_Record_RecordSet with record class Tinebase_Group_Model_Group
     */
    public function getGroups($_filter = NULL, $_sort = NULL, $_dir = 'ASC', $_start = NULL, $_limit = NULL)
    {        
        $select = $this->groupsTable->select();
        
        if($_filter !== NULL) {
            $select->where('`name` LIKE ?', '%' . $_filter . '%');
        }
        if($_sort !== NULL) {
            $select->order("$_sort $_dir");
        }
        if($_start !== NULL) {
            $select->limit($_limit, $_start);
        }
        
        $rows = $this->groupsTable->fetchAll($select);

        $result = new Tinebase_Record_RecordSet('Tinebase_Group_Model_Group', $rows->toArray());
        
        return $result;
    }
    
    /**
     * get group by name
     *
     * @param string $_name
     * @return Tinebase_Group_Model_Group
     */
    public function getGroupByName($_name)
    {        
        $select = $this->groupsTable->select();
        
        $select->where('name = ?', $_name);
        
        $row = $this->groupsTable->fetchRow($select);

        if($row === NULL) {
            throw new Tinebase_Record_Exception_NotDefined('group not found');
        }
        
        $result = new Tinebase_Group_Model_Group($row->toArray());
        
        return $result;
    }
    
    /**
     * get group by id
     *
     * @param string $_name
     * @return Tinebase_Group_Model_Group
     */
    public function getGroupById($_groupId)
    {   
        $groupdId = Tinebase_Group_Model_Group::convertGroupIdToInt($_groupId);     
        
        $select = $this->groupsTable->select();
        
        $select->where('id = ?', $groupdId);
        
        $row = $this->groupsTable->fetchRow($select);
        
        if($row === NULL) {
            throw new Tinebase_Record_Exception_NotDefined('group not found');
        }

        $result = new Tinebase_Group_Model_Group($row->toArray());
        
        return $result;
    }
}