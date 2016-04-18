var cwd = require('cwd');
var path = require('path');
var fs = require('fs');
var webpack = require('webpack');
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
module.exports = phifty;
