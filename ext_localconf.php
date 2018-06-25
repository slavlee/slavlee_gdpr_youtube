<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function($extKey)
	{
		/**
		 * hook is called before Caching / pages on their way in the cache.
		 */
		$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] = 'Slavlee\SlavleeGdprYoutube\Hooks\YouTubeHook->disarm';
	},
	$_EXTKEY
);