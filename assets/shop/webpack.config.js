const path = require('path');
const webpack = require("webpack");
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const WebpackShellPluginNext = require('webpack-shell-plugin-next');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const globImporter = require('node-sass-glob-importer');
const glob = require("glob");

module.exports = (env) => {

  const mode = env.production ? 'production' : 'development';
  const devtool = mode === 'development' ? 'eval-source-map' : 'nosources-source-map';
  const stats = mode === 'development' ? 'errors-warnings' : { children: false };

  let entries = {};
  let child_themes = glob.sync("./scripts/child-theme/*");
  child_themes.forEach(filepath=>{
    entries[path.parse(filepath).name] = [filepath];
  });

  entries['theme'] = ['./scripts/theme.js'];

  let config = {
    mode: mode,
    devtool: devtool,
    stats: stats,
    entry: entries,
    output: {
      filename: 'js/[name].js',
      path: path.resolve(__dirname, '../../public/assets/shop'),
      publicPath: '/assets/',
    },
    resolve: {
      alias: {
        styles: path.resolve(__dirname, './styles/'),
        vue: mode === 'production' ? 'vue/dist/vue.min.js' : 'vue/dist/vue.js'
      }
    },
    plugins: [
      new CleanWebpackPlugin(),
      new MiniCssExtractPlugin({
        filename: './css/[name].css',
      }),
      new CopyPlugin({
        patterns: [
          { from: path.resolve(__dirname, "img"), to: path.resolve(__dirname, '../../public/assets/shop/img') },
          { from: path.resolve(__dirname, "fonts"), to: path.resolve(__dirname, '../../public/assets/shop/fonts') }
        ]})
    ],
    module: {
      rules: [
        {
          test: /\.(sc|sa|c)ss$/,
          use: [
            {
              loader: MiniCssExtractPlugin.loader,
            },
            {
              loader: 'string-replace-loader',
              options: {
                multiple: [
                  { search: '../../../img/', replace: '../img/', flags: 'g' },
                  { search: '../../img/', replace: '../img/', flags: 'g' },
                  { search: '../img/', replace: '../img/', flags: 'g' },
                  { search: '../../../fonts/', replace: '../fonts/', flags: 'g' },
                  { search: '../../fonts/', replace: '../fonts/', flags: 'g' },
                  { search: '../fonts/', replace: '../fonts/', flags: 'g' }
                ]
              }
            },
            {
              loader: 'css-loader',
              options: {
                url: false
              }
            },
            {
              loader:'postcss-loader'
            },
            {
              loader: 'sass-loader',
              options: {
                sassOptions:{
                  importer: globImporter()
                },
                sourceMap: true
              }
            }
          ]
        }
      ]
    }
  };

  if ( env.WEBPACK_WATCH && env.shopify) {

    config.plugins.push(
      new WebpackShellPluginNext({
        onBuildStart:{
          scripts: ['echo Webpack build in progress...'],
        },
        onBuildEnd:{
          scripts: ['echo Build Complete'],
          parallel: true
        }
      })
    )
  }

  return config;
}
