/**
 * Js for Rating special page
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */
( function( mw, $, bs ) {
	Ext.onReady( function() {
		Ext.Loader.setPath(
			'BS.Rating',
			bs.em.paths.get( 'BlueSpiceRating' ) + '/resources/BS.Rating'
		);
		Ext.require( 'BS.Rating.article.grid.Panel', function(){
			new BS.Rating.article.grid.Panel( {
				renderTo: 'bs-ratingarticle-grid'
			});
		});
	});
})( mediaWiki, jQuery, blueSpice );