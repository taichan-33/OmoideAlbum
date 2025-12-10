import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";
import { VitePWA } from "vite-plugin-pwa";

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
        VitePWA({
            registerType: "autoUpdate",
            outDir: "public",
            manifest: {
                name: "Omoide Album",
                short_name: "Omoide",
                description: "Couple Trip & Memory Album",
                theme_color: "#ffffff",
                start_url: "/",
                scope: "/",
                icons: [
                    {
                        src: "/icons/icon-192x192.png",
                        sizes: "192x192",
                        type: "image/png",
                    },
                    {
                        src: "/icons/icon-512x512.png",
                        sizes: "512x512",
                        type: "image/png",
                    },
                ],
            },
            devOptions: {
                enabled: false,
            },
            workbox: {
                maximumFileSizeToCacheInBytes: 5000000,
                navigateFallback: null,
                globIgnores: ["**/*.map", "**/manifest.webmanifest"],
            },
        }),
    ],

    optimizeDeps: {
        exclude: ["jvectormap-content"],
    },
});
