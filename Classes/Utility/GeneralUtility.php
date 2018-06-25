<?php
declare(strict_types=1);
namespace Slavlee\SlavleeGdprYoutube\Utility;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/***
 *
 * This file is part of the "Memory" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2017 Kevin Chileong Lee &lt;support@slavlee.de&gt;, Slavlee
 *
 ***/

class GeneralUtility
{
	/**
	 * Returns the current TypoScript Settings of the plugin
	 * @return array
	 */
	static public function getTypoScriptSettings()
	{
		$allTS = self::loadAllTypoScriptSettings();
		
		if (isset($allTS['page.']))
		{
			foreach($allTS['plugin.'] as $tsKey => $tsProperty)
			{
				if ($tsKey == 'tx_slavleegdpryoutube.')
				{
					$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
					/**
					 * 
					 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService
					 */
					$typoScriptService = $objectManager->get(\TYPO3\CMS\Extbase\Service\TypoScriptService::class);
					return $typoScriptService->convertTypoScriptArrayToPlainArray($tsProperty['settings.']);
				}
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Get All TypoScript Settings for this page
	 */
	static public function loadAllTypoScriptSettings() {
		$pageUid = $GLOBALS['TSFE']->id;
		/** @var \TYPO3\CMS\Frontend\Page\PageRepository $sysPageObj */
		$sysPageObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\Page\PageRepository');
		$rootLine = $sysPageObj->getRootLine($pageUid);
		/** @var \TYPO3\CMS\Core\TypoScript\ExtendedTemplateService $TSObj */
		$TSObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\TypoScript\ExtendedTemplateService');
		$TSObj->tt_track = 0;
		$TSObj->init();
		$TSObj->runThroughTemplates($rootLine);
		$TSObj->generateConfig();
	
		return $TSObj->setup;
	}
}