const webpack = require("webpack");
const path = require("path");
const ExtractTextPlugin = require("extract-text-webpack-plugin");

const assetsPath = path.resolve(__dirname, "resources/assets/javascripts");

module.exports = {
  mode: "development",
  devtool: "inline-source-map",
  devServer: {
    contentBase: path.join(__dirname, "./public/assets"),
    compress: true,
    port: 8123,
    historyApiFallback: true,
    https: true
  },
  entry: {
    "studip-base": assetsPath + "/entry-base.js",
    "studip-admission": assetsPath + "/entry-admission.js",
    "studip-enrolment": assetsPath + "/entry-enrolment.js",
    "studip-files": assetsPath + "/entry-files.js",
    "studip-filesdashboard": assetsPath + "/entry-filesdashboard.js",
    "studip-raumzeit": assetsPath + "/entry-raumzeit.js",
    "studip-settings": assetsPath + "/entry-settings.js",
    "studip-statusgroups": assetsPath + "/entry-statusgroups.js",
    "studip-subcourses": assetsPath + "/entry-subcourses.js",
    "studip-userfilter": assetsPath + "/entry-userfilter.js",
    "studip-widgets": assetsPath + "/entry-widgets.js",
    "studip-wysiwyg": assetsPath + "/entry-wysiwyg.js",
    "print": path.resolve(__dirname, "resources/assets/stylesheets") + "/print.less"
  },
  output: {
    path: path.resolve(__dirname, "public/assets"),
    chunkFilename: "javascripts/[name].chunk.js",
    filename: "javascripts/[name].js"
  },
  module: {
    rules: [
      {
        test: /\.css$/,
        use: ExtractTextPlugin.extract({
          use: [
            {
              loader: "css-loader",
              options: {
                url: false,
                importLoaders: 1
              }
            },
            {
              loader: "postcss-loader"
            }
          ]
        })
      },
      {
        test: /\.less$/,
        use: ExtractTextPlugin.extract({
          use: [
            {
              loader: "css-loader",
              options: {
                url: false,
                importLoaders: 1
              }
            },
            {
              loader: "postcss-loader"
            },
            {
              loader: "less-loader",
              options: {
                relativeUrls: false
              }
            }
          ]
        })
      }
    ]
  },
  plugins: [
    new ExtractTextPlugin({
      filename: "stylesheets/[name].css"
    }),
    new webpack.ProvidePlugin({
      $: "jquery",
      jQuery: "jquery",
      "window.$": "jquery",
      "window.jQuery": "jquery"
    })
  ]
};
