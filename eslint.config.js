import ycs77, { GLOB_TS, GLOB_VUE } from '@ycs77/eslint-config'

export default ycs77(
  {
    vue: true,
    typescript: true,
    ignores: [
      '**/auto-components.d.ts',
      '**/auto-imports.d.ts',
    ],
  },
  {
    files: [GLOB_TS, GLOB_VUE],
    rules: {
      'no-alert': 'off',
      'no-console': 'off',
      'ts/ban-ts-comment': 'off',
    },
  },
  {
    files: [GLOB_VUE],
    rules: {
      'vue/no-template-shadow': 'off',
    },
  },
)
