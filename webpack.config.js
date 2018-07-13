const path = require( "path" );
const webpack = require( "webpack" );

module.exports = {
	entry: {
		"admin-edit-page": "./js/src/admin-edit-page.js",
		"admin-edit-profile": "./js/src/admin-edit-profile.js",
		"admin-edit-profile-secondary": "./js/src/admin-edit-profile-secondary.js",
		"admin-profile-list-table": "./js/src/admin-profile-list-table.js",
		"admin-user-profile": "./js/src/admin-user-profile.js",
		"directory-template": "./js/src/directory-template.js",
	},
	output: {
		path: __dirname + "/js",
		filename: "[name].min.js"
	},
	mode: "production",
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: "babel-loader"
				}
			}
		]
	}
};
