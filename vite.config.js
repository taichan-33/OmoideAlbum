import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                compilerOptions: {
                    isCustomElement: (tag) => tag === "trix-editor",
                },
            },
        }),
    ],

    // ★★★ (追加) この設定を追加 ★★★
    optimizeDeps: {
        /**
         * 'jvectormap-content' パッケージは ESモジュールではない
         * (jQueryのグローバルスコープに依存する古い形式のJS) ため、
         * Viteの依存関係スキャナから除外(exclude)する。
         * これにより、"could not be resolved" エラーを回避する。
         */
        exclude: ["jvectormap-content"],
    },
});
