<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2010- Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

/**
 * Main installer
 */
function com_install()
{
	$no_errors = TRUE;

	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	//-- common text
	$msg = '';
	$BR = '<BR />';
	
	//-- OK, to make the installer aware of our translations we need to explicitly load
	//   the components language file - this should work as the should already be copied in.
    $language = JFactory::getLanguage();
    $language->load('com_easytablepro');  // Can't use defined values in installer obj

	//--get the db object...
	$db = & JFactory::getDBO();

	// Check for a DB connection
	if(!$db){
		$msg .= $img_ERROR.JText::_( 'UNABLE_TO_CONNECT_TO_DATABASE_' ).$BR;
		$msg .= $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	}
	else
	{
		$msg .= $img_OK.JText::_( 'CONNECTED_TO_THE_DATABASE_' ).$BR;
	}
	
	// Get the list of tables in $db
	$et_table_list =  $db->getTableList();
	if(!$et_table_list)
	{
		$msg .= $img_ERROR.JText::_( 'COULDN__T_GET_LIST_OF_TABLES_IN_DATABASE_FOR_INSTALL_' ).$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_( 'SUCCESSFULLY_RETREIVED_LIST_OF_TABLES_IN_DATABASE_' ).$BR;
	}

	// Check for the core table
	if(!in_array($db->getPrefix().'easytables', $et_table_list))
	{
		$msg .= $img_ERROR.JText::_( 'CORE_EASYTABLE_TABLE_NOT_FOUND_' ).$BR;
		$msg .= $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_( 'EASYTABLE_CORE_TABLE_SETUP_SUCCESSFUL_' ).$BR;
	}

	// Check for the metadata table
	if(!in_array($db->getPrefix().'easytables_table_meta',$et_table_list))
	{
		$msg .=  $img_ERROR.JText::_( 'UNABLE_TO_FIND_META_TABLE' ).$BR;
		$msg .=  $db->getErrorMsg().$BR;
		$no_errors = FALSE;
	} else {
			$msg .= $img_OK.JText::_( 'EASYTABLE_META_TABLE_SETUP_SUCCESSFUL_' ).$BR;
			$et_settings_exist_query = "SELECT `params` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` = '0'";
			$db->setQuery($et_settings_exist_query);
			$settingsExist = $db->query();
			if(!$settingsExist)
			{
				// Add default settings records to meta table.
				$et_settings_query = "INSERT INTO `jos_easytables_table_meta` ".
				"(`easytable_id`,`position`,`label`,`description`,`type`,`list_view`,`detail_link`,`detail_view`,`fieldalias`,`params`) ".
				"VALUES (0, 0, 'Settings', 'Component Settings', 0, 0, 0, 0, 'settings', ".
				"'allowAccess=Super Administrator\nallowLinkingAccess=Super Administrator,".
				"Manager\nallowTableManagement=Super Administrator,Manager\nallowDataUpload=".
				"Super Administrator,Administrator,Manager\nallowDataEditing=Super Administrator".
				",Administrator,Manager\nrestrictedTables=\nmaxFileSize=3000000\nchunkSize=50\nuninstall_type=0\n\n');";

				$db->setQuery($et_settings_query);
				if( $et_settings_result = $db->query() )
				{
					$msg .= $img_OK.JText::_( 'EASYTABLE_META_SETTINGS' ).$BR;
				}
				else
				{
					$msg .=  $img_ERROR.JText::_( 'UNABLE_TO_CREATE_SETTINGS' ).$BR;
					$msg .=  $db->getErrorMsg().$BR;
					$no_errors = FALSE;
				}
			} else {
				$msg .= $img_OK.JText::_( 'EASYTABLE_META_SETTING_RECORD_EXISTS' ).$BR;
			}
	}
	
	// Check perform any table upgrades in this last section.
	// 1. Remove the column for the 'showsearch' parameter
	//-- See if the column exists --//
	$tableFieldsResult = $db->getTableFields('#__easytables');
	$columnNames = $tableFieldsResult['#__easytables'];

	if(array_key_exists('showsearch', $columnNames))
	{
		$msg .= $img_ERROR.JText::_( 'EASYTABLES_HAS_COLUMN__SHOWSEARCH__' ).$BR;
		$et_updateQry = "ALTER TABLE #__easytables DROP COLUMN `showsearch`;";
		$db->setQuery($et_updateQry);
		$et_updateResult = $db->query();
		if(!$et_updateResult)
		{
			$msg .= $img_ERROR.JText::_( 'ALTER_TABLE_FAILED_FOR_COLUMN__SHOWSEARCH__' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'EASYTABLES_UPDATED___SUCCESSFULLY_REMOVED_COLUMN__SHOWSEARCH__' ).$BR;
		}
	}
	else
	{
		$msg .= $img_OK.JText::_( 'EASYTABLE_TABLE_STRUCTURES_ARE_UP_TO_DATE_' ).$BR;
	}

    // 2. Add the params field to the meta table for Pro features.
	//-- See if the column exists --//
	$tableFieldsResult = $db->getTableFields('#__easytables_table_meta');
	$columnNames = $tableFieldsResult['#__easytables_table_meta'];
	if(array_key_exists('params', $columnNames))
	{
		$msg .= $img_OK.JText::_( 'EASYTABLE_META_TABLE_STRUCTURES_ARE_UP_TO_DATE_' ).$BR;
	}
	else
	{
		$msg .= $img_ERROR.JText::_( 'EASYTABLE_META_TABLE_IS_MISSING__PARAMS__COLUMN_' ).$BR;
		$et_updateQry = "ALTER TABLE #__easytables_table_meta ADD COLUMN `params` TEXT;";
		$db->setQuery($et_updateQry);
		$et_updateResult = $db->query();
		if(!$et_updateResult)
		{
			$msg .= $img_ERROR.JText::_( 'ALTER_TABLE_FAILED_FOR_COLUMN___PARAMS__' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'EASYTABLE_META_TABLE_SUCCESSFULLY_UPDATED_WITH___PARAMS___COLUMN_' ).$BR;
		}
	}

	// If all is good so far we can get the current version.
	if($no_errors)
	{
		// Must break out version function in view to a utility class - ** must setup a utility class ** doh!
		// No doubt this will end in grief then we'll fix it but for now version is in 2 places.... time, time, oh for more time....
		// See - the lack of time did bite you - now you're undoing the work from the last version... make more time!
		$et_this_version = '0.9.0';
		//
		
		// Update the version entry in the Table comment to the current version.
		$et_updateQry = "ALTER TABLE #__easytables COMMENT='".$et_this_version."'";
		$db->setQuery($et_updateQry);
		$et_updateResult = $db->query();
		if(!$et_updateResult)
		{
			$msg .= $img_ERROR.JText::_( 'COULDN__T_UPDATE_VERSION_IN_TABLE_COMMENT_' ).$BR;
			$no_errors = FALSE;
		}
		else
		{
			$msg .= $img_OK.JText::_( 'EASYTABLES_UPDATED_VERSION_IN_TABLE_COMMENT_' ).$BR;
		}
	}

	// Ok, lets append the wrap message and get the heck outta here.
	if($no_errors)
	{
		$msg .= $img_OK.JText::_( 'EASYTABLE_INSTALLATION_SUCCESSFUL_' ).$BR;
	}
	else
	{
		$msg .= $img_ERROR.'<span style="color:red;">'.JText::_( 'EASYTABLE_INSTALLATION_FAILED_' ).'</span>'.$BR;
	}

	echo $msg;
	return $no_errors;
}// function
