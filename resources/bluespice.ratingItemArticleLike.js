bs.rating.ItemArticleLike = function ( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );

	this.$voteButton = this.makeVoteButton();

	this.getEl().append( this.$voteButton );
	this.getEl().append( this.makeUserVoted() );

	if ( !this.data.get( 'usercanmodify', false ) ) {
		this.$voteButton.prop( 'disabled', true );
	}

	this.updateVoteButtonAccessibility();
	if ( this.userVoted() ) {
		this.$voteButton.addClass( 'uservoted' );
		this.$label.addClass( 'uservoted' );
		this.$userVoted.show();
	}

	if ( this.getVoteCount() > 0 ) {
		this.$voteIcon.addClass( 'recommended' );
	}

	this.$voteButton.on( 'click', () => {
		if ( !this.data.get( 'usercanmodify', false ) ) {
			return false;
		}
		this.addLoadingMask();
		this.vote( this.userVoted() ? false : 1 ).done( ( result ) => {
			if ( result.success === true ) {
				this.reset( result.payload.data );
				this.removeLoadingMask();
			}
		} );
		return false;
	} );
};
OO.inheritClass( bs.rating.ItemArticleLike, bs.rating.Item );
bs.rating.register(
	'articlelike',
	'\\BlueSpice\\Rating\\RatingItem\\ArticleLike',
	bs.rating.ItemArticleLike
);

bs.rating.ItemArticleLike.prototype.getData = function () {
	const data = bs.rating.ItemArticleLike.super.prototype.getData.apply( this );
	data.articleid = mw.config.get( 'wgArticleId', 0 );
	return data;
};

bs.rating.ItemArticleLike.prototype.reset = function ( data ) {
	bs.rating.ItemArticleLike.super.prototype.reset.apply( this, [ data ] );
	this.getEl().find( '.bs-rating-articlelike-label' ).replaceWith(
		this.makeLabel()
	);
	this.updateVoteButtonAccessibility();
	if ( this.userVoted() ) {
		this.$voteIcon.addClass( 'recommended' );
		this.$userVoted.show();
	} else {
		this.$userVoted.hide();
		if ( this.getVoteCount() === 0 ) {
			this.$voteIcon.removeClass( 'recommended' );
		}
	}
};

bs.rating.ItemArticleLike.prototype.updateVoteButtonAccessibility = function () {
	if ( this.userVoted() ) {
		this.$voteButton.attr( 'aria-pressed', 'true' );
	} else {
		this.$voteButton.attr( 'aria-pressed', 'false' );
	}
};

bs.rating.ItemArticleLike.prototype.makeVoteButton = function () {
	this.$voteButton = $( '<button>' ).attr( {
		class: 'bs-rating-articlelike-button'
	} );

	this.$voteButton.append( this.makeVoteIcon() );
	this.$voteButton.append( this.makeLabel() );

	return this.$voteButton;
};

bs.rating.ItemArticleLike.prototype.makeVoteIcon = function () {
	this.$voteIcon = $( '<span>' ).attr( {
		class: 'bs-rating-articlelike-icon'
	} );

	return this.$voteIcon;
};

bs.rating.ItemArticleLike.prototype.makeLabel = function () {
	let label;

	if ( this.data.get( 'usercanmodify', false ) ) {
		label = mw.message(
			this.getVoteCount() > 0 ?
				'bs-rating-articlelike-uratingtextservoted' :
				'bs-rating-articlelike-ratingtext',
			this.getVoteCount()
		).text();
	} else {
		label = mw.message( 'bs-rating-articlelike-ratingtext-reader-title', this.getVoteCount() ).text();
	}

	this.$label = $( '<span>' ).attr( {
		class: 'bs-rating-articlelike-label'
	} ).html( label );

	return this.$label;
};

bs.rating.ItemArticleLike.prototype.makeUserVoted = function () {
	this.$userVoted = $( '<span>' ).attr( {
		class: 'bs-rating-articlelike-uservoted',
		title: mw.message( 'bs-rating-articlelike-uservoted-title' ).text(),
		'aria-label': mw.message( 'bs-rating-articlelike-uservoted-title' ).text()
	} ).hide();

	return this.$userVoted;
};
