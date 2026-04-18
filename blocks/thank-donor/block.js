import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Disabled, PanelBody, TextareaControl, TextControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

registerBlockType('giftflow/thank-donor', {
    apiVersion: 3,
    title: __('Thank Donor', 'giftflow'),
    description: __(
        'Thank-you message for donors after a successful donation, with optional link to the donor account.',
        'giftflow'
    ),
    icon: 'yes-alt',
    category: 'giftflow',
    attributes: {
        heading: {
            type: 'string',
            default: __('🎉 Thank You!', 'giftflow'),
        },
        message: {
            type: 'string',
            default: __('Your donation has been received. We appreciate your support!', 'giftflow'),
        },
        accountNotice: {
            type: 'string',
            default: __(
                "We've created an account for you using the email from your donation. Your login details have been sent to your inbox.",
                'giftflow'
            ),
        },
        showAccountNotice: {
            type: 'boolean',
            default: true,
        },
        buttonText: {
            type: 'string',
            default: __('View My Donations', 'giftflow'),
        },
        buttonUrl: {
            type: 'string',
            default: '',
        },
        showButton: {
            type: 'boolean',
            default: true,
        },
    },
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const blockProps = useBlockProps();

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Content', 'giftflow')} initialOpen>
                        <TextControl
                            label={__('Heading', 'giftflow')}
                            value={attributes.heading}
                            onChange={(heading) => setAttributes({ heading })}
                        />
                        <TextareaControl
                            label={__('Message', 'giftflow')}
                            value={attributes.message}
                            onChange={(message) => setAttributes({ message })}
                            rows={3}
                        />
                        <ToggleControl
                            label={__('Show account notice', 'giftflow')}
                            checked={attributes.showAccountNotice}
                            onChange={(showAccountNotice) => setAttributes({ showAccountNotice })}
                        />
                        {attributes.showAccountNotice && (
                            <TextareaControl
                                label={__('Account notice', 'giftflow')}
                                value={attributes.accountNotice}
                                onChange={(accountNotice) => setAttributes({ accountNotice })}
                                rows={4}
                                help={__('Basic HTML allowed (links, bold, etc.).', 'giftflow')}
                            />
                        )}
                    </PanelBody>
                    <PanelBody title={__('Button', 'giftflow')} initialOpen={false}>
                        <ToggleControl
                            label={__('Show button', 'giftflow')}
                            checked={attributes.showButton}
                            onChange={(showButton) => setAttributes({ showButton })}
                        />
                        {attributes.showButton && (
                            <>
                                <TextControl
                                    label={__('Button text', 'giftflow')}
                                    value={attributes.buttonText}
                                    onChange={(buttonText) => setAttributes({ buttonText })}
                                />
                                <TextControl
                                    label={__('Button URL', 'giftflow')}
                                    value={attributes.buttonUrl}
                                    onChange={(buttonUrl) => setAttributes({ buttonUrl })}
                                    help={__(
                                        'Leave empty to use the default donor account link (same as ?gf-direct-to=donor-account).',
                                        'giftflow'
                                    )}
                                    type="url"
                                />
                            </>
                        )}
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <Disabled>
                        <ServerSideRender block="giftflow/thank-donor" attributes={attributes} />
                    </Disabled>
                </div>
            </>
        );
    },
});
