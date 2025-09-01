<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { PencilSquareIcon } from '@heroicons/vue/24/outline'
import Alert from '@/Components/Alert.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'

const props = defineProps({
    settings: Object,
    admin_settings: Object
})

const breadcrumbs = [{ name: 'Setting', href: route('setting.edit'), current: false }]

const form = useForm({
    settings: props.settings,
    admin_settings: props.admin_settings
})

function submit() {
    form.patch(route('setting.update'), {
        onFinish: () => {}
    })
}
</script>
<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <div class="bg-white shadow">
            <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
                <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                    <div class="flex-1 min-w-0">
                        <Breadcrumb :breadcrumbs="breadcrumbs" />
                    </div>
                    <div class="mt-6 h-9 flex space-x-3 md:mt-0 md:ml-4"></div>
                </div>
            </div>
        </div>

        <div class="py-5">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:justify-between items-center px-4 py-5 border-b border-gray-200 sm:px-8">
                        <p class="max-w-2xl leading-10 text-gray-700 text-lg font-medium mb-4 sm:mb-0">Settings</p>
                    </div>

                    <Alert />

                    <form @submit.prevent="submit">
                        <dl class="space-y-8 px-5 py-6">
                            <div class="max-w-xl mx-auto">
                                <div class="grid grid-cols-4 gap-4">
                                    <div v-for="item in form.settings" :key="item.id" class="col-span-4 sm:col-span-4">
                                        <label :for="item.name" class="block text-sm font-medium text-gray-700">
                                            {{ item.title }}
                                            <span v-show="['rename', 'hidden', 'time'].includes(item.type)" class="uppercase font-bold">({{ item.type }})</span>
                                        </label>

                                        <div v-if="item.type == 'text'" class="mt-1 flex rounded-md shadow-sm">
                                            <input
                                                type="text"
                                                :id="item.name"
                                                :placeholder="item.title"
                                                v-model="item.value"
                                                class="focus:ring-indigo-400 focus:border-indigo-400 hover:bg-gray-100 focus:bg-transparent block w-full px-4 sm:text-sm border-gray-300 rounded" />
                                        </div>
                                        <div v-if="item.type == 'textarea'" class="mt-1 flex rounded-md shadow-sm">
                                            <textarea
                                                :id="item.name"
                                                :placeholder="item.title"
                                                v-model="item.value"
                                                rows="4"
                                                class="focus:ring-indigo-400 focus:border-indigo-400 hover:bg-gray-100 focus:bg-transparent block w-full px-4 sm:text-sm border-gray-300 rounded"></textarea>
                                        </div>
                                        <div v-if="item.type == 'checkbox'" class="mt-1 flex">
                                            <input
                                                type="checkbox"
                                                :id="item.name"
                                                :placeholder="item.title"
                                                v-model="item.value"
                                                class="mt-1 group-checkbox form-checkbox w-5 h-5 focus:ring-primary-600 focus:border-primary-600 text-primary-600 transition duration-150 ease-in-out rounded select-none cursor-pointer" />
                                        </div>
                                        <div v-if="item.type == 'time'" class="mt-1 flex">
                                            <input
                                                type="time"
                                                :id="item.name"
                                                :placeholder="item.title"
                                                v-model="item.value"
                                                class="focus:ring-indigo-400 focus:border-indigo-400 hover:bg-gray-100 focus:bg-transparent block w-full px-4 sm:text-sm border-gray-300 rounded" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="admin_settings.length > 0">
                                <div class="relative my-8">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-gray-300" /></div>
                                    <div class="relative flex justify-center">
                                        <span class="px-3 bg-white text-lg font-medium text-gray-900"> Admin Settings </span>
                                    </div>
                                </div>

                                <div class="max-w-xl mx-auto">
                                    <div class="grid grid-cols-4 gap-4">
                                        <div v-for="item in form.admin_settings" :key="item.id" class="col-span-4 sm:col-span-4">
                                            <label :for="item.name" class="block text-sm font-medium text-gray-700">
                                                {{ item.title }}
                                                <span v-show="['rename', 'hidden'].includes(item.type)" class="uppercase font-bold">({{ item.type }})</span>
                                            </label>

                                            <div v-if="item.type == 'rename'" class="mt-1 flex rounded-md shadow-sm">
                                                <input
                                                    type="text"
                                                    :id="item.name"
                                                    :placeholder="item.title"
                                                    v-model="item.value"
                                                    class="focus:ring-indigo-400 focus:border-indigo-400 hover:bg-gray-100 focus:bg-transparent block w-full px-4 sm:text-sm border-gray-300 rounded" />
                                            </div>
                                            <div v-if="item.type == 'hidden'" class="mt-1 flex">
                                                <input
                                                    type="text"
                                                    :id="item.name"
                                                    :placeholder="item.title"
                                                    v-model="item.value"
                                                    class="focus:ring-indigo-400 focus:border-indigo-400 hover:bg-gray-100 focus:bg-transparent block w-full px-4 sm:text-sm border-gray-300 rounded" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="relative">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-gray-300" /></div>
                                <div class="relative flex justify-center ml-4"><span class="px-3 bg-white text-lg font-medium text-gray-900"></span></div>
                            </div>

                            <div class="max-w-xl mx-auto">
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        <PencilSquareIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                                        Update
                                    </button>
                                </div>
                            </div>
                        </dl>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
