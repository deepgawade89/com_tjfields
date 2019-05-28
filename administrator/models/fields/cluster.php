<?php
/**
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of allocated cluster
 *
 * @since  __DEPLOY_VERSION__
 */

class JFormFieldCluster extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Cluster';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	__DEPLOY_VERSION__
	 */
	protected $loadExternally = 0;

	/**
	 * The form field value.
	 *
	 * @var    mixed
	 * @since  __DEPLOY_VERSION__
	 */
	protected $value = '';

	/**
	 * Method to get a list of options for cluster field.
	 *
	 * @return array An array of JHtml options.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$user = Factory::getUser();
		$app = Factory::getApplication();
		$clusterId = $app->input->getInt('cluster_id', 0);

		$options = array();

		if (!$user->id)
		{
			return $options;
		}

		if (!empty($clusterId))
		{
			$this->value = $clusterId;
		}

		$superUser    = $user->authorise('core.admin');

		// Initialize array to store dropdown options
		$options[] = HTMLHelper::_('select.option', "", Text::_('COM_TJFIELDS_OWNERSHIP_CLUSTER'));

		// Get com_subusers component status
		$clusterExist = ComponentHelper::getComponent('com_cluster', true)->enabled;

		if (!$clusterExist)
		{
			return $options;
		}

		JLoader::import("/components/com_cluster/includes/cluster", JPATH_ADMINISTRATOR);
		$ClusterModel = ClusterFactory::model('ClusterUsers', array('ignore_request' => true));
		$ClusterModel->setState('list.group_by_client_id', 1);
		$ClusterModel->setState('filter.published', 1);

		if (!$superUser && !$user->authorise('core.manageall.cluster', 'com_cluster'))
		{
			$ClusterModel->setState('filter.user_id', $user->id);
		}

		// Get all assigned cluster entries
		$clusters = $ClusterModel->getItems();

		// Get com_subusers component status
		$subUserExist = ComponentHelper::getComponent('com_subusers', true)->enabled;

		if ($subUserExist)
		{
			JLoader::import("/components/com_subusers/includes/rbacl", JPATH_ADMINISTRATOR);
		}

		if (!empty($clusters))
		{
			foreach ($clusters as $cluster)
			{
				// Check rbacl component active and normal user is logged-in

				if ($subUserExist && (!$superUser && !$user->authorise('core.manageall.cluster', 'com_cluster')))
				{
					// Check user has permission for mentioned cluster
					if (RBACL::authorise($user->id, 'com_cluster', 'core.manage.cluster', $cluster->cluster_id))
					{
						$options[] = HTMLHelper::_('select.option', $cluster->cluster_id, trim($cluster->name));
					}
				}
				else
				{
					$options[] = HTMLHelper::_('select.option', $cluster->cluster_id, trim($cluster->name));
				}
			}
		}

		return $options;
	}
}