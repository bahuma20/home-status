const webpack = require('webpack');
const path = require('path');
const HtmlWebpackPlugin = require('html-webpack-plugin');

const config = {
    entry: ['regenerator-runtime/runtime.js', './js/main.js'],
    output: {
        path: path.resolve(__dirname, '../public/app'),
        filename: 'bundle.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.css$/,
                use: [
                    'style-loader',
                    'css-loader'
                ]
            },
            {
                test: /\.scss$/,
                use: [
                    'style-loader',
                    'css-loader',
                    'sass-loader'
                ]
            }
        ]
    },
    plugins: [
        new HtmlWebpackPlugin({
            templateContent: ({htmlWebpackPlugin}) => '<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>' + htmlWebpackPlugin.options.title + '</title></head><body><div class="stage"></div><script src="https://unpkg.com/@lottiefiles/lottie-player@0.2.0/dist/lottie-player.js"></script></body></html>',
            filename: 'index.html',
        }),
        new webpack.DefinePlugin({
            "API_URL": JSON.stringify(process.env.API_URL),
        }),
    ],
    devServer: {
        static: {
            directory: path.resolve(__dirname, '../public/'),
        },
        compress: true,
        port: 9000,
    }
};

module.exports = config;
