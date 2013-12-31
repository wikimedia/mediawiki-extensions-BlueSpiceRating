/**
 * Js for Rating special page
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    1.0.0 beta
 * @version    $Id: SpecialRating.js 9041 2013-03-27 13:08:24Z pwirth $
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

RatingManagerI18N = {
	cbRatingTypeLabel: 'Type',
	cbRatingTypeEmptyText: 'Choose the type',
    titleTitle: 'Title',
    titleRating: 'Rating',
	titleVotes: 'Number of ratings',
    ptbDisplayMsgText: 'Pages {0} - {1} of {2}',
    ptbEmptyMsgText  : 'No ratings found.',
    ptbBeforePageText: 'Page',
    ptbAfterPageText : 'of {0}',
    loading: 'Loading additional information...'
}

RatingManager = Ext.extend(Ext.Panel, {
     initComponent: function() {
	
		var conf = {
			//Define standard store fields
			fields: ['reftype', 'ref', 'vote', 'votes', 'refcontent' ],

			//Define standard columns
			columns: [
				{
					id: 'reftype', 
					header:RatingManagerI18N.titleTitle, 
					dataIndex: 'ref',
					renderer: function(value, metaData, record, rowIndex, colIndex, store) {
						return record.data.refcontent;
					},
					sortable: false
				},
				{
					header: RatingManagerI18N.titleRating,
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
							stars = stars + '<img src="' + wgScriptPath + '/bluespice-mw/ext/Rating/images/star.png" />';
						}
						if(valuef >= 5) {
							value++;
							stars = stars + '<img src="' + wgScriptPath + '/bluespice-mw/ext/Rating/images/star-half.png" />';
						}
						for(var i = 0; i < 5-value; i++) {
							stars = stars + '<img src="' + wgScriptPath + '/bluespice-mw/ext/Rating/images/star-empty.png" />';
						}
						
						var content = stars + ' '+'('+gvalue+')';
						return content;
					}
				},
				{
					id: 'votes', 
					header:RatingManagerI18N.titleVotes, 
					dataIndex: 'votes'
				}
			]
		};
		
		this.cbRatingTypeFilter = new Ext.form.ComboBox({
			    fieldLabel   : RatingManagerI18N.cbRatingTypeLabel,
			    emptyText    : RatingManagerI18N.cbRatingTypeEmptyText,
			    displayField : 'reftypei18n',
			    valueField   : 'reftype',
			    typeAhead    : true,
			    triggerAction: 'all',
			    store: new Ext.data.JsonStore({
				    url   : wgScriptPath + '/index.php',
					baseParams: {
						action: 'ajax',
						rs: 'SpecialRating::ajaxGetRatingTypes'
					},
				    root  : 'types',
				    fields: ['reftype', 'reftypei18n']
			    }),
			    tpl: '<tpl for="."><div class="x-combo-list-item">{reftypei18n}</div><tpl if="xindex == 1"><hr /></tpl></tpl>'
			});

	    this.cbRatingTypeFilter.on( 'select', this.cbRatingTypeFilterSelectionChanged, this );
		
		this.jstrRatingData = new Ext.data.JsonStore({
			url: wgScriptPath + '/index.php',
			autoLoad: true,
			clearOnLoad: true,
			baseParams: {
				action: 'ajax',
				rs: 'SpecialRating::ajaxGetAllRatings'
			},
			root: 'ratings',
			/*idProperty: 'pageid',*/
			fields: conf.fields,
			totalProperty: 'total',
			remoteSort: true
		});

		this.colModel = new Ext.grid.ColumnModel({ columns: conf.columns });

		this.gdpnlRatings = new Ext.grid.GridPanel({
			loadMask: true,
			region: 'center',
			store: this.jstrRatingData,
			autoHeight: true,
			sm: new Ext.grid.RowSelectionModel({
				singleSelect:true
			}),
			viewConfig: {
				forceFit: true
			},
			colModel: this.colModel,
			stripeRows: true,
			bbar: new Ext.PagingToolbar({
			   pageSize      : 15,
			   store         : this.jstrRatingData,
			   displayInfo   : true,
			   displayMsg    : RatingManagerI18N.ptbDisplayMsgText,
			   emptyMsg      : RatingManagerI18N.ptbEmptyMsgText,
			   beforePageText: RatingManagerI18N.ptbBeforePageText,
			   afterPageText : RatingManagerI18N.ptbAfterPageText
			}),
			tbar: {
				items: [
					this.cbRatingTypeFilter,
				]
			}
		});

		//HINT:http://dev.sencha.com/deploy/ext-3.4.0/examples/grid/binding-with-classes.js
		this.items = [
			this.gdpnlRatings
		];

		RatingManager.superclass.initComponent.call(this);
    },
	
	cbRatingTypeFilterSelectionChanged: function ( oComboBox, oSelectedRecord, iSelectedIndex ) {
		this.jstrRatingData.setBaseParam( 'reftype', oSelectedRecord.get( 'reftype' ) );
		this.jstrRatingData.load();
    }
});

Ext.onReady(function() {
    var CurrentRatingManager = new RatingManager({
		renderTo: 'bs-rating-grid'
    });
    CurrentRatingManager.show();
});