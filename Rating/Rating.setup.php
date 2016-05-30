<?php
$sDir = $IP.DS.'extensions/BlueSpiceRating/Rating';
BsExtensionManager::registerExtension( 
	'Rating', 
	BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, 
	BsACTION::LOAD_SPECIALPAGE, 
	$sDir 
);

$wgExtensionMessagesFiles['Rating'] = "$sDir/languages/Rating.i18n.php";
$wgExtensionMessagesFiles['RatingMagic'] = "$sDir/languages/Rating.i18n.magic.php";
$wgExtensionMessagesFiles['RatingAlias'] = "$sDir/languages/SpecialRating.alias.php";

$wgMessagesDirs['WatchList'] = "$sDir/i18n";

$wgAutoloadClasses['Rating'] = "$sDir/Rating.class.php";
$wgAutoloadClasses['RatingItem'] = "$sDir/includes/RatingItem.class.php";
$wgAutoloadClasses['BSApiTasksRating'] = "$sDir/includes/api/BSApiTasksRating.php";
$wgAutoloadClasses['SpecialRating'] = "$sDir/includes/specials/SpecialRating.class.php";

$wgAutoloadClasses['ViewRatingItemLike'] = "$sDir/views/view.RatingItemLike.php";
$wgAutoloadClasses['ViewRatingItemStars'] = "$sDir/views/view.RatingItemStars.php";
$wgAutoloadClasses['ViewHeadlineElementRating'] = "$sDir/views/view.HeadlineElementRating.php";
$wgAutoloadClasses['ViewStateBarTopElementRating'] = "$sDir/views/view.StateBarTopElementRating.php";
$wgAutoloadClasses['ViewStateBarBodyElementRating'] = "$sDir/views/view.StateBarBodyElementRating.php";

$wgSpecialPages['Rating'] = 'SpecialRating';
$wgSpecialPageGroups['Rating'] = 'bluespice';

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
	'dependencies' => array(
		'ext.bluespice',
	),
	'position' => 'bottom',
	'messages' => array(
		'bs-rating-not-allowed',
	),
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.specialRating'] = array(
	'scripts' => 'bluespice.specialRating.js',
	'dependencies' => array(
		'ext.bluespice.extjs',
	),
	'messages' => array(
		'bs-rating-specialrating-cbRatingTypeLabel',
	'bs-rating-specialrating-cbRatingTypeEmptyText',
	'bs-rating-specialrating-titleTitle',
	'bs-rating-specialrating-titleRating',
	'bs-rating-specialrating-titleVotes',
	'bs-rating-specialrating-ptbDisplayMsgText',
	'bs-rating-specialrating-ptbEmptyMsgText',
	'bs-rating-specialrating-ptbBeforePageText',
	'bs-rating-specialrating-ptbAfterPageText',
	)
) + $aResourceModuleTemplate;

//$wgAjaxExportList[] = 'Rating::ajaxVote';
//$wgAjaxExportList[] = 'Rating::ajaxReloadRating';
$wgAjaxExportList[] = 'SpecialRating::ajaxGetRatingTypes';
$wgAjaxExportList[] = 'SpecialRating::ajaxGetAllRatings';

$wgAPIModules['rating'] = 'BSApiTasksRating';

$wgHooks['LoadExtensionSchemaUpdates'][] = 'Rating::getSchemaUpdates';
unset($aResourceModuleTemplate);
unset($sDir);