<script setup>
import { onMounted } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusOptions from '@/Components/StatusOptions.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import Alert from '@/Components/Alert.vue'
import Status from '@/Components/Status.vue'

import { PlusIcon } from '@heroicons/vue/24/solid'

import {
    ArrowRightOnRectangleIcon,
    ChatBubbleBottomCenterTextIcon,
    ArrowTopRightOnSquareIcon,
    PencilSquareIcon,
    TrashIcon,
    CogIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    auth: Object,
    users: Array,
    branches: Array
})

onMounted(() => {
    $('#table').DataTable({
        lengthMenu: [
            [10, 25, 50, 100, 200],
            [10, 25, 50, 100, 200]
        ],
        length: 10,
        dom: "<'flex justify-center sm:justify-end mb-3'B><'flex flex-col sm:flex-row justify-between'lf><'block overflow-auto'rt><'flex flex-col sm:flex-row justify-between'ip>",
        buttons: ['copy', 'excel']
    })
})

const destroy = (route, message = 'Are you sure you want to delete?') => {
    if (confirm(message)) {
        router.delete(route)
    }
}

const breadcrumbs = [
    { name: 'Users', href: route('user.index'), current: false },
    { name: 'List Page', href: '#', current: false }
]

const colorClasses = {
    super_admin: 'bg-red-100 text-red-700 rounded-full',
    waiter: 'bg-yellow-100 text-yellow-800 rounded-full',
    chef: 'bg-blue-100 text-blue-700 rounded-full',
    barista: 'bg-purple-100 text-purple-700 rounded-full',
    rider: 'bg-pink-100 text-pink-700 rounded-full',

    pending: 'bg-yellow-100 text-yellow-900 rounded-md',
    active: 'bg-green-100 text-green-900 rounded-md',
    inactive: 'bg-rose-100 text-rose-900 rounded-md'
}
</script>
<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <div class="bg-white shadow">
            <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
                <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                    <div class="flex-1 min-w-0">
                        <Breadcrumb :breadcrumbs="breadcrumbs" />
                    </div>
                    <div class="mt-6 h-9 flex space-x-3 md:mt-0 md:ml-4">
                        <Link
                            :href="route('user.create')"
                            class="inline-flex items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-primary-400">
                            <PlusIcon class="-ml-1 mr-2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            Create
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-5">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:justify-between items-center px-4 py-5 border-b border-gray-200 sm:px-8">
                        <p class="max-w-2xl leading-10 text-gray-700 text-lg font-medium mb-4 sm:mb-0">Users</p>

                        <div class="flex-shrink-0 flex space-x-3">
                            <Link
                                :href="route('user.digest')"
                                class="inline-flex items-center w-40 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-primary-400">
                                <CogIcon class="-ml-1 mr-2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                Email Digest
                            </Link>

                            <Link
                                :href="route('branch.access')"
                                class="inline-flex items-center w-40 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-primary-400">
                                <CogIcon class="-ml-1 mr-2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                Branch Users
                            </Link>

                            <!-- <Link :href="route('user.history')" class="inline-flex items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-primary-400">
                                <ChatBubbleBottomCenterTextIcon class="-ml-1 mr-2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                History
                            </Link> -->
                        </div>
                    </div>

                    <Alert />

                    <table class="table-auto sm:table-fixed min-w-full w-full" id="table">
                        <thead>
                            <tr>
                                <th class="w-12 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-center">S.N.</th>
                                <th class="w-1/3 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Name</th>
                                <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Role</th>
                                <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Branch</th>
                                <th class="w-24 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Status</th>
                                <th class="w-36 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(user, index) in users" :key="index" :class="[index % 2 == 0 ? 'bg-white' : 'bg-gray-50', 'border-b']">
                                <td class="p-2 text-center">{{ index + 1 }}</td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    <div class="flex items-center">
                                        <div class="shrink-0 h-10 w-10 mr-4">
                                            <img class="h-10 w-10 rounded-full" :src="user?.image_url ?? $page.props.app.avatar" alt="" />
                                        </div>
                                        <div>
                                            <div class="text-sm leading-5 font-semibold text-gray-700">{{ user.name }}</div>
                                            <div class="text-sm leading-5 text-gray-600">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    <Status class="ml-1" :name="user.role_name" :colors="colorClasses" />
                                    <!-- <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium">{{ user.role_name }}</span> -->
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    <span v-if="!user.is_admin" class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium">{{
                                        branches
                                            .filter((branch) => user.branch_access[branch.id])
                                            .map((branch) => branch.name)
                                            .join(', ')
                                    }}</span>
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    <Status class="ml-3" :name="user.status" :colors="colorClasses" />
                                    <!-- <StatusOptions class="ml-3" :model="user" :href="route('user.status.update', user.id)" /> -->
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    <div class="flex justify-end">
                                        <Link
                                            v-show="$page.props.auth.user.id != user.id && $page.props.auth.user.is_admin"
                                            :href="route('user.login', user.id)"
                                            class="text-yellow-600 hover:text-yellow-800 ml-3"
                                            title="login">
                                            <ArrowRightOnRectangleIcon class="w-6 h-6" aria-hidden="true" />
                                        </Link>
                                        <Link :href="route('user.show', user.id)" class="text-primary-600 hover:text-primary-800 ml-3" title="detail">
                                            <ArrowTopRightOnSquareIcon class="w-6 h-6" aria-hidden="true" />
                                        </Link>
                                        <Link :href="route('user.edit', user.id)" class="text-indigo-600 hover:text-indigo-800 ml-3" title="edit">
                                            <PencilSquareIcon class="w-6 h-6" aria-hidden="true" />
                                        </Link>
                                        <button @click="destroy(route('user.destroy', user.id))" class="text-red-600 hover:text-red-800 ml-3" title="delete">
                                            <TrashIcon class="w-6 h-6" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
