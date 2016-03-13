var cwd = require('cwd');
var __root = cwd();


var phifty = {};

phifty.moduleDirectory = path.join(__dirname, 'node_modules');

phifty.webpackExcludePaths = function() {
  var excludePaths = [];
  excludePaths.push(moduleDirectory);
  excludePaths.push(path.resolve(__root, 'node_modules'));
  excludePaths.push(/node_modules/);
  return excludePaths;
};

phifty.assetEntries = function() {
  var assetEntryJson = path.resolve(__root, '.asset-entries.json');
  try {
    var json = fs.readFileSync(assetEntryJson);
    return JSON.parse(json);
  } catch (e) {
    return null;
  }
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

module.exports = phifty;
