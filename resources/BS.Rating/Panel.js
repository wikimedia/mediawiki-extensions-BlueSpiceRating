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

Ext.define( 'BS.Rating.Panel', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],
	id: 'bs-specialrating-extgrid',
	features: [],

	initComponent: function() {
		this.strMain = new BS.store.BSApi( {
			apiAction: 'bs-ratingarticle-store',
			fields: [ 'page_title', 'rat_reftype', 'rat_ref', 'vote', 'votes', 'refcontent' ]
		});

		this.columns = [{
			id: 'reftype',
			header: mw.message('bs-rating-specialrating-titleTitle').plain(),
			dataIndex: 'page_title',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				return record.data.refcontent;
			},
			sortable: true,
			filterable: true
		},{
			header: mw.message('bs-rating-specialrating-titleRating').plain(),
			sortable: true,
			dataIndex: 'vote',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				return value;
			}
		},{
			id: 'votes',
			header:mw.message('bs-rating-specialrating-titleVotes').plain(),
			dataIndex: 'votes',
			sortable: true
		}];

		this.filters = Ext.create('Ext.ux.grid.FiltersFeature', {
			encode: true,
			local: false,
			filters: [{
				type: 'numeric',
				dataIndex: 'vote',
				menuItems: ['gt', 'lt', 'eq']
			}, {
				type: 'numeric',
				dataIndex: 'votes',
				menuItems: ['gt', 'lt', 'eq']
			}]
		});

		this.gpMainConf.features = [this.filters];

		this.colMainConf.columns = this.columns;
		this.callParent( arguments );
	},

	makeActionColumn: function( cols ) {
		return false;
	},
	makeTbar : function() {
		return false;
	}
});