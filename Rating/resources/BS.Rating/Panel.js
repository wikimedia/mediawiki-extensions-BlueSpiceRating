/**
 * Js for Rating special page
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.22.0
 * @version    $Id: SpecialRating.js 9041 2013-03-27 13:08:24Z pwirth $
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.Rating.Panel', {
	extend: 'BS.Panel',
	height: '600px',

	initComponent: function() {

		this.callParent( arguments );
	},

	afterInitComponent: function() {
		this.strMain = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url : bs.util.getAjaxDispatcherUrl( 
					'SpecialRating::ajaxGetAllRatings'
				),
				reader: {
					type: 'json',
					root: 'ratings',
					totalProperty: 'total',
					idProperty: 'ref'
				}
			},
			autoLoad: true,
			remoteSort: true,
			sorters: [{
				property: 'ref',
				direction: 'ASC'
			}],
			fields: ['reftype', 'ref', 'vote', 'votes', 'refcontent' ]
		});

		this.strTypes = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url : bs.util.getAjaxDispatcherUrl( 
					'SpecialRating::ajaxGetRatingTypes'
				),
				reader: {
					type: 'json',
					root: 'types',
					totalProperty: 'total',
					idProperty: 'reftype'
				}
			},
			autoLoad: true,
			remoteSort: true,
			fields: ['reftype', 'reftypei18n']
		});

		this.cbRatingTypeFilter = Ext.create('Ext.form.ComboBox', {
			fieldLabel   : mw.message('bs-rating-specialrating-cbRatingTypeLabel').plain(),
			emptyText    : mw.message('bs-rating-specialrating-cbRatingTypeEmptyText').plain(),
			displayField : 'reftypei18n',
			valueField   : 'reftype',
			typeAhead    : true,
			triggerAction: 'all',
			store: this.strTypes,
			tpl: '<tpl for="."><div class="x-combo-list-item">{reftypei18n}</div><tpl if="xindex == 1"><hr /></tpl></tpl>'
		});

		//this.cbRatingTypeFilter.on( 'select', this.cbRatingTypeFilterSelectionChanged, this );
		
		this.columns = [
			{
				id: 'reftype', 
				header: mw.message('bs-rating-specialrating-titleTitle').plain(), 
				dataIndex: 'ref',
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					return record.data.refcontent;
				},
				sortable: true
			},
			{
				header: mw.message('bs-rating-specialrating-titleRating').plain(),
				sortable: true,
				dataIndex: 'vote',
				renderer: function(value, metaData, record, rowIndex, colIndex, store) {
					var stars = '';
					var gvalue = value;
					var valuef = 0;
					var aCurVal = (value + "").split(".");
					value = parseInt(aCurVal[0]);
					if( aCurVal[1] != undefined) {
						valuef = parseInt(aCurVal[1]);
					}

					for(var i = 0; i < value; i++) {
						stars = stars + '<img src="' + mw.config.get( 'wgScriptPath') + '/extensions/BlueSpiceRating/Rating/resources/images/star.png" />';
					}
					if(valuef >= 5) {
						value++;
						stars = stars + '<img src="' + mw.config.get( 'wgScriptPath') + '/extensions/BlueSpiceRating/Rating/resources/images/star-half.png" />';
					}
					for(var i = 0; i < 5-value; i++) {
						stars = stars + '<img src="' + mw.config.get( 'wgScriptPath') + '/extensions/BlueSpiceRating/Rating/resources/images/star-empty.png" />';
					}

					var content = stars + ' '+'('+gvalue+')';
					return content;
				}
			},
			{
				id: 'votes', 
				header:mw.message('bs-rating-specialrating-titleVotes').plain(), 
				dataIndex: 'votes'
			}
		];
		
		//this.colModel = Ext.create('Ext.grid.ColumnModel', { columns: this.columns });

		this.filters = Ext.create('Ext.ux.grid.FiltersFeature', {
			encode: true,
			local: false,
			filters: [{
				type: 'string',
				dataIndex: 'ref',
				menuItems: ['ct']
			}, {
				type: 'numeric',
				dataIndex: 'vote',
				menuItems: ['gt', 'lt', 'eq']
			}, {
				type: 'numeric',
				dataIndex: 'votes',
				menuItems: ['gt', 'lt', 'eq']
			}]
		});

		this.gdpnlRatings = Ext.create('Ext.grid.GridPanel',{
			features: [this.filters],
			loadMask: true,
			region: 'center',
			store: this.strMain,
			autoHeight: true,
			sm: Ext.create( 'Ext.selection.RowModel', {
				mode: "SINGLE"
			}),
			viewConfig: {
				forceFit: true
			},
			//colModel: this.colModel,
			columns: { 
				items: this.columns,
				defaults: {
					flex: 1
				}
			},
			stripeRows: true,
			bbar: Ext.create('Ext.PagingToolbar', {
				pageSize      : 15,
				store         : this.strMain,
				displayInfo   : true,
				displayMsg    : mw.message('bs-rating-specialrating-ptbDisplayMsgText').plain(),
				emptyMsg      : mw.message('bs-rating-specialrating-ptbEmptyMsgText').plain(),
				beforePageText: mw.message('bs-rating-specialrating-ptbBeforePageText').plain(),
				afterPageText : mw.message('bs-rating-specialrating-ptbAfterPageText').plain()
			}),
			tbar: {
				items: [
					//this.cbRatingTypeFilter, not needed
				]
			}
		});

		this.items = [
			this.gdpnlRatings
		];
	},

	cbRatingTypeFilterSelectionChanged: function ( oComboBox, oSelectedRecord, iSelectedIndex ) {
		this.jstrRatingData.setBaseParam( 'reftype', oSelectedRecord.get( 'reftype' ) );
		this.jstrRatingData.load();
	}
});