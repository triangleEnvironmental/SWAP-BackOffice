const defaultTheme = require('tailwindcss/defaultTheme');
const plugin = require('tailwindcss/plugin')

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    darkMode: 'class', // or 'media' or 'class'
    theme: {
        asideScrollbars: {
            light: 'light',
            gray: 'gray'
        },
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            zIndex: {
                '-1': '-1'
            },
            flexGrow: {
                5: '5'
            },
            maxHeight: {
                'screen-menu': 'calc(100vh - 3.5rem)',
                modal: 'calc(100vh - 160px)'
            },
            transitionProperty: {
                position: 'right, left, top, bottom, margin, padding',
                textColor: 'color'
            },
            keyframes: {
                fadeOut: {
                    from: {opacity: 1},
                    to: {opacity: 0}
                },
                fadeIn: {
                    from: {opacity: 0},
                    to: {opacity: 1}
                }
            },
            animation: {
                fadeOut: 'fadeOut 250ms ease-in-out',
                fadeIn: 'fadeIn 250ms ease-in-out'
            }
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        plugin(function ({ matchUtilities, theme }) {
            matchUtilities(
                {
                    'aside-scrollbars': value => {
                        const track = value === 'light' ? '50' : '900'
                        const thumb = value === 'light' ? '300' : '600'
                        const color = value === 'light' ? 'gray' : value

                        return {
                            scrollbarColor: `${theme(`colors.${color}.${thumb}`)} ${theme(`colors.${color}.${track}`)}`,
                            '&::-webkit-scrollbar-track': {
                                backgroundColor: theme(`colors.${color}.${track}`)
                            },
                            '&::-webkit-scrollbar-thumb': {
                                backgroundColor: theme(`colors.${color}.${thumb}`)
                            }
                        }
                    }
                },
                { values: theme('asideScrollbars') }
            )
        })
    ],
};
