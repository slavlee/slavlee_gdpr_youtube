<?php
declare(strict_types = 1);
namespace Slavlee\SlavleeGdprYoutube\Hooks;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/*
 *  Copyright (c) 2017 Kevin Chileong Lee, http://www.slavlee.de
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

/**
 * Hook to disarm YouTube Embeddings and embed a 2 click solution
 * @author Kevin Chileong Lee
 * @copyright (c) 2018. Kevin Chileong Lee
 */
class YouTubeHook implements \TYPO3\CMS\Core\SingletonInterface
{
	/**
	 * TypoScript settings of this extension
	 * @var array
	 */
	protected $settings = [];
	
	/**
	 * Matches of YouTube Videos inside the HTML document
	 * @var array
	 */
	private $matches = [];
	
	/**
	 * Create a YouTubeHook
	 * @return void
	 */
	public function __construct()
	{
		$this->settings = \Slavlee\SlavleeGdprYoutube\Utility\GeneralUtility::getTypoScriptSettings();
	}
	
	/**
	 * Disarm YouTube Embedding and embed a 2 click solution
	 * $inController->content has the HTML content
	 * @param array $inParams
	 * @param \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $inController
	 * @return void
	 */
	public function disarm(&$inParams, &$inController)
	{
		// Make sure we are in the frontend
		if (TYPO3_MODE !== 'FE' || $this->settings['disable']) {	
            return;
        }
        
        // Replace youtube.com with youtube-nocookie.com
        if ($this->settings['enablePrivacyMode'])
        {
        	$this->enablePrivacyMode($inController->content);
        }
        
        if ($this->hasYouTube($inController->content))
        {
        	// disarm it
        	$this->disarmYouTube($inController->content);
        }
        
        if ($this->hasVimeo($inController->content))
        {
        	// disarm it
        	$this->disarmVimeo($inController->content);
        }
	}
	
	/**
	 * Disarm the iframr
	 * @param string $iFrame
	 * @param string $iFrameWidth
	 * @param string $iFrameHeight
	 * @param string $translateKey
	 * @return string
	 */
	protected function disarmIFrame($iFrame, $iFrameWidth, $iFrameHeight, $translateKey = 'gdpr.youtube.notice')
	{		
		return '<div class="slavleeYouTube-iframe-disarmed" data-iframe="' . base64_encode($iFrame) . '"><img src="/typo3conf/ext/slavlee_gdpr_youtube/Resources/Public/Images/play.svg" /></div><div class="slavleeYouTube-iframe-notice">' . $this->getGDPRNotice($translateKey) . '</div>';
	}
	
	/**
	 * Disarm Vimeo
	 * @param string $content
	 * @return void
	 */
	protected function disarmVimeo(&$content)
	{
		return $this->disarmYouTube($content, 'gdpr.vimeo.notice');
	}
	
	/**
	 * Disarm YouTube
	 * @param string $content
	 * @param string $translateKey
	 * @return void
	 */
	protected function disarmYouTube(&$content, $translateKey = 'gdpr.youtube.notice')
	{
		foreach($this->matches as $match)
		{
			// extract url
			$url = $this->extractUrl($match);
			
			if ($url)
			{
				// get iframe size
				$iFrameHeight = $this->getIFrameHeight($match);
				$iFrameWidth = $this->getIFrameWidth($match);
				
				// disarm iframe
				$disarmed = $this->disarmIFrame($match, $iFrameWidth, $iFrameHeight, $translateKey);
				
				// Wrap in DIV for 2 click solution				
				$content = str_replace($match, '<div class="slavleeYouTube-iframe slavleeYouTube-doubleclick"><div class="slavleeYouTube-youtube-logo slavleeYouTube-logo" style="width: ' . $iFrameWidth . '; height: ' . $iFrameHeight . ';">' . $disarmed . '</div></div>', $content);
			}
		}
	}
	
	/**
	 * Replace youtube.com with youtube-nocookie.com
	 * @param string $content
	 * @return void
	 */
	protected function enablePrivacyMode(&$content)
	{
		$content = str_replace('src="http://www.youtube.com', 'src="http://www.youtube-nocookie.com', $content);
		$content = str_replace('src="https://www.youtube.com', 'src="https://www.youtube-nocookie.com', $content);
	}
	
	/**
	 * Extract the url of the given iframe string
	 * @param string $iFrame
	 * @return string
	 */
	protected function extractUrl($iFrame)
	{
		$matches = [];
		
		if (preg_match('/src="([^"]*)"/', $iFrame, $matches))
		{
			return $matches[1];
		}
		
		return FALSE;
	}
	
	/**
	 * Return the gdpr notice with links to privacy policies
	 * @param string $translateKey
	 * @return string
	 */
	protected function getGDPRNotice($translateKey = 'gdpr.youtube.notice')
	{
		if (!empty($this->settings['notice']))
		{
			return $this->settings['notice'];
		}
		
		$privacyPageUrl = $this->getPrivacyPageUrl();
		
		return LocalizationUtility::translate($translateKey, 'slavlee_gdpr_youtube', array($privacyPageUrl));
	}
	
	/**
	 * Extract the height of the iFrame from HTML Code
	 * @param unknown $iFrame
	 */
	protected  function getIFrameHeight($iFrame)
	{
		if (!empty($this->settings['logo']['height']))
		{
			return $this->settings['logo']['height'];
		}
		
		$matches = [];
		
		if (preg_match('/height="([^"]*)"/', $iFrame, $matches))
		{
			if (preg_match('/[0-9]$/', $matches[1]))
			{
				$matches[1] .= 'px';
			}
			return $matches[1];
		}
		
		return FALSE;
	}
	
	/**
	 * Extract the width of the iFrame from HTML Code
	 * @param unknown $iFrame
	 */
	protected  function getIFrameWidth($iFrame)
	{
		if (!empty($this->settings['logo']['width']))
		{
			return $this->settings['logo']['width'];
		}
		
		$matches = [];
	
		if (preg_match('/width="([^"]*)"/', $iFrame, $matches))
		{
			if (preg_match('/[0-9]$/', $matches[1]))
			{
				$matches[1] .= 'px';
			}
			return $matches[1];
		}
	
		return FALSE;
	}
	
	/**
	 * Return privacy page url
	 * @return string
	 */
	protected function getPrivacyPageUrl()
	{
		$objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
		$uriBuilder = $objectManager->get(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);
	
		return $uriBuilder->reset()->setTargetPageUid($this->settings['privacyPage'])->build();
	}
	
	/**
	 * Checks if there are YouTube Videos on the HTML Content
	 * @param string $content
	 * @return bool
	 */
	protected function hasYouTube(&$content)
	{
		$this->matches = $iFrameMatches = [];
		
		if (preg_match_all('/<iframe[^<>]*?>[^<>]*?<\/iframe>/', $content, $iFrameMatches))
		{
			foreach($iFrameMatches[0] as $iFrameMatch)
			{
				if (preg_match('/youtube.com|youtube-nocookie.com/', $iFrameMatch))
				{
					$this->matches[] = $iFrameMatch;
				}
			}
		}
		
		return count($this->matches) > 0;
	}
	
	/**
	 * Checks if there are Vimeo Videos on the HTML Content
	 * @param string $content
	 * @return bool
	 */
	protected function hasVimeo(&$content)
	{
		$this->matches = $iFrameMatches = [];
	
		if (preg_match_all('/<iframe[^<>]*?>[^<>]*?<\/iframe>/', $content, $iFrameMatches))
		{
			foreach($iFrameMatches[0] as $iFrameMatch)
			{
				if (preg_match('/player.vimeo.com/', $iFrameMatch))
				{
					$this->matches[] = $iFrameMatch;
				}
			}
		}
	
		return count($this->matches) > 0;
	}
}