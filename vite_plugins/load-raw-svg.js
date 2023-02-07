// For CKEditor SVG error getAttribute

import fs from "fs";

export default (options) => {
  return {
    name: 'vite-plugin-load-raw-svg',
    transform(code, id) {
      if (options.fileRegex.test(id)) {
        // eslint-disable-next-line no-param-reassign
        code = fs.readFileSync(id, 'utf8');

        const json = JSON.stringify(code)
          .replace(/\u2028/g, '\\u2028')
          .replace(/\u2029/g, '\\u2029');
        return {
          code: `export default ${json}`,
          map: { mappings: '' },
        };
      }
    },
    generateBundle(_, bundle) {
      for (const [filename, info] of Object.entries(bundle)) {
        if (options.fileRegex.test(info.name)) {
          delete bundle[filename];
        }
      }
    },
  };
}
