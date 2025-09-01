<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { onMounted, onUpdated, reactive } from 'vue'

import Alert from '@/Components/Alert.vue'
import Breadcrumb from '@/Components/Breadcrumb.vue'
import Combobox from '@/Components/Combobox.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

import { PlusIcon } from '@heroicons/vue/24/solid'

import { ArrowPathIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { watch } from 'vue'

const props = defineProps({
    string_change: Object,
    navigation: Object,

    filter: Object,
    end_date: String,
    start_date: String,

    branches: Array,
    products: Array,
    managers: Array,
    waiters: Array,
    customers: Array,
    payment_methods: Array
})

const form = reactive(props.filter)
// const form = useForm(props.filter)

const breadcrumbs = [
    { name: props.string_change.order_s, href: route('order.index'), current: false },
    { name: 'List Page', href: '#', current: false }
]

const bill_statuses = [
    { id: 'Paid', name: 'Paid' },
    { id: 'Due', name: 'Due' }
]

onUpdated(() => {
    console.log('updated')
    loadAjaxData()
})

onMounted(() => {
    console.log('mounted')
    loadAjaxData()
})

window.showFullScreenImage = function (imageUrl) {
    window.open(imageUrl, '_blank', 'fullscreen=yes')
}

watch(
    () => form.customer_name,
    (name) => {
        const customer = props.customers.find((c) => c.name === name)
        form.customer_id = customer ? customer.id : null
    }
)

const loadAjaxData = () => {
    $('#ajax_table').DataTable({
        responsive: true,
        serverSide: true,
        processing: true,
        destroy: true,
        lengthMenu: [
            [10, 10, 25, 50, 100, 200],
            [10, 10, 25, 50, 100, 200]
        ],
        length: 10,
        dom: "<'flex flex-col sm:flex-row justify-between'lf><'block overflow-auto 'rt><'flex flex-col sm:flex-row justify-between items-center'ip>",
        ajax: {
            url: $('#ajax_table').data('url'),
            type: 'GET',
            data: form
        },
        order: [[1, 'desc']],
        rowCallback: function (row, data) {
            if (parseFloat(data.due_amount) > 0) {
                row.classList.add('text-red-600')
            } else {
                row.classList.remove('text-red-600')
            }
        },
        columns: [
            {
                data: 'SrNo',
                class: 'px-2 sm:px-4 py-1 sm:py-2 whitespace-wrap border-b border-gray-200 text-sm leading-5 sorting_1',
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1
                }
            },
            {
                class: 'px-2 sm:px-4 py-1 sm:py-2 whitespace-wrap border-b border-gray-200 text-sm leading-5',
                render: function (data, type, row, meta) {
                    return row.datetime_format + ' ' + row.branch_invoice
                }
            },
            { class: 'w-30 px-2 sm:px-4 py-1 sm:py-2 whitespace-wrap border-b border-gray-200 text-sm leading-5', data: 'customer_name' },
            { class: 'w-30 px-2 sm:px-4 py-1 sm:py-2 whitespace-wrap border-b border-gray-200 text-sm leading-5', data: 'mobile' },
            { class: 'w-10 px-2 sm:px-4 py-1 sm:py-2 whitespace-nowrap border-b border-gray-200 text-sm leading-5', data: 'discount_amount' },
            {
                class: 'w-10 px-2 sm:px-4 py-1 sm:py-2 whitespace-nowrap border-b border-gray-200 text-sm leading-5',
                data: 'image',
                render: function (data, type, row) {
                    return data ? `<img src="${data}" alt="Image" class="h-10 w-10" onclick="showFullScreenImage('${data}')">` : ''
                }
            },
            { class: 'w-10 px-2 sm:px-4 py-1 sm:py-2 whitespace-nowrap border-b border-gray-200 text-sm leading-5', data: 'commission_amount' },
            { class: 'px-2 sm:px-4 py-1 sm:py-2 whitespace-nowrap border-b border-gray-200 text-sm leading-5', data: 'total' },
            { class: 'px-2 sm:px-4 py-1 sm:py-2 whitespace-nowrap border-b border-gray-200 text-sm leading-5', data: 'due_amount' },
            { class: 'px-2 sm:px-4 py-1 sm:py-2 whitespace-wrap border-b border-gray-200 text-sm leading-5', data: 'action', sortable: false }
        ]
    })
}

const clearFilter = () => {
    for (const [key, value] of Object.entries(form)) {
        form[key] = ''
    }
}

const submit = () => {
    router.visit(route('order.index'), {
        data: form
    })
}

const destroy = (route, message = 'Are you sure you want to delete?') => {
    if (confirm(message)) {
        router.delete(route)
    }
}
</script>
<template>
    <Head title="Orders" />

    <AuthenticatedLayout>
        <div>
            <div class="bg-white shadow">
                <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
                    <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                        <div class="flex-1 min-w-0">
                            <Breadcrumb :breadcrumbs="breadcrumbs" />
                        </div>
                        <div class="mt-6 h-9 flex space-x-3 md:mt-0 md:ml-4">
                            <Link
                                :href="route('pos.create')"
                                class="inline-flex items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-offset-gray-100 focus:ring-primary-400">
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
                            <p class="max-w-2xl leading-10 text-gray-700 text-lg font-medium mb-4 sm:mb-0">
                                {{ string_change.order_s }}
                                <!-- <span v-if="start_date != end_date">({{ start_date }} - {{ end_date }})</span>
								<span v-else>({{ start_date }})</span> -->
                            </p>
                            <div class="flex-shrink-0 flex space-x-3"></div>
                        </div>

                        <Alert />

                        <form @submit.prevent="submit">
                            <dl class="px-5 py-5 mx-auto max-w-5xl">
                                <div class="py-2 sm:grid sm:grid-cols-8 sm:gap-4">
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Manager</label>
                                        <Combobox class="mt-1" v-model="filter.manager_id" :items="managers" />
                                    </dd>

                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Waiter</label>
                                        <Combobox class="mt-1" v-model="filter.waiter_id" :items="waiters" />
                                    </dd>

                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Customer</label>
                                        <input
                                            v-model="filter.customer_name"
                                            list="customer-list"
                                            placeholder="Select a customer"
                                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 bg-white" />
                                        <datalist id="customer-list">
                                            <option v-for="customer in customers" :key="customer.id" :value="customer.name"></option>
                                        </datalist>
                                    </dd>

                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                                        <Combobox class="mt-1" v-model="filter.payment_method_id" :items="payment_methods" />
                                    </dd>

                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Bill Status</label>
                                        <Combobox class="mt-1" v-model="filter.bill_status" :items="bill_statuses" />
                                    </dd>

                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <input
                                            v-model="filter.start_date"
                                            type="date"
                                            class="mt-1 block w-full px-4 focus:ring-indigo-400 focus:border-indigo-400 hover:bg-gray-100 focus:bg-transparent sm:text-sm border-gray-300 rounded" />
                                    </dd>

                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                                        <input
                                            v-model="filter.end_date"
                                            type="date"
                                            class="mt-1 block w-full px-4 focus:ring-indigo-400 focus:border-indigo-400 hover:bg-gray-100 focus:bg-transparent sm:text-sm border-gray-300 rounded" />
                                    </dd>

                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Action</label>
                                        <div class="flex mt-1 space-x-0 rounded" role="group">
                                            <button
                                                type="submit"
                                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-l shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                                                <MagnifyingGlassIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                                                Search
                                            </button>

                                            <button
                                                @click="clearFilter"
                                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-primary-600 rounded-r shadow-sm text-sm font-medium text-primary-700 bg-white hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600">
                                                <ArrowPathIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                                                Clear
                                            </button>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </form>

                        <table class="table-auto sm:table-fixed min-w-full w-full" id="ajax_table" :data-url="route('order.load')">
                            <thead>
                                <tr>
                                    <th class="w-5 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">S.N.</th>
                                    <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Date</th>
                                    <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Customer</th>
                                    <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Number</th>
                                    <!-- <th class="w-30 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Description</th> -->
                                    <th class="w-20 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Discount</th>
                                    <th class="w-20 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Image</th>

                                    <th class="w-10 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Commission</th>
                                    <!-- <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">VAT</th> -->
                                    <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Total</th>
                                    <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Due Amount</th>
                                    <th class="w-28 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
