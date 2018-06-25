#################################
## CONSTATNS CATEGORIES
#################################
# customsubcategory=100=LLL:EXT:slavlee_gdpr_youtube/Resources/Private/Language/locallang_typoscript.xlf:cat_logo
# customsubcategory=200=LLL:EXT:slavlee_gdpr_youtube/Resources/Private/Language/locallang_typoscript.xlf:cat_settings

###################################################################
# PLUGINS - START
###################################################################
plugin.tx_slavleegdpryoutube {
	settings {
		logo {
			# cat=slavlee gdpr youtube/100/100; type=string; label=Custom logo width
			width = 0
			# cat=slavlee gdpr youtube/100/100; type=string; label=Custom logo height
			height = 0
		}
		# cat=slavlee gdpr youtube/200/100; type=bool; label=Disable replacement
		disable = 0
		# cat=slavlee gdpr youtube/200/200; type=bool; label=Enable privacy mode
		enablePrivacyMode = 1
		# cat=slavlee gdpr youtube/200/300; type=int+; label=Privacy page
		privacyPage = 0
		# cat=slavlee gdpr youtube/200/400; type=string; label=jQuery
		jquery = EXT:slavlee_gdpr_youtube/Resources/Public/Js/jquery-3.3.1.min.js
	}
}