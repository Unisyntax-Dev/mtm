const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

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
            { test: /\.tsx?$/, use: 'ts-loader', exclude: /node_modules/ },
            { test: /\.s?css$/, use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'] }
        ]
    },
    resolve: { extensions: ['.tsx', '.ts', '.js'] },
    plugins: [ new MiniCssExtractPlugin({ filename: '[name].css' }) ]
};
