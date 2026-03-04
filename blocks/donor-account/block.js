import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Disabled, PanelBody, SelectControl, ToggleControl, BaseControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType(
	'giftflow/donor-account',
	{
		apiVersion: 3,
		title: 'Donor Account',
		icon: 'admin-users',
		category: 'giftflow',
		attributes: {
		},
		edit: (props) => {
			const { attributes, setAttributes } = props;
			const blockProps                    = useBlockProps();

			return (
			<>
				<div {...blockProps} >
					<Disabled>
						<ServerSideRender
							block      = "giftflow/donor-account"
							attributes = { attributes }
						/>
					</ Disabled>
				</ div>
			</>
		);
		},
	}
);
