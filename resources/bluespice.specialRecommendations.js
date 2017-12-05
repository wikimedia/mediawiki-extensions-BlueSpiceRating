/**
 * Js for Rating special page
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
Ext.Loader.setPath(
	'BS.Rating',
	bs.em.paths.get( 'BlueSpiceRating' ) + '/resources/BS.Rating'
);
Ext.create( 'BS.Rating.articlelike.grid.Panel', {
	renderTo: 'bs-ratingarticlelike-grid'
});