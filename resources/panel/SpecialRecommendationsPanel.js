bs.util.registerNamespace( 'ext.bluespice.rating.panel' );

ext.bluespice.rating.panel.SpecialRecommendationsPanel = function ( cfg ) {
	ext.bluespice.rating.panel.SpecialRecommendationsPanel.super.apply( this, cfg );
	this.$element = $( '<div>' );

	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-ratingarticlelike-store',
		pageSize: 25
	} );

	this.setup();

	this.store.connect( this, {
		reload: () => {
			bs.rating.init();
		}
	} );

	this.store.reload();
};

OO.inheritClass( ext.bluespice.rating.panel.SpecialRecommendationsPanel, OO.ui.PanelLayout );

ext.bluespice.rating.panel.SpecialRecommendationsPanel.prototype.setup = function () {
	const gridCfg = this.setupGridConfig();
	this.grid = new OOJSPlus.ui.data.GridWidget( gridCfg );
	this.$element.append( this.grid.$element );
};

ext.bluespice.rating.panel.SpecialRecommendationsPanel.prototype.setupGridConfig = function () {
	const gridCfg = {
		exportable: true,
		style: 'differentiate-rows',
		columns: {
			page_namespace: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-rating-specialrating-label-namespace' ).text(),
				type: 'text',
				sortable: true,
				valueParser: ( value, row ) => new OO.ui.HtmlSnippet( mw.html.element(
					'a',
					{
						href: mw.util.getUrl( 'Special:AllPages', {
							namespace: value
						} )
					},
					row.page_namespace_text
				) )
			},
			page_title: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-rating-specialrating-titleTitle' ).text(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value, row ) => new OO.ui.HtmlSnippet( mw.html.element(
					'a',
					{
						href: row.page_namespace == bs.ns.NS_MAIN ? // eslint-disable-line eqeqeq
							mw.util.getUrl( value ) :
							mw.util.getUrl( `${ row.page_namespace_text }:${ value }` )
					},
					value
				) )
			},
			totalcount: {
				headerText: mw.message( 'bs-rating-special-recommendations-label-recommendation' ).text(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value, row ) => new OO.ui.HtmlSnippet( row.content )
			}
		},
		store: this.store,
		provideExportData: () => {
			const deferred = $.Deferred();

			( async () => {
				try {
					this.store.setPageSize( 99999 );
					const response = await this.store.reload();
					const $table = $( '<table>' );

					const $thead = $( '<thead>' )
						.append( $( '<tr>' )
							.append( $( '<th>' ).text( mw.message( 'bs-rating-specialrating-label-namespace' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-rating-specialrating-titleTitle' ).text() ) )
							.append( $( '<th>' ).text( mw.message( 'bs-rating-special-recommendations-label-recommendation' ).text() ) )
						);

					const $tbody = $( '<tbody>' );
					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) {
							const record = response[ id ];
							$tbody.append( $( '<tr>' )
								.append( $( '<td>' ).text( record.page_namespace_text ) )
								.append( $( '<td>' ).text( record.page_title ) )
								.append( $( '<td>' ).text( record.totalcount ) )
							);
						}
					}

					$table.append( $thead, $tbody );

					deferred.resolve( `<table>${ $table.html() }</table>` );
				} catch ( error ) {
					deferred.reject( 'Failed to load data' );
				}
			} )();

			return deferred.promise();
		}
	};

	return gridCfg;
};
