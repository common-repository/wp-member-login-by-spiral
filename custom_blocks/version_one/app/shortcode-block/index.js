import block_icons from '../icons/index';
import './editor.scss';
import tranlate from "../../../translator";


const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;
const 
{ 
    InspectorControls,
    BlockControls,
    AlignmentToolbar,
    BlockAlignmentToolbar,
    InnerBlocks
} = wp.blockEditor;
const {
    PanelBody,
    Tip
} = wp.components;

// 1
const SML_SHOW_TEMPLATE = [
    ['core/shortcode', { text: '[sml-show-template]' }]
];
// 2
const SML_IS_LOGGED_IN = [
    ['core/shortcode', { text: '[sml-is-logged-in]' }],
    ['core/paragraph', {}],
    ['core/shortcode', { text: '[/sml-is-logged-in]' }]
];
//3
const SML_IS_LOGGED_IN_HIDE = [
    ['core/shortcode', { text: '[sml-is-logged-in-hide]' }],
    ['core/paragraph', {}],
    ['core/shortcode', { text: '[/sml-is-logged-in-hide]' }]
];
//4
const SML_USER_PROP = [
    ['core/shortcode', { text: '[sml-user-prop key=""]' }]
];
//5
const SML_LINK = [
    ['core/shortcode', { text: '[sml-link link_text="" link="" key=""]' }]
];
//6
const SML_IS_LOGGED_MAYPAGE_TITLE = [
    ['core/shortcode', { text: '[sml-is-logged-mypage id="" title=""]' }]
];
//7
const SML_IS_LOGGED_MAYPAGE_IMAGE = [
    ['core/shortcode', { text: '[sml-is-logged-mypage id="" image=""]' }]
];
//8
const SML_IS_LOGIN_TYPE = [
    ['core/shortcode', { text: '[sml-is-logged-in-type key="" value=""]' }],
    ['core/paragraph', {}],
    ['core/shortcode', { text: '[/sml-is-logged-in-type]' }]
];
//9
const SML_IS_LOGGED_IN_RULE = [
    ['core/shortcode', { text: '[sml-is-logged-in-rule rule_name=""]' }],
    ['core/paragraph', {}],
    ['core/shortcode', { text: '[/sml-is-logged-in-rule]' }]
];

registerBlockType('spiral/sml-show-template', {
    title: tranlate('login_form'),
    description: tranlate('login_form_description_1'),
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-show-template ', 'spiral-member-login'),
        __('sml show template ', 'spiral-member-login'),
        __('Login Form', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                    <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2021082403/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>     
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_SHOW_TEMPLATE}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-is-logged-in', {
    title: tranlate('show_by_logged_in'),
    description: tranlate('show_by_logged_in_description_1'),
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-user-prop', 'spiral-member-login'),
        __('sml user prop', 'spiral-member-login'),
        __('show by logged in', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{ tranlate('show_by_logged_in_description_2') }</p> 
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_20210817/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>     
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_IS_LOGGED_IN}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-is-logged-in-hide', {
    title:  tranlate('hide_by_logged_in'),
    description: tranlate('hide_by_logged_in_description_1'),
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-user-prop', 'spiral-member-login'),
        __('sml user prop', 'spiral-member-login'),
        __('hide by logged in', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{tranlate('hide_by_logged_in_description_2') }</p>
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2021081702/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>     
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_IS_LOGGED_IN_HIDE}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-user-prop', {
    title: tranlate('display_user_data'),
    description: tranlate('display_user_data_description_1'),
    // common, formatting, layout, widgets, embed
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-user-prop', 'spiral-member-login'),
        __('sml user prop', 'spiral-member-login'),
        __('display user data', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    getEditWrapperProps: ({ block_alignment }) => {
        if ('left' === block_alignment || 'right' === block_alignment || 'full' === block_alignment) {
            return { 'data-align': block_alignment };
        }
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{  tranlate('display_user_data_description_2') }</p>
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2021082402/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_USER_PROP}
                    />
                </PanelBody>
            </div>
        ];
    },
    save: (props) => {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-link', {
    title: tranlate('link_with_user_data'),
    description: tranlate('link_with_user_data_description_1'),
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-link', 'spiral-member-login'),
        __('sml link', 'spiral-member-login'),
        __('Link with user data', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    getEditWrapperProps: ({ block_alignment }) => {
        if ('left' === block_alignment || 'right' === block_alignment || 'full' === block_alignment) {
            return { 'data-align': block_alignment };
        }
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{ tranlate('link_with_user_data_description_2') }</p>
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2022011802/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_LINK}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-is-logged-mypage-title', {
    title: tranlate('text_link'),
    description: tranlate('text_link_description_1'),
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-is-logged-mypage_title ', 'spiral-member-login'),
        __('sml is logged mypage title', 'spiral-member-login'),
        __('Text Link', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{ tranlate('text_link_description_2') }</p>
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2021090102/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_IS_LOGGED_MAYPAGE_TITLE}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-is-logged-mypage-image', {
    title: tranlate('image_link'),
    description: tranlate('image_link_description_1'),
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-is-logged-mypage ', 'spiral-member-login'),
        __('sml is logged mypage ', 'spiral-member-login'),
        __('Text Link', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{ tranlate('image_link_description_2') }</p>
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2021090103/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_IS_LOGGED_MAYPAGE_IMAGE}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-is-logged-in-type', {
    title: tranlate('show_by_user_data'),
    description:  tranlate('show_by_user_data_description_1'),
    // common, formatting, layout, widgets, embed
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-user-prop', 'spiral-member-login'),
        __('sml user prop', 'spiral-member-login'),
        __('display user data', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    getEditWrapperProps: ({ block_alignment }) => {
        if ('left' === block_alignment || 'right' === block_alignment || 'full' === block_alignment) {
            return { 'data-align': block_alignment };
        }
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{ tranlate('show_by_user_data_description_2') }</p>
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2021082405/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>     
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <BlockControls>
                    <BlockAlignmentToolbar
                        value={props.attributes.block_alignment}
                        onChange={(new_val) => {
                            props.setAttributes({ block_alignment: new_val })
                        }} />
                    <AlignmentToolbar
                        value={props.attributes.text_alignment}
                        onChange={(new_val) => {
                            props.setAttributes({ text_alignment: new_val });
                        }} />
                </BlockControls>
                <PanelBody>
                    <InnerBlocks
                        template={SML_IS_LOGIN_TYPE}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});
registerBlockType('spiral/sml-is-logged-in-rule', {
    title: tranlate('show_by_extract_rule'),
    description: tranlate('show_by_extract_rule_description_1'),
    // common, formatting, layout, widgets, embed
    category: 'spiral-member-login',
    icon: block_icons.wapuu,
    keywords: [
        __('sml-is-logged-in-rule', 'spiral-member-login'),
        __('sml is logged in rule', 'spiral-member-login'),
        __('Show by extract rule', 'spiral-member-login')
    ],
    supports: {
        html: false
    },
    getEditWrapperProps: ({ block_alignment }) => {
        if ('left' === block_alignment || 'right' === block_alignment || 'full' === block_alignment) {
            return { 'data-align': block_alignment };
        }
    },
    edit: (props) => {
        return [
            <InspectorControls>
                <PanelBody initialOpen={true}>
                    <Tip>
                        <p>{ tranlate('show_by_extract_rule_description_2') }</p>
                        <a href="https://apl-support.pi-pe.co.jp/wpmls_info/wpmls_2021082406/" target="_blank">{  tranlate('display_user_data_description_deail') }</a>     
                    </Tip>
                </PanelBody>
            </InspectorControls>,
            <div className={props.className}>
                <PanelBody>
                    <InnerBlocks
                        template={SML_IS_LOGGED_IN_RULE}
                    />
                </PanelBody>
            </div>
        ];
    },
    save(props) {
        return (
            <InnerBlocks.Content />
        )
    }
});