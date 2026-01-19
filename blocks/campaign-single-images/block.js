import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled } from '@wordpress/components';
const { useSelect } = wp.data;

registerBlockType(
	'giftflow/campaign-single-images',
	{
		apiVersion: 3,
		title: 'Campaign Single Images',
		icon: 'format-gallery',
		category: 'giftflow',
		usesContext: ['postId'],
		edit: (props) => {
			const { attributes, ...rest } = props;
			const blockProps = useBlockProps();

			// if context.postId is 0 or empty, set attributes.__editorPostId to 0
			attributes.__editorPostId = rest?.context?.postId ?? 0;

			return (
			<div {...blockProps}>
				<Disabled >
					<ServerSideRender
						block="giftflow/campaign-single-images"
						attributes={ attributes } />
				</Disabled>
			</div>
		);
		},
	}
);
