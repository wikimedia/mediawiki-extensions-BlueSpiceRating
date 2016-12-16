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

bs.rating.ItemArticle = function( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );
	var me = this;
	me.$starRating = $el.starRating({
		starSize: 14,
		useFullStars: true,
		disableAfterRate: false,
		strokeWidth: 9,
		strokeColor: 'black',
		initialRating: me.getVoteAverage(),
		starGradient: {
			start: '#93BFE2',
			end: '#105694'
		},
		callback: function(currentRating, $el){
			$el.starRating( 'setReadOnly', true );
			me.addLoadingMask( $el );
			me.vote( currentRating ).done( function( result ) {
				if( !result.success ) {
					currentRating = me.getVoteAverage();
				}
				$el.starRating( 'setReadOnly', false );
				me.removeLoadingMask( $el );
				if( result.payload.data ) {
					me.reset( result.payload.data );
				}
			});
		}
	});
};
OO.inheritClass( bs.rating.ItemArticle, bs.rating.Item );
bs.rating.register( 'article', 'RatingItemArticle', bs.rating.ItemArticle );

bs.rating.ItemArticle.prototype.getData = function() {
	//well, thats a way to implement parrent calls ^^
	var data = bs.rating.ItemArticle.super.prototype.getData.apply( this );
	data.articleid = mw.config.get( 'wgArticleId', 0 );
	return data;
};
bs.rating.ItemArticle.prototype.reset = function( data ) {
	bs.rating.ItemArticle.super.prototype.reset.apply( this, [data] );
	this.$starRating.starSize = 100;
};