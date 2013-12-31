<?php
$sDir = __DIR__;
BsExtensionManager::registerExtension( 
	'Rating', 
	BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, 
	BsACTION::LOAD_SPECIALPAGE, 
	$sDir 
);

$wgExtensionMessagesFiles['Rating'] = "$sDir/languages/Rating.i18n.php";
$wgExtensionMessagesFiles['RatingMagic'] = "$sDir/languages/Rating.i18n.magic.php";
$wgExtensionMessagesFiles['RatingAlias'] = "$sDir/languages/SpecialRating.alias.php";

$wgAutoloadClasses['RatingItem'] = "$sDir/includes/RatingItem.class.php";
$wgAutoloadClasses['SpecialRating'] = "$sDir/includes/specials/SpecialRating.class.php";

$wgAutoloadClasses['ViewRatingItemLike'] = "$sDir/views/view.RatingItemLike.php";
$wgAutoloadClasses['ViewRatingItemStars'] = "$sDir/views/view.RatingItemStars.php";
$wgAutoloadClasses['ViewHeadlineElementRating'] = "$sDir/views/view.HeadlineElementRating.php";
$wgAutoloadClasses['ViewStateBarTopElementRating'] = "$sDir/views/view.StateBarTopElementRating.php";
$wgAutoloadClasses['ViewStateBarBodyElementRating'] = "$sDir/views/view.StateBarBodyElementRating.php";

$wgSpecialPages['SpecialRating'] = 'SpecialRating';
$wgSpecialPageGroups['SpecialRating'] = 'bluespice';

$aResourceModuleTemplate = array(
	'dependencies' => 'ext.bluespice',
	'localBasePath' => $IP . '/extensions/BlueSpiceRating/Rating/resources',
	'remoteExtPath' => 'BlueSpiceRating/Rating/resources'
);
$wgResourceModules['ext.bluespice.rating.styles'] = array(
	'styles' => 'bluespice.rating.css',
	'position' => 'top',
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.rating'] = array(
	'scripts' => 'bluespice.rating.js',
	'position' => 'bottom',
	'messages' => array(
		//'bs-flaggedrevsconnector-response-success',
		//'bs-flaggedrevsconnector-response-failure',
	),
) + $aResourceModuleTemplate;

$wgAjaxExportList[] = 'Rating::ajaxVote';
$wgAjaxExportList[] = 'Rating::ajaxReloadRating';

$wgAjaxExportList[] = 'SpecialRating::ajaxGetRatingTypes';
$wgAjaxExportList[] = 'SpecialRating::ajaxGetAllRatings';