<?php
/**
 * @package	   EasyTables
 * @author	   Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author	   Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

$pmf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/helpers/managerfunctions.php';
require_once $pmf;
/**
 * HTML View class for the EasyTables Component
 *
 * @package	   EasyTables
 * @subpackage Views
 */

class EasyTableViewEasyTable extends JView
{
	function getTableIDForName ($tableName)
	{
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_("Couldn't talk to the database while trying to get a table ID for table:").' '.$tableName);
		}
		// Get the id for this table
		$query = "SELECT id FROM ".$db->nameQuote('#__easytables')." WHERE `datatablename` ='".$tableName."'";
		$db->setQuery($query);
		
		$id = $db->loadResult();
		if($id) {
			return $id;
		} else {
			return 0;
		}
	}

	/*
	 * getListView	- accepts the name of an element and a flog
	 *				- returns img url for either the tick or the X used in backend components
	*/
	function getListViewImage ($rowElement, $flag=0)
	{
		$btn_title = '';
		if(substr($rowElement,0,4)=='list')
		{
			$btn_title = JText::_( "COM_EASYTABLEPRO_TABLE_TOGGLE_APPEARS_IN_LIST_TT" );
		}
		elseif(substr($rowElement,7,4) == 'link')
		{
			$btn_title = JText::_( "COM_EASYTABLEPRO_TABLE_TURN_ON_DETAIL_LINK_TT" );
		}
		elseif(substr($rowElement,0,6)=='detail')
		{
			$btn_title = JText::_( "COM_EASYTABLEPRO_TABLE_TURN_ON_IN_DETAIL_VIEW_TT" );
		}
		elseif(substr($rowElement,0,6)=='search')
		{
			$btn_title = JText::_( 'COM_EASYTABLEPRO_TABLE_TOGGLE_FIELD_SEARCH_VISIBILITY_TT' );
		}

		if($flag)
		{
			$theImageString = 'tick.png';
		}
		else
		{
			$theImageString = 'publish_x.png';
		}

		$theListViewImage = '<img src="images/'.$theImageString.'" name="'.$rowElement.'_img" border="0" title="'.$btn_title.'" alt="'.$btn_title.'" class="hasTip"/>';
		
		return($theListViewImage);
	}

	function toggleState( &$row, $i, $imgY = 'tick.png', $imgX = 'publish_x.png', $prefix='' )
	{
		$img	= $row->published ? $imgY : $imgX;
		$task	= $row->published ? 'unpublish' : 'publish';
		$alt	= $row->published ? JText::_( 'JPUBLISHED' ) : JText::_( 'COM_EASYTABLEPRO_UNPUBLISHED' );
		$action = $row->published ? JText::_( 'COM_EASYTABLEPRO_TABLE_TURN_OFF_SETTING' ) : JText::_( 'COM_EASYTABLEPRO_TABLE_TURN_ON_SETTING' );
		$href	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i
				 .'\',\''. $prefix.$task .'\')" title="'. $action .'"><img src="images/'
				 . $img .'" border="0" alt="'. $alt .'" /></a>';
		return $href;
	}

	function getTypeList ($id, $selectedType=0)
	{
		$selectOptionText =	 '<select name="type'.$id.'" onchange="changeTypeWarning()" class="hasTip" title="'.JText::_( 'COM_EASYTABLEPRO_TABLE_FIELD_TYPE_DESC' ).'">';// start our html select structure
		$selectOptionText .= '<option value="0" '.($selectedType ? '':'selected="selected"').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_TEXT').'</option>';				// Type 0 = Text
		$selectOptionText .= '<option value="1" '.($selectedType==1 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_IMAGE').'</option>';			// Type 1 = Image URL
		$selectOptionText .= '<option value="2" '.($selectedType==2 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_LINK_URL').'</option>';	// Type 2 = Fully qualified URL
		$selectOptionText .= '<option value="3" '.($selectedType==3 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_EMAIL').'</option>';			// Type 3 = Email address
		$selectOptionText .= '<option value="4" '.($selectedType==4 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_TABLE_LABEL_NUMBER').'</option>';		// Type 4 = Numbers
		$selectOptionText .= '<option value="5" '.($selectedType==5 ? 'selected="selected"':'').'>'.JText::_('COM_EASYTABLEPRO_LABEL_DATE').'</option>';			// Type 5 = Dates
		$selectOptionText .= '</select>';																						// close our html select structure
		
		return($selectOptionText);
	}

	function getFieldOptions ($params=null)
	{
		$fieldOptions = '';
		if ( isset ($params) )
		{
			$paramsObj = new JParameter ($params);
			$rawFieldOptions = $paramsObj->get('fieldoptions','');
			if(strlen ( $rawFieldOptions )) {
				if(substr($rawFieldOptions,0,1) == 'x') {
					$unpackedFieldOptions = htmlentities ( pack("H*", substr($rawFieldOptions,1) ));
					$fieldOptions = $unpackedFieldOptions;
				} else {
					$fieldOptions = $rawFieldOptions;
				}
			}
		}
		return($fieldOptions);
	}

	/**
	 * EasyTable view display method
	 * 
	 * @return void
	 **/
	function display($tpl = null)
	{
		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Allow Table Management
		$atmSettings = explode(',', $settings->get('allowTableManagement'));
		// Allow Data Upload
		$aduSettings = explode(',', $settings->get('allowDataUpload'));
		// Get the current user
		$user =& JFactory::getUser();
		$etupld = in_array($user->usertype, $aduSettings) ? true : false;
		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$doc->addScript(JURI::base().'components/com_'._cppl_this_com_name.'/assets/js/'._cppl_base_com_name.'table.js');
		$doc->addStyleSheet(JURI::base().'components/com_'._cppl_this_com_name.'/'._cppl_base_com_name.'.css');

		JRequest::setVar( 'hidemainmenu', 1 );

		//get the current task
		$et_task = JRequest::getVar('task');

		//get the EasyTable
		$row =& JTable::getInstance('EasyTable', 'Table');
		$cid = JRequest::getVar( 'cid', array(0), '', 'array');
		$id = $cid[0];
		if(($id == 0) && ($et_task != 'add'))
		{
			$datatablename = JRequest::getVar('datatablename','');
			if($datatablename == "")
			{
				JError::raiseError( 500, JText::_( "COM_EASYTABLEPRO_TABLE_NO_VIEW_GO_AWAY" ) );
			}
			$id = $this->getTableIDForName($datatablename);
			if($id == 0) JError::raiseError( 500, JText::_( "COM_EASYTABLEPRO_TABLE_NO_TABLE_GO_AWAY" ).$datatablename );
		}

		$row->load($id);
		// Where do we come from
		$from = JRequest::getVar( 'from', '' );
		$default_order_sql = " ORDER BY position;";

		if($from == 'create') {
			$default_order_sql = " ORDER BY id;";
		}

		
		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,JText::_("COM_EASYTABLEPRO_TABLE_GET_STATS_DB_ERROR").' '.$id);
		}
		// Get the meta data for this table
		$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$id.$default_order_sql;
		$db->setQuery($query);
		
		$easytables_table_meta = $db->loadRowList();
		$ettm_field_count = count($easytables_table_meta);

		// Check for the existence of a matching data table
		$ettd = FALSE;
		$etet = FALSE;
		$ettd_tname = $db->getPrefix().'easytables_table_data_'.$id;
		$allTables = $db->getTableList();

		$ettd = in_array($ettd_tname, $allTables);

		$state = 'Unpublished';
		
		$ettd_datatablename = $row->datatablename;
		if($ettd_datatablename != '')
		{
			$ettd = TRUE;
			$etet = TRUE;
			$ettd_tname = $ettd_datatablename;
		}

		if( $ettd )
		{
			// Get the record count for this table
			$query = "SELECT COUNT(*) FROM ".$db->nameQuote($ettd_tname);
			$db->setQuery($query);
			$ettd_records = $db->query();

			$ettd_record_count = mysql_result($ettd_records,0);

			if($row->published)
			{
				$state = 'Published';
			}
		}
		else
		{
			$easytables_table_data ='';
			$ettd_record_count = 0;
			
			// Make sure that a table with no associated data table is never published
			$row->published = FALSE;
			$state = 'Unpublished';
		}
		

		// keep the data for the tmpl
		$this->assignRef('row', $row);
		if(!$ettd)	// Do not allow it to be published until a table is created.
		{
			$this->assignRef('published', JHTML::_('select.booleanlist', 'published', 'class="inputbox" disabled="disabled"', $row->published ));
		}
		else
		{
			$this->assignRef('published', JHTML::_('select.booleanlist', 'published', 'class="inputbox"', $row->published ));
		}
		
		// Parameters for this table instance
		$paramsdata = $row->params;
		$paramsdefs = JPATH_COMPONENT_ADMINISTRATOR.'/models/easytable.xml';
		$params = new JParameter( $paramsdata, $paramsdefs );
		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Max file size for uploading
		$umfs = ET_MgrHelpers::umfs();
		$maxFileSize = $settings->get('maxFileSize', $umfs); //Get the max file size for uploads from Pref's, default to servers PHP setting.

		$this->assignRef('params', $params);
		$this->assign('maxFileSize', $maxFileSize);

		$this->assign('id',$id);		
		$this->assignRef('state',$state);
		$this->assignRef('createddate', JHTML::date($row->created_));
		$this->assignRef('modifieddate', JHTML::date($row->modified_));
		$this->assignRef('easytables_table_meta',$easytables_table_meta);
		$this->assignRef('ettm_field_count',$ettm_field_count);
		$this->assignRef('ettd',$ettd);
		$this->assignRef('etet',$etet);
		$this->assignRef('etupld',$etupld);
		$this->assignRef('ettd_tname',$ettd_tname);
		$this->assignRef('CSVFileHasHeaders', JHTML::_('select.booleanlist', 'CSVFileHasHeaders', 'class="inputbox"', 0 ));
		if($ettd)
		{
			$this->assignRef('easytables_table_data',$easytables_table_data);
			$this->assignRef('ettd_record_count',$ettd_record_count);
		}

		parent::display($tpl);
	}// function
}// class