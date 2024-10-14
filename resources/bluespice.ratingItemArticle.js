bs.rating.ItemArticle = function ( $el, type, data ) {
	bs.rating.Item.call( this, $el, type, data );

	const userVotes = this.getCurrentUserRatings();
	const myRating = userVotes.length ? userVotes[ 0 ][ this.VALUE ] : 0;

	this.$starGroup = $( '<div>' ).attr( {
		role: 'radiogroup',
		class: 'bs-rating-article-stargroup',
		title: mw.message( 'bs-rating-ratingvalue-title', this.getVoteAverage() ).text(),
		'aria-label': mw.message( 'bs-rating-ratingvalue-title', this.getVoteAverage() ).text()
	} );

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

	this.$starGroup.starRating( 'setRating', this.getVoteAverage(), false );
	this.$starGroup.attr( {
		title: mw.message( 'bs-rating-ratingvalue-title', this.getVoteAverage() ).text(),
		'aria-label': mw.message( 'bs-rating-ratingvalue-title', this.getVoteAverage() ).text()
	} );

	if ( this.userVoted() ) {
		this.$userVoted.show();
	} else {
		this.$userVoted.hide();
	}

	const userVotes = this.getCurrentUserRatings();
	if ( userVotes.length > 0 ) {
		this.$userVoted.attr( {
			title: mw.message( 'bs-rating-yourrating', userVotes[ 0 ][ this.VALUE ] ),
			'aria-label': mw.message( 'bs-rating-yourrating', userVotes[ 0 ][ this.VALUE ] )
		} );
	}
};

bs.rating.ItemArticle.prototype.makeStarRating = function ( myRating ) {
	this.$starRating = this.$starGroup.starRating( {
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
				if ( !result.success ) {
					currentRating = this.getVoteAverage();
				}
				$el.starRating( 'setReadOnly', false );
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
	const $numVotes = $( '<span>' ).attr( {
		class: 'bs-rating-article-numvotes',
		title: mw.message( 'bs-rating-ratingcount-title', this.getVoteCount() ).text(),
		'aria-label': mw.message( 'bs-rating-ratingcount-title', this.getVoteCount() ).text(),
	} ).html(
		`(${this.getVoteCount()})`
	);

	return $numVotes;
};

bs.rating.ItemArticle.prototype.makeUserVoted = function ( myRating ) {
	this.$userVoted = $( '<span>' ).attr( {
		class: 'bs-rating-article-uservoted',
		title: mw.message( 'bs-rating-yourrating', myRating ),
		'aria-label': mw.message( 'bs-rating-yourrating', myRating )
	} ).hide();

	if ( this.userVoted() ) {
		this.$userVoted.show();
	}

	return this.$userVoted;
};

bs.rating.ItemArticle.prototype.setReadOnly = function ( value ) {
	this.$starGroup.starRating( 'setReadOnly', value || true );
};
