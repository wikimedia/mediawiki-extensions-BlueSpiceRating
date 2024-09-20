/**
 * @author     Patric Wirth
 * @package    BluespiceRating
 * @subpackage Rating
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 */

bs.rating.ItemArticleLike = function( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );
	this.makeVoteButton();
	this.makeNumVotes();
	this.makeUserVoted();

	this.getEl().append( this.$voteButton );
	this.getEl().append( this.$numVotes );
	this.getEl().append( this.$userVoted );

	if ( this.userVoted() ) {
		this.$voteButton.addClass( 'uservoted' );
		this.$voteButton.attr( 'aria-pressed', 'true' );
		this.$numVotes.addClass( 'uservoted' );
		this.$userVoted.show();
	} else {
		this.$voteButton.attr( 'aria-pressed', 'false' );
	}

	this.$voteButton.on( 'keydown', ( e ) => {
		if ( e.key === 'Enter' || e.key === ' ' ) {
			e.preventDefault();
			e.stopPropagation();
			this.$voteButton.click();
		}
	});

	$( this.getEl() ).on(
		"click",
		".bs-rating-articlelike-button, .bs-rating-articlelike-numvotes",
		() => {
		if( !this.data.get( 'usercanmodify', false ) ) {
			return false;
		}
		this.addLoadingMask();
		this.vote( this.userVoted() ? false : 1 ).done( ( result ) => {
			if( result.success === true ) {
				this.reset( result.payload.data );
				this.removeLoadingMask();
			}
		});
		return false;
	});
};
OO.inheritClass( bs.rating.ItemArticleLike, bs.rating.Item );
bs.rating.register(
	'articlelike',
	"\\BlueSpice\\Rating\\RatingItem\\ArticleLike",
	bs.rating.ItemArticleLike
);

bs.rating.ItemArticleLike.prototype.getData = function() {
	var data = bs.rating.ItemArticleLike.super.prototype.getData.apply( this );
	data.articleid = mw.config.get( 'wgArticleId', 0 );
	return data;
};

bs.rating.ItemArticleLike.prototype.reset = function( data ) {
	bs.rating.ItemArticleLike.super.prototype.reset.apply( this, [data] );
	this.getEl().find('.bs-rating-articlelike-numvotes').replaceWith(
		this.makeNumVotes()
	);
	if( this.userVoted() ) {
		this.$voteButton.addClass( 'uservoted' );
		this.$voteButton.attr( 'aria-pressed', 'true' );
		this.$voteButton.attr( 'aria-label', mw.message( 'bs-rating-articlelike-uratingtextservoted', this.getVoteCount() ).plain() );
		this.$numVotes.addClass( 'uservoted' );
		this.$userVoted.show();
	} else {
		this.$voteButton.removeClass( 'uservoted' );
		this.$voteButton.attr( 'aria-pressed', 'false' );
		this.$voteButton.attr( 'aria-label', mw.message( 'bs-rating-articlelike-ratingtext', this.getVoteCount() ).plain() );
		this.$numVotes.removeClass( 'uservoted' );
		this.$userVoted.hide();
	}
};

bs.rating.ItemArticleLike.prototype.makeVoteButton = function( data ) {
	this.$voteButton = $('<button></button>').attr( {
		'class': 'bs-rating-articlelike-button',
		'role': 'button',
		'aria-pressed': 'false',
		'aria-label': mw.message(
			this.userVoted()
			? 'bs-rating-articlelike-uratingtextservoted'
			: 'bs-rating-articlelike-ratingtext', this.getVoteCount()
		).plain(),
		'tabindex': '0'
	} );

	return this.$voteButton;
};

bs.rating.ItemArticleLike.prototype.makeNumVotes = function( data ) {
	this.$numVotes = $(
		'<span class="bs-rating-articlelike-numvotes">'
		+ mw.message(
			this.userVoted()
				? 'bs-rating-articlelike-uratingtextservoted'
				: 'bs-rating-articlelike-ratingtext',
			this.getVoteCount()
		).parse()
		+ '</span>'
	);
	return this.$numVotes;
};

bs.rating.ItemArticleLike.prototype.makeUserVoted = function( data ) {
	this.$userVoted = $(
		'<span class="bs-rating-articlelike-uservoted"></span>'
	).hide();
};
