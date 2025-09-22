<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";

const props = defineProps({
	report: Object,
	canEdit: Boolean,
});

// Normalize inventory data to handle both old and new data structures
const normalizedInventoryData = computed(() => {
	if (!props.report.inventory_data) return [];

	return props.report.inventory_data.map((item) => {
		// Check if it's the new format (has current_stock but no quantity_to_order in inventory_data)
		if (
			item.hasOwnProperty("current_stock") &&
			!item.hasOwnProperty("quantity_to_order")
		) {
			// New format: combine inventory_data with quantity_to_order from separate field
			const medicineName = item.medicine_name;
			const quantityToOrder = props.report.quantity_to_order?.[medicineName] || 0;

			return {
				medicine_name: medicineName,
				current_stock: item.current_stock,
				quantity_to_order: quantityToOrder,
				category: item.category || "Medicine",
				type: item.type || "Unknown",
			};
		}

		// Check if it's the old format with quantity_to_order embedded
		if (
			item.hasOwnProperty("current_stock") &&
			item.hasOwnProperty("quantity_to_order")
		) {
			return {
				...item,
				category: item.category || "Medicine",
				type: item.type || "Unknown",
			};
		}

		// Handle very old format (has total_quantity instead of current_stock)
		if (item.hasOwnProperty("total_quantity")) {
			const medicineName = item.medicine_name;
			const quantityToOrder = props.report.quantity_to_order?.[medicineName] || 0;

			return {
				medicine_name: medicineName,
				current_stock: item.total_quantity,
				quantity_to_order: quantityToOrder,
				category: item.category || "Medicine",
				type: item.type || "Unknown",
			};
		}

		// Fallback for unknown formats
		return {
			medicine_name: item.medicine_name,
			current_stock: 0,
			quantity_to_order: 0,
			category: "Unknown",
		};
	});
});

// Filter supplies from the inventory data
const suppliesData = computed(() => {
	if (!normalizedInventoryData.value) return [];

	return normalizedInventoryData.value.filter((item) => {
		const category = item.category || "";
		return category === "Supply";
	});
});

// Filter medicines only (exclude supplies) from the inventory data
const medicinesData = computed(() => {
	if (!normalizedInventoryData.value) return [];

	return normalizedInventoryData.value.filter((item) => {
		const category = item.category || "";
		return category === "Medicine";
	});
});

// Form for updating inventory data (medicines only)
const inventoryForm = useForm({
	inventory_data: [...(medicinesData.value || [])],
});

// Form for updating supplies data separately
const suppliesForm = useForm({
	supplies_data: [...(suppliesData.value || [])],
});

// Form for submitting the report (all data)
const submitForm = useForm({
	inventory_data: [...(normalizedInventoryData.value || [])],
});

// Watch for changes in props.report and update forms accordingly
watch(
	() => props.report.inventory_data,
	(newData) => {
		const medicines = medicinesData.value;
		const supplies = suppliesData.value;
		const allData = normalizedInventoryData.value;
		if (medicines && supplies && allData) {
			inventoryForm.inventory_data = [...medicines];
			suppliesForm.supplies_data = [...supplies];
			submitForm.inventory_data = [...allData];
		}
	},
	{ deep: true }
);

const updateInventory = () => {
	// Create a combined form with both medicines and supplies data
	const allInventoryData = [
		...inventoryForm.inventory_data, // medicines with updated quantities
		...suppliesForm.supplies_data.map((supply) => ({
			medicine_name: supply.medicine_name,
			current_stock: supply.current_stock,
			quantity_to_order: 0, // supplies don't have quantity to order
			category: supply.category || "Supply",
		})),
	];

	const completeUpdateForm = useForm({
		inventory_data: allInventoryData,
	});

	completeUpdateForm.put(route("monthly-reports.update-order-requests", props.report.id));
};

const submitReport = () => {
	// Debug: Log what we're sending
	console.log("Submitting report - first saving data:", {
		report_id: props.report.id,
		inventory_data_count: inventoryForm.inventory_data.length,
		supplies_data_count: suppliesForm.supplies_data.length,
		sample_medicine: inventoryForm.inventory_data[0],
		sample_supply: suppliesForm.supplies_data[0],
	});

	// Create a combined form with all data (medicines and supplies)
	const allInventoryData = [
		...inventoryForm.inventory_data, // medicines with updated quantities
		...suppliesForm.supplies_data.map((supply) => ({
			medicine_name: supply.medicine_name,
			current_stock: supply.current_stock,
			quantity_to_order: 0, // supplies don't have quantity to order
			category: supply.category || "Supply",
		})),
	];

	const completeForm = useForm({
		inventory_data: allInventoryData,
	});

	// First save all data (medicines + supplies) using the complete form, then submit
	completeForm.put(route("monthly-reports.update-order-requests", props.report.id), {
		onSuccess: () => {
			// After successfully saving data, submit the report
			console.log("Data saved successfully, now submitting report");
			submitForm.post(route("monthly-reports.submit", props.report.id));
		},
		onError: (errors) => {
			console.error("Error saving data before submission:", errors);
		},
	});
};

const monthNames = [
	"January",
	"February",
	"March",
	"April",
	"May",
	"June",
	"July",
	"August",
	"September",
	"October",
	"November",
	"December",
];
</script>

<template>
	<Head
		:title="`Monthly Report - ${monthNames[report.report_month - 1]} ${
			report.report_year
		}`"
	/>

	<div class="px-4 sm:px-6 lg:px-8">
		<div class="sm:flex sm:items-center">
			<div class="sm:flex-auto">
				<div class="mb-4">
					<Link
						:href="route('monthly-reports.index')"
						class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500"
					>
						<svg
							class="-ml-1 mr-1 h-5 w-5"
							xmlns="http://www.w3.org/2000/svg"
							viewBox="0 0 20 20"
							fill="currentColor"
						>
							<path
								fill-rule="evenodd"
								d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
								clip-rule="evenodd"
							/>
						</svg>
						Back to Reports
					</Link>
				</div>
				<h1 class="text-2xl font-semibold text-gray-900">Monthly Inventory Report</h1>
				<p class="mt-2 text-sm text-gray-700">
					{{ report.campus }} - {{ monthNames[report.report_month - 1] }}
					{{ report.report_year }}
				</p>
				<div class="mt-2">
					<span
						:class="[
							'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
							report.status === 'draft'
								? 'bg-yellow-100 text-yellow-800'
								: report.status === 'submitted'
								? 'bg-green-100 text-green-800'
								: 'bg-blue-100 text-blue-800',
						]"
					>
						{{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
					</span>
				</div>
			</div>
		</div>

		<!-- Inventory Data -->
		<div class="mt-8">
			<div class="bg-white shadow overflow-hidden sm:rounded-lg">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
						Medicine Inventory
					</h3>

					<div v-if="medicinesData && medicinesData.length > 0">
						<form @submit.prevent="updateInventory" v-if="canEdit">
							<div
								class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
							>
								<table class="min-w-full divide-y divide-gray-300">
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
											v-for="(item, index) in inventoryForm.inventory_data"
											:key="index"
										>
											<td
												class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
											>
												{{ item.medicine_name }}
											</td>
											<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
												<input
													v-model.number="
														inventoryForm.inventory_data[index].current_stock
													"
													type="number"
													min="0"
													class="w-24 rounded-md border-gray-300 text-sm"
												/>
											</td>
											<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
												<input
													v-model.number="
														inventoryForm.inventory_data[index].quantity_to_order
													"
													type="number"
													min="0"
													class="w-24 rounded-md border-gray-300 text-sm"
												/>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<div class="mt-6 flex justify-end space-x-3">
								<button
									type="submit"
									:disabled="inventoryForm.processing"
									class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
								>
									Update Inventory
								</button>
								<button
									@click="submitReport"
									:disabled="submitForm.processing"
									type="button"
									class="inline-flex justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
								>
									Submit Report
								</button>
							</div>
						</form>

						<!-- Read-only view for submitted reports -->
						<div
							v-else
							class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
						>
							<table class="min-w-full divide-y divide-gray-300">
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
									<tr v-for="item in medicinesData" :key="item.medicine_name">
										<td
											class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
										>
											{{ item.medicine_name }}
										</td>
										<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
											{{ item.current_stock }}
										</td>
										<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
											{{ item.quantity_to_order }}
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div v-else class="text-center py-8">
						<p class="text-gray-500">No medicine data available for this report.</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Supplies Inventory Section - Always show both in edit and read-only modes -->
		<div v-if="suppliesData && suppliesData.length > 0" class="mt-8">
			<div class="bg-white shadow overflow-hidden sm:rounded-lg">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 flex items-center">
						<span class="w-3 h-3 rounded-full mr-2 bg-green-500"></span>
						Supplies Inventory ({{ suppliesData.length }} items)
					</h3>

					<!-- Editable supplies form -->
					<div v-if="canEdit" class="overflow-x-auto">
						<table
							class="min-w-full divide-y divide-gray-300 border border-gray-200 rounded-lg"
						>
							<thead class="bg-green-50">
								<tr>
									<th
										class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider border-r border-green-200"
									>
										Supply Item
									</th>
									<th
										class="px-4 py-3 text-center text-xs font-medium text-green-800 uppercase tracking-wider"
									>
										Current Stock
									</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr
									v-for="(supply, index) in suppliesForm.supplies_data"
									:key="`supply-edit-${supply.medicine_name}`"
								>
									<td
										class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200"
									>
										{{ supply.medicine_name }}
									</td>
									<td
										class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-700"
									>
										<input
											v-model.number="suppliesForm.supplies_data[index].current_stock"
											type="number"
											min="0"
											class="w-24 rounded-md border-gray-300 text-sm text-center"
										/>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- Read-only supplies view -->
					<div v-else class="overflow-x-auto">
						<table
							class="min-w-full divide-y divide-gray-300 border border-gray-200 rounded-lg"
						>
							<thead class="bg-green-50">
								<tr>
									<th
										class="px-6 py-3 text-left text-xs font-medium text-green-800 uppercase tracking-wider border-r border-green-200"
									>
										Supply Item
									</th>
									<th
										class="px-4 py-3 text-center text-xs font-medium text-green-800 uppercase tracking-wider"
									>
										Current Stock
									</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr
									v-for="supply in suppliesData"
									:key="`supply-readonly-${supply.medicine_name}`"
								>
									<td
										class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200"
									>
										{{ supply.medicine_name }}
									</td>
									<td
										class="px-4 py-4 whitespace-nowrap text-sm text-center text-gray-700"
									>
										{{ supply.current_stock }}
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
