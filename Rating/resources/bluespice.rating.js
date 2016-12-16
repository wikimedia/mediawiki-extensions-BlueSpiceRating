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

OO = window.OO; //To make OOJS available within the RL modules
window.bs = window.bs || {};
window.bs.rating = window.bs.rating || {};
bs.rating.factory = new OO.Factory();
bs.rating.types = {};
bs.rating.register = function( sType, sStaticName, oClass ) {
	oClass.static.name = sStaticName;
	bs.rating.factory.register( oClass );
	bs.rating.types[sType] = sStaticName;
};
bs.rating.config = mw.config.get('BSRatingConfig', {});
bs.rating.init = function(){
	$(".bs-rating").each( function() {
		var type = $(this).attr( 'data-type' );
		if( !type || type === '' || typeof type === 'undefined' ) {
			type = 'base';
		}
		if( typeof bs.rating.types[type] === "undefined" ) {
			throw "Unregistered type: " + type;
		}
		var data = JSON.parse( $(this).attr('data-item') );
		var ratingitem = bs.rating.factory.create(
			bs.rating.types[type],
			$(this),
			type,
			data
		);
		console.log(ratingitem);
	});
};

mw.loader.using( mw.config.get('BSSocialModuleScripts', []) ).done( function() {
	bs.rating.init();
});