<?php
/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - www.techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Tjfields.
 */
class TjfieldsViewGroups extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors));
		}

		TjfieldsHelper::addSubmenu('groups');

		$this->addToolbar();

		if(JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/tjfields.php';

		$state	= $this->get('State');
		$canDo	= TjfieldsHelper::getActions($state->get('filter.category_id'));

		JToolBarHelper::title(JText::_('COM_TJFIELDS_TITLE_GROUPS'), 'groups.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR.'/views/group';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
			    JToolBarHelper::addNew('group.add','JTOOLBAR_NEW');
		    }

		/*    if ($canDo->get('core.edit') && isset($this->items[0])) {
			    JToolBarHelper::editList('group.edit','JTOOLBAR_EDIT');
		    }*/

        }

		if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::custom('groups.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			    JToolBarHelper::custom('groups.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'groups.delete','JTOOLBAR_DELETE');
            }

          /*  if (isset($this->items[0]->state)) {
			    JToolBarHelper::divider();
			    JToolBarHelper::archiveList('groups.archive','JTOOLBAR_ARCHIVE');
            }*/
            if (isset($this->items[0]->checked_out)) {
            	JToolBarHelper::custom('groups.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
		}

        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
		    if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			    JToolBarHelper::deleteList('', 'groups.delete','JTOOLBAR_EMPTY_TRASH');
			    JToolBarHelper::divider();
		    } else if ($canDo->get('core.edit.state')) {
			    JToolBarHelper::trash('groups.trash','JTOOLBAR_TRASH');
			    JToolBarHelper::divider();
		    }
        }

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_tjfields');
		}

        $this->extra_sidebar = '';

		if(JVERSION >= '3.0')
		{
			//Set sidebar action - New in 3.0
			JHtmlSidebar::setAction('index.php?option=com_tjfields&view=groups');

			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true)
			);
		}
	}

	protected function getSortFields()
	{
		return array(
		'a.id' => JText::_('JGRID_HEADING_ID'),
		'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
		'a.state' => JText::_('JSTATUS'),
		'a.created_by' => JText::_('COM_TJFIELDS_GROUPS_CREATED_BY'),
		'a.name' => JText::_('COM_TJFIELDS_GROUPS_NAME'),
		'a.client' => JText::_('COM_TJFIELDS_GROUPS_CLIENT'),
		'a.client_type' => JText::_('COM_TJFIELDS_GROUPS_CLIENT_TYPE'),
		);
	}


}
