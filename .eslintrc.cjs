/* eslint-env node */
module.exports = {
  root: true,
  extends: [
    '@ycs77',
  ],
  env: {
    'vue/setup-compiler-macros': true,
  },
  rules: {
    'no-alert': 'off',
    'no-console': 'off',
    'vue/no-template-shadow': 'off',
    '@typescript-eslint/ban-ts-comment': 'off',
  },
}
