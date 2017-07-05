const cwd = require('cwd');
const path = require('path');
const fs = require('fs');
const webpack = require('webpack');
var __root = cwd();

var phifty = {};

phifty.moduleDirectory = path.join(__dirname, 'node_modules');

phifty.webpackExcludePaths = function() {
  var excludePaths = [];
  excludePaths.push(this.moduleDirectory);
  excludePaths.push(path.resolve(__root, 'node_modules'));
  excludePaths.push(/node_modules/);
  return excludePaths;
};

phifty.assetEntries = function() {
  var files = [
    path.resolve(__root, '.asset-entries.json'),
    path.resolve(__dirname, '.asset-entries.json'),
  ];
  while (files.length > 0) {
    var file = files.pop();
    try {
      var json = fs.readFileSync(file);
      return JSON.parse(json);
    } catch (e) {

    }
  }
  return null;
};
phifty.assetAliases = function() {
  var entries = this.assetEntries();
  if (entries) {
    var aliases = {};
    for (var key in entries.stash) {
      aliases[key] = entries.stash[key].source_dir;
    }
    return aliases;
  }
  return {};
};
phifty.webpack = webpack;

phifty.buildWebpackConfig = function(configRoot) {

    const excludePaths = phifty.webpackExcludePaths();
    const aliases = phifty.assetAliases(); // load asset packages

    return {
        entry: configRoot + '/entry',

        output: { path: configRoot, filename: './bundle.js' },

        module: {
            loaders: [
                {
                    "test": /\.tsx?$/,
                    "loader": "ts-loader",
                    "exclude": [/node_modules/,excludePaths]
                },
                {
                    "test": /\.jsx?$/,
                    "loader": "babel-loader",
                    "query": {
                        "presets": ["react", "es2015"],
                        "plugins": ["transform-class-properties", "transform-decorators-legacy"]
                    },
                    "exclude": [/node_modules/,excludePaths]
                }
            ]
        },

        externals: {
            // don't bundle the 'react' npm package with our bundle.js
            // but get it from a global 'React' variable
            'jquery': 'jQuery'
            'react': 'React',
            'react-dom': 'ReactDOM',
        },

        resolve: {
            extensions: ['', '.ts', '.tsx', '.js', '.jsx'],
            fallback: [ path.join(configRoot, "node_modules"), phifty.moduleDirectory],
            alias: aliases
        },

        resolveLoader: {
            fallback: [ path.join(configRoot, "node_modules"), phifty.moduleDirectory]
        }
    };
};

module.exports = phifty;
