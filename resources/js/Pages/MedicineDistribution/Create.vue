<template>
	<div class="py-12">
		<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 bg-white border-b border-gray-200">
					<div class="flex justify-between items-center mb-6">
						<h2 class="text-2xl font-semibold text-gray-900">Create Medicine Distribution</h2>
						<Link
							:href="route('medicine-distributions.index')"
							class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
						>
							Back to Distributions
						</Link>
					</div>

					<form @submit.prevent="submit" class="space-y-6">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<!-- From Campus (Read-only) -->
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-1">
									From Campus
								</label>
								<input
									:value="userCampus"
									type="text"
									class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
									readonly
								/>
								<p class="mt-1 text-sm text-gray-500">This is your assigned campus</p>
							</div>

							<!-- To Campus -->
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-1">
									To Campus *
								</label>
								<select
									v-model="form.to_campus"
									class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
									:class="{ 'border-red-500': form.errors.to_campus }"
									required
								>
									<option value="">Select Campus</option>
									<option 
										v-for="campus in campuses" 
										:key="campus" 
										:value="campus"
										:disabled="campus === userCampus"
									>
										{{ campus }}
									</option>
								</select>
								<div v-if="form.errors.to_campus" class="mt-1 text-sm text-red-600">
									{{ form.errors.to_campus }}
								</div>
							</div>

							<!-- To Department -->
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-1">
									To Department *
								</label>
								<input
									v-model="form.to_department"
									type="text"
									class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
									:class="{ 'border-red-500': form.errors.to_department }"
									placeholder="e.g., Medical, Administration, Nursing"
									required
								/>
								<div v-if="form.errors.to_department" class="mt-1 text-sm text-red-600">
									{{ form.errors.to_department }}
								</div>
							</div>

							<!-- Medicine Selection -->
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-1">
									Medicine *
								</label>
								<select
									v-model="form.medicine_id"
									@change="updateInventoryOptions"
									class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
									:class="{ 'border-red-500': form.errors.medicine_id }"
									required
								>
									<option value="">Select Medicine</option>
									<option 
										v-for="medicine in medicines" 
										:key="medicine.id" 
										:value="medicine.id"
									>
										{{ medicine.name }} ({{ medicine.type }})
									</option>
								</select>
								<div v-if="form.errors.medicine_id" class="mt-1 text-sm text-red-600">
									{{ form.errors.medicine_id }}
								</div>
							</div>

							<!-- Inventory/Batch Selection -->
							<div v-if="selectedMedicine">
								<label class="block text-sm font-medium text-gray-700 mb-1">
									Batch/Inventory *
								</label>
								<select
									v-model="form.inventory_id"
									@change="updateSelectedInventory"
									class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
									:class="{ 'border-red-500': form.errors.inventory_id }"
									required
								>
									<option value="">Select Batch</option>
									<option 
										v-for="inventory in selectedMedicine.inventory" 
										:key="inventory.id" 
										:value="inventory.id"
									>
										Batch: {{ inventory.batch_number }} | Available: {{ inventory.quantity }} | Exp: {{ formatDate(inventory.expiry_date) }}
									</option>
								</select>
								<div v-if="form.errors.inventory_id" class="mt-1 text-sm text-red-600">
									{{ form.errors.inventory_id }}
								</div>
							</div>

							<!-- Quantity -->
							<div>
								<label class="block text-sm font-medium text-gray-700 mb-1">
									Quantity to Distribute *
								</label>
								<input
									v-model.number="form.quantity_distributed"
									type="number"
									min="1"
									:max="selectedInventory?.quantity || 999999"
									class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
									:class="{ 'border-red-500': form.errors.quantity_distributed }"
									required
								/>
								<div v-if="selectedInventory" class="mt-1 text-sm text-gray-500">
									Available: {{ selectedInventory.quantity }}
								</div>
								<div v-if="form.errors.quantity_distributed" class="mt-1 text-sm text-red-600">
									{{ form.errors.quantity_distributed }}
								</div>
							</div>
						</div>

						<!-- Notes -->
						<div>
							<label class="block text-sm font-medium text-gray-700 mb-1">
								Notes
							</label>
							<textarea
								v-model="form.notes"
								rows="3"
								class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
								:class="{ 'border-red-500': form.errors.notes }"
								placeholder="Optional notes about this distribution..."
							></textarea>
							<div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
								{{ form.errors.notes }}
							</div>
						</div>

						<!-- Distribution Summary -->
						<div v-if="selectedMedicine && selectedInventory && form.quantity_distributed" class="bg-blue-50 border border-blue-200 rounded-md p-4">
							<h4 class="text-sm font-medium text-blue-800 mb-2">Distribution Summary:</h4>
							<div class="text-sm text-blue-700 space-y-1">
								<div><strong>Medicine:</strong> {{ selectedMedicine.name }}</div>
								<div><strong>From:</strong> {{ userCampus }} → <strong>To:</strong> {{ form.to_campus }}</div>
								<div><strong>Department:</strong> {{ form.to_department }}</div>
								<div><strong>Batch:</strong> {{ selectedInventory.batch_number }}</div>
								<div><strong>Quantity:</strong> {{ form.quantity_distributed }} units</div>
								<div><strong>Expiry Date:</strong> {{ formatDate(selectedInventory.expiry_date) }}</div>
							</div>
						</div>

						<!-- Submit Buttons -->
						<div class="flex justify-end space-x-3 pt-6 border-t">
							<Link
								:href="route('medicine-distributions.index')"
								class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
							>
								Cancel
							</Link>
							<button
								type="submit"
								:disabled="form.processing"
								class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
							>
								{{ form.processing ? 'Creating...' : 'Create Distribution' }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
	medicines: Array,
	campuses: Array,
	userCampus: String,
});

const form = useForm({
	medicine_id: '',
	inventory_id: '',
	to_campus: '',
	to_department: '',
	quantity_distributed: '',
	notes: '',
});

const selectedMedicine = ref(null);
const selectedInventory = ref(null);

const updateInventoryOptions = () => {
	const medicine = props.medicines.find(m => m.id == form.medicine_id);
	selectedMedicine.value = medicine;
	selectedInventory.value = null;
	form.inventory_id = '';
	form.quantity_distributed = '';
};

const updateSelectedInventory = () => {
	if (selectedMedicine.value) {
		const inventory = selectedMedicine.value.inventory.find(i => i.id == form.inventory_id);
		selectedInventory.value = inventory;
	}
};

const formatDate = (date) => {
	return new Date(date).toLocaleDateString();
};

const submit = () => {
	form.post(route('medicine-distributions.store'), {
		onSuccess: () => {
			// Redirect handled by controller
		},
	});
};
</script>