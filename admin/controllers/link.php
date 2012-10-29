<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */

jimport('joomla.application.component.controllerform');
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';

class EasyTableProControllerLink extends JControllerForm
{
	function linkTable()
	{
		// Setup the basic variables
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;
		// Retrieve the selected table...
		$linkTable = $jInput->get('tablesForLinking', '');
		// Create a linked table entry
		if ($id = $this->createLinkedTableEntry($linkTable))
		{
			// and then pass them into editor view.
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_LINK_SUCCESSFULLY_LINKED_TO_EXTERNAL_TABLE', $linkTable));
			$this->setRedirect("index.php?option=com_easytablepro&view=link&layout=result&tmpl=component&id=$id&let=$linkTable");
		}
		else
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_LINK_FAILED_TO_CREATE_LINKED_TABLE_RECORD', $linkTable),'WARNING');
		}
	}

	function createLinkedTableEntry ($tableName)
	{
		// Setup the basic variables
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;
		// Setup defaults for the new table entry...
		$data = array();
		$data['easytablealias'] = $tableName;
		$data['easytablename'] = $tableName;
		$data['defaultimagedir'] = '/images/stories/';
		$data['description'] =  JText::sprintf ( 'COM_EASYTABLEPRO_LINK_LINKED_TO_DESC',$tableName);
		$data['datatablename'] = $tableName;
		
		// Load the table model and use it to create our table entry...
		// Add the table path and load an instance
		$tpaths = JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
		$table = JTable::getInstance('Table','EasyTableProTable');
		$item = $table->load();
		if ($table->save($data))
		{
			$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_LINK_SUCCESSFULLY_CREATED_EASYTABLE_REC_FOR', $tableName));

			$id = $table->id;
			// Create Meta records

			if ($this->createMetaForLinkedTable($tableName, $id))
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_LINK_SUCCESSFULLY_ADDED_FIELDS', $tableName));
			}
			else
			{
				$jAp->enqueueMessage(JText::sprintf('COM_EASYTABLEPRO_LINK_FAILED_TO_ADD_FIELDS_FOR', $tableName),'WARNING');;
			}

		}
		else
		{
			$id = false;
		}

		return $id;
	}

	function createMetaForLinkedTable ($tableName, $id)
	{
		// Get a database object
		$db = JFactory::getDBO();
		if (!$db)
		{
			// @TODO remove this and replace with a less drastic result --- warning and cleanup after our failure?
			JError::raiseError(500,JText::sprintf('COM_EASYTABLEPRO_LINK_COULDNT_GET_DB_OBJ_TRYING_TO_CREATE_META_FOR_LINKED_TABLE', $tableName, $id));
		}

		$fieldsArray = $db->getTableColumns($tableName);
		$theColumnCount = count($fieldsArray);

		// Construct the SQL
		$insert_Meta_SQL_start = 'INSERT INTO `#__easytables_table_meta` ( `id` , `easytable_id` , `position` , `label` , `fieldalias`, `type` ) VALUES ';
		$insert_Meta_SQL_row = '';

		$pos_in_Array = 0;
		foreach ( $fieldsArray as $fname=>$ftype )
		{
			if ($pos_in_Array > 0) $insert_Meta_SQL_row .= ', ';
			$ftypeAsInt = $this->convertType($ftype);
			$insert_Meta_SQL_row .= "( NULL , '$id', '$pos_in_Array', '$fname', '$fname', '$ftypeAsInt')";
			$pos_in_Array++;
		}

		// better terminate the statement
		$insert_Meta_SQL_end = ';';
		// pull it altogether
		$insert_Meta_SQL = $insert_Meta_SQL_start.$insert_Meta_SQL_row.$insert_Meta_SQL_end;
		// Run the SQL to insert the Meta records
		$db->setQuery($insert_Meta_SQL);
		$insert_Meta_result = $db->query();

		if (!$insert_Meta_result)
		{
			// @TODO remove this and replace with a less drastic result --- warning and cleanup after our failure?
			JError::raiseError(500,JText::sprintf('COM_EASYTABLEPRO_LINK_META_INSERT_FAILED_FOR_LINKED_TABLE', $id, $db->explain()));
			return false;
		}
		return true;
	}

	function convertType($ftype)
	{
		switch ( $ftype )
		{
			case "int":
			case "tinyint":
			case "float":
				$ftypeAsInt = 4;
				break;
			case "datetime":
			case "time":
				$ftypeAsInt = 5;
				break;
			default:
				$ftypeAsInt = 0;
				break;
		}

		return $ftypeAsInt;
	}
}

// class
