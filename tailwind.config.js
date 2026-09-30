module.exports = {
	prefix: 'tw-',
	important: '.ci-abx-shell',
	content: [
		'./includes/**/*.php',
		'./views/**/*.php',
		'./assets/js/**/*.js'
	],
	corePlugins: {
		preflight: false
	},
	theme: {
		extend: {
			colors: {
				ci: {
					orange: '#d83d00',
					'dark': '#b83200'
				},
				'wp-admin': '#4f4f67'
			}
		}
	},
	plugins: []
};
