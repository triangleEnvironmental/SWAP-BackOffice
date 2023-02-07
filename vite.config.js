import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import loadRawSvg from "./vite_plugins/load-raw-svg";
import loadMixinCss from "./vite_plugins/load-mixin-css";

export default defineConfig({
  plugins: [
    laravel({
      input: 'resources/js/app.js',
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    // loadRawSvg({
    //   fileRegex: /@ckeditor\/.*\.svg/,
    // }),
    // loadMixinCss({
    //   fileRegex: /@ckeditor\/.*\.css/,
    // }),
  ],
});
