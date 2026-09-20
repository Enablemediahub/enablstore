import eslint from '@eslint/js';
import prettier from 'eslint-config-prettier';
import globals from 'globals';
import vue from 'eslint-plugin-vue';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    eslint.configs.recommended,
    ...tseslint.configs.recommended,
    ...vue.configs['flat/recommended'],
    prettier,
    {
        files: ['resources/js/**/*.{ts,vue}'],
        languageOptions: {
            globals: {
                ...globals.browser,
                route: 'readonly',
            },
            parserOptions: {
                parser: tseslint.parser,
            },
        },
        rules: {
            'vue/multi-word-component-names': 'off',
            '@typescript-eslint/no-unused-vars': [
                'error',
                { argsIgnorePattern: '^_', varsIgnorePattern: '^_' },
            ],
        },
    },
    {
        ignores: ['public/build/**', 'vendor/**', 'node_modules/**'],
    },
);
