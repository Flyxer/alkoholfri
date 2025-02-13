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
import {useSelect} from '@wordpress/data';
import { useState } from 'react';
import { PanelBody, TextControl, ToggleControl,SelectControl,RangeControl } from '@wordpress/components';

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

	const categories = useSelect(select =>
		select('core').getEntityRecords('taxonomy', 'category')
	);
	const tags = useSelect(select =>
		select('core').getEntityRecords('taxonomy', 'post_tag')
	);

	let size = attributes.size || "small";
	let rows = attributes.rows || 1;
	let excerpt_length = attributes.excerpt_length || 15;

	let words = [
		"hus", "bil", "bok", "stol", "bord", "dør", "vindu", "skole", "jobb", "mat",
		"vann", "kaffe", "brød", "melk", "eple", "hund", "katt", "fugl", "tre", "blomst",
		"sol", "måne", "stjerne", "himmel", "sky", "regn", "snø", "vind", "fjell", "sjø",
		"elv", "skog", "gress", "jord", "stein", "sand", "vei", "gate", "bro", "tog",
		"buss", "fly", "båt", "sykkel", "fot", "hånd", "øye", "nese", "munn", "øre",
		"hår", "hode", "arm", "bein", "hjerte", "lunge", "hjerne", "mage", "rygg", "kne",
		"klokke", "telefon", "data", "bok", "penn", "papir", "blyant", "saks", "nøkkel", "lommebok",
		"veske", "sekk", "sko", "jakke", "bukse", "skjorte", "genser", "lue", "vott", "skjerf",
		"seng", "pute", "dyne", "teppe", "lampe", "speil", "bilde", "sofa", "hylle", "skap",
		"kjøkken", "bad", "stue", "soverom", "gang", "trapp", "tak", "gulv", "vegg", "gardin"
	];

	return (
		<div {...blockProps}>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'flyxer-blocks' ) }>
					<TextControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __(
							'Overskrift'
						) }
						value={ attributes.title || '' }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
					/>
					<SelectControl
						label="Størrelse"
						value={ attributes.size }
						options={ [
							{ label: 'Liten', value: 'small' },
							{ label: 'Høy', value: 'tall' },
						] }
						onChange={ ( newSize ) => setAttributes( {size: newSize} ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
					{categories &&
						<SelectControl
							multiple
							value={attributes.categories_selected}
							label={__('Kategori')}
							options={categories.map(({id, name}) => ({label: name, value: id}))}
							onChange={(selected) => {
								// I haven't tested this code so I'm not sure what onChange returns.
								// But assuming it returns an array of selected values:
								setAttributes({categories_selected: selected})
							}}

						/>
					}
					<a onClick={function(e) {e.preventDefault; setAttributes({categories_selected: []}); }}>Tøm kategorier</a>
					{tags &&
						<SelectControl
							multiple
							value={attributes.tags_selected}
							label={__('Tag')}
							options={tags.map(({id, name}) => ({label: name, value: id}))}
							onChange={(selected) => {
								// I haven't tested this code so I'm not sure what onChange returns.
								// But assuming it returns an array of selected values:

								setAttributes({tags_selected: selected})
							}}

						/>
					}
					<a onClick={function(e) {e.preventDefault; setAttributes({tags_selected: []}); }}>Tøm stikkord</a>

					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label="Columns"
						value={ rows }
						onChange={ ( value ) => setAttributes( {rows: value} ) }
						min={ 1 }
						max={ 30 }
					/>
					<RangeControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label="Sammendrag ord"
						value={ excerpt_length }
						onChange={ ( value ) => setAttributes( {excerpt_length: value} ) }
						min={ 10 }
						max={ 50 }
					/>
				</PanelBody>
			</InspectorControls>
			<div className={"block_post_card "+size }>
				{
					attributes.title && <h2>{attributes.title}</h2>
				}
				{[...Array(3 * rows)].map((x, i) =>
					<div className={"card"} key={i}>
						<figure></figure>
						<span className="text">
							<span className="title">
								{
									size == "tall" && <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-move-right"><path d="M18 8L22 12L18 16"/><path d="M2 12H22"/></svg>
								}
								Tittel
							</span>
						{
							size == "small" && <><span className="summary">{
								[...Array(excerpt_length)].map((x, i) => {
									return words[Math.floor(Math.random() * words.length)];
								}).join(" ")
							}</span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" className="lucide lucide-move-right"><path d="M18 8L22 12L18 16"/><path d="M2 12H22"/></svg></>
						}
						</span>
					</div>
				)}
				{ attributes.categories_selected.map( item => (
					<div className="category" data-id={ item }></div>
				) ) }
				{ attributes.tags_selected.map( item => (
					<div className="tag" data-id={ item }></div>
				) ) }
			</div>
		</div>
	);
}
