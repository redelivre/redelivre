module.exports = {
	entry:     {
		'js/forms-block':      './js/forms-block.js',
		'js/polls-block':      './js/polls-block.js',
		'js/quizzes-block':    './js/quizzes-block.js'
	},
	output:    {
		path:     __dirname + '/',
		filename: '[name].min.js',
	},

	module: {
		loaders: [
			{
				test: /.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
		},
		],
	},
};
