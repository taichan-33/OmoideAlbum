<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    badges: Array, // バッジ情報を受け取る
});

const user = usePage().props.auth.user;

const form = useForm({
    _method: "PUT",
    name: user.name,
    email: user.email,
    password: "",
    password_confirmation: "",
    profile_photo: null,
    show_bot_status:
        user.show_bot_status !== undefined
            ? Boolean(user.show_bot_status)
            : true,
});

const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfilePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
    form.profile_photo = photo;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const submit = () => {
    form.post(route("profile.update"), {
        errorBag: "updateProfileInformation",
        preserveScroll: true,
        onSuccess: () =>
            form.reset("password", "password_confirmation", "profile_photo"),
    });
};
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                プロフィール編集
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Success Message -->
                        <div
                            v-if="$page.props.flash.success"
                            class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                            role="alert"
                        >
                            <span class="block sm:inline">{{
                                $page.props.flash.success
                            }}</span>
                        </div>

                        <!-- Badges Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                獲得した称号
                            </h3>
                            <div
                                v-if="badges.length === 0"
                                class="text-gray-500 text-sm"
                            >
                                まだ称号を獲得していません。たくさん旅行して集めましょう！
                            </div>
                            <div
                                v-else
                                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"
                            >
                                <div
                                    v-for="badge in badges"
                                    :key="badge.id"
                                    class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg border border-yellow-200 text-center"
                                >
                                    <div class="text-4xl mb-2">
                                        {{ badge.icon_path }}
                                    </div>
                                    <div
                                        class="font-bold text-gray-800 text-sm"
                                    >
                                        {{ badge.name }}
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1">
                                        {{ badge.description }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-2">
                                        {{
                                            new Date(
                                                badge.pivot.obtained_at
                                            ).toLocaleDateString()
                                        }}
                                        獲得
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Profile Photo -->
                            <div class="mb-6">
                                <label
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                >
                                    プロフィール画像
                                </label>

                                <!-- Current Profile Photo -->
                                <div v-show="!photoPreview" class="mt-2">
                                    <img
                                        :src="user.profile_photo_url"
                                        :alt="user.name"
                                        class="rounded-full h-20 w-20 object-cover"
                                    />
                                </div>

                                <!-- New Profile Photo Preview -->
                                <div v-show="photoPreview" class="mt-2">
                                    <span
                                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                                        :style="
                                            'background-image: url(\'' +
                                            photoPreview +
                                            '\');'
                                        "
                                    />
                                </div>

                                <button
                                    class="mt-2 mr-2 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    type="button"
                                    @click.prevent="selectNewPhoto"
                                >
                                    新しい画像を選択
                                </button>

                                <input
                                    ref="photoInput"
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="updateProfilePhotoPreview"
                                />

                                <div
                                    v-if="form.errors.profile_photo"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.profile_photo }}
                                </div>
                            </div>

                            <!-- Name -->
                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                    for="name"
                                >
                                    名前
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required
                                />
                                <div
                                    v-if="form.errors.name"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                    for="email"
                                >
                                    メールアドレス
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required
                                />
                                <div
                                    v-if="form.errors.email"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                    for="password"
                                >
                                    新しいパスワード (変更する場合のみ)
                                </label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                />
                                <div
                                    v-if="form.errors.password"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.password }}
                                </div>
                            </div>

                            <!-- Password Confirmation -->
                            <div class="mb-6">
                                <label
                                    class="block text-gray-700 text-sm font-bold mb-2"
                                    for="password_confirmation"
                                >
                                    パスワード確認
                                </label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                />
                            </div>

                            <!-- Bot Status Toggle -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.show_bot_status"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">
                                        タイムラインに「クイックン」の気分を表示する
                                    </span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end">
                                <button
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    保存
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
