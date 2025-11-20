bs.rating.ItemArticle = function ( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );

	const userVotes = this.getCurrentUserRatings();
	const myRating = userVotes.length ? userVotes[ 0 ][ this.VALUE ] : 0;

	this.totalStars = 5;

	let rating = Number( myRating );
	if ( isNaN( rating ) ) {
		rating = 0;
	}
	this.$starGroup = $( '<div>' ).addClass( 'bs-rating-article-star-group-cnt' );
	this.$starGroup.append( this.makeStarRating( myRating ) );
	this.getEl().append( this.$starGroup );

	this.getEl().append( this.makeNumVotes() );
	this.getEl().append( this.makeUserVoted( myRating ) );
};

OO.inheritClass( bs.rating.ItemArticle, bs.rating.Item );

bs.rating.register(
	'article',
	'\\BlueSpice\\Rating\\RatingItem\\Article',
	bs.rating.ItemArticle
);
bs.rating.ItemArticle.prototype.getStarRating = function () {
	return this.$starRating;
};

bs.rating.ItemArticle.prototype.getData = function () {
	const data = bs.rating.ItemArticle.super.prototype.getData.apply( this );
	data.articleid = mw.config.get( 'wgArticleId', 0 );
	return data;
};

bs.rating.ItemArticle.prototype.reset = function ( data ) {
	bs.rating.ItemArticle.super.prototype.reset.apply( this, [ data ] );

	const rating = this.getVoteAverage();
	this.$starGroup.ratingGroup( 'updateRating', {
		groupLabel: mw.message( 'bs-rating-article-star-group-desc', rating, this.totalStars ).text(),
		averageRating: rating
	} );

	const numVotesMessage = mw.message( 'bs-rating-ratingcount-title', this.getVoteCount() ).text();
	this.$numVotes
		.attr( 'title', numVotesMessage )
		.find( '.visually-hidden' )
		.text( numVotesMessage );

	const userVotes = this.getCurrentUserRatings();
	if ( userVotes.length > 0 ) {
		const userVotedMessage = mw.message( 'bs-rating-yourrating', userVotes[ 0 ][ this.VALUE ] ).text();
		this.$userVoted
			.attr( 'title', userVotedMessage )
			.find( '.visually-hidden' )
			.text( userVotedMessage );
	}
	if ( this.userVoted() ) {
		this.$userVoted.show();
	} else {
		this.$userVoted.hide();
	}
};

bs.rating.ItemArticle.prototype.makeStarRating = function ( myRating ) {
	this.$starRating = this.$starGroup.ratingGroup( {
		totalStars: this.totalStars,
		starName: mw.message( 'bs-rating-button-name' ).text(),
		formId: 'bs-rating-article-form',
		groupLabel: mw.message( 'bs-rating-article-star-group-desc', this.getVoteAverage(), this.totalStars ).text(),
		formDesc: mw.message( 'bs-rating-article-star-group-form-desc' ).text(),
		averageRating: this.getVoteAverage(),
		currentRating: myRating,
		readOnly: !this.data.get( 'usercanmodify', false ),
		callback: ( rating, $el ) => {
			this.addLoadingMask( $el );
			this.vote( rating ).done( ( result ) => {
				if ( !result.success ) {
					rating = this.getVoteAverage();
				}
				$el.ratingGroup( 'updateRating', {
					groupLabel: mw.message( 'bs-rating-article-star-group-desc', rating, this.totalStars ).text(),
					averageRating: rating
				} );
				this.removeLoadingMask( $el );
				if ( result.payload.data ) {
					this.reset( result.payload.data );
				}
			} );
		}
	} );
	return this.$starRating;
};

bs.rating.ItemArticle.prototype.makeNumVotes = function () {
	const numVotesMessage = mw.message( 'bs-rating-ratingcount-title', this.getVoteCount() ).text();

	this.$numVotes = $( '<span>' )
		.attr( {
			class: 'bs-rating-article-numvotes',
			title: numVotesMessage
		} )
		.append( `(${ this.getVoteCount() })` )
		.append( $( '<span>' )
			.attr( 'class', 'visually-hidden' )
			.text( numVotesMessage )
		);

	return this.$numVotes;
};

bs.rating.ItemArticle.prototype.makeUserVoted = function ( myRating ) {
	const userVotedMessage = mw.message( 'bs-rating-yourrating', myRating ).text();

	this.$userVoted = $( '<span>' )
		.attr( {
			class: 'bs-rating-article-uservoted',
			title: userVotedMessage
		} )
		.append( $( '<span>' )
			.attr( 'class', 'visually-hidden' )
			.text( userVotedMessage )
		)
		.hide();

	if ( this.userVoted() ) {
		this.$userVoted.show();
	}

	return this.$userVoted;
};

bs.rating.ItemArticle.prototype.setReadOnly = function ( value ) {
	this.$starGroup.starRating( 'setReadOnly', value || true );
};
