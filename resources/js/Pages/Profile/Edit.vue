<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

const user = usePage().props.auth.user;

const form = useForm({
    _method: "PUT",
    name: user.name,
    email: user.email,
    password: "",
    password_confirmation: "",
    profile_photo: null,
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
