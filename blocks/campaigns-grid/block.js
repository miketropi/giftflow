import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { Disabled, PanelBody, SelectControl, TextControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

registerBlockType('giftflow/campaigns-grid', {
    apiVersion: 3,
    title: __('Campaigns Grid', 'giftflow'),
    description: __('Display a responsive grid of published campaigns with progress and pagination.', 'giftflow'),
    icon: 'grid-view',
    category: 'giftflow',
    attributes: {
        perPage: {
            type: 'number',
            default: 9,
        },
        orderby: {
            type: 'string',
            default: 'date',
        },
        order: {
            type: 'string',
            default: 'DESC',
        },
        category: {
            type: 'string',
            default: '',
        },
        search: {
            type: 'string',
            default: '',
        },
        customClass: {
            type: 'string',
            default: '',
        },
    },
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const blockProps = useBlockProps();
        const [categoryOptions, setCategoryOptions] = useState([
            { label: __('All categories', 'giftflow'), value: '' },
        ]);

        useEffect(() => {
            apiFetch({ path: '/wp/v2/campaign-tax?per_page=100' })
                .then((terms) => {
                    if (!Array.isArray(terms)) {
                        return;
                    }
                    setCategoryOptions([
                        { label: __('All categories', 'giftflow'), value: '' },
                        ...terms.map((t) => ({
                            label: t.name,
                            value: String(t.id),
                        })),
                    ]);
                })
                .catch(() => {
                    // REST may be unavailable; keep "All categories" only.
                });
        }, []);

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Query', 'giftflow')} initialOpen>
                        <RangeControl
                            label={__('Campaigns per page', 'giftflow')}
                            value={attributes.perPage}
                            onChange={(v) => setAttributes({ perPage: v == null ? 9 : v })}
                            min={1}
                            max={48}
                        />
                        <SelectControl
                            label={__('Order by', 'giftflow')}
                            value={attributes.orderby}
                            options={[
                                { label: __('Date', 'giftflow'), value: 'date' },
                                { label: __('Title', 'giftflow'), value: 'title' },
                                { label: __('Modified', 'giftflow'), value: 'modified' },
                                { label: __('Menu order', 'giftflow'), value: 'menu_order' },
                            ]}
                            onChange={(orderby) => setAttributes({ orderby })}
                        />
                        <SelectControl
                            label={__('Order', 'giftflow')}
                            value={attributes.order}
                            options={[
                                { label: __('Newest / Z–A first', 'giftflow'), value: 'DESC' },
                                { label: __('Oldest / A–Z first', 'giftflow'), value: 'ASC' },
                            ]}
                            onChange={(order) => setAttributes({ order })}
                        />
                        <SelectControl
                            label={__('Campaign category', 'giftflow')}
                            value={attributes.category}
                            options={categoryOptions}
                            onChange={(category) => setAttributes({ category })}
                            help={__('Filter by a single campaign category.', 'giftflow')}
                        />
                        <TextControl
                            label={__('Search', 'giftflow')}
                            value={attributes.search}
                            onChange={(search) => setAttributes({ search })}
                            help={__('Optional keyword filter (post title/content).', 'giftflow')}
                        />
                    </PanelBody>
                    <PanelBody title={__('Advanced', 'giftflow')} initialOpen={false}>
                        <TextControl
                            label={__('Extra CSS class', 'giftflow')}
                            value={attributes.customClass}
                            onChange={(customClass) => setAttributes({ customClass })}
                            help={__('Single class name added to the grid wrapper.', 'giftflow')}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps}>
                    <Disabled>
                        <ServerSideRender
                            block="giftflow/campaigns-grid"
                            attributes={attributes}
                        />
                    </Disabled>
                </div>
            </>
        );
    },
});
