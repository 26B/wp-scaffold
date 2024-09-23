const config = {
	extends: ['@10up/stylelint-config'],
	rules: {
		'scale-unlimited/declaration-strict-value': [
			'/color/',
			{
				ignoreValues: [
					'currentcolor',
					'currentColor',
					'inherit',
					'initial',
					'transparent',
					'unset',
				],
			},
		],
		'custom-property-pattern': [
			'^([a-z][a-z0-9]*)(-[a-z0-9]+)*$|^wp[-]{1,2}([a-z][a-z0-9]*)([-]{1,2}[a-z0-9]+)*$',
			{
				message: 'Expected custom property name to be kebab-case or wp--kebab--case',
			},
		],
	},
};

module.exports = config;
