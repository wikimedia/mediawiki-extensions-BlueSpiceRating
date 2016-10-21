/**
 * Js for RatingComments extension
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    1.0.0 beta
 * @package    Bluespice_Extensions
 * @subpackage RatedComments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
$(document).bind( 'BsRatingItemRate', function(event, data) {
	if( data.view !== 'ViewRatedCommentsFormRating' ) {
		return;
	}
	data.preventDefault = true;
	$('#bs-ratedcomments-ratinginput').val(data.value);
	data.container.attr('data-value', data.value);
});

$(document).bind( 'BsRatingInitDone', function(event, data) {
	//var stateBarContent = $('#bs-sb-content');
	//if( stateBarContent ) {
		$('.bs-statebar-gotoratedcomment').each( function(event) {
			$(this).unbind('click').bind('click', function(event) { //workaround: prevents from double click handler
				$("#bs-data-after-content").tabs('select','#bs-shoutbox');
				$('html, body').animate({
					scrollTop: $('#bs-data-after-content').offset().top
				}, 500);
				event.stopImmediatePropagation();
				return false;
			});
		});
	//}
});

$(document).ready(function(){
	$('#bs-ratedcomments-toogle-button, #bs-ratedcomments-viewtoggler').click(function(){
		$('#bs-users-ratedcomment').slideToggle( 'fast' );
		$('#bs-sb-loading').hide();
		var sCurImg = $('#bs-ratedcomments-viewtoggler-image').attr( 'src' );
		sCurImg.match('_more')
			? $('#bs-ratedcomments-viewtoggler-image').attr( 'src', mw.config.get( 'wgScriptPath')+'/extensions/BlueSpiceRatedComments/RatedComments/resources/images/bs-ratedcomments-viewtoggler_less.png')
			: $('#bs-ratedcomments-viewtoggler-image').attr( 'src', mw.config.get( 'wgScriptPath')+'/extensions/BlueSpiceRatedComments/RatedComments/resources/images/bs-ratedcomments-viewtoggler_more.png');
	});


	$( "#bs-ratedcomments-form" ).submit( function() {
		var sRating = $('#bs-ratedcomments-ratinginput').val();
		var sTitle = $('#bs-ratedcomments-sbtitle').val();
		var sMessage = $('#bs-ratedcomments-message').val();

		if( sRating === "") {
			bs.util.alert('bs-ratedComments-alert', { textMsg: 'bs-ratedcomments-err-enterrating' } );
			return false;
		}
		if( sTitle === "") {
			bs.util.alert('bs-ratedComments-alert', { textMsg: 'bs-ratedcomments-err-entertitle' } );
			return false;
		}
		if( sMessage === '' || sMessage === BsShoutBox.defaultMessage ) {
			// TODO MRG (01.07.11 16:57): this is not internationalized because
			// this would mean an additional script file for one very unlikey error message.
			bs.util.alert('bs-ratedComments-alert', { textMsg: 'bs-ratedcomments-err-entermessage' } );
			return false;
		}
		BsShoutBox.ajaxLoader.fadeIn();
		$.ajax({
			dataType: "json",
			type: 'post',
			url: mw.util.wikiScript( 'api' ),
			data: {
				action: 'bs-ratedcomments-tasks',
				task: 'insertRatedComment',
				format: 'json',
				token: mw.user.tokens.get('editToken', ''),
				taskData: JSON.stringify({
					"articleId": mw.config.get( "wgArticleId" ),
					"message": sMessage,
					"rating" : sRating,
					"title" : sTitle
				})
			},
			success: function( oData, oTextStatus ) {
				$('#bs-ratedcomments').replaceWith("");
				var oHeadRItem = $('.bs-rating-item.bs-rating-stars.bs-statebar-top-icon, .bs-rating-item.bs-rating-stars.bs-headline-rating');
				if( oHeadRItem !== undefined ) {
					BsRating.reload( oHeadRItem );
				}
				BsShoutBox.updateShoutbox();
			}
		});
		return false;
	});
	$(document).on( "click", ".bs-rc-archive", function() {
		var iShoutID = $(this).parent().parent().parent().attr('id');
		bs.util.confirm(
			'bs-shoutbox-confirm', {
				titleMsg: 'bs-shoutbox-confirm-title',
				textMsg: 'bs-shoutbox-confirm-text'
			}, {
				ok: function() {
					BsShoutBox.ajaxLoader.fadeIn();
					$.ajax({
						dataType: "json",
						type: 'post',
						url: mw.util.wikiScript( 'api' ),
						data: {
							action: 'bs-ratedcomments-tasks',
							task: 'archiveRatedComment',
							format: 'json',
							token: mw.user.tokens.get( 'editToken', '' ),
							taskData: JSON.stringify({
								"articleId": mw.config.get( "wgArticleId" ),
								"shoutId" : iShoutID.replace(/bs-sb-/, "")
							})
						},
						success: function( oData, oTextStatus ) {
							var oHeadRItem = $('.bs-rating-item.bs-rating-stars.bs-statebar-top-icon, .bs-rating-item.bs-rating-stars.bs-headline-rating');
							if( oHeadRItem !== undefined ) {
								BsRating.reload( oHeadRItem );
							}
							BsShoutBox.updateShoutbox();
						}
					});
				}
			}
		);
	});

	$(document).on( "click", ".bs-rc-edit", function() {
		var oStoreShout = $(this).parent().parent().parent();
		var iShoutID = oStoreShout.attr('id');
		BsShoutBox.ajaxLoader.fadeIn();
		bs.api.tasks.execSilent(
			'ratedcomments',
			'getMessageForm',
			{
				"shoutId" : iShoutID.replace(/bs-sb-/, ""),
				"articleid" : mw.config.get( "wgArticleId" )
			}
		).done(
			function( result ) {
				$('#bs-sb-' + result.payload.shoutId).replaceWith(result.payload.view);
				BsRating.init();
				$('#bs-ratedcomments-ratinginput-edit').val($('.bs-rating-item.bs-rating-stars.bs-rc-form-rating ').attr('data-value'));
				$( "#bs-ratedcomments-form-edit" ).submit( function() {

					var sShoutID = result.payload.shoutId;
					$('#bs-ratedcomments-ratinginput-edit').val($('.bs-rating-item.bs-rating-stars.bs-rc-form-rating ').attr('data-value'));
					var sRating = $('#bs-ratedcomments-ratinginput-edit').val();
					var sTitle = $('#bs-ratedcomments-sbtitle-edit').val();
					var sMessage = $('#bs-ratedcomments-message-edit').val();

					if( sRating === "") {
						bs.util.alert('bs-ratedComments-alert', { textMsg: 'bs-ratedcomments-err-enterrating' } );
						return false;
					}
					if( sTitle === "") {
						bs.util.alert('bs-ratedComments-alert', { textMsg: 'bs-ratedcomments-err-entertitle' } );
						return false;
					}
					if( sMessage === '' || sMessage === BsShoutBox.defaultMessage ) {
						// TODO MRG (01.07.11 16:57): this is not internationalized because
						// this would mean an additional script file for one very unlikey error message.
						bs.util.alert('bs-ratedComments-alert', { textMsg: 'bs-ratedcomments-err-entermessage' } );
						return false;
					}

					if( sShoutID === '' ) return false;

					$.ajax({
						dataType: "json",
						type: 'post',
						url: mw.util.wikiScript( 'api' ),
						data: {
							action: 'bs-ratedcomments-tasks',
							task: 'updateRatedComment',
							format: 'json',
							token: mw.user.tokens.get('editToken', ''),
							taskData: JSON.stringify({
								"articleId": mw.config.get( "wgArticleId" ),
								"message": sMessage,
								"rating" : sRating,
								"title" : sTitle,
								"shoutId" : sShoutID
							})
						},
						success: function( oData, oTextStatus ) {
							var oHeadRItem = $('.bs-rating-item.bs-rating-stars.bs-statebar-top-icon, .bs-rating-item.bs-rating-stars.bs-headline-rating');
							if( oHeadRItem !== undefined ) {
								BsRating.reload( oHeadRItem );
							}
							BsShoutBox.updateShoutbox();
						}
					});
					//we prevent the refresh of the page after submitting the form
					return false;
				});
			}
		);
	});

	$(document).on( "click", ".bs-ratedcomments-helpful-button", function() {
		var oStoreShout = $(this).parent().parent().parent();
		var iShoutID = oStoreShout.attr('id');
		var callback = function( data ) {
			return function( result ) {
				if( result.success === true ) {
					$('#' + data.replaceID).replaceWith(result.payload['view']);
					BsRating.init();
					BsRating.reload( $('#bs-ratedcomments-helpful-all-' + iShoutID.replace(/bs-sb-/, "")) );
				} else {
					//alert(result['msg']);
				}
			};
		};
		BsRating.vote($(this), callback);
	});
});