const mix = require('laravel-mix');
require('@tinypixelco/laravel-mix-wp-blocks');

// Get all JS files in assets/js directory
const fs = require('fs');
const path = require('path');

mix.block('blocks/index.js', 'blocks-build');

mix.babelConfig({
	presets: [
		['@babel/preset-env', { modules: false }],
		['@babel/preset-react', { runtime: 'classic' }]
	],
	plugins: [
		['@babel/plugin-transform-runtime', { regenerator: true }]
	]
});

mix.webpackConfig({
	externals: {
		react: 'wp.element',
		'react-dom': 'wp.element',
		'@wordpress/element': 'wp.element',
	}
});


// mix.react()
//    .setPublicPath('assets')

// Function to get all JS files in a directory
const getJsFiles = (dir) => {
	const jsFiles = {};
	fs.readdirSync(dir)
		.filter(file => file.endsWith('.js') && !file.includes('.bundle'))
		.forEach(file => {
			const filename = path.basename(file, '.js');
			jsFiles[filename] = path.join(dir, file);
		});
	return jsFiles;
};

// Function to get all SCSS files in a directory
const getScssFiles = (dir) => {
	const scssFiles = {};
	fs.readdirSync(dir)
		.filter(file => file.endsWith('.scss'))
		.forEach(file => {
			const filename = path.basename(file, '.scss');
			scssFiles[filename] = path.join(dir, file);
		});
	return scssFiles;
};

// Get JS and SCSS files
const jsFiles = getJsFiles('assets/js');
const scssFiles = getScssFiles('assets/css');

// Process JS files
Object.keys(jsFiles).forEach(filename => {
    mix.js(jsFiles[filename], `assets/js/${filename}.bundle.js`);
});

// Process SCSS files
Object.keys(scssFiles).forEach(filename => {
	mix.sass(scssFiles[filename], `assets/css/${filename}.bundle.css`);
});


// for admin 
mix
	.js('admin/js/admin.js', 'assets/js/admin.bundle.js')
	.sass('admin/css/admin.scss', 'assets/css/admin.bundle.css')
	.options({
			processCssUrls: false
	});;


mix.override(config => {
	config.module.rules.forEach(rule => {
		if (String(rule.test).includes('js')) {
			rule.use.forEach(loader => {
				if (loader.loader === 'babel-loader') {
					loader.options.plugins = (loader.options.plugins || []).concat([
						["@babel/plugin-transform-runtime", {
							"regenerator": true
						}]
					]);

					loader.options.presets.push(['@babel/preset-react', { runtime: 'classic' }]);
				}
			});
		}
	});
});