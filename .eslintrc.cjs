// Minimal ESLint config to enforce semicolons across JS
// Extend further (e.g., vue plugin) when packages are installed
module.exports = {
  root: true,
  env: {
    browser: true,
    node: true,
    es2022: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-recommended',
  ],
  parser: 'vue-eslint-parser',
  parserOptions: {
    parser: {
      js: 'espree',
      ts: 'espree',
    },
    ecmaVersion: 'latest',
    sourceType: 'module',
    ecmaFeatures: {
      jsx: false,
    },
  },
  rules: {
    // Enforce terminating semicolons
    semi: ['error', 'always'],
    // Explicitly forbid JSX usage anywhere
    'no-restricted-syntax': [
      'error',
      { selector: 'JSXElement', message: 'JSX is not allowed in this project.' },
      { selector: 'JSXFragment', message: 'JSX is not allowed in this project.' },
    ],
  },
  ignorePatterns: [
    'node_modules/',
    'vendor/',
    'public/build/',
    'public/hot',
  ],
};
