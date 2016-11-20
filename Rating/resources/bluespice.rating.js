/**
 * Js for Rating extension
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.27.0
 * @package    BluespiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

BSRating = {};
BSRatingItem = {};
BSRating.config = mw.config.get('BSRatingConfig', {});
BSRating.init = function(){
	$(".bs-rating").each( function() {
		console.log(this);
		var $rating = $(this).starRating({
			starSize: 14,
			useFullStars: true,
			disableAfterRate: false,
			strokeWidth: 9,
			strokeColor: 'black',
			initialRating: 0,
			starGradient: {
				start: '#93BFE2',
				end: '#105694'
			},
			callback: function(currentRating, $el){
				$el.starRating( 'setReadOnly', true );
				BSRating.addLoadingMask( $el );
				bs.api.tasks.execSilent(
					'rating',
					'vome',
					{}
				).done( function( result ) {
					console.log(result);
					$el.starRating( 'setReadOnly', false );
					BSRating.removeLoadingMask( $el );
				});
			}
		});
	});
};
BSRating.addLoadingMask = function( $el ) {
	$el.append(
		'<div class="bs-rating-loading-overlay">'
		+ '<div class="bs-rating-loader"></div>'
		+ '</div>'
	);
};
BSRating.removeLoadingMask = function( $el ) {
	$el.find('.bs-rating-loading-overlay').remove();
};

var aModules = mw.config.get( 'BSSocialModuleScripts', [] );
mw.loader.using( aModules ).done(function() {
	BSRating.init();
});

$(document).on( 'BsStateBarRegisterToggleClickElements', function(event, elements) {
	elements.push( $('#bs-rating-statelink') );
});