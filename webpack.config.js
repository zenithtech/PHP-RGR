// var webpack = require("webpack");

module.exports = {
    // plugins: [
    //     new webpack.DefinePlugin({
    //         'process.env': {
    //             'NODE_ENV': JSON.stringify('production')
    //         }
    //     }),
    //     new webpack.optimize.UglifyJsPlugin({
    //         output: {
    //             // comments: false
    //         },
    //         compress: {
    //             warnings: false
    //         }
    //     })
    // ],
    entry: './public/js/js.js',
    output: {
        path: __dirname + '/public/js',
        pathinfo: false,
        filename: 'js_bundle.js'
    },
    module: {
        loaders: [{
            test: /\.js$/,
            exclude: /node_modules/,
            loader: 'babel-loader',
            query: {
                presets: [
                    'react',
                    'es2015',
                    'stage-0'
                ],
                plugins: [
                	__dirname + '/public/js/babelRelayPlugin'
                ]
            }
        }]
    }
};
