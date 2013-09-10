/**
 * Js for Rating extension
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    1.0.0 beta
 * @version    $Id: Rating.js 10349 2013-09-10 08:57:06Z pwirth $
 * @package    Bluespice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

BsRatinI18n = {
	ratingItemNotAllowed: 'Unfortunatley, you are not allowed to rate.'
};
/*
 * PW(17.09.2012) replaced all .data("value") with .attr("data-value") JQuery 1.4.2 does not support data attributes
 */
BsRating = {
	init: function() {
		var oStateLink = $('#bs-rating-statelink');
		if( oStateLink ){
			oStateLink.unbind('click').bind('click', function() { //workaround: prevents from double click handler
				$('#bs-statebar-view').slideToggle( 'fast' );
				return false; //Prevent normal function of the anchor tag.
			});
		}
		$('.bs-rating-item-notallowed').each( function() {
			$(this).unbind('click').bind('click', function() { //workaround: prevents from double click handler
				BlueSpice.alert( BsRatinI18n.ratingItemNotAllowed );
			});
		});

		//handle all rating items
		$('.bs-rating-item.bs-rating-stars').each( function(){
			if( $(this).attr('data-votable') !== undefined && $(this).attr('data-votable') === "true") {
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
		if( inputObject.attr('data-value') === undefined ) return;
		var replaceID = '#' + inputObject.parent().attr('data-replace');

		if( inputAsContainer === undefined || inputAsContainer === false) {
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
			votable	  : container.attr('data-votable'),
			userID    : container.attr('data-userid'),
			replaceID : container.attr('data-replace'),
			preventDefault : false
		}
		return data;
	},
	vote: function( inputObject, callback ) {
		var data = BsRating.getItemData( inputObject, false );
		$(document).trigger('BsRatingItemRate', [ data ]);
		if( data.preventDefault ) return;

		if( callback === undefined || callback == '') {
			callback = function( data ) {
				return function( result ) {
					if( result['success'] == true) {
						if(data.refType == 'article') {
							$('#sb-RatingState').replaceWith(result['view']);
							var oSBBodyRItem = $('.bs-rating-item.bs-rating-stars.bs-statebar-body-itembody');
							if( oSBBodyRItem !== undefined ) {
								BsRating.reload( oSBBodyRItem );
							}
						}
						$('#' + data.replaceID).replaceWith(result['view']);
						BsRating.init();
					} else {
						//alert(result['msg']);
					}
				}
			}
		}

		inputObject.parent().html( '<div id="bs-rating-load" style="width:' + inputObject.parent().width() + 'px;background:transparent url(' + wgScriptPath + '/bluespice-core/resources/bluespice/images/bs-ajax-loader-bar-squere-blue.gif) center center no-repeat" >&nbsp;</div>');
		$.getJSON(
			wgScriptPath + '/index.php',
			{
				action:'ajax',
				rs:'Rating::ajaxVote',
				rsargs: [
					data.refType,
					data.ref,
					data.value,
					data.view,
					data.votable,
					data.userID,
					wgArticleId
				]
			},
			callback( data )
		);
	},
	reload: function( inputObject ) {
		var data = BsRating.getItemData( inputObject, true );
		$(document).trigger('BsRatingItemReload', [ data ]);
		if( data.preventDefault ) return;

		var callback = function( data ) {
			return function( result ) {
				if( result['success'] == true) {
					$('#' + data.replaceID).replaceWith(result['view']);
					BsRating.init();
				} else {
					//alert(result['msg']);
				}
			}
		}

		inputObject.html( '<div class="bs-rating-load" style="width:' + inputObject.parent().width() + 'px;background:transparent url(' + wgScriptPath + '/bluespice-core/resources/bluespice/images/bs-ajax-loader-bar-squere-blue.gif) center center no-repeat" >&nbsp;</div>');
		$.getJSON(
			wgScriptPath + '/index.php',
			{
				action:'ajax',
				rs:'Rating::ajaxReloadRating',
				rsargs: [
					data.refType,
					data.ref,
					data.view,
					data.votable,
					data.userID,
				]
			},
			callback( data )
		);
	},
	starsMouseOut: function(currElement, currValue, siblings, value) {
		if( value === undefined ) return;

		var currValueF = 0;
		var aCurVal = (currValue + "").split(".");
		currValue = parseInt(aCurVal[0]);
		if( aCurVal[1] != undefined) {
			currValueF = parseInt(aCurVal[1]);
		}

		siblings.push(currElement);
		for(var i = 0; i <= siblings.length; i++) {
			var sibling = $(siblings[i]);

			if( sibling.attr('data-value') <= currValue) {
				sibling.attr("src", wgScriptPath + '/bluespice-mw/ext/Rating/images/star.png');
			} else {
				if(currValue == 0) {
					sibling.attr("src", wgScriptPath + '/bluespice-mw/ext/Rating/images/star-notrated.png');
				} else {
					if( currValueF >= 5 && parseInt(sibling.attr('data-value')) - 1 == currValue) {
						sibling.attr("src", wgScriptPath + '/bluespice-mw/ext/Rating/images/star-half.png');
					} else {
						sibling.attr("src", wgScriptPath + '/bluespice-mw/ext/Rating/images/star-empty.png');
					}
				}
			}
		}
	},
	starsMouseOver: function(currElement, value, siblings) {
		if( value === undefined ) return;

		currElement.attr("src", wgScriptPath + '/bluespice-mw/ext/Rating/images/star.png');
		for(var i = 0; i <= siblings.length; i++) {
			var sibling = $(siblings[i]);
			if( sibling.attr('data-value') <= value) {
				sibling.attr("src", wgScriptPath + '/bluespice-mw/ext/Rating/images/star.png');
			} else { 
				sibling.attr("src", wgScriptPath + '/bluespice-mw/ext/Rating/images/star-empty.png');
			}
		}
	}
}

$(document).bind( 'BsRatingItemRate', function(event, data) {
	if(data.view != 'ViewStateBarBodyElementRating') return;
	data.preventDefault = true;
	var inputObject = $('.bs-rating-item.bs-rating-stars.bs-statebar-body-itembody').parent();
	data.inputObject = inputObject;

	var callback = function( data ) {
		return function( result ) {
			if( result['success'] == true) {
				data.inputObject.replaceWith(result['view']);
				var oHeadRItem = $('.bs-rating-item.bs-rating-stars.bs-statebar-top-icon, .bs-rating-item.bs-rating-stars.bs-headline-rating');
				if( oHeadRItem !== undefined ) {
					BsRating.reload( oHeadRItem );
				}
				//BsRating.init();
			} else {
				//alert(result['msg']);
			}
		}
	}

	inputObject.html( '<div id="bs-rating-load" style="width:' + inputObject.width() + 'px;background:transparent url(' + wgScriptPath + '/bluespice-core/resources/bluespice/images/bs-ajax-loader-bar-squere-blue.gif) center center no-repeat" >&nbsp;</div>');
	$.getJSON(
		wgScriptPath + '/index.php',
		{
			action:'ajax',
			rs:'Rating::ajaxVote',
			rsargs: [
				data.refType,
				data.ref,
				data.value,
				data.view,
				data.votable,
				data.userID,
				wgArticleId,
				data.subType,
			]
		},
		callback( data )
	);
});

$(document).ready(function(){
	BsRating.init();
});