<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { ref, reactive, watch } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
	inventory: Object,
	medicines: Array,
	supplies: Array,
	stats: Object,
	filters: Object,
	canGenerateReport: Boolean,
	canCompileReports: Boolean,
	canManageInventory: Boolean,
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedItem = ref(null);
const showNewMedicineForm = ref(false);

// Filter and search state
const currentFilters = reactive({
	type: props.filters.type || "all",
	search: props.filters.search || "",
	per_page: props.filters.per_page || 15,
	view: props.filters.view || "grouped",
});

const form = reactive({
	medicine_id: "",
	quantity: "",
	expiry_date: "",
	batch_number: "",
	distributor: "",
	low_stock_threshold: 10,
});

// New medicine form
const newMedicineForm = reactive({
	name: "",
	description: "",
	type: "",
	unit: "tablet",
	dosage_strength: "",
	form: "",
});

// State for expanded rows (to show batch details)
const expandedRows = ref(new Set());

// Watch for filter changes and update URL
watch(
	[
		() => currentFilters.type,
		() => currentFilters.search,
		() => currentFilters.per_page,
		() => currentFilters.view,
	],
	() => {
		const params = new URLSearchParams();
		if (currentFilters.type !== "all") params.set("type", currentFilters.type);
		if (currentFilters.search) params.set("search", currentFilters.search);
		if (currentFilters.per_page !== 15) params.set("per_page", currentFilters.per_page);
		if (currentFilters.view !== "grouped") params.set("view", currentFilters.view);

		router.get(route("inventory.index"), Object.fromEntries(params), {
			preserveState: true,
			replace: true,
		});
	},
	{ debounced: 500 }
);

const clearFilters = () => {
	currentFilters.type = "all";
	currentFilters.search = "";
	currentFilters.per_page = 15;
	currentFilters.view = "grouped";
};

const toggleRowExpansion = (medicineId) => {
	if (expandedRows.value.has(medicineId)) {
		expandedRows.value.delete(medicineId);
	} else {
		expandedRows.value.add(medicineId);
	}
};

const isRowExpanded = (medicineId) => {
	return expandedRows.value.has(medicineId);
};

const openAddModal = () => {
	resetForm();
	showAddModal.value = true;
};

const openEditModal = (item) => {
	selectedItem.value = item;
	Object.assign(form, {
		medicine_id: item.medicine_id,
		quantity: item.quantity,
		expiry_date: item.expiry_date,
		batch_number: item.batch_number || "",
		distributor: item.distributor || "",
		low_stock_threshold: item.low_stock_threshold,
	});
	showEditModal.value = true;
};

const closeModals = () => {
	showAddModal.value = false;
	showEditModal.value = false;
	showNewMedicineForm.value = false;
	selectedItem.value = null;
	resetForm();
	// Reset new medicine form
	Object.keys(newMedicineForm).forEach((key) => {
		newMedicineForm[key] = key === "unit" ? "tablet" : "";
	});
};

const resetForm = () => {
	Object.assign(form, {
		medicine_id: "",
		quantity: "",
		expiry_date: "",
		batch_number: "",
		distributor: "",
		low_stock_threshold: 10,
	});
};

const submitForm = async () => {
	try {
		if (showEditModal.value && selectedItem.value) {
			await axios.put(`/inventory/${selectedItem.value.id}`, form);
			alert("Inventory item updated successfully!");
		} else {
			await axios.post("/inventory", form);
			alert("Inventory item added successfully!");
		}
		closeModals();
		// Refresh page data
		window.location.reload();
	} catch (error) {
		console.error("Submit error:", error);
		alert("Error saving inventory item");
	}
};

const deleteItem = async (item) => {
	if (confirm("Are you sure you want to delete this inventory item?")) {
		try {
			await axios.delete(`/inventory/${item.id}`);
			alert("Inventory item deleted successfully!");
			// Refresh page data
			window.location.reload();
		} catch (error) {
			console.error("Delete error:", error);
			alert("Error deleting inventory item");
		}
	}
};

// New medicine creation functions
const createNewMedicine = async () => {
	try {
		const response = await axios.post("/medicines", newMedicineForm);
		const newMedicine = response.data;

		// Reset new medicine form
		Object.keys(newMedicineForm).forEach((key) => {
			newMedicineForm[key] = key === "unit" ? "tablet" : "";
		});

		// Set the newly created medicine in the inventory form
		form.medicine_id = newMedicine.id;
		showNewMedicineForm.value = false;

		alert("New medicine created successfully! Now add inventory details.");

		// Refresh the page to get the updated medicines list
		window.location.reload();
	} catch (error) {
		console.error("Create medicine error:", error);
		alert(
			"Error creating medicine: " + (error.response?.data?.message || "Unknown error")
		);
	}
};

const toggleNewMedicineForm = () => {
	showNewMedicineForm.value = !showNewMedicineForm.value;
	if (!showNewMedicineForm.value) {
		// Reset form when closing
		Object.keys(newMedicineForm).forEach((key) => {
			newMedicineForm[key] = key === "unit" ? "tablet" : "";
		});
	}
};

const getStockStatus = (item) => {
	if (item.quantity <= item.low_stock_threshold) {
		return { class: "bg-red-100 text-red-800", text: "Low Stock" };
	}
	if (item.quantity <= item.low_stock_threshold * 2) {
		return { class: "bg-yellow-100 text-yellow-800", text: "Medium Stock" };
	}
	return { class: "bg-green-100 text-green-800", text: "Good Stock" };
};

const getExpiryStatus = (expiryDate) => {
	const today = new Date();
	const expiry = new Date(expiryDate);
	const daysToExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));

	if (daysToExpiry < 0) {
		return { class: "bg-red-100 text-red-800", text: "Expired" };
	}
	if (daysToExpiry <= 30) {
		return { class: "bg-yellow-100 text-yellow-800", text: `${daysToExpiry} days` };
	}
	return { class: "bg-green-100 text-green-800", text: `${daysToExpiry} days` };
};

// Get available items for the form dropdown based on current filter
const getAvailableItems = () => {
	if (currentFilters.type === "medicines") {
		return props.medicines.filter(
			(item) => !["Equipment", "Supply", "Medical Supply"].includes(item.type)
		);
	} else if (currentFilters.type === "supplies") {
		return props.supplies;
	}
	return [...props.medicines, ...props.supplies];
};
</script>

<template>
	<Head title="Inventory Management" />

	<div class="page-container">
		<!-- Page header -->
		<div class="page-header">
			<div class="flex justify-between items-center">
				<div>
					<h1 class="text-3xl font-bold text-neutral-900">Inventory Management</h1>
					<p class="mt-2 text-neutral-600">
						Track medicine stock levels and expiry dates
					</p>
				</div>
				<div class="space-x-3">
					<Link
						v-if="canGenerateReport"
						:href="route('inventory.generate-report')"
						class="btn-secondary"
					>
						Generate Report
					</Link>
					<Link
						v-if="canCompileReports"
						:href="route('monthly-reports.index')"
						class="btn-secondary"
					>
						View All Reports
					</Link>
					<button 
						v-if="canManageInventory"
						@click="openAddModal" 
						class="btn-primary"
					>
						Add Item
					</button>
				</div>
			</div>
		</div>

		<!-- Stats Cards -->
		<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
			<div class="card p-6">
				<div class="flex items-center">
					<div class="p-2 bg-primary/10 rounded-lg">
						<svg
							class="w-6 h-6 text-primary"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
							/>
						</svg>
					</div>
					<div class="ml-4">
						<p class="text-sm font-medium text-neutral-500">Total Items</p>
						<p class="text-2xl font-bold text-neutral-900">{{ stats.total_items }}</p>
					</div>
				</div>
			</div>
			<div class="card p-6">
				<div class="flex items-center">
					<div class="p-2 bg-success/10 rounded-lg">
						<svg
							class="w-6 h-6 text-success"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
							/>
						</svg>
					</div>
					<div class="ml-4">
						<p class="text-sm font-medium text-neutral-500">Medicines</p>
						<p class="text-2xl font-bold text-neutral-900">{{ stats.medicines_count }}</p>
					</div>
				</div>
			</div>
			<div class="card p-6">
				<div class="flex items-center">
					<div class="p-2 bg-info/10 rounded-lg">
						<svg
							class="w-6 h-6 text-info"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
							/>
						</svg>
					</div>
					<div class="ml-4">
						<p class="text-sm font-medium text-neutral-500">Supplies</p>
						<p class="text-2xl font-bold text-neutral-900">{{ stats.supplies_count }}</p>
					</div>
				</div>
			</div>
			<div class="card p-6">
				<div class="flex items-center">
					<div class="p-2 bg-warning/10 rounded-lg">
						<svg
							class="w-6 h-6 text-warning"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"
							/>
						</svg>
					</div>
					<div class="ml-4">
						<p class="text-sm font-medium text-neutral-500">Alerts</p>
						<p class="text-2xl font-bold text-neutral-900">
							{{ stats.low_stock_count + stats.expiring_soon_count }}
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Filters and Search -->
		<div class="card mb-8">
			<div class="px-6 py-4 border-b border-neutral-200">
				<div
					class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0"
				>
					<!-- Filter Tabs -->
					<div class="flex space-x-1">
						<button
							@click="currentFilters.type = 'all'"
							:class="[
								'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
								currentFilters.type === 'all'
									? 'bg-primary text-white'
									: 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100',
							]"
						>
							All Items ({{ stats.total_items }})
						</button>
						<button
							@click="currentFilters.type = 'medicines'"
							:class="[
								'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
								currentFilters.type === 'medicines'
									? 'bg-success text-white'
									: 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100',
							]"
						>
							Medicines ({{ stats.medicines_count }})
						</button>
						<button
							@click="currentFilters.type = 'supplies'"
							:class="[
								'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
								currentFilters.type === 'supplies'
									? 'bg-info text-white'
									: 'text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100',
							]"
						>
							Supplies ({{ stats.supplies_count }})
						</button>
					</div>

					<!-- Search and Items per page -->
					<div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
						<div class="relative">
							<input
								v-model="currentFilters.search"
								type="text"
								placeholder="Search items..."
								class="pl-10 pr-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
							/>
							<svg
								class="absolute left-3 top-2.5 h-5 w-5 text-neutral-400"
								fill="none"
								stroke="currentColor"
								viewBox="0 0 24 24"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
								/>
							</svg>
						</div>
						<select
							v-model="currentFilters.view"
							class="px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
						>
							<option value="grouped">Grouped View</option>
							<option value="detailed">Detailed View</option>
						</select>
						<select
							v-model="currentFilters.per_page"
							class="px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
						>
							<option value="10">10 per page</option>
							<option value="15">15 per page</option>
							<option value="25">25 per page</option>
							<option value="50">50 per page</option>
						</select>
						<button
							v-if="currentFilters.search || currentFilters.type !== 'all'"
							@click="clearFilters"
							class="px-4 py-2 text-sm text-neutral-600 hover:text-neutral-900 border border-neutral-300 rounded-lg hover:bg-neutral-50"
						>
							Clear Filters
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Inventory table -->
		<div class="card overflow-hidden">
			<div class="px-6 py-4 border-b border-neutral-200">
				<div class="flex justify-between items-center">
					<h2 class="text-lg font-medium text-neutral-900">
						Current Inventory
						<span class="text-sm text-neutral-500 font-normal">
							({{
								currentFilters.view === "grouped"
									? "Grouped by Medicine"
									: "Individual Batches"
							}})
						</span>
					</h2>
				</div>
			</div>
			<div class="overflow-x-auto">
				<!-- Grouped View -->
				<table
					v-if="currentFilters.view === 'grouped'"
					class="min-w-full divide-y divide-neutral-200"
				>
					<thead class="bg-neutral-50">
						<tr>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								<span class="sr-only">Expand</span>
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Medicine
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Type
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Total Quantity
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Batches
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Stock Status
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Earliest Expiry
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Latest Added
							</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						<template v-for="item in inventory.data" :key="item.medicine_id">
							<!-- Main grouped row -->
							<tr
								class="hover:bg-neutral-50 cursor-pointer"
								@click="toggleRowExpansion(item.medicine_id)"
							>
								<td class="px-6 py-4 whitespace-nowrap">
									<button class="flex items-center text-gray-400 hover:text-gray-600">
										<svg
											:class="{ 'rotate-90': isRowExpanded(item.medicine_id) }"
											class="w-4 h-4 transition-transform"
											fill="none"
											stroke="currentColor"
											viewBox="0 0 24 24"
										>
											<path
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="2"
												d="M9 5l7 7-7 7"
											/>
										</svg>
									</button>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div>
										<div class="text-sm font-medium text-gray-900">
											{{ item.medicine?.name }}
										</div>
										<div class="text-sm text-gray-500">
											{{ item.medicine?.description || "No description" }}
										</div>
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span
										:class="[
											'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
											['Equipment', 'Supply', 'Medical Supply'].includes(
												item.medicine?.type
											)
												? 'bg-blue-100 text-blue-800'
												: 'bg-green-100 text-green-800',
										]"
									>
										{{ item.medicine?.type }}
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm font-medium text-gray-900">
										{{ item.total_quantity }} {{ item.medicine?.unit }}
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-gray-900">{{ item.batch_count }} batches</div>
									<div v-if="item.expiring_batches > 0" class="text-xs text-orange-600">
										{{ item.expiring_batches }} expiring soon
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span
										v-if="item.low_stock_batches > 0"
										class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"
									>
										{{ item.low_stock_batches }} Low Stock
									</span>
									<span
										v-else
										class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
									>
										In Stock
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
									{{
										item.earliest_expiry
											? new Date(item.earliest_expiry).toLocaleDateString()
											: "-"
									}}
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
									{{
										item.latest_added
											? new Date(item.latest_added).toLocaleDateString()
											: "-"
									}}
								</td>
							</tr>

							<!-- Expanded batch details -->
							<tr v-if="isRowExpanded(item.medicine_id)" class="bg-gray-50">
								<td colspan="8" class="px-6 py-4">
									<div class="overflow-x-auto">
										<table class="min-w-full divide-y divide-gray-200">
											<thead class="bg-gray-100">
												<tr>
													<th
														class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
													>
														Batch
													</th>
													<th
														class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
													>
														Quantity
													</th>
													<th
														class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
													>
														Expiry Date
													</th>
													<th
														class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
													>
														Distributor
													</th>
													<th
														class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
													>
														Date Added
													</th>
													<th
														class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
													>
														Stock Status
													</th>
													<th
														class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"
													>
														Actions
													</th>
												</tr>
											</thead>
											<tbody class="divide-y divide-gray-200">
												<tr
													v-for="batch in item.batches"
													:key="batch.id"
													class="hover:bg-gray-100"
												>
													<td class="px-4 py-2 text-sm text-gray-900">
														{{ batch.batch_number || "-" }}
													</td>
													<td class="px-4 py-2 text-sm text-gray-900">
														{{ batch.quantity }} {{ item.medicine?.unit }}
														<div class="text-xs text-gray-500">
															Threshold: {{ batch.low_stock_threshold }}
														</div>
													</td>
													<td class="px-4 py-2 text-sm text-gray-900">
														{{ new Date(batch.expiry_date).toLocaleDateString() }}
													</td>
													<td class="px-4 py-2 text-sm text-gray-900">
														{{ batch.distributor || "-" }}
													</td>
													<td class="px-4 py-2 text-sm text-gray-900">
														{{
															batch.date_added
																? new Date(batch.date_added).toLocaleDateString()
																: "-"
														}}
													</td>
													<td class="px-4 py-2">
														<span
															:class="[
																'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
																getStockStatus(batch).class,
															]"
														>
															{{ getStockStatus(batch).text }}
														</span>
													</td>
													<td class="px-4 py-2 text-sm font-medium space-x-2">
														<button
															v-if="canManageInventory"
															@click.stop="openEditModal(batch)"
															class="text-blue-600 hover:text-blue-900"
														>
															Edit
														</button>
														<button
															v-if="canManageInventory"
															@click.stop="deleteItem(batch)"
															class="text-red-600 hover:text-red-900"
														>
															Delete
														</button>
														<span
															v-if="!canManageInventory"
															class="text-gray-400 text-sm"
														>
															View only
														</span>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>

				<!-- Detailed View (Original) -->
				<table v-else class="min-w-full divide-y divide-neutral-200">
					<thead class="bg-neutral-50">
						<tr>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Item
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Type
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Dosage/Form
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Quantity
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Stock Status
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Expiry Date
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Expiry Status
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Batch
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Distributor
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Date Added
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						<tr v-for="item in inventory.data" :key="item.id" class="hover:bg-neutral-50">
							<td class="px-6 py-4 whitespace-nowrap">
								<div>
									<div class="text-sm font-medium text-gray-900">
										{{ item.medicine?.name }}
									</div>
									<div class="text-sm text-gray-500">
										{{ item.medicine?.description || "No description" }}
									</div>
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<span
									:class="[
										'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
										['Equipment', 'Supply', 'Medical Supply'].includes(
											item.medicine?.type
										)
											? 'bg-blue-100 text-blue-800'
											: 'bg-green-100 text-green-800',
									]"
								>
									{{ item.medicine?.type }}
								</span>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm text-gray-900">
									<span v-if="item.medicine?.dosage_strength">{{
										item.medicine.dosage_strength
									}}</span>
									<span v-else class="text-gray-400">No dosage</span>
								</div>
								<div class="text-xs text-gray-500">
									<span v-if="item.medicine?.form">{{ item.medicine.form }}</span>
									<span v-else class="text-gray-400">No form</span>
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm text-gray-900">
									{{ item.quantity }} {{ item.medicine?.unit }}
								</div>
								<div class="text-xs text-gray-500">
									Threshold: {{ item.low_stock_threshold }}
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<span
									:class="[
										'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
										getStockStatus(item).class,
									]"
								>
									{{ getStockStatus(item).text }}
								</span>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
								{{ new Date(item.expiry_date).toLocaleDateString() }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<span
									:class="[
										'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
										getExpiryStatus(item.expiry_date).class,
									]"
								>
									{{ getExpiryStatus(item.expiry_date).text }}
								</span>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
								{{ item.batch_number || "-" }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
								{{ item.distributor || "-" }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
								{{
									item.date_added ? new Date(item.date_added).toLocaleDateString() : "-"
								}}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
								<button
									v-if="canManageInventory"
									@click="openEditModal(item)"
									class="text-blue-600 hover:text-blue-900"
								>
									Edit
								</button>
								<button 
									v-if="canManageInventory"
									@click="deleteItem(item)" 
									class="text-red-600 hover:text-red-900"
								>
									Delete
								</button>
								<span
									v-if="!canManageInventory"
									class="text-gray-400 text-sm"
								>
									View only
								</span>
							</td>
						</tr>
					</tbody>
				</table>

				<div v-if="inventory.data.length === 0" class="text-center py-12">
					<svg
						class="mx-auto h-12 w-12 text-gray-400"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
						/>
					</svg>
					<h3 class="mt-2 text-sm font-medium text-gray-900">No inventory items found</h3>
					<p class="mt-1 text-sm text-gray-500">
						{{
							currentFilters.search || currentFilters.type !== "all"
								? "Try adjusting your search or filters."
								: "Get started by adding your first inventory item."
						}}
					</p>
					<div class="mt-6">
						<button
							v-if="currentFilters.search || currentFilters.type !== 'all'"
							@click="clearFilters"
							class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
						>
							Clear filters
						</button>
						<button
							v-else-if="canManageInventory"
							@click="openAddModal"
							class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary/90"
						>
							Add first item
						</button>
					</div>
				</div>
			</div>

			<!-- Pagination -->
			<div v-if="inventory.data.length > 0" class="px-6 py-4 border-t border-neutral-200">
				<div class="flex items-center justify-between">
					<div class="flex-1 flex justify-between sm:hidden">
						<!-- Mobile pagination -->
						<a
							v-if="inventory.prev_page_url"
							:href="inventory.prev_page_url"
							class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
						>
							Previous
						</a>
						<a
							v-if="inventory.next_page_url"
							:href="inventory.next_page_url"
							class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
						>
							Next
						</a>
					</div>
					<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
						<div>
							<p class="text-sm text-gray-700">
								Showing
								<span class="font-medium">{{ inventory.from }}</span>
								to
								<span class="font-medium">{{ inventory.to }}</span>
								of
								<span class="font-medium">{{ inventory.total }}</span>
								results
							</p>
						</div>
						<div>
							<nav
								class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
								aria-label="Pagination"
							>
								<!-- Previous Page Link -->
								<a
									v-if="inventory.prev_page_url"
									:href="inventory.prev_page_url"
									class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
								>
									<span class="sr-only">Previous</span>
									<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
										<path
											fill-rule="evenodd"
											d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
											clip-rule="evenodd"
										/>
									</svg>
								</a>

								<!-- Page Numbers -->
								<template v-for="link in inventory.links" :key="link.label">
									<a
										v-if="!link.url && link.label.includes('Previous')"
										class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed"
									>
										<span class="sr-only">Previous</span>
										<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
											<path
												fill-rule="evenodd"
												d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
												clip-rule="evenodd"
											/>
										</svg>
									</a>
									<a
										v-else-if="!link.url && link.label.includes('Next')"
										class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed"
									>
										<span class="sr-only">Next</span>
										<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
											<path
												fill-rule="evenodd"
												d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
												clip-rule="evenodd"
											/>
										</svg>
									</a>
									<a
										v-else-if="
											link.url &&
											!link.label.includes('Previous') &&
											!link.label.includes('Next')
										"
										:href="link.url"
										:class="[
											'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
											link.active
												? 'z-10 bg-primary border-primary text-white'
												: 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
										]"
									>
										{{ link.label }}
									</a>
								</template>

								<!-- Next Page Link -->
								<a
									v-if="inventory.next_page_url"
									:href="inventory.next_page_url"
									class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
								>
									<span class="sr-only">Next</span>
									<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
										<path
											fill-rule="evenodd"
											d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
											clip-rule="evenodd"
										/>
									</svg>
								</a>
							</nav>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Add/Edit Modal -->
		<div
			v-if="showAddModal || showEditModal"
			class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
		>
			<div
				class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white"
			>
				<div class="mt-3">
					<div class="flex justify-between items-center mb-4">
						<h3 class="text-lg font-medium text-gray-900">
							{{ showEditModal ? "Edit Inventory Item" : "Add New Inventory Item" }}
						</h3>
						<button @click="closeModals" class="text-gray-400 hover:text-gray-600">
							<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M6 18L18 6M6 6l12 12"
								/>
							</svg>
						</button>
					</div>

					<form @submit.prevent="submitForm" class="space-y-4">
						<div>
							<label class="block text-sm font-medium text-gray-700">
								{{
									currentFilters.type === "medicines"
										? "Medicine"
										: currentFilters.type === "supplies"
										? "Supply/Equipment"
										: "Item"
								}}
								*
							</label>
							<select
								v-model="form.medicine_id"
								required
								:disabled="showEditModal"
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100"
							>
								<option value="">
									Select
									{{
										currentFilters.type === "medicines"
											? "a medicine"
											: currentFilters.type === "supplies"
											? "a supply/equipment"
											: "an item"
									}}
								</option>
								<option
									v-for="item in getAvailableItems()"
									:key="item.id"
									:value="item.id"
								>
									{{ item.name }} ({{ item.type }})
								</option>
							</select>
							<p v-if="currentFilters.type !== 'all'" class="mt-1 text-xs text-gray-500">
								Only showing
								{{
									currentFilters.type === "medicines" ? "medicines" : "supplies/equipment"
								}}
								based on current filter
							</p>

							<!-- Add New Medicine Button -->
							<div
								v-if="!showEditModal && currentFilters.type !== 'supplies'"
								class="mt-2"
							>
								<button
									type="button"
									@click="toggleNewMedicineForm"
									class="text-sm text-blue-600 hover:text-blue-800 underline"
								>
									{{ showNewMedicineForm ? "Cancel" : "+ Create New Medicine" }}
								</button>
							</div>
						</div>

						<!-- New Medicine Form -->
						<div
							v-if="showNewMedicineForm && !showEditModal"
							class="border border-blue-200 rounded-lg p-4 bg-blue-50"
						>
							<h4 class="text-sm font-medium text-gray-900 mb-3">Create New Medicine</h4>

							<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
								<div>
									<label class="block text-sm font-medium text-gray-700"
										>Medicine Name *</label
									>
									<input
										v-model="newMedicineForm.name"
										type="text"
										required
										class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
										placeholder="e.g., Paracetamol"
									/>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700">Type *</label>
									<select
										v-model="newMedicineForm.type"
										required
										class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
									>
										<option value="">Select Type</option>
										<option value="Analgesic">Analgesic</option>
										<option value="Antibiotic">Antibiotic</option>
										<option value="Antihistamine">Antihistamine</option>
										<option value="Antitussive">Antitussive</option>
										<option value="NSAID">NSAID</option>
										<option value="Vitamin">Vitamin</option>
										<option value="Medical Supply">Medical Supply</option>
										<option value="Medical Device">Medical Device</option>
										<option value="Other">Other</option>
									</select>
								</div>
							</div>

							<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
								<div>
									<label class="block text-sm font-medium text-gray-700"
										>Dosage Strength</label
									>
									<input
										v-model="newMedicineForm.dosage_strength"
										type="text"
										class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
										placeholder="e.g., 500mg, 10ml"
									/>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700">Form</label>
									<select
										v-model="newMedicineForm.form"
										class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
									>
										<option value="">Select Form</option>
										<option value="tablet">Tablet</option>
										<option value="capsule">Capsule</option>
										<option value="syrup">Syrup</option>
										<option value="injection">Injection</option>
										<option value="cream">Cream</option>
										<option value="ointment">Ointment</option>
										<option value="drops">Drops</option>
										<option value="spray">Spray</option>
										<option value="swab">Swab</option>
										<option value="roll">Roll</option>
										<option value="piece">Piece</option>
									</select>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700">Unit *</label>
									<select
										v-model="newMedicineForm.unit"
										required
										class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
									>
										<option value="tablet">tablet</option>
										<option value="capsule">capsule</option>
										<option value="ml">ml</option>
										<option value="piece">piece</option>
										<option value="bottle">bottle</option>
										<option value="box">box</option>
										<option value="vial">vial</option>
									</select>
								</div>
							</div>

							<div class="mb-4">
								<label class="block text-sm font-medium text-gray-700">Description</label>
								<textarea
									v-model="newMedicineForm.description"
									rows="2"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
									placeholder="Brief description of the medicine"
								></textarea>
							</div>

							<button
								type="button"
								@click="createNewMedicine"
								class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
							>
								Create Medicine & Continue
							</button>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-medium text-gray-700">Quantity *</label>
								<input
									v-model="form.quantity"
									type="number"
									min="0"
									required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
								/>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700"
									>Low Stock Threshold *</label
								>
								<input
									v-model="form.low_stock_threshold"
									type="number"
									min="1"
									required
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
								/>
							</div>
						</div>

						<div>
							<label class="block text-sm font-medium text-gray-700">Expiry Date *</label>
							<input
								v-model="form.expiry_date"
								type="date"
								required
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
							/>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-medium text-gray-700"
									>Batch Number</label
								>
								<input
									v-model="form.batch_number"
									type="text"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
								/>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700">Distributor</label>
								<input
									v-model="form.distributor"
									type="text"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
								/>
							</div>
						</div>

						<div class="flex justify-end space-x-4 mt-6">
							<button
								@click="closeModals"
								type="button"
								class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
							>
								Cancel
							</button>
							<button
								type="submit"
								class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
							>
								{{ showEditModal ? "Update" : "Add" }} Item
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>
