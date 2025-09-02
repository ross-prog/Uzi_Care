<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, reactive } from "vue";
import axios from "axios";

const props = defineProps({
	inventory: Object,
	medicines: Array,
});

const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedItem = ref(null);

const form = reactive({
	medicine_id: "",
	quantity: "",
	expiry_date: "",
	batch_number: "",
	supplier: "",
	cost_per_unit: "",
	low_stock_threshold: 10,
});

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
		supplier: item.supplier || "",
		cost_per_unit: item.cost_per_unit || "",
		low_stock_threshold: item.low_stock_threshold,
	});
	showEditModal.value = true;
};

const closeModals = () => {
	showAddModal.value = false;
	showEditModal.value = false;
	selectedItem.value = null;
	resetForm();
};

const resetForm = () => {
	Object.assign(form, {
		medicine_id: "",
		quantity: "",
		expiry_date: "",
		batch_number: "",
		supplier: "",
		cost_per_unit: "",
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

const getStockStatus = (item) => {
	if (item.quantity <= item.low_stock_threshold) {
		return { class: "badge-danger", text: "Low Stock" };
	}
	if (item.quantity <= item.low_stock_threshold * 2) {
		return { class: "badge-warning", text: "Medium Stock" };
	}
	return { class: "badge-success", text: "Good Stock" };
};

const getExpiryStatus = (expiryDate) => {
	const today = new Date();
	const expiry = new Date(expiryDate);
	const daysToExpiry = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));

	if (daysToExpiry < 0) {
		return { class: "badge-danger", text: "Expired" };
	}
	if (daysToExpiry <= 30) {
		return { class: "badge-warning", text: `${daysToExpiry} days` };
	}
	return { class: "badge-success", text: `${daysToExpiry} days` };
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
				<button @click="openAddModal" class="btn-primary">Add Item</button>
			</div>
		</div>

		<!-- Inventory table -->
		<div class="card overflow-hidden">
			<div class="px-6 py-4 border-b border-neutral-200">
				<h2 class="text-lg font-medium text-neutral-900">Current Inventory</h2>
			</div>
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-neutral-200">
					<thead class="bg-neutral-50">
						<tr>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
							>
								Medicine
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
								Supplier
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Cost/Unit
							</th>
							<th
								class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
							>
								Actions
							</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						<tr v-for="item in inventory.data" :key="item.id">
							<td class="px-6 py-4 whitespace-nowrap">
								<div>
									<div class="text-sm font-medium text-gray-900">
										{{ item.medicine?.name }}
									</div>
									<div class="text-sm text-gray-500">{{ item.medicine?.type }}</div>
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
								{{ item.supplier || "-" }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
								{{ item.cost_per_unit ? `₱${item.cost_per_unit}` : "-" }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
								<button
									@click="openEditModal(item)"
									class="text-blue-600 hover:text-blue-900"
								>
									Edit
								</button>
								<button @click="deleteItem(item)" class="text-red-600 hover:text-red-900">
									Delete
								</button>
							</td>
						</tr>
					</tbody>
				</table>

				<div v-if="inventory.data.length === 0" class="text-center py-8 text-gray-500">
					No inventory items found. Add some items to get started.
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
							<label class="block text-sm font-medium text-gray-700">Medicine *</label>
							<select
								v-model="form.medicine_id"
								required
								:disabled="showEditModal"
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100"
							>
								<option value="">Select a medicine</option>
								<option
									v-for="medicine in medicines"
									:key="medicine.id"
									:value="medicine.id"
								>
									{{ medicine.name }} ({{ medicine.type }})
								</option>
							</select>
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
								<label class="block text-sm font-medium text-gray-700">Supplier</label>
								<input
									v-model="form.supplier"
									type="text"
									class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
								/>
							</div>
						</div>

						<div>
							<label class="block text-sm font-medium text-gray-700"
								>Cost per Unit (₱)</label
							>
							<input
								v-model="form.cost_per_unit"
								type="number"
								step="0.01"
								min="0"
								class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
							/>
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
