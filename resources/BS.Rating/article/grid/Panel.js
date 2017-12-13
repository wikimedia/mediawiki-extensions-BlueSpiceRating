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

Ext.define( 'BS.Rating.article.grid.Panel', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],
	id: 'bs-specialrating-extgrid',
	features: [],

	initComponent: function() {
		this.strMain = new BS.store.BSApi( {
			apiAction: 'bs-ratingarticle-store',
			fields: [
				'page_namespace',
				'page_title',
				'rat_reftype',
				'rat_ref',
				'average',
				'totalcount',
				'content',
				'item'
			]
		});
		this.strMain.on( 'load', function( store, records, options ) {
			bs.rating.init();
		});  

		var filter = [];
		var namespaces = mw.config.get(
			'bsgRatingArticleAcitveNamespaces',
			{}
		);
		for( var idx in namespaces ) {
			if( !namespaces[idx] ) {
				continue;
			}
			filter.push( namespaces[idx] );
		}

		this.columns = [{
			id: 'page_namespace',
			header: mw.message ('bs-rating-specialrating-label-namespace' ).plain(),
			dataIndex: 'page_namespace',
			sortable: true,
			filterable: true,
			filter: {
				type: 'list',
				options: filter
			}
		},{
			id: 'page_title',
			header: mw.message( 'bs-rating-specialrating-titleTitle' ).plain(),
			dataIndex: 'page_title',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				var ns = bs.util.getNamespaceText(
					record.get( 'page_namespace' )
				);
				if( ns && ns !== "" ) {
					value = ns + ':' + value;
				}
				return '<a href = "' + mw.util.wikiGetlink( value ) + '">'
					+ value
					+ '</a>';
			},
			sortable: true,
			filterable: true
		},{
			header: mw.message( 'bs-rating-specialrating-titleRating' ).plain(),
			sortable: true,
			dataIndex: 'average',
			renderer: function(value, metaData, record, rowIndex, colIndex, store) {
				return record.get('content');
			}
		},{
			id: 'votes',
			header:mw.message( 'bs-rating-specialrating-titleVotes' ).plain(),
			dataIndex: 'totalcount',
			sortable: true
		}];

		this.filters = Ext.create('Ext.ux.grid.FiltersFeature', {
			encode: true,
			local: false,
			filters: [/*{ is not working -.-
				/*type: 'list',
				dataIndex: 'page_namepsace',
				options: ['Foo', 'Bar']
			},*/{
				type: 'string',
				dataIndex: 'page_title',
				menuItems: ['ct', 'nct', 'sw', 'ew', 'eq', 'neq']
			},{
				type: 'numeric',
				dataIndex: 'average',
				menuItems: ['gt', 'lt', 'eq']
			}, {
				type: 'numeric',
				dataIndex: 'totalcount',
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