<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

/*
 * Frontend Component
 */

// No direct access
defined('_JEXEC') or die('Restricted Access');

// Include dependencies
jimport('joomla.application.component.controller');

$jInput = JFactory::getApplication()->input;
$vName = $jInput->get('view', 'tables');

if ($vName === 'tables')
{
	$jInput->set('task', $vName . '.' . 'display');
}

$controller = JController::getInstance('EasyTablePro');
$controller->execute($jInput->get('task'));
$controller->redirect();
