<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @link       http://www.seepeoplesoftware.com
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport( 'joomla.application.component.model' );

/**
 * EasyTableMeta Model
 *
 * @package    EasyTables
 * @subpackage Models
 */
class EasyTableModelEasyTableMeta extends JModel
{
	var $_data = null;
	
	/**
	 * Gets the tables
	 * @return data
	 */
	function &getData($id)
	{
		if (empty($this->_data))
			{
			// @todo change to using new query format for better db support
				$query = "SELECT * FROM #__easytables_table_meta WHERE id = '$id' ORDER BY easytablename ASC";
				
				$this->_data = $this->_getList($query);
			}
		return $this->_data;
	}// function
}// class
