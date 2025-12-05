<script setup>
import { ref } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);
const user = usePage().props.auth.user;

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link
                                :href="route('trips.index')"
                                class="text-xl font-bold text-gray-800 flex items-center gap-2"
                            >
                                <i class="bi bi-journal-album"></i>
                                夫婦思い出アルバム
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div
                            class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"
                        >
                            <Link
                                :href="route('trips.index')"
                                :active="route().current('trips.index')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                            >
                                思い出アルバム
                            </Link>
                            <Link
                                :href="route('tags.index')"
                                :active="route().current('tags.index')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                            >
                                タグ管理
                            </Link>
                            <Link
                                :href="route('suggestions.index')"
                                :active="route().current('suggestions.index')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                            >
                                AI旅行プランナー
                            </Link>
                            <Link
                                :href="route('map.index')"
                                :active="route().current('map.index')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                            >
                                制覇マップ
                            </Link>
                            <Link
                                :href="route('stats.index')"
                                :active="route().current('stats.index')"
                                class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                            >
                                旅行統計
                            </Link>
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative">
                            <div v-if="user" class="relative group">
                                <button
                                    type="button"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                >
                                    {{ user.name }}
                                    <svg
                                        class="ml-2 -mr-0.5 h-4 w-4"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <div
                                    class="absolute right-0 w-48 bg-white rounded-md shadow-lg py-1 hidden group-hover:block z-50 ring-1 ring-black ring-opacity-5"
                                >
                                    <Link
                                        :href="route('profile.edit')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        マイページ編集
                                    </Link>
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        ログアウト
                                    </Link>
                                </div>
                            </div>
                            <div v-else>
                                <Link
                                    :href="route('login')"
                                    class="text-sm text-gray-700 underline"
                                    >Login</Link
                                >
                                <Link
                                    :href="route('register')"
                                    class="ml-4 text-sm text-gray-700 underline"
                                    >Register</Link
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button
                            @click="
                                showingNavigationDropdown =
                                    !showingNavigationDropdown
                            "
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    :class="{
                                        hidden: showingNavigationDropdown,
                                        'inline-flex':
                                            !showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{
                                        hidden: !showingNavigationDropdown,
                                        'inline-flex':
                                            showingNavigationDropdown,
                                    }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="sm:hidden"
            >
                <div class="pt-2 pb-3 space-y-1">
                    <Link
                        :href="route('trips.index')"
                        :active="route().current('trips.index')"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
                    >
                        思い出アルバム
                    </Link>
                    <Link
                        :href="route('tags.index')"
                        :active="route().current('tags.index')"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
                    >
                        タグ管理
                    </Link>
                    <Link
                        :href="route('suggestions.index')"
                        :active="route().current('suggestions.index')"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
                    >
                        AI旅行プランナー
                    </Link>
                    <Link
                        :href="route('map.index')"
                        :active="route().current('map.index')"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
                    >
                        制覇マップ
                    </Link>
                    <Link
                        :href="route('stats.index')"
                        :active="route().current('stats.index')"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
                    >
                        旅行統計
                    </Link>
                </div>

                <!-- Responsive Settings Options -->
                <div v-if="user" class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">
                            {{ user.name }}
                        </div>
                        <div class="font-medium text-sm text-gray-500">
                            {{ user.email }}
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <Link
                            :href="route('profile.edit')"
                            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
                        >
                            マイページ編集
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out"
                        >
                            ログアウト
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
