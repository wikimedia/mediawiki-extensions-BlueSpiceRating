ext.bluespice = ext.bluespice || {};
ext.bluespice.rating = ext.bluespice.rating || {};
ext.bluespice.rating.panel = ext.bluespice.rating.panel || {};

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
				headerText: mw.message( 'bs-rating-specialrating-label-namespace' ).plain(),
				type: 'text',
				sortable: true,
				valueParser: ( value, row ) => {
					return new OO.ui.HtmlSnippet( mw.html.element(
						'a',
						{
							href: mw.util.getUrl( 'Special:AllPages', {
								namespace: value
							} )
						},
						row.page_namespace_text
					) );
				}
			},
			page_title: { // eslint-disable-line camelcase
				headerText: mw.message( 'bs-rating-specialrating-titleTitle' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value, row ) => {
					return new OO.ui.HtmlSnippet( mw.html.element(
						'a',
						{
							href: row.page_namespace == bs.ns.NS_MAIN ? // eslint-disable-line eqeqeq, max-len
								mw.util.getUrl( value ) :
								mw.util.getUrl( `${row.page_namespace_text}:${value}` )
						},
						value
					) );
				}
			},
			totalcount: {
				headerText: mw.message( 'bs-rating-special-recommendations-label-recommendation' ).plain(),
				type: 'text',
				sortable: true,
				filter: { type: 'text' },
				valueParser: ( value, row ) => {
					return new OO.ui.HtmlSnippet( row.content );
				}
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
					let $row = $( '<tr>' );

					$row.append( $( '<td>' ).text( mw.message( 'bs-rating-specialrating-label-namespace' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-rating-specialrating-titleTitle' ).text() ) );
					$row.append( $( '<td>' ).text( mw.message( 'bs-rating-special-recommendations-label-recommendation' ).text() ) );

					$table.append( $row );

					for ( const id in response ) {
						if ( response.hasOwnProperty( id ) ) { // eslint-disable-line no-prototype-builtins, max-len
							const record = response[ id ];
							$row = $( '<tr>' );

							$row.append( $( '<td>' ).text( record.page_namespace_text ) );
							$row.append( $( '<td>' ).text( record.page_title ) );
							$row.append( $( '<td>' ).text( record.totalcount ) );

							$table.append( $row );
						}
					}

					deferred.resolve( `<table>${$table.html()}</table>` );
				} catch ( error ) {
					deferred.reject( 'Failed to load data' );
				}
			} )();

			return deferred.promise();
		}
	};

	return gridCfg;
};
