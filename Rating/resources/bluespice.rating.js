/**
 * Js for Rating extension
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    1.0.0 beta
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/*
 * PW(17.09.2012) replaced all .data("value") with .attr("data-value") JQuery 1.4.2 does not support data attributes
 */
BsRating = {
	init: function() {
		$('.bs-rating-item-notallowed').each( function() {
			$(this).unbind('click').bind('click', function() { //workaround: prevents from double click handler
				BlueSpice.alert( mw.message('bs-rating-not-allowed').plain() );
			});
		});

		//handle all rating items
		$('.bs-rating-item.bs-rating-stars').each( function(){
			if( typeof $(this).attr('data-votable') !== 'undefined' && $(this).attr('data-votable') === "true") {
				$(this).children('img').unbind('click').click( function() {
					BsRating.vote($(this));
				});
				$(this).children('img').unbind('mouseover').mouseover( function() { 
					BsRating.starsMouseOver($(this), $(this).attr('data-value'), $(this).siblings('img'));
				});
				$(this).children('img').unbind('mouseout').mouseout( function() { 
					BsRating.starsMouseOut($(this), $(this).parent().attr('data-value'), $(this).siblings('img'), $(this).attr('data-value'));
				});
			}
		});

		$(document).trigger('BsRatingInitDone', []);
	},
	getItemData: function( inputObject, inputAsContainer ) {
		if( typeof inputObject.attr('data-value') === 'undefined' ) return;
		var replaceID = '#' + inputObject.parent().attr('data-replace');

		if( typeof inputAsContainer === 'undefined' || inputAsContainer === false) {
			var container = inputObject.parent();
		} else {
			var container = inputObject;
		}

		var data = {
			container : container,
			ref       : container.attr('data-ref'),
			refType   : container.attr('data-reftype'),
			subType   : container.attr('data-subtype'),
			view      : container.attr('data-view'),
			value     : inputObject.attr('data-value'),
			votable   : container.attr('data-votable'),
			userID    : container.attr('data-userid'),
			replaceID : container.attr('data-replace'),
			context   : container.attr('data-context'),
			preventDefault : false
		};
		return data;
	},
	vote: function( inputObject, callback ) {
		var data = BsRating.getItemData( inputObject, false );
		$(document).trigger('BsRatingItemRate', [ data ]);
		if( data.preventDefault ) return;

		if( typeof callback === 'undefined' || callback === '' || !callback ) {
			callback = function( data ) {
				return function( result ) {
					if( result.success === true) {
						if(data.refType === 'article') {
							$('#sb-RatingState').replaceWith(result.payload['view']);
							var oSBBodyRItem = $('.bs-rating-item.bs-rating-stars.bs-statebar-body-itembody');
							if( oSBBodyRItem.length > 0 ) {
								BsRating.reload( oSBBodyRItem );
							}
						}
						$('#' + data.replaceID).replaceWith(result.payload['view']);
						BsRating.init();
					} else {
						alert(result.message);
					}
				};
			};
		}

		inputObject.parent().html( '<div id="bs-rating-load" style="width:' + inputObject.parent().width() + 'px;background:transparent url(' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-ajax-loader-bar-squere-blue.gif) center center no-repeat" >&nbsp;</div>');

		var Api = new mw.Api();
		var taskdata = {};
		for( var i in data ) {
			if( i === 'container' || typeof data[i] === "undefined" ) {
				continue;
			}
			taskdata[i] = data[i];
		}

		taskdata.articleID = wgArticleId;
		Api.post({
			action: 'rating',
			task: 'vote',
			token: mw.user.tokens.get( 'editToken' ),
			taskData: Ext.encode(taskdata)
		}, {
			ok: callback(data)
		});
	},
	reload: function( inputObject ) {
		var data = BsRating.getItemData( inputObject, true );
		$(document).trigger('BsRatingItemReload', [ data ]);
		if( data.preventDefault ) return;

		var callback = function( data ) {
			return function( result ) {
				if( result.success === true ) {
					$('#' + data.replaceID).replaceWith(result.payload['view']);
					BsRating.init();
				} else {
					//alert(result['msg']);
				}
			};
		};

		inputObject.html( '<div class="bs-rating-load" style="width:' + inputObject.parent().width() + 'px;background:transparent url(' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-ajax-loader-bar-squere-blue.gif) center center no-repeat" >&nbsp;</div>');
		var Api = new mw.Api();
		var taskdata = {};
		for( var i in data ) {
			if( i === 'container' || typeof data[i] === "undefined" ) {
				continue;
			}
			taskdata[i] = data[i];
		}

		Api.post({
			action: 'rating',
			task: 'reloadRating',
			token: mw.user.tokens.get( 'editToken' ),
			taskData: Ext.encode( taskdata )
		}, {
			ok: callback(data)
		});
	},
	starsMouseOut: function(currElement, currValue, siblings, value) {
		if( typeof value === 'undefined' ) return;

		var currValueF = 0;
		var aCurVal = (currValue + "").split(".");
		currValue = parseInt(aCurVal[0]);
		if( typeof aCurVal[1] !== 'undefined') {
			currValueF = parseInt(aCurVal[1]);
		}

		siblings.push(currElement);
		for(var i = 0; i <= siblings.length; i++) {
			var sibling = $(siblings[i]);

			if( sibling.attr('data-value') <= currValue) {
				sibling.attr("src", wgScriptPath + '/extensions/BlueSpiceRating/Rating/resources/images/star.png');
			} else {
				if(currValue === 0) {
					sibling.attr("src", wgScriptPath + '/extensions/BlueSpiceRating/Rating/resources/images/star-notrated.png');
				} else {
					if( currValueF >= 5 && parseInt(sibling.attr('data-value')) - 1 === currValue) {
						sibling.attr("src", wgScriptPath + '/extensions/BlueSpiceRating/Rating/resources/images/star-half.png');
					} else {
						sibling.attr("src", wgScriptPath + '/extensions/BlueSpiceRating/Rating/resources/images/star-empty.png');
					}
				}
			}
		}
	},
	starsMouseOver: function(currElement, value, siblings) {
		if( typeof value === 'undefined' ) return;

		currElement.attr("src", wgScriptPath + '/extensions/BlueSpiceRating/Rating/resources/images/star.png');
		for(var i = 0; i <= siblings.length; i++) {
			var sibling = $(siblings[i]);
			if( sibling.attr('data-value') <= value) {
				sibling.attr("src", wgScriptPath + '/extensions/BlueSpiceRating/Rating/resources/images/star.png');
			} else { 
				sibling.attr("src", wgScriptPath + '/extensions/BlueSpiceRating/Rating/resources/images/star-empty.png');
			}
		}
	}
};

$(document).bind( 'BsStateBarBodyLoadComplete', function(event, data) {
	BsRating.init();
});

$(document).bind( 'BsRatingItemRate', function(event, data) {
	if(data.view !== 'ViewStateBarBodyElementRating') return;
	data.preventDefault = true;
	var inputObject = $('.bs-rating-item.bs-rating-stars.bs-statebar-body-itembody').parent();
	data.inputObject = inputObject;

	var callback = function( data ) {
		return function( result ) {
			if( result.success === true) {
				data.inputObject.replaceWith(result.payload['view']);
				var oHeadRItem = $('.bs-rating-item.bs-rating-stars.bs-statebar-top-icon, .bs-rating-item.bs-rating-stars.bs-headline-rating');
				if( typeof oHeadRItem !== 'undefined' ) {
					BsRating.reload( oHeadRItem );
				}
				//BsRating.init();
			} else {
				//alert(result['msg']);
			}
		};
	};

	inputObject.html( '<div id="bs-rating-load" style="width:' + inputObject.width() + 'px;background:transparent url(' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-ajax-loader-bar-squere-blue.gif) center center no-repeat" >&nbsp;</div>');
	var Api = new mw.Api();
	var taskdata = {};
	for( var i in data ) {
		if( i === 'container' || typeof data[i] === "undefined" || i === 'inputObject' ) {
			continue;
		}
		taskdata[i] = data[i];
	}

	taskdata.articleID = wgArticleId;
	Api.post({
		action: 'rating',
		task: 'vote',
		token: mw.user.tokens.get( 'editToken' ),
		taskData: Ext.encode(taskdata)
	}, {
		ok: callback(data)
	});
});

$(document).ready(function(){
	BsRating.init();
});