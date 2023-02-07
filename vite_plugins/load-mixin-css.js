// For CKEditor CSS mixin and nested

const fs = require('fs')
const postcss = require('postcss')
const postcssImport = require('postcss-import')
const postcssNesting = require('postcss-nesting')
const postcssMixins = require('postcss-mixins')

export default (options) => {
  return {
    name: 'vite-plugin-load-mixin-css',
    enforce: 'pre',
    async transform(code, id) {
      if (options.fileRegex.test(id) && /@mixin/.test(code)) {
        const compiledNestedCss = await postcss([
          postcssImport(),
          postcssMixins(),
          postcssNesting({noIsPseudoSelector: true})
        ]).process(code, {from: id})

        return {
          code: compiledNestedCss.css,
          map: null,
        };
      }
      return undefined
    },
  };
}
