import ycs77, { GLOB_TS, GLOB_VUE } from '@ycs77/eslint-config'

export default ycs77({
  vue: true,
  typescript: true,
  ignores: [
    'composer.json',
    'tsconfig.json',
    'lang/**/*',
  ],
})
  .append({
    files: [GLOB_TS, GLOB_VUE],
    rules: {
      'no-alert': 'off',
      'no-console': 'off',

      'antfu/curly': 'off',

      'ts/prefer-ts-expect-error': 'off',
    },
  })
