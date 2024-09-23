const config = require('10up-toolkit/config/webpack.config.js');
const { sync: glob } = require('fast-glob');
const { resolve, extname } = require('path');
const { existsSync } = require('fs');

const [scriptConfig, moduleConfig] = config;

function getBlockStylesheetEntrypoints() {
	// get all stylesheets located in the assets/css/blocks directory and subdirectories
	const blockStylesheetDirectory = resolve(process.cwd(), './assets/css/blocks').replace(/\\/g, '/');

	// get all stylesheets in the blocks directory
	const stylesheets = glob(
		// glob only accepts forward-slashes this is required to make things work on Windows
		`${blockStylesheetDirectory}/**/*.css`,
		{
			absolute: true,
		},
	);

	const additionalEntryPoints = {};
	try {
		stylesheets.forEach((filePath) => {
			const blockName = filePath
				.replace(`${blockStylesheetDirectory}/`, '')
				.replace(extname(filePath), '');
			additionalEntryPoints[`autoenqueue/${blockName}`] = resolve(filePath);
		});
	} catch (error) {
		console.error(error); // eslint-disable-line no-console
	}

	return additionalEntryPoints;
}
module.exports = [
	{
		...scriptConfig,
		entry: () => {
			const additionalEntryPoints = getBlockStylesheetEntrypoints();
			const coreEntryPoints = scriptConfig.entry();

			const entryPoints = Object.assign(coreEntryPoints, additionalEntryPoints);

			// filter entrypoints to only include those that exist
			Object.keys(entryPoints).forEach((key) => {
				if (!existsSync(entryPoints[key])) {
					delete entryPoints[key];
				}
			});

			return entryPoints;
		},
	},
	moduleConfig,
];
