/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {InnerBlocks,InspectorControls,useBlockProps} from '@wordpress/block-editor';
import { useState } from 'react';
import { PanelBody, TextControl, ToggleControl,SelectControl } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */


export default function Edit({attributes, setAttributes}) {
	const blockProps = useBlockProps();




	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'copyright-date-block' ) }>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __(
							'Knapplink'
						) }
						value={ attributes.linkURL || '' }
						onChange={ ( value ) =>
							setAttributes( { linkURL: value } )
						}
					/>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __(
							'Knapptekst'
						) }
						value={ attributes.linkText || '' }
						onChange={ ( value ) =>
							setAttributes( { linkText: value } )
						}
					/>
					<SelectControl
						label="Farge"
						value={ attributes.color }
						options={ [
							{ label: 'Lila', value: 'purple' },
							{ label: 'Grønn', value: 'green' },
							{ label: 'Gul', value: 'yellow' },
							{ label: 'Grå', value: 'grey' },
						] }
						onChange={ ( newColor ) => setAttributes( {color: newColor} ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>
			<div className={"block_container "+attributes.color}>
				<div class={"text"}>
					<InnerBlocks />
				</div>
				{attributes.linkURL && attributes.linkText &&
				<div class={"buttons"}>
					<a href={attributes.linkURL}>{attributes.linkText}</a>
				</div>}
			</div>
		</div>
	);
}
