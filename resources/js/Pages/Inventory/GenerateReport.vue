<template>
	<Head title="Generate Report" />

	<div class="px-4 sm:px-6 lg:px-8">
		<!-- Header -->
		<div class="sm:flex sm:items-center">
			<div class="sm:flex-auto">
				<h1 class="text-3xl font-bold text-gray-900">
					Monthly Inventory Report - {{ campus }}
				</h1>
				<p class="mt-2 text-sm text-gray-700">
					Report Period: {{ reportMonth }} | Generated: {{ reportDate }}
				</p>
				<div v-if="existingReport" class="mt-2">
					<span
						class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
					>
						Report already exists for this month - will update existing report
					</span>
				</div>
			</div>
		</div>

		<!-- Main Content -->
		<div class="mt-8">
			<!-- Statistics Overview -->
			<div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
				<h3 class="text-lg font-medium text-gray-900 mb-4">Inventory Overview</h3>
				<div class="grid grid-cols-2 gap-6 mb-6">
					<!-- Medicines Statistics -->
					<div class="bg-blue-50 p-4 rounded-lg">
						<h5 class="text-sm font-medium text-blue-800 mb-3">Medicines</h5>
						<div class="grid grid-cols-2 gap-2">
							<div>
								<div class="text-lg font-bold text-blue-600">{{ medicinesCount }}</div>
								<div class="text-xs text-blue-600">Total Types</div>
							</div>
							<div>
								<div class="text-lg font-bold text-red-600">{{ medicinesLowStock }}</div>
								<div class="text-xs text-red-600">Low Stock</div>
							</div>
						</div>
					</div>

					<!-- Supplies Statistics -->
					<div class="bg-green-50 p-4 rounded-lg">
						<h5 class="text-sm font-medium text-green-800 mb-3">Supplies</h5>
						<div class="grid grid-cols-2 gap-2">
							<div>
								<div class="text-lg font-bold text-green-600">{{ suppliesCount }}</div>
								<div class="text-xs text-green-600">Total Types</div>
							</div>
							<div>
								<div class="text-lg font-bold text-red-600">{{ suppliesLowStock }}</div>
								<div class="text-xs text-red-600">Low Stock</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Inventory Report Form -->
			<form
				@submit.prevent="submitReport"
				class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
			>
				<h3 class="text-lg font-medium text-gray-900 mb-6">
					Monthly Inventory Report & Restock Orders
				</h3>

				<!-- Medicines Section -->
				<div class="mb-8">
					<h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
						<svg
							class="w-5 h-5 mr-2 text-blue-500"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.415-3.414l5-5A2 2 0 009 9.586V5L8 4z"
							></path>
						</svg>
						Medicines Inventory
					</h4>
					<div class="overflow-x-auto">
						<table class="min-w-full divide-y divide-gray-200">
							<thead class="bg-gray-50">
								<tr>
									<th
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Medicine Name
									</th>
									<th
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Current Stock
									</th>
									<th
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Quantity to Order
									</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr
									v-for="(item, index) in medicinesData"
									:key="`med-${index}`"
									:class="{
										'bg-red-50': item.is_low_stock,
										'bg-yellow-50': item.is_expiring_soon && !item.is_low_stock,
									}"
								>
									<td
										class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
									>
										{{ item.medicine_name }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ item.total_quantity }}
										<span class="text-xs text-gray-500"
											>({{ item.batch_count }} batches)</span
										>
									</td>
									<td class="px-6 py-4 whitespace-nowrap">
										<input
											v-model.number="orderRequests[item.originalIndex].quantity_to_order"
											type="number"
											min="0"
											class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
											placeholder="0"
										/>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Supplies Section -->
				<div class="mb-8">
					<h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
						<svg
							class="w-5 h-5 mr-2 text-green-500"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M9 9l3-3 3 3"
							></path>
						</svg>
						Supplies Inventory
					</h4>
					<div class="overflow-x-auto">
						<table class="min-w-full divide-y divide-gray-200">
							<thead class="bg-gray-50">
								<tr>
									<th
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Item Name
									</th>
									<th
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Stock
									</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr
									v-for="(item, index) in suppliesData"
									:key="`supply-${index}`"
									:class="{
										'bg-red-50': item.is_low_stock,
									}"
								>
									<td
										class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
									>
										{{ item.medicine_name }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ item.total_quantity }}
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<!-- Form Actions -->
				<div class="flex items-center justify-between pt-6 border-t border-gray-200">
					<Link
						:href="route('inventory.index')"
						class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
					>
						Back to Inventory
					</Link>

					<div class="space-x-3">
						<button
							type="button"
							@click="saveDraft"
							class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
						>
							{{ existingReport ? "Update Draft" : "Save as Draft" }}
						</button>
						<button
							type="submit"
							class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
						>
							{{ existingReport ? "Update Report" : "Submit Monthly Report" }}
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router, Head } from "@inertiajs/vue3";

const props = defineProps({
	inventoryData: Array,
	campus: String,
	reportDate: String,
	existingReport: Object,
	reportMonth: String,
});

// Initialize order requests for each inventory item
const orderRequests = ref([]);

// Separate medicines and supplies with original indices
const medicinesData = computed(() => {
	if (!props.inventoryData) return [];
	return props.inventoryData
		.map((item, index) => ({ ...item, originalIndex: index }))
		.filter((item) => !item.is_supply);
});

const suppliesData = computed(() => {
	if (!props.inventoryData) return [];
	return props.inventoryData
		.map((item, index) => ({ ...item, originalIndex: index }))
		.filter((item) => item.is_supply);
});

// Initialize order requests immediately
if (props.inventoryData) {
	orderRequests.value = props.inventoryData.map((item) => ({
		medicine_name: item.medicine_name,
		quantity_to_order: 0, // Start with 0, let user fill as needed
	}));
}

// Computed properties for statistics
const medicinesCount = computed(() => medicinesData.value.length);

const suppliesCount = computed(() => suppliesData.value.length);

const medicinesLowStock = computed(
	() => medicinesData.value.filter((item) => item.is_low_stock).length
);

const suppliesLowStock = computed(
	() => suppliesData.value.filter((item) => item.is_low_stock).length
);

// Save as draft
const saveDraft = () => {
	const data = {
		inventory_data: props.inventoryData,
		order_requests: orderRequests.value,
		status: "draft",
	};

	console.log("Attempting to save draft with data:", data);
	console.log("Order requests:", orderRequests.value);
	console.log("Route URL:", route("inventory.save-report"));

	router.post(route("inventory.save-report"), data, {
		onSuccess: (page) => {
			console.log("Draft saved successfully:", page);
			alert("Draft saved successfully!");
			// The redirect from the controller will handle navigation
		},
		onError: (errors) => {
			console.error("Error saving draft:", errors);
			alert("Error saving draft: " + JSON.stringify(errors));
		},
		onStart: () => {
			console.log("Started sending draft request...");
		},
		onFinish: () => {
			console.log("Finished draft request...");
		},
	});
};

// Submit final report
const submitReport = () => {
	const data = {
		inventory_data: props.inventoryData,
		order_requests: orderRequests.value,
		status: "submitted",
	};

	console.log("Attempting to submit report with data:", data);
	console.log("Order requests:", orderRequests.value);
	console.log("Route URL:", route("inventory.save-report"));

	router.post(route("inventory.save-report"), data, {
		onSuccess: (page) => {
			console.log("Report submitted successfully:", page);
			alert("Report submitted successfully!");
			// The redirect from the controller will handle navigation
		},
		onError: (errors) => {
			console.error("Error submitting report:", errors);
			alert("Error submitting report: " + JSON.stringify(errors));
		},
		onStart: () => {
			console.log("Started sending submit request...");
		},
		onFinish: () => {
			console.log("Finished submit request...");
		},
	});
};
</script>
