import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ShimmerBar, ensureShimmerStyles } from '../_editor-utils';

registerBlockType( 'giftflow/donor-account', {
	apiVersion: 3,
	title: __( 'Donor Account', 'giftflow' ),
	icon: 'admin-users',
	category: 'giftflow',
	edit: function Edit() {
		const blockProps = useBlockProps( {
			className: 'giftflow-donor-account',
		} );
		ensureShimmerStyles();

		const tabs = [
			{ label: __( 'Dashboard', 'giftflow' ), active: true },
			{ label: __( 'My Donations', 'giftflow' ) },
			{ label: __( 'My Account', 'giftflow' ) },
		];

		return (
			<>
				<InspectorControls>
					<PanelBody
						title={ __( 'About', 'giftflow' ) }
						initialOpen={ true }
					>
						<p
							style={ {
								color: '#757575',
								fontSize: 13,
								margin: 0,
							} }
						>
							{ __(
								'Displays the donor account interface with Dashboard, My Donations, and Account Settings tabs. Visitors see a login form if not authenticated.',
								'giftflow'
							) }
						</p>
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					{ /* Mirrors render.php: the navigation column lives in the sidebar wrapper, so the preview uses the same layout rules as the front end. */ }
					<div className="giftflow-donor-account__sidebar">
						<nav className="giftflow-donor-account__nav">
							{ tabs.map( ( tab, i ) => (
								<span
									key={ i }
									className={
										'giftflow-donor-account__nav-item' +
										( tab.active ? ' is-active' : '' )
									}
								>
									{ tab.label }
								</span>
							) ) }
						</nav>
					</div>
					<div
						className="giftflow-donor-account__content"
						style={ { minHeight: 260 } }
					>
						<div
							style={ {
								display: 'flex',
								flexDirection: 'column',
								gap: 16,
							} }
						>
							<div
								style={ {
									display: 'grid',
									gridTemplateColumns: 'repeat(3, 1fr)',
									gap: 12,
								} }
							>
								{ [ 1, 2, 3 ].map( ( n ) => (
									<div
										key={ n }
										style={ {
											padding: 16,
											border: '1px solid #e5e7eb',
											borderRadius: 8,
											textAlign: 'center',
										} }
									>
										<ShimmerBar
											height={ 22 }
											width="60%"
											style={ { margin: '0 auto 8px' } }
										/>
										<ShimmerBar
											height={ 13 }
											width="80%"
											style={ { margin: '0 auto' } }
										/>
									</div>
								) ) }
							</div>
							<div
								style={ {
									border: '1px solid #e5e7eb',
									borderRadius: 8,
									overflow: 'hidden',
								} }
							>
								<div
									style={ {
										display: 'flex',
										backgroundColor: '#f9fafb',
										padding: '8px 14px',
										borderBottom: '1px solid #e5e7eb',
										gap: 12,
									} }
								>
									{ [
										__( 'ID', 'giftflow' ),
										__( 'Amount', 'giftflow' ),
										__( 'Status', 'giftflow' ),
										__( 'Date', 'giftflow' ),
									].map( ( h, i ) => (
										<div
											key={ i }
											style={ {
												flex: 1,
												fontSize: 12,
												fontWeight: 600,
												color: '#666',
											} }
										>
											{ h }
										</div>
									) ) }
								</div>
								{ [ 1, 2, 3 ].map( ( r ) => (
									<div
										key={ r }
										style={ {
											display: 'flex',
											padding: '10px 14px',
											borderBottom: '1px solid #f3f4f6',
											gap: 12,
										} }
									>
										{ [ 35, 55, 30, 45 ].map( ( w, i ) => (
											<div
												key={ i }
												style={ { flex: 1 } }
											>
												<ShimmerBar
													height={ 12 }
													width={ `${ w }%` }
												/>
											</div>
										) ) }
									</div>
								) ) }
							</div>
						</div>
					</div>
				</div>
			</>
		);
	},
} );
