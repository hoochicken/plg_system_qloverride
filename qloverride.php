<?php
/**
 * @package        plg_system_qloverride
 * @copyright    Copyright (C) 2024 ql.de All rights reserved.
 * @author        Mareike Riegel mareike.riegel@ql.de
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

//no direct access
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.plugin.plugin');

class plgSystemQloverride extends CMSPlugin
{
    private const APP_CLIENT_ADMINISTRATOR = 'administrator';
    private const APP_CLIENT_SITE = 'site';
    private const APP_CLIENT_API = 'api';
    private const OPERATING_SYSTEM_UNKNOWN = 'unknown';
    private const OPERATING_SYSTEM_LINUX = 'linux';
    private const OPERATING_SYSTEM_WINDOWS = 'windows';

    public function __construct(&$subject, $config)
    {
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('plg_content_qloverride', dirname(__FILE__));
        parent::__construct($subject, $config);
    }

    public function onAfterRoute()
    {
        // generate basic variables
        $app = Factory::getApplication();
        $currentComponent = $app->input->get('option');
        $operatingSystem = $this->params->get('operating_system', '');

        $adminComponentsToOverride = explode(',', trim($this->params->get('component_name_administrator', '')));
        array_walk($adminComponentsToOverride, function(&$item) { $item = trim($item);});

        $siteComponentsToOverride = explode(',', trim($this->params->get('component_name_site', '')));
        array_walk($siteComponentsToOverride, function(&$item) { $item = trim($item);});

        if ($app->isClient(static::APP_CLIENT_ADMINISTRATOR) && in_array($currentComponent, $adminComponentsToOverride)) {
            $this->overrideAdmin($currentComponent, $operatingSystem);
        }

        if ($app->isClient(static::APP_CLIENT_SITE) && in_array($currentComponent, $siteComponentsToOverride)) {
            $this->overrideSite($currentComponent);
        }
    }

    private function overrideAdmin(string $componentName, string $operatingSystem)
    {
        $slash = in_array($operatingSystem, [static::OPERATING_SYSTEM_LINUX,])
            ? '/'
            : '\\';
        Form::addFormPath(sprintf('%s%s/administrator/components/%s/models/forms', JPATH_SITE, $slash, $componentName));
        Form::addFormPath(sprintf('%s/templates/system/forms/%s', JPATH_ADMINISTRATOR, $componentName));
    }

    private function overrideSite(string $componentName)
    {
        Form::addFormPath(sprintf('%s/components/%s/models/forms', JPATH_SITE, $componentName));
        Form::addFormPath(sprintf('%s/templates/system/forms/%s', JPATH_SITE, $componentName));
    }
}
