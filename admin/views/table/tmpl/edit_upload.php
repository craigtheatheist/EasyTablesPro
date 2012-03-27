<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 27 March 2012
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
?>
<fieldset class="adminform" id="tableimport">
	<legend><?php echo JText::_('COM_EASYTABLEPRO_UPLOAD_A_DATA_FILE_LEGEND'); ?></legend>
	<!-- MAX_FILE_SIZE must precede the file input field -->
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->maxFileSize ?>" />
	<label for="fileInputBox"><?php
		if($this->item->ettd) {
			echo JText::_( 'COM_EASYTABLEPRO_TABLE_SELECT_AN_UPDATE_FILE' ); 
		} else {
			echo JText::_( 'COM_EASYTABLEPRO_TABLE_SELECT_A_NEW_CSV_FILE' );
		}
	?>:</label><input name="tablefile" type="file" id="fileInputBox" />
	<?php if($this->item->ettd) {
		echo '<input type="button" value="'.JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN' ).'" onclick="javascript: submitbutton(\'updateETDTable\')" id="fileUploadBtn" />';
	} else {
		echo '<input type="button" value="'.JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_BTN' ).'" onclick="javascript: submitbutton(\'createETDTable\')" id="fileUploadBtn" />';
	} ?><legend style="clear:both;"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_FILE_HAS_HEADINGS' ); ?></legend>
	<label for="CSVFileHasHeaders0" id="CSVFileHasHeaders0-lbl" class="radiobtn">No</label>
	<input type="radio" name="CSVFileHasHeaders" id="CSVFileHasHeaders0" value="0" checked="checked" class="inputbox">
	<label for="CSVFileHasHeaders1" id="CSVFileHasHeaders1-lbl" class="radiobtn">Yes</label>
	<input type="radio" name="CSVFileHasHeaders" id="CSVFileHasHeaders1" value="1" class="inputbox">
	<div id="uploadWhileModifyingNotice">
		<p><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_DISABLED_TABLE_MODIFIED_MSG' ); ?></p>
		<p><em><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_RE_ENABLE_BY_SAVING_MSG' ); ?></em></p>
	</div>
<?php if($this->item->ettd) { // For those uses that manage to save a table without importing data... ?>
	<legend style="clear:both;"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_INTENTION_TT' );?></legend>
	<label for="uploadType0"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_REPLACE' ); ?></label>
	<input type="radio" name="uploadType" id="uploadType0" value="0" class="inputbox" checked="checked" />
	<label for="uploadType1"><?php echo JText::_( 'COM_EASYTABLEPRO_TABLE_UPLOAD_APPEND' ); ?></label>
	<input type="radio" name="uploadType" id="uploadType1" value="1" class="inputbox" />
<?php }; ?>
</fieldset>