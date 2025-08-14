const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

module.exports = {
    entry: {
        public: ['./assets/src/public/index.tsx', './assets/src/public/scss/public.scss'],
        admin:  ['./assets/src/admin/index.tsx',  './assets/src/admin/scss/admin.scss']
    },
    output: {
        path: path.resolve(__dirname, 'assets/dist'),
        filename: '[name].js',
        clean: true
    },
    devtool: 'source-map',
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                use: 'ts-loader',
                exclude: /node_modules/
            },
            {
                test: /\.s?css$/,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader']
            },
            {
                test: /\.(png|jpe?g|gif|svg|woff2?|eot|ttf)$/i,
                type: 'asset/resource',
                generator: {
                    filename: 'assets/[name][ext]' // в dist попадёт как assets/icon.svg
                }
            }
        ]
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js']
    },
    plugins: [
        new MiniCssExtractPlugin({ filename: '[name].css' }),
        new CopyWebpackPlugin({
            patterns: [
                {
                    from: path.resolve(__dirname, 'assets/src/public/icons'),
                    to: path.resolve(__dirname, 'assets/dist/icons'),
                    noErrorOnMissing: true
                }
            ]
        })
    ]
};
