"use strict";Object.defineProperty(exports, "__esModule", {value: true}); function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }var _defaultTheme = require('tailwindcss/defaultTheme'); var _defaultTheme2 = _interopRequireDefault(_defaultTheme);
var _forms = require('@tailwindcss/forms'); var _forms2 = _interopRequireDefault(_forms);

/** @type {import('tailwindcss').Config} */
exports. default = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ..._defaultTheme2.default.fontFamily.sans],
                display: ['Inter', ..._defaultTheme2.default.fontFamily.sans],
            },
            colors: {
                "primary": "#62bd19",
                "background-light": "#f6f7f8",
                "background-dark": "#111921",
            },
        },
    },

    plugins: [_forms2.default],
};
 /* v7-26ffbd22eef5691e */