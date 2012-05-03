<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
$pvf = ''.JPATH_COMPONENT_SITE.'/views/viewfunctions.php';
require_once $pvf;

class EasyTableViewEasyTableRecord extends JView
{
	var $_etvetr_currenttable = null;

	function getFieldAliasForMetaID ($mId = 0)
	{
		if($mId)
		{
			$db = JFactory::getDBO();
			if(!$db){
				JError::raiseError(500,JText::_( "COM_EASYTABLEPRO_SITE_DB_NOT_AVAILABLE_PROCESSING_LINKED_TABLE" ).$mId );
			}
			$fafmID_query = "SELECT fieldalias FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE id = $mId";

			$db->setQuery($fafmID_query);

			return($db->loadResult());
		}
		return FALSE;
	}

	function &fieldMeta($id, $restrict_to_view ='')
	{
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500, JText::_( "COM_EASYTABLEPRO_SITE_DB_NOT_AVAILABLE_WHEN_GETTING_META_RECORD" ));
		}
		if($restrict_to_view == '')
		{
			$query = "SELECT `label`, `fieldalias`, `type`, `detail_link`, `description`, `id`, `detail_view`, `list_view`, `params` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE `easytable_id` ='$id' ORDER BY position;";
		}
		else
		{
			$query = "SELECT  `label`, `fieldalias`, `type`, `detail_link`, `description`, `id`, `detail_view`, `list_view`, `params` FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id ='$id' AND `$restrict_to_view` = '1' ORDER BY position;";
		}

		$db->setQuery($query);
		$meta = $db->loadRowList();
		return $meta;
	}

	function fieldAliassForDetail($metaArray, $lkf_id)
	{
		return($this->fieldAliass($metaArray, $lkf_id, 6));
	}

	function fieldAliassForList($metaArray, $lkf_id)
	{
		return($this->fieldAliass($metaArray, $lkf_id, 7));
	}

	function fieldAliass($metaArray, $lkf_id, $ListOrDetailSelector)
	{
		// Convert the list of meta records into the list of fields that can be used in the SQL
		$fields = array();
		$fields[] = ' `'.$this->getKeyFieldName().'` AS `id'; //put the primary key as id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			if(($aRow[5] == $lkf_id) || ($aRow[$ListOrDetailSelector] == '1'))
			{
				$fields[] .= $aRow[1]; // compile a list of the fieldalias'
			}
		}
		return($fields);
	}
	
	/*
		Field alias' for all fields NOT in the selected view
	*/
	function fieldAliassForDetail_NIV($metaArray, $lkf_id)
	{
		return($this->fieldAliasNotInView($metaArray, $lkf_id, 6));
	}
	function fieldAliassForList_NIV($metaArray, $lkf_id)
	{
		return($this->fieldAliasNotInView($metaArray, $lkf_id, 7));
	}
	function fieldAliasNotInView($metaArray, $lkf_id, $ListOrDetailSelector)
	{
		// Convert the list of meta records into the list of fields that can be used in the SQL
		$fields = array();
		$fields[] = ' `'.$this->getKeyFieldName().'` AS `id'; //put the primary key as id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			if(($aRow[5] == $lkf_id) || ($aRow[$ListOrDetailSelector] == '0'))
			{
				$fields[] .= $aRow[1]; // compile a list of the fieldalias'
			}
		}
		return($fields);
	}

	function fieldLabelsForDetail($metaArray, $lkf_id)
	{
		return ($this->fieldLabels($metaArray, $lkf_id, 6));
	}
	function fieldLabelsForList($metaArray, $lkf_id)
	{
		return ($this->fieldLabels($metaArray, $lkf_id, 7));
	}
	function fieldLabels($metaArray, $lkf_id, $ListOrDetailSelector)
	{
		// Convert the list of meta records into the list of fields labels
		$labels = array();
		$labels[] = 'id'; //put the id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			if(($aRow[5] == $lkf_id) || ($aRow[$ListOrDetailSelector] == '1'))
			{
				$labels[] .= $aRow[0]; // compile a list of the field labels
			}
		}
		return($labels);
	}
	
	function fieldTypes($metaArray)
	{
		// Convert the list of meta records into the list of fields types
		$types = array();
		$types[] = 'id'; //put the id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			$types[] .= $aRow[2]; // compile a list of the field types
		}
		return($types);
	}
	
	function fieldDetailLink($metaArray)
	{
		// Convert the list of meta records into the list of Detail Link flags for the fields
		$types = array();
		$types[] = 'id'; //put the id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			$types[] .= $aRow[3]; // compile a list of the detail link flags
		}
		return($types);
	}

	function fieldOptions($metaArray)
	{
		// Convert the list of meta records into the list of Options for the fields
		$types = array();
		$types[] = 'id'; //put the id in first for accessing detail view of a table row

		foreach($metaArray as $aRow) 
		{
			$types[] .= $aRow[8]; // compile a list of the field options
		}
		return($types);
	}

	function prevRecordLink($tableId=0,$tableAlias='',$currentRecordId=0)
	{
		return $this->recordLink($tableId, $tableAlias, $currentRecordId);
	}
	
	function nextRecordLink($tableId=0,$tableAlias='',$currentRecordId=0)
	{
		return $this->recordLink($tableId, $tableAlias, $currentRecordId, TRUE);
	}

	function getDataTableName()
	{
		if($this->_etvetr_currenttable == null)
		{
			$id = (int)JRequest::getVar('id', 0);
			$this->_etvetr_currenttable = JTable::getInstance('EasyTable','Table');
			$this->_etvetr_currenttable->load($id);
		}
		$_datatablename = $this->_etvetr_currenttable->datatablename;
		if($_datatablename == '')
		{
			$_datatablename = '#__easytables_table_data_'.$this->_etvetr_currenttable->id;
		}
		return $_datatablename;
	}

	function getKeyFieldName()
	{
	 	$q = "SHOW KEYS FROM `".$this->getDataTableName()."` WHERE Key_name = 'PRIMARY'";
		// Get a database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Problems trying to get the primary key: ".$db);
		}
		$db->setQuery($q);

		$pkeys = $db->loadAssocList();
		$firstPrimary = $pkeys[0];
		return $firstPrimary['Column_name'];
	}

	function recordLink($tableId=0,$tableAlias='',$currentRecordId=0,$next=FALSE)
	{
		// Next record?
		if($next)
		{
			$eqSym = '>';
			$sortOrder = 'asc';
		}
		else
		{ // So prev. record.
			$eqSym = '<';
			$sortOrder = 'desc';
		}
		
		$recordLink = '';
		// Get the current database object
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_( "COM_EASYTABLEPRO_SITE_DB_NOT_AVAILABLE_CREATING_NEXTPREV_RECORD_LINK" ).$mId );
		}

		// Build the table name - in prep for using any DB table in ETPro
		$currentTable = $db->nameQuote( $this->getDataTableName() );

		// Assemble the SQL to get the previous record if it exists. Along the lines of:
		// select id from jos_easytables_table_data_2 where `id` < 11 order by `id` desc limit 1
		$kfid_name = $this->getKeyFieldName();
		// $selectSQLQuery = 'select `'.$kfid_name.'` from '.$currentTable.' where `'.$kfid_name.'` '.$eqSym.' '.$currentRecordId.' order by `'.$kfid_name.'` '.$sortOrder.' limit 1';
		$selectSQLQuery = 'select * from '.$currentTable.' where `'.$kfid_name.'` '.$eqSym.' '.$currentRecordId.' order by `'.$kfid_name.'` '.$sortOrder.' limit 1';
		// Get the record
		$db->setQuery($selectSQLQuery);

		// $recID = $db->loadResult();
		$adjacentRow = $db->loadAssoc();
		$recID = $adjacentRow[$kfid_name];

		// Check we have a result
		if($recID)
		{
			// Build the link.
			$recordLink = JRoute::_("index.php?option=com_easytablepro&view=easytablerecord&id=$tableId:$tableAlias&rid=$recID");
		}
		
		return $recordLink;
	}

	function display ($tpl = null)
	{
		$debugMsg = '';
		// Get the table id and the record id
		$id = (int) JRequest::getVar('id',0);
		$rid = (int) JRequest::getVar('rid',0);

		/*
		 *
		 * Get the current ET details and make sure it's published.
		 *
		 */
		$this->_etvetr_currenttable = JTable::getInstance('EasyTable','Table');
		$this->_etvetr_currenttable->load($id);
		if($this->_etvetr_currenttable->published == 0) {
			JError::raiseError(404, JText::_( "COM_EASYTABLEPRO_SITE_A_RECORD_OF_THIS_ID_IS_NOT_AVAILABLE" ).$id.' / '.$rid);
		}
		
		$imageDir = $this->_etvetr_currenttable->defaultimagedir;

		/*
		 * Get Params for linked tables as we'll need them soon
		 */
		$jAp = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// Better breadcrumbs
		$pathway   = $jAp->getPathway();
		$recordLinkLabel = JRequest::getVar('rllabel',$id);
		$pathway->addItem($recordLinkLabel, '');
		
		$params = $jAp->getParams(); // Component wide & menu based params

		$menuitemid = JRequest::getInt( 'Itemid' );
		if ($menuitemid)
		{
			$menus = JSite::getMenu();
			$menu  = $menus->getActive();
			$menuparams = $menus->getParams( $menuitemid );
			$params->merge( $menuparams );
		}

		$tableParams = new JParameter( $this->_etvetr_currenttable->params );
		$params->merge( $tableParams ); // Merge with this tables params

		$show_linked_table = $params->get('show_linked_table','');

		$lt_id = $params->get('id',0);
		$kf_id = $params->get('key_field',0);
		$lkf_id = $params->get('linked_key_field',0);

		/* Check the user against table access */
		// Create a user $access object for the current $user
		$user = JFactory::getUser();
		$access = new stdClass();
		// Check to see if the user has access to view the table
		$aid	= $user->get('aid');

		if ($tableParams->get('access') > $aid)
		{
			if ( ! $aid )
			{
				// Redirect to login
				$uri		= JFactory::getURI();
				$return		= $uri->toString();

				$url  = 'index.php?option=com_user&amp;view=login';
				$url .= '&amp;return='.base64_encode($return);;

				$jAp->redirect($url, JText::_('COM_EASYTABLEPRO_SITE_RESTRICTED_TABLE') );
			}
			else{
				JError::raiseWarning( 403, JText::_('ALERTNOTAUTH') );
				return;
			}
		}

		/*
		 *
		 * Get the META records for this EasyTable and use them to create sql for data table selection
		 *
		 */
		$db = JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_( 'COM_EASYTABLEPRO_SITE_DB_NOT_AVAILABLE_FOR_TABLE_ID' ).$id);
		}
		// Get the meta data for this table
		$easytables_table_meta = $this->fieldMeta($id);

		// If any of the fields are designated as eMail load the JS file to allow cloaking.
		$doc = JFactory::getDocument();
		if(ET_VHelper::hasEmailType($easytables_table_meta))
		{
			$doc->addScript(JURI::base().'media/com_easytablepro/js/easytableprotable_fe.js');
		}

		// Convert the list of meta records into the list of fields that can be used in the SQL
		// the basic row list must be filtered for the detail view
		$fields = implode('`, `', $this->fieldAliassForDetail($easytables_table_meta, $kf_id) );
		// Also get the fields not in the detail view
		$fields_NIV = implode('`, `', $this->fieldAliassForDetail_NIV($easytables_table_meta, $kf_id) );

		$query = "SELECT ".$fields_NIV."` FROM ".$db->nameQuote($this->getDataTableName())." WHERE ".$this->getKeyFieldName()."=$rid;";
		$db->setQuery($query);
		$easytables_table_record_FNILV =$db->loadAssoc();

		/*
		 *
		 * Get the specific DATA record using sql of detail_view fields
		 *
		 */
		$query = "SELECT ".$fields."` FROM ".$db->nameQuote($this->getDataTableName())." WHERE ".$this->getKeyFieldName()."=$rid;";
		$db->setQuery($query);
		$easytables_table_record =$db->loadRow();
		$db->setQuery($query);
		$et_tr_assoc = $db->loadAssoc();

		// Get field ID for Record Title
		$titleFieldID = $params->get('title_field','');
		$titleSuffix = '';
		if(($titleFieldID != '') && ($titleFieldID != 0))
		{
			$titleFieldAlias = $this->getFieldAliasForMetaID($titleFieldID);
			$titleSuffix = ' - '.$et_tr_assoc[$titleFieldAlias];
			$origTitle = $doc->getTitle();
			$doc->setTitle($origTitle.$titleSuffix);
		}

		// Setup the rest of the params related to display
		$show_description = $params->get('show_description',0);
		$show_created_date = $params->get('show_created_date',0);
		$show_modified_date = $params->get('show_modified_date',0);
		$show_next_prev_record_links = $params->get('show_next_prev_record_links',0);
		
		// Setup the record view specific params
		$title_links_to_table = $params->get('title_links_to_table',0);
		$show_linked_table = $params->get('show_linked_table',0);

		$start_page = $jAp->getUserState( "$option.start_page", 0 );
		$pageclass_sfx = $params->get('pageclass_sfx','');

		// Generate Page title
		if( $title_links_to_table ) {
			// Create a backlink
			$backlink = 'index.php?option=com_easytablepro&amp;view=easytable&amp;id='.$id.':'.$this->_etvetr_currenttable->easytablealias.'&amp;start='.$start_page;
			$backlink = JRoute::_($backlink);

			$pt = '<a href="'.$backlink.'">'.htmlspecialchars($this->_etvetr_currenttable->easytablename.$titleSuffix).'</a>';
		} else {
			$pt = htmlspecialchars($this->_etvetr_currenttable->easytablename.$titleSuffix);
		}

		// Generate Table description
		$et_desc = '';
		if($this->_etvetr_currenttable->description != '') $et_desc = '<div class="et_description">'.$this->_etvetr_currenttable->description.'</div>';

		if($show_next_prev_record_links)
		{
			$prevrecord = $this->prevRecordLink($id,$this->_etvetr_currenttable->easytablealias,$rid);
			$nextrecord = $this->nextRecordLink($id,$this->_etvetr_currenttable->easytablealias,$rid);
		}
		else
		{
			$prevrecord = '';
			$nextrecord = '';
		}

		// Before we 'possibly' load the linked table, we store a copy for the view to use
		$this->assign('easytable_parent',(clone $this->_etvetr_currenttable));
		
		/*
		 *
		 * If there is a Linked Table we need to assemble the SQL
		 * and extract the related records.
		 *
		 */
		// Using the linked table bits assemble the SQL to get the related records
		if($lt_id)
		{
			// First, get the meta about our linked table
			$this->_etvetr_currenttable->load($lt_id);
			
			// Then get the fieldalias of the Key_Field ie. the col name in the primary table
			$kf_alias = $this->getFieldAliasForMetaID($kf_id);

			// From the record for the primary table get the value to match against in the linked table
			$kf_search_value = $et_tr_assoc[$kf_alias];
			
			$lkf_alias = $this->getFieldAliasForMetaID($lkf_id); // Get the alias (column name) of the linked key field

			$linked_table_meta = $this->fieldMeta($lt_id,'list_view');

			$linked_fields_to_get = implode('`, `', $this->fieldAliassForList($linked_table_meta,$lkf_id) );

			// Get linked Records
			$linked_records_SQL = "SELECT $linked_fields_to_get` FROM ".$db->nameQuote($this->getDataTableName())." WHERE `$lkf_alias` = '$kf_search_value'";
			$db->setQuery($linked_records_SQL);
			$linked_records = $db->loadAssocList();
			
			$tableHasRecords = count($linked_records);

			$this->assign('tableHasRecords', $tableHasRecords);
			if($tableHasRecords)
			{
				// Get the fields of the linked records that are not shown in the list view
				$linked_fields_to_get_FNILV = implode('`, `', $this->fieldAliassForList_NIV($this->fieldMeta($lt_id),$lkf_id) );
				$linked_records_FNILV_SQL = "SELECT $linked_fields_to_get_FNILV` FROM ".$db->nameQuote($this->getDataTableName())." WHERE `$lkf_alias` = '$kf_search_value'";
				$db->setQuery($linked_records_FNILV_SQL);
				$linked_records_FNILV = $db->loadAssocList();
				$this->assignRef('linked_records_FNILV',$linked_records_FNILV);

				$linked_easytable = JTable::getInstance('EasyTable','Table');
				$linked_easytable->load($lt_id);

				$linked_easytable_alias = $linked_easytable->easytablealias; // We get the alias for use in the table id
				$this->assign('linked_easytable_alias',$linked_easytable_alias);

				$linked_easytable_description = $linked_easytable->description; // The description to use it as the 'summary' value in the <table>
				$this->assign('linked_easytable_description',$linked_easytable_description);

				$linked_table_imageDir = $linked_easytable->defaultimagedir;   // We use this to prepend all image type data
				$this->assign('linked_table_imageDir', $linked_table_imageDir );

				$linked_field_types = $this->fieldTypes($linked_table_meta);  // Heading, types and other meta for the linked table
				$this->assignRef('linked_field_types', $linked_field_types );

				$linked_field_links_to_detail = $this->fieldDetailLink($linked_table_meta); // Flags for the detail link
				$this->assignRef('linked_field_links_to_detail', $linked_field_links_to_detail);

				$linked_fields_alias = $this->fieldAliassForList($linked_table_meta,$lkf_id);  // Field alias for use in CSS class for each field
				$this->assignRef('linked_fields_alias', $linked_fields_alias );

				$linked_field_options = $this->fieldOptions($linked_table_meta); // Field Options for use in table
				$this->assignRef('linked_field_options', $linked_field_options );

				$linked_field_labels = $this->fieldLabelsForList($linked_table_meta,$lkf_id); // Labels/field headings for use in table
				$this->assignRef('linked_field_labels', $linked_field_labels );

				$this->assignRef('linked_records', $linked_records );
			}
		}

		// Assigning these items for use in the tmpl
		$this->assign('show_description', $show_description);
		$this->assign('show_created_date', $show_created_date);
		$this->assign('show_modified_date', $show_modified_date);
		$this->assign('linked_table', $lt_id);
		$this->assign('prevrecord', $prevrecord);
		$this->assign('nextrecord', $nextrecord);
		$this->assign('title_links_to_table', $title_links_to_table);
		$this->assign('show_linked_table', $show_linked_table);
		$this->assign('pt', $pt);
		$this->assign('et_desc', $et_desc);

		$this->assign('pageclass_sfx',$pageclass_sfx);

		$this->assign('tableId', $id);
		$this->assign('imageDir', $imageDir);
		$this->assign('currentImageDir', $imageDir);
		$this->assignRef('backlink', $backlink);
		$this->assignRef('easytables_table_meta',$easytables_table_meta);
		$this->assignRef('easytables_table_record',$easytables_table_record);
		$this->assignRef('et_tr_assoc', $et_tr_assoc);
		$this->assignRef('easytables_table_record_FNILV', $easytables_table_record_FNILV);
		parent::display($tpl);
	}
}