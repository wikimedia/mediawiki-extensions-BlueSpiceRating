( function ( $ ) {

	'use strict';

	// Create the defaults once
	const pluginName = 'ratingGroup';

	const noop = function () {};
	const defaultOptions = {
		groupId: 'rating-form',
		groupLabel: 'Rating group',
		groupClass: 'rating-group',
		totalStars: 5,
		averageRating: 0,
		currentRating: 0,
		starSize: '0.83em',
		starName: 'Rating',
		readOnly: false,
		disable: false,
		callback: noop
	};

	const Plugin = function ( element, options ) {
		this.$el = $( element );
		this.$cnt = $( '<div>' ).addClass( 'rating-group-cnt' );
		this.options = Object.assign( {}, defaultOptions, options );

		this.init();
	};

	const privateMethods = {
		init: function () {
			this.$cnt.empty();
			this.renderStarGroup();
			this.$el.append( this.$cnt );
			this.addEventListeners();
		},
		addEventListeners: function () {
			if ( this.options.readOnly === false ) {
				const $stars = this.$cnt.find( '.rating-star-item' );
				$stars.on( 'click', this.handleClick.bind( this ) );
				$stars.on( 'keydown', this.handleKeyDown.bind( this ) );
				$stars.on( 'keyup', this.handleKeyUp.bind( this ) );
				$stars.on( 'mouseover', this.handleMouseOver.bind( this ) );
				this.$cnt.on( 'mouseout', this.handleMouseOut.bind( this ) );
			}
		},
		handleClick: function ( event ) {
			const curRating = this.getIndex( event.currentTarget );
			this.applyRating( curRating );
		},
		handleKeyDown: function ( event ) {
			const curRating = this.getIndex( event.currentTarget );
			if ( event.originalEvent.code === 'Tab' ) {
				this.resetCurrentRatingHighlight();
			} else if ( ( event.originalEvent.code === 'ArrowLeft' ) && ( curRating === 1 ) ) {
				event.stopPropagation();
				event.preventDefault();
				return false;
			} else if ( event.originalEvent.code === 'ArrowRight' && curRating === this.options.totalStars ) {
				event.stopPropagation();
				event.preventDefault();
				return false;
			}
		},
		handleKeyUp: function ( event ) {
			const curRating = this.getIndex( event.currentTarget );
			if ( event.originalEvent.code === 'Tab' ) {
				this.setCurrentRatingHighlight( curRating );
			} else if ( event.originalEvent.code === 'ArrowLeft' ) {
				this.resetCurrentRatingHighlight();
				this.setCurrentRatingHighlight( curRating );
			} else if ( event.originalEvent.code === 'ArrowRight' ) {
				this.resetCurrentRatingHighlight();
				this.setCurrentRatingHighlight( curRating );
			}
		},
		handleMouseOver: function ( event ) {
			const curRating = this.getIndex( event.currentTarget );
			this.resetCurrentRatingHighlight();
			this.setCurrentRatingHighlight( curRating );
		},
		handleMouseOut: function () {
			this.resetCurrentRatingHighlight();
		},
		resetCurrentRatingHighlight: function () {
			for ( let index = 0; index < this.options.totalStars; index++ ) {
				const item = this.$cnt.find( '.rating-star-item' )[ index ];
				$( item ).removeClass( 'cur-highlight' );
			}
		},
		setCurrentRatingHighlight: function ( rating ) {
			for ( let index = 0; index < rating; index++ ) {
				const item = this.$cnt.find( '.rating-star-item' )[ index ];
				$( item ).addClass( 'cur-highlight' );
			}
		},
		renderStarGroup: function () {
			let stars = '';
			const totalStars = this.options.totalStars;
			for ( let i = 1; i <= totalStars; i++ ) {
				let checked = false;
				if ( this.options.currentRating === i ) {
					checked = true;
				}

				const averageRating = Math.round( this.options.averageRating );
				let avgHightlight = false;
				if ( averageRating >= i ) {
					avgHightlight = true;
				}

				const star = this.renderStarItem( i, avgHightlight, checked );
				stars += star;
			}

			if ( this.options.readOnly === false ) {
				const $formGroup = $( '<form>' ).attr( {
					id: this.options.groupId,
					'aria-label': this.options.groupLabel,
					class: this.options.groupClass + ' rating-group-form'
				} );
				const $starGroup = $( '<div>' ).attr( {
					'aria-labelledby': this.options.groupId,
					role: 'radiogroup',
					class: this.options.groupClass + ' rating-group-stars'
				} );

				$starGroup.append( stars );
				$formGroup.append( $starGroup );
				this.$cnt.append( $formGroup );
			} else {
				const $starGroup = $( '<div>' ).attr( {
					id: this.options.groupId,
					class: this.options.groupClass + ' rating-group-stars',
					'aria-label': this.options.groupLabel
				} );

				$starGroup.append( stars );
				this.$cnt.append( $starGroup );
			}
		},
		renderStarItem: function ( starIndex, avgHightlight, checked ) {
			const name = this.options.starName;
			const size = this.options.starSize;

			let checkedAttrib = '';
			let checkedClass = '';
			let checkedAttr = '';
			if ( checked === true ) {
				checkedAttrib = 'aria-checked=true';
				checkedClass = ' checked';
				checkedAttr = ' checked="checked"';
			}

			let avgHightlightClass = '';
			if ( avgHightlight === true ) {
				avgHightlightClass = ' avg-highlight';
			}

			let item = '';
			if ( this.options.readOnly === false ) {
				item = '<div class="rating-star-item' + checkedClass + avgHightlightClass + '" id="rating-star-item-' + starIndex + '" style="width:' + size + ';  height:' + size + ';" data-index="' + starIndex + '">' +
				'<input type="radio" class="rating-star-radio-btn" id="rating-star-input-' + starIndex + '" value="' + starIndex + '" name="' + name + '" ' + checkedAttrib + checkedAttr + '/>' +
				'<label for="rating-star-' + starIndex + '">' +
				this.getStarSvg( size ) +
				'</label></div>';
			} else {
				item = '<div class="rating-star-item' + checkedClass + avgHightlightClass + '" id="rating-star-item-' + starIndex + '" style="width:' + size + ';  height:' + size + ';" data-index="' + starIndex + '">' +
				'<span for="rating-star-' + starIndex + '">' +
				this.getStarSvg( size ) +
				'</span></div>';
			}

			return item;
		},
		getStarSvg: function ( size ) {
			return '<svg viewBox="0 0 576 512" width="' + size + '" height="' + size + '">' +
				'<path d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z" />' +
				'</svg>';
		},
		applyRating: function ( rating ) {
			this.options.currentRating = rating;
			this.executeCallback( rating );
			this.updateLiveRegion( rating );
		},
		getIndex: function ( element ) {
			const index = $( element ).attr( 'data-index' );
			return Number( index );
		},
		updateLiveRegion: function ( rating ) {
			if ( !this.$liveRegion ) {
				this.$liveRegion = $( '<div>' ).attr( {
					class: 'visually-hidden',
					'aria-live': 'polite',
					role: 'status'
				} ).appendTo( this.$el );
			}
			this.$liveRegion.text( mw.message( 'bs-rating-star-rating-star-selected-announce', rating ).text() );
		},
		executeCallback: function ( rating ) {
			const callback = this.options.callback;
			callback( rating, this.$el );
		}
	};

	const publicMethods = {
		setDisabled: function ( value ) {
			const disabledPluginName = 'plugin_' + pluginName;
			const $el = $( this );
			const $plugin = $el.data( disabledPluginName );

			$plugin.options.readOnly = value;

			// $plugin.init() will flicker
			const $radioButtons = $plugin.$cnt.find( 'input' );
			for ( let i = 0; i < $radioButtons.length; i++ ) {
				if ( value === true ) {
					$( $radioButtons[ i ] ).attr( 'disabled', true );
				} else {
					$( $radioButtons[ i ] ).attr( 'disabled', false );
				}
			}
		},
		updateRating: function ( options ) {
			const updatePluginName = 'plugin_' + pluginName;
			const $el = $( this );
			const $plugin = $el.data( updatePluginName );

			$plugin.options = Object.assign( $plugin.options, options );

			// $plugin.init() will flicker
			if ( options.hasOwnProperty( 'disable' ) ) {
				$plugin.setDisabled( options.disable );
			}

			if ( options.hasOwnProperty( 'groupLabel' ) ) {
				let $labelEls = $plugin.$cnt.find( '.rating-group-form' );
				if ( $labelEls.length === 0 ) {
					$labelEls = $plugin.$cnt.find( '.rating-group' );
				}
				if ( $labelEls.length > 0 ) {
					const $labelEl = $( $labelEls.first() );
					$labelEl.attr( 'aria-label', options.groupLabel );
				}
			}

			if ( options.hasOwnProperty( 'averageRating' ) ) {
				for ( let i = 0; i < $plugin.options.totalStars; i++ ) {
					const item = $plugin.$cnt.find( '.rating-star-item' )[ i ];
					$( item ).removeClass( 'avg-highlight' );
				}
				for ( let i = 0; i < options.averageRating; i++ ) {
					const item = $plugin.$cnt.find( '.rating-star-item' )[ i ];
					$( item ).addClass( 'avg-highlight' );
				}
			}
		}
	};

	// Avoid Plugin.prototype conflicts
	Object.assign( Plugin.prototype, privateMethods );

	$.fn[ pluginName ] = function ( options ) {
		// if option is a public method
		if ( !$.isPlainObject( options ) ) {
			if ( publicMethods.hasOwnProperty( options ) ) {
				return publicMethods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
			} else {
				throw new Error( 'Method ' + options + ' does not exist on ' + pluginName + '.js' );
			}
		}
		return this.each( function () {
			// preventing against multiple instantiations
			if ( !$.data( this, 'plugin_' + pluginName ) ) {
				$.data( this, 'plugin_' + pluginName, new Plugin( this, options ) );
			}
		} );
	};
}( jQuery ) );
