import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/sass/app.scss", // 元のファイルに合わせて .scss にしています
                "resources/js/app.js",
            ],
            refresh: true,
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
