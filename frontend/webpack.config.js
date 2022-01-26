module.exports = {
    entry: ['regenerator-runtime/runtime.js', './js/main.js'],
    output: {
        path: __dirname + '/../public/app',
        filename: 'app.js'
    },
    devtool: 'source-map',
    module: {
        rules: [{
            test: /\.m?js$/,
            exclude: /node_modules/,
            use: {
                loader: 'babel-loader',
                options: {
                    presets: ['@babel/preset-env']
                }
            }
        }]
    }
}
