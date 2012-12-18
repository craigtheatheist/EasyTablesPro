<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * EasyTablePro Table Model
 *
 * @package    EasyTablePro
 * @subpackage Models
 */
class EasyTableProModelTable extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Table', $prefix = 'EasyTableProTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	/**
	* Method to get the record form.
	*
	* @param	array	$data		Data for the form.
	* @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	* @return	mixed	A JForm object on success, false on failure
	* @since	1.6
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_easytablepro.table', 'table', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_easytable.edit.table.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}


	/**
	 * Method to set the EasyTable identifier
	 *
	 * @access	public
	 * @param	int EasyTable identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}//function


	function getItem($pk = null) {
		// @TODO Cache this item!
		$item = parent::getItem($pk);
		$kPubState = 'Published';
		$kUnpubState = 'Unpublished';

		// If we have an actual record (and not a new item) then we need to load the meta records
		if ($item->id > 0)
		{
			// Get a database object
			$db = JFactory::getDBO();
			if (!$db)
			{
				JError::raiseError(500,JText::sprintf("COM_EASYTABLEPRO_TABLE_GET_STATS_DB_ERROR", $pk));
			}

			// Get a list of accessible tables
			$allTables = $db->getTableList();

			// Lets see if there's a defined name...
			$ettd_datatablename = $item->datatablename;
			// Lets validate that external table
			if ($ettd_datatablename != '')
			{
				$et_ext_table = TRUE;
				$ettd_tname = $ettd_datatablename;
			}
			else
			{
				$et_ext_table = FALSE;
				$ettd_tname = $db->getPrefix().'easytables_table_data_' . $item->id;
			}
			// Next we check for an actual data table
			$et_datatable_found = in_array($ettd_tname, $allTables);
			// If we have an actual data table we need to grab the primary key
			if($et_datatable_found)
			{
				$query = 'SHOW KEYS FROM '.$db->quoteName($ettd_tname).
				' WHERE '.$db->quoteName('Key_name').' = '.$db->quote('Primary');
				$db->setQuery($query);
				$pkObject = $db->loadObject();
				$et_Keyname = $pkObject->Column_name;
			}
			else
			{
				$et_Keyname = '';
			}

			// Ok store these bits
			$item->set('ettd', $et_datatable_found);
			$item->set('etet', $et_ext_table);
			$item->set('ettd_tname', $ettd_tname);
			$item->et_Keyname = $et_Keyname;

			// Now that we have the base easytable record we have to retrieve the associated field records (ie. the meta about each field in the table)
			// As a nicety if the easytable has just been created we sort the meta records (ie. the fields meta) in the original creation order (ie. the order found in the original import file)
			$jinput = JFactory::getApplication()->input;
			$from = $jinput->get( 'from', '' );

			if ($from == 'create')
			{
				$default_order_sql = $db->quoteName('id');
			}
			else
			{
				$default_order_sql = $db->quoteName('position');
			}

			// Get the meta data for this table
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__easytables_table_meta'));
			$where = $db->quoteName('easytable_id').' = '.$db->quote($item->id);
			$query->where($where);
			$query->order($default_order_sql);
			$db->setQuery($query);

			$easytables_table_meta = $db->loadAssocList('id');


			// OK now if there are meta records we add them to the item before returning it
			if (count($easytables_table_meta))
			{
				$item->set('table_meta', $easytables_table_meta);
				$item->set('ettm_field_count', count($easytables_table_meta));
			}

			// By default we assume unpublished but we check...
			$state = 'Unpublished';

			if ($et_datatable_found)
			{
				// Get the record count for this table
				$query = "SELECT COUNT(*) FROM ".$db->nameQuote($ettd_tname);
				$db->setQuery($query);
				$ettd_record_count = $db->loadResult();
				$item->set('ettd_record_count', $ettd_record_count);

				// Only if we have a data table and the owner has published it we set the state
				if ($item->published)
				{
					$state = $kPubState;
				}
			}
			else
			{
				$easytables_table_data ='';
				$ettd_record_count = 0;

				// Make sure that a table with no associated data table is never published
				$item->published = FALSE;
				$state = $kUnpubState;
			}

			$item->set('pub_state', $state);
		}
		else
		{
			// We have a new Table record being created...
			$item->set('table_meta', array());
			$item->set('ettm_field_count', 0);
			$item->set('ettd', false);

			$item->set('etet', false);

			$item->set('ettd_tname', '');
			$item->set('ettd_record_count',0);
			$item->set('pub_state', $kUnpubState);
		}

		return $item;
	}

	/**
	 * Method to get a record
	 * @return object with data
	 */
	function &getData()
	{
		// Load the data
		if (empty( $this->_data))
		{
			$query = ' SELECT * FROM #__easytable '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data)
		{
			$this->_data = new stdClass();
			$this->_data->id = 0;
		}
		return $this->_data;
	}//function

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		$row = $this->getTable();

		$data = JRequest::get( 'post' );

		// Bind the form fields to the table
		if (!$row->bind($data))
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the record is valid
		if (!$row->check())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the table to the database
		if (!$row->store())
		{
			$this->setError( $row->getErrorMsg() );
			return false;
		}

		return true;
	}//function

	public function delete(&$pks)
	{
		// Check for request forgeries

		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app	= JFactory::getApplication();

		// @todo move the parent::delete($pks) after the foreach
		// @todo load each table first so we can check if it's a linked table
		// Initialise Variables
		$pks = (array) $pks;
		$db = $this->getDbo();

		// If the master table record was deleted successfully
		// we can proceed to delete the related meta-records
		foreach ($pks as $i => $pk)
		{
			// Set the query
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__easytables_table_meta'));
			// Set the 'where' to the table id
			$query->where($db->quoteName('easytable_id').' = '.$db->quote($pk));
			$db->setQuery($query);

			if ($db->query())
			{
				$app->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_ALL_META_DATA_FOR_TABLE_ID_X_WAS_DELETED', $pk));
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_NOT_ALL_META_DATA_FOR_TABLE_ID_X_COULD_BE_DELETED', $pk));
			}
			// and the data table.
			// @todo Only try to drop the table if it's not a linked table.
			$table = $this->getTable();
			$table->load($pk);
			if ($table->datatablename == '')
			{
				// Build the DROP SQL
				$ettd_table_name = $db->quoteName('#__easytables_table_data_'.$pk);
				$query = 'DROP TABLE '.$ettd_table_name.';';
				$db->setQuery($query);
				if ($db->query())
				{
					$app->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_SUCCESSFULLY_DROPPED_DATA_FOR_TABLE_X', $table->easytablename));
				}
				else
				{
					$app->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_IMPORT_FAILED_TO_DROP_DATA_FOR_TABLE_X', $table->easytablename));
				}
			}
			else
			{
				$app->enqueueMessage(JText::sprintf('<strong>%s</strong> is a linked external table, data left in place.', $table->easytablename));
			}
		}
		// Call the parent
		if (!parent::delete($pks))
		{
			$app->enqueueMessage(JText::sprintf('EasyTable Pro! encountered a problem, trying to delete the EasyTables record.', $pk));
		}
		return true;
	}

	public function createETTD ($id, $ettdColumnAliass)
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		$app	= JFactory::getApplication();

		/*
		 * WARNING HERE AFTER BE OLDE CODE FROM DAYS GONE BY AND LONG PAST
		 */
		// we turn the arrays of column names into the middle section of the SQL create statement
		$ettdColumnSQL = implode('` TEXT NOT NULL , `', $ettdColumnAliass);

		// Build the SQL create the ettd
		$create_ETTD_SQL = 'CREATE TABLE `#__easytables_table_data_'.$id.'` (`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT , `';

		// Insert exlpoded
		$create_ETTD_SQL .= $ettdColumnSQL;

		// close the sql with the primary key
		$create_ETTD_SQL .= '` TEXT NOT NULL ,  PRIMARY KEY ( `id` ) )';

		// Uncomment the next line if trying to debug a CSV file error
		// JError::raiseError(500,'$id = '.$id.'<br />$ettdColumnAliass = '.$ettdColumnAliass.'<br />$ettdColumnSQL = '.$ettdColumnSQL.'<br />createETTD SQL = '.$create_ETTD_SQL );

		// Get a database object
		$db = JFactory::getDBO();
		if (!$db)
		{
			JError::raiseError(500,"Couldn't get the database object while trying to create table: $id");
		}

		// Set and execute the SQL query
		$db->setQuery($create_ETTD_SQL);
		$ettd_creation_result = $db->query();

		if (!$ettd_creation_result)
		{
			JError::raiseError(500, "Failure in data table creation, likely cause is invalid column headings; actually DB explanation: ".$db->explain());
		}
		return $this->ettdExists($id);
	}

}// class
