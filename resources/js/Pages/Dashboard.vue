<script setup>
import { onMounted, ref, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Alert from '@/Components/Alert.vue'
import { ChartLine, ChartColumn } from '@/charts'

import { BeakerIcon, BriefcaseIcon, BanknotesIcon, ScaleIcon, SparklesIcon } from '@heroicons/vue/24/outline'

import { CheckCircleIcon, ChevronDownIcon, ChevronRightIcon, BuildingOfficeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
    app: Object,
    auth: Object,
    greeting: String,

    errors: Object,
    

    chart_data: Object,
    cards: Array,
    branch: Object,
    address: String,
    sale_branches: Array
})

onMounted(() => {
    console.log('mounted')
    loadChart()
})

watch(
    () => props.branch,
    () => {
        console.log('on branch change')
        loadChart()
    }
)

const loadChart = () => {
    if (props.chart_data.hourly.length > 0) {
        ChartLine('chart_line', props.chart_data.hourly, 'Hourly', 'Sales')
    }

    if (props.chart_data.daily.length > 0) {
        ChartColumn('chart_column', props.chart_data.daily, 'Daily', 'Sales')
    }

    if (props.chart_data.monthly.length > 0) {
        ChartColumn('chart_column_monthly', props.chart_data.monthly, 'Monthly', 'Sales')
    }

    if (props.chart_data.branches.length > 0) {
        ChartPie('chart_pie', props.data.branches)
    }
}
const sidebarOpen = ref(false)

const statusStyles = {
    success: 'bg-green-100 text-green-800',
    processing: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-gray-100 text-gray-800'
}
</script>
<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="bg-white shadow">
            <div class="px-4 sm:px-6 lg:max-w-6xl lg:mx-auto lg:px-8">
                <div class="py-6 md:flex md:items-center md:justify-between lg:border-t lg:border-gray-200">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center">
                            <img class="hidden h-16 w-16 rounded-full sm:block" :src="auth.user.image_url ?? $page.props.app.avatar" alt="" />

                            <div>
                                <div class="flex items-center">
                                    <img class="h-16 w-16 rounded-full sm:hidden" :src="auth.user.image_url ?? $page.props.app.avatar" alt="" />
                                    <h1 class="ml-3 text-2xl font-bold leading-7 text-gray-900 sm:leading-9 sm:truncate">
                                        , {{ auth.user.name }}
                                    </h1>
                                </div>

                                <dl class="mt-6 flex flex-col sm:ml-3 sm:mt-1 sm:flex-row sm:flex-wrap">
                                    <dt class="sr-only">Company</dt>
                                    <dd class="flex items-start text-sm text-gray-500 font-medium capitalize sm:mr-6">
                                        <BuildingOfficeIcon class="shrink-0 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
                                        {{ branch ? branch.name + ' - ' + (branch?.address ?? '') : 'Showing records from all branch' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <Alert />
            </div>
        </div>

        <div class="mt-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-lg leading-6 font-medium text-gray-900">Overview</h2>
                <div class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Card -->
                    <div v-if="sale_branches.length > 1" class="row-span-3 bg-white overflow-hidden shadow rounded-lg">
                        <div class="flex items-center gap-x-4 border-b border-gray-900/5 bg-gray-50 p-6">
                            <div class="text-sm font-medium text-gray-600 truncate">Currently per head sales</div>
                        </div>
                        <dl class="-my-3 divide-y divide-gray-100 px-6 py-4 text-sm leading-6">
                            <div v-for="branch in sale_branches" :key="branch.id" class="flex justify-between gap-x-4 py-3">
                                <dt class="text-gray-500">{{ branch.name }}</dt>
                                <dd class="flex items-start gap-x-2">
                                    <div class="font-medium text-gray-900">{{ branch.per_head_cost }}</div>
                                    <div class="text-primary-900 bg-primary-50 rounded-md py-1 px-2 text-xs font-medium">{{ branch.total_guest }}</div>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div v-for="card in cards" :key="card.name" class="bg-white overflow-hidden shadow rounded-lg d-flex flex-column">
                        <div class="p-5 flex-grow" >
                            <div class="flex items-center">
                                <div class="shrink-0">
                                    <component :is="card.icon" class="h-6 w-6 text-gray-400" aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">{{ card.name }}</dt>
                                        <dd>
                                            <div class="text-lg font-medium text-gray-900">
                                                {{ card.amount }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>

                            </div>


                            <div v-if="card.extra_info" class="flex items-center" style="margin-top: 20px;">
                                <div class="shrink-0">
                                    <component :is="card.icon" class="h-6 w-6 text-gray-400" aria-hidden="true" />
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-small text-gray-500 truncate" style="font-size: 12px;"> {{ card.extra_info.name }}  <strong class="text-lg font-medium text-gray-900" style="font-size: 12px;">{{ card.extra_info.amount }}</strong></dt>
                                        <!-- <dd>
                                            <div class="text-lg font-medium text-gray-900">
                                                {{ card.extra_info.amount }}
                                            </div>
                                        </dd> -->
                                    </dl>
                                </div>
                            </div>
                        </div>
       <!-- "View all" link and count record -->
    <div class="bg-gray-50 px-5 py-3 mt-auto">
        <div class="text-sm flex justify-between">
            <Link :href="card.href" class="font-medium text-primary-700 hover:text-primary-900"> View all </Link>
            <span class="font-medium text-primary-700 hover:text-primary-900">{{ card.count_record ?? '' }}</span>
        </div>
    </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-lg leading-6 font-medium text-gray-900">Charts</h2>
                <div class="mt-2 grid grid-cols-1 gap-5 2xl:grid-cols-2">
                    <div
                        v-show="chart_data.branches.length > 0"
                        id="chart_pie"
                        style="height: 450px"
                        class="w-full bg-white overflow-hidden shadow rounded-lg"></div>
                    <div
                        v-show="chart_data.hourly.length > 0"
                        id="chart_line"
                        style="height: 450px"
                        class="w-full bg-white overflow-hidden shadow rounded-lg 2xl:col-span-2"></div>
                    <div
                        v-show="chart_data.daily.length > 0"
                        id="chart_column"
                        style="height: 450px"
                        class="w-full bg-white overflow-hidden shadow rounded-lg"></div>
                    <div
                        v-show="chart_data.monthly.length > 0"
                        id="chart_column_monthly"
                        style="height: 450px"
                        class="w-full bg-white overflow-hidden shadow rounded-lg"></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
