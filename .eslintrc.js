module.exports = {
  'env': {
    'browser': true,
    'amd': true,
    'es6': true,
  },
  'extends': ['eslint:recommended'],
  'parserOptions': {
    'ecmaFeatures': {
      'experimentalObjectRestSpread': true,
    },
    'sourceType': 'module'
  },
  'globals': {
    'module': false,
    'require': false
  },
  'rules': {
    'indent': [
      'error',
      2
    ],
    'linebreak-style': [
      'error',
      'unix'
    ],
    'quotes': [
      'error',
      'single'
    ],
    'semi': [
      'error',
      'always'
    ]
  }
};
