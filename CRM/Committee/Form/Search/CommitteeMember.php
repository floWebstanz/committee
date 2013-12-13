<?php

/**
 * A custom contact search
 */
class CRM_Committee_Form_Search_CommitteeMember extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
  }

  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(ts('Members of the committee'));

/* definition du formulaire */

    $form->add('text',
      'committee_id',
      ts('Committee ID'),
      TRUE
    );

    $form->add('text',
      'relationship_type',
      ts('Relationship types'),
      TRUE
    );

    // Optionally define default search values
    $form->setDefaults(array(
      'committee_id' => '818',
      'relationship_type' => '5,6,7,8,18',
    ));
/* fin de la definition du form */

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('committee_id','relationship_type'));
  }

  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      ts('Contact Id') => 'contact_id',
      ts('email') => 'email',
      ts('Name') => 'sort_name',
      ts('Organisation') => 'organization_name'
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
DISTINCT contact_a.id as contact_id,  
civicrm_email.email,   
contact_a.sort_name,
contact_a.organization_name 
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  function from() {
    return "FROM 
civicrm_contact contact_a 
Inner Join   civicrm_relationship On contact_a.id = civicrm_relationship.contact_id_a   
Inner Join   civicrm_email On contact_a.id = civicrm_email.contact_id 
Inner Join   civicrm_contact contact_b On contact_b.id = civicrm_relationship.contact_id_b
    ";
  }

  /**
   * Construct a SQL WHERE clause
   *
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    $params = array();
    $relationship   = CRM_Utils_Array::value('relationship_type',$this->_formValues);
    if (strpos($relationship, ';', true) !== false) {
      die ("invalid param");
    }
    $where = "
civicrm_relationship.relationship_type_id in ($relationship) AND
contact_b.id = %1 AND civicrm_relationship.is_active = 1 AND contact_a.is_deleted = 0";

    $count  = 1;
    $committee_id   = CRM_Utils_Array::value('committee_id',$this->_formValues);

    $params[1] = array($committee_id, 'Integer');

    return $this->whereClause($where, $params);
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
//    $row['sort_name'] .= ' ( altered )';
  }
}
