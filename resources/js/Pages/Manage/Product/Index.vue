<template>
    <Head title="Products" />

    <div>
        <div class="bg-white shadow">
            <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
                <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                    <div class="flex-1 min-w-0">
                        <Breadcrumb :breadcrumbs="breadcrumbs" />
                    </div>
                    <div class="mt-6 h-9 flex space-x-3 md:mt-0 md:ml-4">
                        <Link :href="route('product.create')" class="inline-flex items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-primary-400">
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
                        <p class="max-w-2xl leading-10 text-gray-700 text-lg font-medium mb-4 sm:mb-0">{{ string_change.product_s }}</p>

                        <div class="flex-shrink-0 flex space-x-3">
                            <Link :href="route('product_category.specification')" class="inline-flex items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white hover:bg-gray-50 text-sm font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-primary-400">
                                <CogIcon class="-ml-1 mr-2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                Specification
                            </Link>
                        </div>
                    </div>

                    <Alert />

                    <form @submit.prevent="submit">
                        <dl class="space-y-8 px-5 py-6">
                            <div class="max-w-5xl mx-auto space-y-4 sm:space-y-6">
                                <div class="grid grid-cols-4 gap-4">
                                    <div class="col-span-2 sm:col-span-1">
                                        <label class="mb-1 text-sm font-medium text-gray-700">Category</label>
                                        <Listbox class="mt-1" v-model="form.category_id" :items="categories" />
                                    </div>
								</div>
							</div>
						</dl>
					</form>

                    <table class="table-auto sm:table-fixed min-w-full w-full" id="table">
                        <thead>
                            <tr>
                                <th class="w-12 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-center">S.N.</th>
                                <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Name</th>
                                <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Category</th>
                                <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">{{ string_change.product }} Cost</th>
                                <th class="px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">Margin</th>
                                <th v-show="false" class="w-64 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-left">{{ string_change.item_s }}</th>
                                <th class="w-40 px-5 py-2 border-b bg-gray-100 text-xs font-bold uppercase tracking-wider text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr v-for="(product, index) in products" :key="product.id" :class="[index % 2 == 0 ? 'bg-white' : 'bg-gray-50', 'border-b']">
                                <td class="p-2 text-center"> {{ (index + 1) }} </td>

                                <td class="p-2 whitespace-wrap">
                                    <div class="flex items-center">
                                        <!-- <div class="shrink-0 h-10 w-10 mr-4">
                                            <img class="h-10 w-10 rounded-full" :src="product.image" alt="" />
                                        </div> -->
                                        <div>
                                            <div class="text-sm leading-5 font-semibold text-gray-700">{{ product.name }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    {{ product.category_name }}
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    &#2547; {{ product.rate }}
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    <div class="flex items-center">
                                        <div class="">
                                            <div class="text-sm leading-5 font-semibold text-gray-700">Amount: {{ product.margin_amount }}</div>
                                            <div class="text-sm leading-5 font-semibold text-gray-700">Percent: {{ product.margin_percent }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td v-show="false" class="p-2 whitespace-nowrap">
                                    <table class="table-auto min-w-full text-xs divide-y divide-gray-300">
                                        <tbody class="bg-transparent border divide-y divide-gray-300">
                                            <tr>
                                                <th class="px-2 py-1 border border-gray-300 w-5">#</th>
                                                <th class="px-2 py-1 border border-gray-300">{{ string_change.item }}</th>
                                                <th class="px-2 py-1 border border-gray-300 w-14">Qty</th>
                                            </tr>
                                            <tr v-for="(item, key) in product.group_items" :key="item.id">
                                                <td class="px-2 py-1 border border-gray-300 text-center">{{ (key + 1) }}</td>
                                                <td class="px-2 py-1 border border-gray-300 text-center">{{ item.item_name }}</td>
                                                <td class="px-2 py-1 border border-gray-300 text-center">{{ item.quantity_use + ' ' + item.unit_use }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>

                                <td class="px-3 py-1 whitespace-wrap break-words">
                                    <div class="flex justify-end">
                                        <Link :href="route('product.edit', product.id)" class="text-indigo-600 hover:text-indigo-800 ml-3" title="edit">
                                            <PencilSquareIcon class="w-6 h-6" aria-hidden="true" />
                                        </Link>
                                        <button @click="destroy(route('product.destroy', product.id))" class="text-red-600 hover:text-red-800 ml-3" title="delete">
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
    </div>
</template>

<script>
import { reactive } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusOptions from '@/Components/StatusOptions.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Alert from '@/Components/Alert.vue';
import ModelOptions from '@/Components/ModelOptions.vue';
import Listbox from '@/Components/Listbox.vue';

import Status from '@/Components/Status.vue';

import {
    PlusIcon,
    CogIcon,
} from '@heroicons/vue/24/solid';

import {
    ArrowTopRightOnSquareIcon,
    PencilSquareIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

export default {
    layout: AuthenticatedLayout,

    components: {
        Alert,
        Breadcrumb,
        Listbox,
        Head,
        Link,
        ModelOptions,
        Status,
        StatusOptions,

        ArrowTopRightOnSquareIcon,
        CogIcon,
        PencilSquareIcon,
        PlusIcon,
        TrashIcon,
    },

    props:{
        string_change: Object,

        products: Array,
        categories: Array,
        filter: Object,
    },

    mounted() {
        $('#table').DataTable({
            lengthProduct: [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
            length: 10,
            dom: "<'flex justify-center sm:justify-end mb-3'B><'flex flex-col sm:flex-row justify-between'lf><'block overflow-auto'rt><'flex flex-col sm:flex-row justify-between'ip>",
            buttons: [
                'copy', 'excel'
            ],
        });
    },

    methods: {
        destroy(route, message = "Are you sure you want to delete?") {
            if(confirm(message)) { router.delete(route); }
        },
    },

    watch: {
        'form.category_id'(){
            this.submit();
        }
    },

    setup(props) {
        const breadcrumbs = [
            { name: props.string_change.product_s, href: route('product.index'), current: false },
            { name: 'List Page', href: '#', current: false },
        ];

        const form = reactive({
            end_date: props.filter.end_date,
            start_date: props.filter.start_date,
            category_id: props.filter.category_id,
        })

        function submit() {
            router.visit(route('product.index'), {
  				data: form,
			});
        }

        return {
            breadcrumbs,
            form,
            submit,
        }
    }
}
</script>
