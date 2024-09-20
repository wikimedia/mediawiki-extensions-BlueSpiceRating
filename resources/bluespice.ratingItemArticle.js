/**
 * Js for Rating extension
 *
 * @author     Patric Wirth
 * @package    BlueSpiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.rating.ItemArticle = function( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );
	const userVotes = this.getCurrentUserRatings();
	const myRating = userVotes.length ? userVotes[0][this.VALUE] : 0;
	this.makeStarRating( myRating );
	this.makeNumVotes();
	this.makeUserVoted();
	this.getEl().append( this.$numVotes );
	this.getEl().append( this.$userVoted );
	this.$userVoted.attr(
		'title',
		mw.message( 'bs-rating-yourrating', myRating )
	);
	if( this.userVoted() ) {
		this.$userVoted.show();
	}
};
OO.inheritClass( bs.rating.ItemArticle, bs.rating.Item );
bs.rating.register(
	'article',
	'\\BlueSpice\\Rating\\RatingItem\\Article',
	bs.rating.ItemArticle
);
bs.rating.ItemArticle.prototype.getStarRating = function() {
	return this.$starRating;
};
bs.rating.ItemArticle.prototype.getData = function() {
	//well, thats a way to implement parrent calls ^^
	var data = bs.rating.ItemArticle.super.prototype.getData.apply( this );
	data.articleid = mw.config.get( 'wgArticleId', 0 );
	return data;
};
bs.rating.ItemArticle.prototype.reset = function( data ) {
	bs.rating.ItemArticle.super.prototype.reset.apply( this, [data] );
	this.getEl().starRating(
		'setRating',
		this.getVoteAverage(),
		false
	);
	if( this.userVoted() ) {
		this.$userVoted.show();
	} else {
		this.$userVoted.hide();
	}
	this.$numVotes.html('(' + this.getVoteCount() + ')');
	var aUserVotes = this.getCurrentUserRatings();
	if( aUserVotes.length > 0 ) {
		this.$userVoted.attr(
			'title',
			mw.message( 'bs-rating-yourrating', aUserVotes[0][this.VALUE])
		);
	}
};
bs.rating.ItemArticle.prototype.makeStarRating = function( myRating ) {
	var $el = this.$el;
	this.$starRating = $el.starRating({
		starSize: 14,
		useFullStars: true,
		disableAfterRate: true,
		strokeWidth: 9,
		strokeColor: 'black',
		readOnly: !this.data.get( 'usercanmodify', false ),
		initialRating: this.getVoteAverage(),
		myRating: myRating,
		starGradient: {
			start: '#93BFE2',
			end: '#105694'
		},
		callback: ( currentRating, $el ) => {
			$el.starRating( 'setReadOnly', true );
			this.addLoadingMask( $el );
			this.vote( currentRating ).done( ( result ) => {
				if( !result.success ) {
					currentRating = this.getVoteAverage();
				}
				$el.starRating( 'setReadOnly', false );
				this.removeLoadingMask( $el );
				if( result.payload.data ) {
					this.reset( result.payload.data );
				}
			});
		}
	});
};

bs.rating.ItemArticle.prototype.makeNumVotes = function( data ) {
	this.$numVotes = $(
		'<span class="bs-rating-article-numvotes">('
		+ this.getVoteCount()
		+ ')</span>'
	);
};
bs.rating.ItemArticle.prototype.makeUserVoted = function( data ) {
	this.$userVoted = $(
		'<span class="bs-rating-article-uservoted"></span>'
	).hide();
};

bs.rating.ItemArticle.prototype.setReadOnly = function( value ) {
	this.$el.starRating( 'setReadOnly', value || true );
};
