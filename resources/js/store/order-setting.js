import { defineStore } from "pinia";
import { useSessionStorage } from "@vueuse/core";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

export const useOrderSettingStore = defineStore("order-setting", {
    state: () => ({
		refreshDuration: 30 * 60,
		executionTime: useSessionStorage(appName + "/order/executionTime", null),
		categories: useSessionStorage(appName + "/order/categories", []),
		customers: useSessionStorage(appName + "/order/customers", []),
		dineTypes: useSessionStorage(appName + "/order/dineTypes", []),
		discountTypes: useSessionStorage(appName + "/order/discountTypes", []),
		employees: useSessionStorage(appName + "/order/employees", []),
		paymentMethods: useSessionStorage(appName + "/order/paymentMethods", []),
		products: useSessionStorage(appName + "/order/products", []),
		riders: useSessionStorage(appName + "/order/riders", []),
		statuses: useSessionStorage(appName + "/order/statuses", []),
		topProducts: useSessionStorage(appName + "/order/topProducts", []),
    }),
	getters: {
		isExpired: (state) => {
			let currentTime = new Date().getTime();
			let executionTime = state.executionTime ?? 0;

			let diffSeconds = (currentTime - executionTime) / 1000;

			return diffSeconds >  state.refreshDuration;
		},
	},
    actions: {
        set(record) {
			this.executionTime = new Date().getTime();
			this.categories = record.categories;
			this.customers = record.customers;
			this.dineTypes = record.dineTypes;
			this.discountTypes = record.discountTypes;
			this.employees = record.employees;
			this.paymentMethods = record.paymentMethods;
			this.products = record.products;
			this.riders = record.riders;
			this.statuses = record.statuses;
			this.topProducts = record.topProducts;		
        },

		async load() {
			await axios.get('/api/basic')
				.then((response) => {
					this.set(response.data);
				}).catch((error) => {
					console.log(error);
				})
		}
    },
});
