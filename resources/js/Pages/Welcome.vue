<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Welcome to Omoide Album" />

    <div
        class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50 relative overflow-hidden selection:bg-indigo-500 selection:text-white"
    >
        <!-- Background Decorations -->
        <div
            class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none"
        >
            <div
                class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-200 rounded-full blur-[120px] opacity-30 animate-pulse-slow"
            ></div>
            <div
                class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-200 rounded-full blur-[120px] opacity-30 animate-pulse-slow delay-1000"
            ></div>
            <div
                class="absolute top-[20%] right-[10%] w-[20%] h-[20%] bg-amber-100 rounded-full blur-[80px] opacity-40 animate-float"
            ></div>
        </div>

        <!-- Navbar -->
        <nav
            class="relative z-10 w-full px-6 py-6 flex justify-between items-center max-w-7xl mx-auto"
        >
            <div class="flex items-center gap-2">
                <div
                    class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="w-6 h-6 text-white"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"
                        />
                    </svg>
                </div>
                <span
                    class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-800 to-gray-600 tracking-tight"
                    >Omoide Album</span
                >
            </div>

            <div class="flex items-center gap-4">
                <Link
                    :href="route('login')"
                    class="text-sm font-semibold text-gray-600 hover:text-gray-900 transition-colors"
                >
                    ログイン
                </Link>
                <Link
                    :href="route('register')"
                    class="px-5 py-2.5 rounded-full bg-gray-900 text-white text-sm font-semibold shadow-lg hover:bg-gray-800 hover:shadow-xl transition-all transform hover:-translate-y-0.5"
                >
                    新規登録
                </Link>
            </div>
        </nav>

        <!-- Hero Section -->
        <main
            class="relative z-10 flex flex-col items-center justify-center min-h-[80vh] px-4 text-center max-w-5xl mx-auto"
        >
            <div
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/60 backdrop-blur-sm border border-white/50 shadow-sm mb-8 animate-fade-in-up"
            >
                <span class="flex h-2 w-2 relative">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"
                    ></span>
                    <span
                        class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"
                    ></span>
                </span>
                <span class="text-sm font-medium text-gray-600"
                    >夫婦のための思い出記録アプリ</span
                >
            </div>

            <h1
                class="text-5xl md:text-7xl font-extrabold text-gray-900 tracking-tight mb-6 leading-tight animate-fade-in-up delay-100"
            >
                ふたりの思い出を、<br />
                <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600"
                    >もっと特別に。</span
                >
            </h1>

            <p
                class="text-lg md:text-xl text-gray-500 max-w-2xl mb-10 leading-relaxed animate-fade-in-up delay-200"
            >
                旅行の記録、訪れた場所の地図、そしてAIによる思い出の要約。<br />
                Omoide
                Albumは、ふたりの大切な時間を美しく残すためのデジタルアルバムです。
            </p>

            <div
                class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto animate-fade-in-up delay-300"
            >
                <Link
                    :href="route('register')"
                    class="px-8 py-4 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-lg shadow-xl shadow-blue-500/30 hover:shadow-2xl hover:shadow-blue-500/40 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-2"
                >
                    <span>今すぐ始める</span>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="w-5 h-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"
                        />
                    </svg>
                </Link>
                <Link
                    :href="route('login')"
                    class="px-8 py-4 rounded-2xl bg-white text-gray-700 font-bold text-lg shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 border border-gray-100 flex items-center justify-center"
                >
                    ログインする
                </Link>
            </div>

            <!-- Feature Preview (Mockup) -->
            <div
                class="mt-20 relative w-full max-w-4xl animate-fade-in-up delay-500"
            >
                <div
                    class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent z-20 h-full w-full pointer-events-none"
                ></div>
                <div
                    class="bg-white rounded-3xl shadow-2xl border border-gray-200/50 overflow-hidden transform rotate-1 hover:rotate-0 transition-transform duration-700"
                >
                    <img
                        src="https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2021&q=80"
                        alt="App Preview"
                        class="w-full h-auto opacity-90 hover:opacity-100 transition-opacity duration-500"
                    />

                    <!-- Floating UI Elements Mockup -->
                    <div
                        class="absolute top-8 left-8 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-lg border border-white/50 transform -rotate-2"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="w-5 h-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">
                                    Last Trip
                                </div>
                                <div class="text-sm font-bold text-gray-800">
                                    北海道 3泊4日
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute bottom-20 right-8 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-lg border border-white/50 transform rotate-2"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    class="w-5 h-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">
                                    Next Goal
                                </div>
                                <div class="text-sm font-bold text-gray-800">
                                    沖縄県に行きたい！
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 py-8 text-center text-gray-400 text-sm">
            &copy; 2025 Omoide Album. All rights reserved.
        </footer>
    </div>
</template>

<style scoped>
.animate-pulse-slow {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.animate-float {
    animation: float 10s ease-in-out infinite;
}
.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
}
.delay-100 {
    animation-delay: 0.1s;
}
.delay-200 {
    animation-delay: 0.2s;
}
.delay-300 {
    animation-delay: 0.3s;
}
.delay-500 {
    animation-delay: 0.5s;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.15;
    }
}
@keyframes float {
    0%,
    100% {
        transform: translateY(0) scale(1);
    }
    50% {
        transform: translateY(-20px) scale(1.05);
    }
}
@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
