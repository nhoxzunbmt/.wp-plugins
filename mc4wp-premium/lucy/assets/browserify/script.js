'use strict';

var Lucy = require('./third-party/lucy.js');
var config = {
	siteUrl: 'https://mc4wp.com/',
	algoliaAppId: 'DA9YFSTRKA',
	algoliaAppKey: 'ce1c93fad15be2b70e0aa0b1c2e52d8e',
	algoliaIndexName: 'wpkb_articles',
	links: [
		{
			text: "<span class=\"dashicons dashicons-book\"></span> Knowledge Base",
			href: "https://kb.mc4wp.com/"
		},
		{
			text: "<span class=\"dashicons dashicons-editor-code\"></span> Code Snippets",
			href: "https://github.com/ibericode/mc4wp-snippets"
		},
		{
			text: "<span class=\"dashicons dashicons-editor-break\"></span> Changelog",
			href: "https://mc4wp.com/changelog/"
		}
	],
	contactLink: 'mailto:support@mc4wp.com'
};

// grab from WP dumped var.
if( window.lucy_config ) {
	config.emailLink = window.lucy_config.email_link;
}

var lucy = new Lucy(
	config.siteUrl,
	config.algoliaAppId,
	config.algoliaAppKey,
	config.algoliaIndexName,
	config.links,
	config.contactLink
);
