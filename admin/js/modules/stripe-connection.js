/**
 * GiftFlow Stripe connection panel.
 *
 * Drives the Sandbox | Live profile tabs and the authenticated connection
 * actions. All state comes from the backend: the module never decides which
 * phases ran, and it never sends identifiers (webhook id, URL, account id) to
 * the server.
 *
 * @package GiftFlow
 * @since 1.2.0
 */

const $ = jQuery.noConflict();

class GiftFlowStripeConnection {
	/**
	 * @param {HTMLElement} element Panel root element.
	 * @param {Object}      config  Panel configuration payload.
	 */
	constructor( element, config ) {
		this.$panel = $( element );
		this.config = config || {};
		this.strings = this.config.strings || {};
		this.rest = this.config.rest || {};
		this.pendingDiff = {};
		this.pendingRebind = {};
		this.busy = false;

		this.statusRegion = this.$panel.find( '[data-role="operation-status"]' );
		this.bind();
	}

	/**
	 * Translate a string key.
	 *
	 * @param {string} key      String key.
	 * @param {string} fallback Fallback text.
	 * @return {string} Translated string.
	 */
	t( key, fallback = '' ) {
		return this.strings[ key ] || fallback || key;
	}

	/**
	 * Wire up all interactions.
	 *
	 * @return {void}
	 */
	bind() {
		const dialog = this.modal();

		if ( dialog ) {
			// A click landing on the dialog element itself is a backdrop click.
			dialog.addEventListener( 'click', ( event ) => {
				if ( event.target === dialog ) {
					this.closeModal();
				}
			} );
		}

		this.tabs().on( 'click', ( event ) => {
			this.selectTab( event.currentTarget.dataset.environment );
		} );

		this.tabs().on( 'keydown', ( event ) => {
			const order = this.config.environments || [];
			const current = order.indexOf( event.currentTarget.dataset.environment );
			let next = null;

			if ( 'ArrowRight' === event.key || 'ArrowDown' === event.key ) {
				next = order[ ( current + 1 ) % order.length ];
			} else if ( 'ArrowLeft' === event.key || 'ArrowUp' === event.key ) {
				next = order[ ( current - 1 + order.length ) % order.length ];
			} else if ( 'Home' === event.key ) {
				next = order[ 0 ];
			} else if ( 'End' === event.key ) {
				next = order[ order.length - 1 ];
			}

			if ( ! next ) {
				return;
			}

			event.preventDefault();
			this.selectTab( next );
			this.tab( next ).trigger( 'focus' );
		} );

		// Every interactive control in the panel declares data-action, including the
		// modal trigger and close button which the busy state must not disable.
		this.$panel.on( 'click', '[data-action]', ( event ) => {
			const button = event.currentTarget;
			const environment = button.dataset.environment;
			const action = button.dataset.action;

			switch ( action ) {
				case 'connect':
					this.connect( environment );
					break;
				case 'check':
					this.check( environment );
					break;
				case 'repair':
					this.showDiff( environment, this.pendingDiff[ environment ], 'repair-confirm' );
					break;
				case 'repair-confirm':
					this.repair( environment, this.pendingDiff[ environment ] );
					break;
				case 'diff-cancel':
					this.hideDiff( environment );
					break;
				case 'toggle-details':
					this.toggleDetails( environment );
					break;
				case 'open-config':
					this.openModal();
					break;
				case 'close-config':
					this.closeModal();
					break;
				case 'edit-credential':
					this.openCredentialEdit( environment, button.dataset.credential );
					break;
				case 'update-credential':
					this.updateCredential( environment, button.dataset.credential );
					break;
				case 'cancel-credential':
					this.closeCredentialEdit( environment, button.dataset.credential );
					break;
				case 'rebind-confirm':
					this.rebind( environment );
					break;
				default:
					break;
			}
		} );

		this.$panel.on( 'input', '.giftflow-stripe-credential-edit input', ( event ) => {
			const input = event.currentTarget;
			const $row = $( input ).closest( '.giftflow-stripe-credential' );

			$row.find( `.giftflow-stripe-reveal[data-target="${ input.id }"]` ).prop( 'disabled', '' === input.value );
			$row.find( '.giftflow-stripe-action[data-action="update-credential"]' ).prop( 'disabled', '' === input.value.trim() );
		} );

		this.$panel.on( 'click', '.giftflow-stripe-reveal', ( event ) => {
			const button = $( event.currentTarget );
			const input = document.getElementById( button.data( 'target' ) );

			if ( ! input ) {
				return;
			}

			const revealing = 'password' === input.type;
			input.type = revealing ? 'text' : 'password';
			button.attr( 'aria-pressed', revealing ? 'true' : 'false' );
			button.find( '[data-role="reveal-label"]' ).text( revealing ? this.t( 'hide' ) : this.t( 'show' ) );
		} );
	}

	/**
	 * Modal element holding the profile configuration.
	 *
	 * @return HTMLDialogElement|null
	 */
	modal() {
		return document.getElementById( 'giftflow-stripe-modal' );
	}

	/**
	 * Open the configuration modal.
	 *
	 * @return {void}
	 */
	openModal() {
		const dialog = this.modal();

		if ( ! dialog ) {
			return;
		}

		if ( typeof dialog.showModal === 'function' ) {
			if ( ! dialog.open ) {
				dialog.showModal();
			}
		} else {
			dialog.setAttribute( 'open', '' );
		}
	}

	/**
	 * Close the configuration modal.
	 *
	 * @return {void}
	 */
	closeModal() {
		const dialog = this.modal();

		if ( ! dialog ) {
			return;
		}

		if ( typeof dialog.close === 'function' ) {
			dialog.close();
		} else {
			dialog.removeAttribute( 'open' );
		}
	}

	/**
	 * Tab buttons.
	 *
	 * @return {jQuery} Tab buttons.
	 */
	tabs() {
		return this.$panel.find( '.giftflow-stripe-tab' );
	}

	/**
	 * A single tab button.
	 *
	 * @param {string} environment Environment id.
	 * @return {jQuery} Tab button.
	 */
	tab( environment ) {
		return this.$panel.find( `.giftflow-stripe-tab[data-environment="${ environment }"]` );
	}

	/**
	 * A single tab panel.
	 *
	 * @param {string} environment Environment id.
	 * @return {jQuery} Tab panel.
	 */
	tabPanel( environment ) {
		return this.$panel.find( `.giftflow-stripe-tabpanel[data-environment="${ environment }"]` );
	}

	/**
	 * Switch the visible profile tab (local view state only).
	 *
	 * @param {string} environment Environment id.
	 * @return {void}
	 */
	selectTab( environment ) {
		if ( ! environment ) {
			return;
		}

		this.tabs().each( ( index, tab ) => {
			const isActive = tab.dataset.environment === environment;
			$( tab ).attr( 'aria-selected', isActive ? 'true' : 'false' );
			$( tab ).attr( 'tabindex', isActive ? '0' : '-1' );
			$( tab ).toggleClass( 'is-active', isActive );
			this.tabPanel( tab.dataset.environment ).prop( 'hidden', ! isActive );
		} );
	}

	/**
	 * Expand or collapse the secondary profile details.
	 *
	 * @param {string} environment Environment id.
	 * @return {void}
	 */
	toggleDetails( environment ) {
		const $panel = this.tabPanel( environment );
		const $details = $panel.find( '[data-role="details"]' );
		const $button = $panel.find( '.giftflow-stripe-action[data-action="toggle-details"]' );
		const expanding = $details.prop( 'hidden' );

		$details.prop( 'hidden', ! expanding );
		$button.attr( 'aria-expanded', expanding ? 'true' : 'false' );
		$button.find( '[data-role="details-label"]' ).text( expanding ? this.t( 'hide_details' ) : this.t( 'show_details' ) );
	}

	/**
	 * A credential row for an environment.
	 *
	 * @param {string} environment Environment id.
	 * @param {string} field       Credential field name.
	 * @return {jQuery} Row element.
	 */
	credentialRow( environment, field ) {
		return this.tabPanel( environment ).find( `.giftflow-stripe-credential[data-credential="${ field }"]` );
	}

	/**
	 * Switch one credential row into edit mode.
	 *
	 * @param {string} environment Environment id.
	 * @param {string} field       Credential field name.
	 * @return {void}
	 */
	openCredentialEdit( environment, field ) {
		const $panel = this.tabPanel( environment );

		// Only one credential is edited at a time.
		$panel.find( '.giftflow-stripe-credential' ).each( ( index, row ) => {
			if ( row.dataset.credential !== field ) {
				this.closeCredentialEdit( environment, row.dataset.credential, false );
			}
		} );

		const $row = this.credentialRow( environment, field );

		$row.find( '[data-role="credential-view"]' ).prop( 'hidden', true );
		$row.find( '[data-role="credential-edit"]' ).prop( 'hidden', false );
		$row.find( '[data-action="edit-credential"]' ).attr( 'aria-expanded', 'true' );
		$row.find( '[data-role="credential-edit"] input' ).trigger( 'focus' );
	}

	/**
	 * Leave edit mode for one credential row.
	 *
	 * @param {string}  environment Environment id.
	 * @param {string}  field       Credential field name.
	 * @param {boolean} restoreFocus Whether to focus the edit button again.
	 * @return {void}
	 */
	closeCredentialEdit( environment, field, restoreFocus = true ) {
		const $row = this.credentialRow( environment, field );

		if ( ! $row.length ) {
			return;
		}

		const $input = $row.find( '[data-role="credential-edit"] input' );
		const $editButton = $row.find( '[data-action="edit-credential"]' );
		const $reveal = $row.find( '.giftflow-stripe-reveal' );

		$input.val( '' ).attr( 'type', 'password' ).removeAttr( 'aria-invalid' );
		$reveal.prop( 'disabled', true ).attr( 'aria-pressed', 'false' );
		$reveal.find( '[data-role="reveal-label"]' ).text( this.t( 'show' ) );
		$row.find( '[data-action="update-credential"]' ).prop( 'disabled', true );
		$row.find( '[data-role="credential-edit"]' ).prop( 'hidden', true );
		$row.find( '[data-role="credential-view"]' ).prop( 'hidden', false );
		$editButton.attr( 'aria-expanded', 'false' );

		if ( restoreFocus ) {
			$editButton.trigger( 'focus' );
		}
	}

	/**
	 * Replace one stored credential.
	 *
	 * @param {string} environment Environment id.
	 * @param {string} field       Credential field name.
	 * @return {Promise<void>} Resolves when the request finished.
	 */
	async updateCredential( environment, field ) {
		if ( this.busy ) {
			return;
		}

		const $row = this.credentialRow( environment, field );
		const input = $row.find( '[data-role="credential-edit"] input' ).get( 0 );
		const value = input ? input.value.trim() : '';

		if ( ! input || '' === value ) {
			if ( input ) {
				input.focus();
			}

			this.announce( this.t( 'value_required' ) );

			return;
		}

		this.setBusy( true, this.t( 'update' ) + '…' );

		let succeeded = false;

		try {
			const result = await this.request( '/credentials', {
				environment,
				field,
				value,
			} );

			succeeded = !! result.success;

			if ( succeeded ) {
				this.renderResult( environment, result );
				return;
			}

			input.setAttribute( 'aria-invalid', 'true' );
			input.focus();
			this.renderResult( environment, result );
		} finally {
			this.setBusy( false );

			if ( succeeded ) {
				this.closeCredentialEdit( environment, field );
			}
		}
	}

	/**
	 * Set the busy state of the panel actions.
	 *
	 * @param {boolean} busy    Whether a request is running.
	 * @param {string}  label   Running label.
	 * @param {string}  environment Environment id.
	 * @return {void}
	 */
	setBusy( busy, label = '', environment = '' ) {
		this.busy = busy;
		this.$panel.find( '.giftflow-stripe-action' ).prop( 'disabled', busy );

		if ( busy ) {
			this.announce( label );
		} else {
			this.syncActionState();
		}
	}

	/**
	 * Re-apply per-profile action availability.
	 *
	 * @return {void}
	 */
	syncActionState() {
		( this.config.environments || [] ).forEach( ( environment ) => {
			const diff = this.pendingDiff[ environment ];
			const hasDiff = !!( diff && diff.fields && Object.keys( diff.fields ).length );

			this.tabPanel( environment )
				.find( '.giftflow-stripe-action[data-action="repair"]' )
				.prop( 'disabled', this.busy || ! hasDiff );
		} );
	}

	/**
	 * Announce an operation status for assistive technology.
	 *
	 * @param {string} message Message text.
	 * @return {void}
	 */
	announce( message ) {
		this.statusRegion.text( message || '' );
	}

	/**
	 * Write an outcome into a profile panel.
	 *
	 * @param {string} environment Environment id.
	 * @param {Object} result      Backend result.
	 * @return {void}
	 */
	renderResult( environment, result ) {
		const $panel = this.tabPanel( environment );

		if ( ! $panel.length ) {
			return;
		}

		const message = result.message || ( result.success ? '' : this.t( 'request_failed' ) );
		$panel.find( '[data-role="status-message"]' ).text( message );
		$panel.find( '[data-role="status-badge"]' ).attr( 'data-error-code', result.code || '' );

		$panel.find( '[data-role="recovery-note"]' ).prop( 'hidden', ! result.recovery );

		if ( result.phases && result.phases.length ) {
			this.announce( message + ' — ' + result.phases.map( ( phase ) => this.phaseLabel( phase ) ).join( ', ' ) );
		} else {
			this.announce( message );
		}

		if ( result.configuration ) {
			this.renderConfiguration( result.configuration );
		}

		const hasFields = !!( result.diff && result.diff.fields && Object.keys( result.diff.fields ).length );

		this.pendingDiff[ environment ] = hasFields ? result.diff : null;

		if ( hasFields ) {
			this.showDiff( environment, result.diff, result.repairable ? 'repair-confirm' : 'diff-cancel' );
		} else {
			this.hideDiff( environment );
		}

		this.syncActionState();
		this.focusInvalidField( environment, result );
	}

	/**
	 * Focus the field a validation error refers to.
	 *
	 * @param {string} environment Environment id.
	 * @param {Object} result      Backend result.
	 * @return {void}
	 */
	focusInvalidField( environment, result ) {
		if ( result.success ) {
			return;
		}

		const code = result.errors && result.errors.length ? result.errors[ 0 ].code : result.code;
		const fieldMap = {
			INVALID_PUBLISHABLE_KEY: 'publishable_key',
			INVALID_SECRET_KEY: 'secret_key',
			MISSING_CREDENTIALS: 'publishable_key',
			KEY_PAIR_MISMATCH: 'publishable_key',
			INVALID_CREDENTIAL_VALUE: 'publishable_key',
		};

		// The credential route reports the exact field it rejected.
		const field = result.credential || fieldMap[ code ];

		if ( ! field ) {
			return;
		}

		this.openCredentialEdit( environment, field );

		const input = this.credentialRow( environment, field ).find( '[data-role="credential-edit"] input' ).get( 0 );

		if ( input ) {
			input.focus();
			input.setAttribute( 'aria-invalid', 'true' );
		}
	}

	/**
	 * Backend-confirmed phase labels.
	 *
	 * @param {string} phase Phase key.
	 * @return {string} Label.
	 */
	phaseLabel( phase ) {
		const labels = {
			checking_credentials: 'Credentials verified',
			finding_webhook: 'Webhook endpoint reconciled',
			configuring_events: 'Events configured',
			saving_local_configuration: 'Configuration saved locally',
			complete: 'Complete',
		};

		return labels[ phase ] || phase;
	}

	/**
	 * Stored profile payload for an environment.
	 *
	 * @param {string} environment Environment id.
	 * @return {Object} Profile payload.
	 */
	profile( environment ) {
		return ( this.config.profiles && this.config.profiles[ environment ] ) || {};
	}

	/**
	 * Refresh the rendered configuration from a backend payload.
	 *
	 * @param {Object} configuration Configuration payload.
	 * @return {void}
	 */
	renderConfiguration( configuration ) {
		const incoming = configuration.profiles || {};

		Object.keys( incoming ).forEach( ( environment ) => {
			// Keep any render-only fields the markup owns while refreshing state.
			this.config.profiles[ environment ] = Object.assign(
				{},
				this.config.profiles[ environment ] || {},
				incoming[ environment ]
			);
		} );

		this.config.active_environment = configuration.active_environment || this.config.active_environment;

		Object.keys( this.config.profiles ).forEach( ( environment ) => {
			const profile = this.config.profiles[ environment ];
			const $panel = this.tabPanel( environment );

			if ( ! $panel.length ) {
				return;
			}

			$panel.find( '[data-role="publishable-masked"]' ).text( profile.publishable_key_masked || this.t( 'secret_missing' ) );
			$panel.find( '[data-role="secret-masked"]' ).text( profile.secret_key_masked || this.t( 'secret_missing' ) );
			$panel.find( '[data-role="webhook-secret-masked"]' ).text( profile.webhook_secret_masked || this.t( 'secret_missing' ) );
			$panel.find( '[data-role="account"]' ).text( profile.stripe_account_id || '' );
			$panel.find( '[data-role="webhook-id"]' ).text( profile.webhook_id || '' );
			$panel.find( '[data-role="ownership"]' ).text( this.ownerLabel( profile.webhook_owner ) );
			$panel.find( '[data-role="status-badge"]' )
				.text( profile.state_label || ( profile.configured ? this.t( 'configured' ) : this.t( 'not_configured' ) ) )
				.attr( 'data-tone', profile.state_tone || 'neutral' )
				.attr( 'data-state', profile.state || 'not_configured' );

			// Keep the compact summary outside the modal in sync.
			this.$panel.find( `[data-role="summary-badge-${ environment }"]` )
				.text( `${ profile.label || environment } · ${ profile.state_label || this.t( 'not_configured' ) }` )
				.attr( 'data-tone', profile.state_tone || 'neutral' )
				.attr( 'data-state', profile.state || 'not_configured' );
			$panel.find( '[data-role="event-count"]' ).text( ( profile.events || [] ).length );
			$panel.find( '[data-role="last-checked"]' ).text( profile.last_checked_display || this.t( 'never' ) );
			$panel.find( '[data-role="last-verified"]' ).text( profile.last_verified_display || this.t( 'never' ) );
		} );
	}

	/**
	 * Owner label for an ownership value.
	 *
	 * @param {string} owner Ownership value.
	 * @return {string} Label.
	 */
	ownerLabel( owner ) {
		const labels = {
			plugin: this.t( 'owner_plugin' ),
			manual: this.t( 'owner_manual' ),
			unknown: this.t( 'owner_unknown' ),
		};

		return labels[ owner ] || labels.unknown;
	}

	/**
	 * POST a request to the plugin REST route.
	 *
	 * @param {string} route   Route path.
	 * @param {Object} payload Request payload.
	 * @return {Promise<Object>} Parsed response body.
	 */
	async request( route, payload ) {
		let response;

		try {
			response = await fetch( `${ this.rest.root }${ route }`, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': this.rest.nonce,
				},
				body: JSON.stringify( payload ),
			} );
		} catch ( error ) {
			return {
				success: false,
				code: '',
				message: this.t( 'network_failed' ),
			};
		}

		let body = {};

		try {
			body = await response.json();
		} catch ( error ) {
			body = {};
		}

		if ( ! response.ok && ! body.code ) {
			body.success = false;
			body.message = ( body.message && body.message.replace( /<[^>]+>/g, '' ) ) || this.t( 'request_failed' );
		}

		return body;
	}

	/**
	 * Connect and auto configure an environment.
	 *
	 * @param {string} environment Environment id.
	 * @return {Promise<void>} Resolves when the request finished.
	 */
	async connect( environment ) {
		if ( this.busy ) {
			return;
		}

		this.setBusy( true, this.t( 'connect_running' ) );

		try {
			const result = await this.request( '/connect', { environment } );

			if ( ! result.success && 'REBIND_REQUIRED' === result.code ) {
				await this.previewRebind( environment );
				return;
			}

			this.renderResult( environment, result );

		} finally {
			this.setBusy( false );
		}
	}

	/**
	 * Run a read-only configuration check.
	 *
	 * @param {string} environment Environment id.
	 * @return {Promise<void>} Resolves when the request finished.
	 */
	async check( environment ) {
		if ( this.busy ) {
			return;
		}

		this.setBusy( true, this.t( 'check_running' ) );

		try {
			const result = await this.request( '/check', { environment } );
			this.renderResult( environment, result );
		} finally {
			this.setBusy( false );
		}
	}

	/**
	 * Apply a previously reported diff.
	 *
	 * @param {string} environment Environment id.
	 * @param {Object} diff        Approved diff.
	 * @return {Promise<void>} Resolves when the request finished.
	 */
	async repair( environment, diff ) {
		if ( this.busy || ! diff ) {
			return;
		}

		this.setBusy( true, this.t( 'repair_running' ) );

		try {
			const result = await this.request( '/repair', { environment, diff } );
			this.hideDiff( environment );
			this.renderResult( environment, result );
		} finally {
			this.setBusy( false );
		}
	}

	/**
	 * Preview and confirm an account rebind.
	 *
	 * @param {string} environment Environment id.
	 * @return {Promise<void>} Resolves when the request finished.
	 */
	async previewRebind( environment ) {
		const result = await this.request( '/rebind-preview', { environment } );

		if ( ! result.success ) {
			this.renderResult( environment, result );
			return;
		}

		this.pendingRebind[ environment ] = result.plan_token;
		this.pendingDiff[ environment ] = result.diff;
		this.showDiff( environment, result.diff, 'rebind-confirm', result.plan );
		this.announce( result.message || '' );
	}

	/**
	 * Execute a confirmed rebind.
	 *
	 * @param {string} environment Environment id.
	 * @return {Promise<void>} Resolves when the request finished.
	 */
	async rebind( environment ) {
		const planToken = this.pendingRebind[ environment ];

		if ( this.busy || ! planToken ) {
			return;
		}

		this.setBusy( true, this.t( 'connect_running' ) );

		try {
			const result = await this.request( '/rebind', { environment, plan_token: planToken } );
			this.hideDiff( environment );
			this.renderResult( environment, result );

			if ( result.success ) {
				delete this.pendingRebind[ environment ];
			}
		} finally {
			this.setBusy( false );
		}
	}

	/**
	 * Render the exact diff and the matching confirmation action.
	 *
	 * @param {string} environment Environment id.
	 * @param {Object} diff        Diff payload.
	 * @param {string} confirmKey  data-action value of the confirm button.
	 * @param {Object} plan        Optional rebind plan.
	 * @return {void}
	 */
	showDiff( environment, diff, confirmKey, plan ) {
		const $panel = this.tabPanel( environment );
		const $diff = $panel.find( '[data-role="diff"]' );
		const $list = $panel.find( '[data-role="diff-list"]' );

		$list.empty();

		if ( diff && diff.fields ) {
			Object.keys( diff.fields ).forEach( ( field ) => {
				$list.append( this.diffRow( field, diff.fields[ field ] ) );
			} );
		}

		if ( plan && plan.actions ) {
			$list.append(
				this.diffRow( 'planned_action', {
					from: plan.previous && plan.previous.account_id ? plan.previous.account_id : '',
					to: Array.isArray( plan.actions ) ? plan.actions.join( ', ' ) : String( plan.actions ),
				} )
			);
		}

		$panel.find( '.giftflow-stripe-action[data-action="repair-confirm"]' ).prop( 'hidden', 'repair-confirm' !== confirmKey );
		$panel.find( '.giftflow-stripe-action[data-action="rebind-confirm"]' ).prop( 'hidden', 'rebind-confirm' !== confirmKey );

		$diff.prop( 'hidden', false );
		$diff.attr( 'role', 'dialog' );
		$diff.attr( 'tabindex', '-1' );
		$diff.trigger( 'focus' );
	}

	/**
	 * Build one diff row.
	 *
	 * @param {string} field Field name.
	 * @param {Object} value Field change.
	 * @return {jQuery} List item.
	 */
	diffRow( field, value ) {
		const labels = {
			status: this.t( 'field_status' ),
			url: this.t( 'field_url' ),
			events: this.t( 'field_events' ),
			account_id: this.t( 'field_account' ),
			planned_action: this.t( 'diff_title' ),
		};

		const label = labels[ field ] || field;
		const from = this.describeValue( value.from );
		const to = this.describeValue( value.to );

		return $( '<li>' )
			.append( $( '<strong>' ).text( label ) )
			.append( document.createTextNode( ': ' ) )
			.append( $( '<code>' ).text( from ) )
			.append( document.createTextNode( ' → ' ) )
			.append( $( '<code>' ).text( to ) );
	}

	/**
	 * Describe a diff value.
	 *
	 * @param {*} value Diff value.
	 * @return {string} Human readable value.
	 */
	describeValue( value ) {
		if ( Array.isArray( value ) ) {
			return value.join( ', ' );
		}

		if ( value && 'object' === typeof value ) {
			return JSON.stringify( value );
		}

		return '' === value || null === value || undefined === value ? '—' : String( value );
	}

	/**
	 * Hide the diff confirmation.
	 *
	 * @param {string} environment Environment id.
	 * @return {void}
	 */
	hideDiff( environment ) {
		const $diff = this.tabPanel( environment ).find( '[data-role="diff"]' );
		$diff.prop( 'hidden', true );
		$diff.find( '[data-role="diff-list"]' ).empty();
	}
}

export default GiftFlowStripeConnection;

( ( w, $ ) => {
	'use strict';

	/**
	 * Initialise the Stripe connection panel when present.
	 *
	 * @return {void}
	 */
	const initStripeConnection = () => {
		const panel = document.getElementById( 'giftflow-stripe-panel' );

		if ( ! panel ) {
			return;
		}

		const configNode = document.getElementById( 'giftflow-stripe-config' );
		let config = {};

		if ( configNode ) {
			try {
				config = JSON.parse( configNode.textContent ) || {};
			} catch ( error ) {
				config = {};
			}
		}

		w.giftflowStripeConnection = new GiftFlowStripeConnection( panel, config );
	};

	$( document ).on( 'DOMContentLoaded', initStripeConnection );
} )( window, jQuery );
