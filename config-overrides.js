const { override } = require('customize-cra');
const path = require('path');

module.exports = override(config => {
  config.cache = {
    type: 'filesystem',
    cacheDirectory: path.resolve(__dirname, '.webpack_cache'),
  };
  return config;
});