import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { resolve } from "path";

export default defineConfig({
      plugins: [
            laravel({
                  input: [
                        'resources/sass/app.scss',
                        'resources/js/app.js',
                        'resources/js/order-edit.js',
                        'resources/js/orders-workspace.js',
                  ],
                  refresh: true,
            }),
            vue(),
      ],
      resolve: {
            alias: {
                  vue: resolve(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js'),
            },
      },
});
