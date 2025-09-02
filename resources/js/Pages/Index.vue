<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, reactive } from "vue";
import axios from "axios";

const props = defineProps({
	medicines: Array,
});

const searchQuery = ref("");
const searchResults = ref([]);
const isSearching = ref(false);
const hasSearched = ref(false);
const searchSuccessful = ref(false);
const searchInitiated = ref(false); // New flag to track if search was actually started
const selectedRecord = ref(null);
const showRecordModal = ref(false);

const currentView = ref("dashboard");

const form = reactive({
	student_id: "",
	student_name: "",
	date: new Date().toISOString().split("T")[0],
	visit_type: "Check-up",
	complaint_diagnosis: "",
	medicines: [],
	equipments: [],
	nurse_notes: "",
	nurse_assigned: "",
});

const visitTypes = [
	"Check-up",
	"Emergency",
	"Follow-up",
	"Consultation",
	"Vaccination",
	"Physical Exam",
];

const searchPatient = async () => {
	if (!searchQuery.value || !/^\d{10}$/.test(searchQuery.value)) {
		alert("Please enter a valid 10-digit student ID (e.g., 2022100683)");
		return;
	}

	// Reset all search-related state first
	hasSearched.value = false;
	searchSuccessful.value = false;
	searchResults.value = [];
	searchInitiated.value = true; // Mark that search was actually initiated

	// Set loading state and switch view
	isSearching.value = true;
	currentView.value = "search";

	try {
		const response = await axios.post("/ehr/search", {
			student_id: searchQuery.value,
		});
		console.log("Search response:", response.data);
		searchResults.value = response.data.records || [];
		hasSearched.value = true; // Only set to true after we have results
		searchSuccessful.value = true; // Mark as successful
		console.log("Search results:", searchResults.value);
	} catch (error) {
		console.error("Search error:", error);
		alert("Error searching for patient records");
		// On error, go back to dashboard instead of showing "no records found"
		currentView.value = "dashboard";
		searchResults.value = [];
		hasSearched.value = false; // Don't set hasSearched to true on error
		searchSuccessful.value = false; // Mark as unsuccessful
		searchInitiated.value = false; // Reset initiated flag on error
	} finally {
		isSearching.value = false;
	}
};

const openAddModal = () => {
	if (searchQuery.value && /^\d{10}$/.test(searchQuery.value)) {
		form.student_id = searchQuery.value;
	}
	currentView.value = "add";
};

const quickAddRecord = (studentId = "") => {
	resetForm();
	if (studentId) {
		form.student_id = studentId;
	}
	currentView.value = "add";
};

const closeAddModal = () => {
	resetForm();
	if (hasSearched.value && searchResults.value.length > 0) {
		currentView.value = "search";
	} else {
		currentView.value = "dashboard";
	}
};

const resetForm = () => {
	Object.assign(form, {
		student_id: "",
		student_name: "",
		date: new Date().toISOString().split("T")[0],
		visit_type: "Check-up",
		complaint_diagnosis: "",
		medicines: [],
		equipments: [],
		nurse_notes: "",
		nurse_assigned: "",
	});
};

const addMedicine = (medicine) => {
	// Check if medicine already exists
	const existingIndex = form.medicines.findIndex((m) => m.name === medicine.name);

	if (existingIndex !== -1) {
		// If medicine exists, increase quantity
		form.medicines[existingIndex].quantity += 1;
	} else {
		// Add new medicine with quantity 1
		form.medicines.push({
			id: medicine.id,
			name: medicine.name,
			quantity: 1,
		});
	}
};

const removeMedicine = (index) => {
	form.medicines.splice(index, 1);
};

const updateMedicineQuantity = (index, quantity) => {
	if (quantity <= 0) {
		removeMedicine(index);
	} else {
		form.medicines[index].quantity = parseInt(quantity);
	}
};

const addEquipment = (equipment) => {
	// Check if equipment already exists
	const existingIndex = form.equipments.findIndex((e) => e.name === equipment.name);

	if (existingIndex !== -1) {
		// If equipment exists, increase quantity
		form.equipments[existingIndex].quantity += 1;
	} else {
		// Add new equipment with quantity 1
		form.equipments.push({
			id: equipment.id,
			name: equipment.name,
			quantity: 1,
		});
	}
};

const removeEquipment = (index) => {
	form.equipments.splice(index, 1);
};

const updateEquipmentQuantity = (index, quantity) => {
	if (quantity <= 0) {
		removeEquipment(index);
	} else {
		form.equipments[index].quantity = parseInt(quantity);
	}
};

const submitForm = async () => {
	if (
		!form.student_id ||
		!form.student_name ||
		!form.complaint_diagnosis ||
		!form.nurse_assigned
	) {
		alert("Please fill in all required fields");
		return;
	}

	try {
		await axios.post("/ehr", form);
		alert("Record added successfully!");
		closeAddModal();
		if (searchQuery.value === form.student_id) {
			searchPatient();
		}
	} catch (error) {
		console.error("Submit error:", error);
		alert("Error adding record");
	}
};

const viewRecord = async (record) => {
	selectedRecord.value = record;
	showRecordModal.value = true;
};

const downloadPDF = async (record) => {
	try {
		const response = await axios.get(`/ehr/${record.id}/pdf`, {
			responseType: "blob",
		});

		const url = window.URL.createObjectURL(new Blob([response.data]));
		const link = document.createElement("a");
		link.href = url;
		link.setAttribute("download", `ehr-record-${record.student_id}-${record.date}.pdf`);
		document.body.appendChild(link);
		link.click();
		link.remove();
		window.URL.revokeObjectURL(url);
	} catch (error) {
		console.error("PDF download error:", error);
		alert("Error downloading PDF");
	}
};

// Navigation functions
const goToDashboard = () => {
	currentView.value = "dashboard";
	searchQuery.value = "";
	searchResults.value = [];
	hasSearched.value = false;
	searchSuccessful.value = false;
	searchInitiated.value = false;
};

const goToSearch = () => {
	currentView.value = "dashboard";
};
</script>

<template>
	<Head title="Electronic Health Records" />

	<div class="page-container">
		<!-- Navigation Breadcrumbs -->
		<div class="mb-4">
			<nav class="flex" aria-label="Breadcrumb">
				<ol class="flex items-center space-x-4">
					<li>
						<div>
							<button
								@click="goToDashboard"
								:class="[
									'text-sm font-medium',
									currentView === 'dashboard'
										? 'text-primary'
										: 'text-neutral-500 hover:text-neutral-700',
								]"
							>
								Dashboard
							</button>
						</div>
					</li>
					<li v-if="currentView === 'search'">
						<div class="flex items-center">
							<svg
								class="flex-shrink-0 h-4 w-4 text-neutral-400"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path
									fill-rule="evenodd"
									d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							<span class="ml-4 text-sm font-medium text-primary">
								Search Results ({{ searchQuery }})
							</span>
						</div>
					</li>
					<li v-if="currentView === 'add'">
						<div class="flex items-center">
							<svg
								class="flex-shrink-0 h-4 w-4 text-neutral-400"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path
									fill-rule="evenodd"
									d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
									clip-rule="evenodd"
								/>
							</svg>
							<span class="ml-4 text-sm font-medium text-primary">Add New Record</span>
						</div>
					</li>
				</ol>
			</nav>
		</div>

		<div class="page-header">
			<div class="flex justify-between items-center">
				<div>
					<h1 class="text-3xl font-bold text-neutral-900">
						<span v-if="currentView === 'dashboard'">Electronic Health Records</span>
						<span v-else-if="currentView === 'search'">Search Results</span>
						<span v-else-if="currentView === 'add'">Add New Record</span>
					</h1>
					<p class="mt-2 text-neutral-600">
						<span v-if="currentView === 'dashboard'"
							>Search and manage patient records</span
						>
						<span v-else-if="currentView === 'search'">
							Found {{ searchResults.length }} record(s) for Student ID: {{ searchQuery }}
						</span>
						<span v-else-if="currentView === 'add'"
							>Create a new patient health record</span
						>
					</p>
				</div>
				<div class="flex space-x-3">
					<button
						v-if="currentView !== 'add'"
						@click="openAddModal"
						class="btn-success flex items-center space-x-2"
					>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M12 6v6m0 0v6m0-6h6m-6 0H6"
							/>
						</svg>
						<span>Add New Record</span>
					</button>
					<button
						v-if="currentView === 'search'"
						@click="goToDashboard"
						class="btn-secondary flex items-center space-x-2"
					>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M10 19l-7-7m0 0l7-7m-7 7h18"
							/>
						</svg>
						<span>Back to Search</span>
					</button>
					<button
						v-if="currentView === 'add'"
						@click="closeAddModal"
						class="btn-secondary flex items-center space-x-2"
					>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M6 18L18 6M6 6l12 12"
							/>
						</svg>
						<span>Cancel</span>
					</button>
					<button
						v-if="currentView !== 'add'"
						class="btn-secondary flex items-center space-x-2"
					>
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
							/>
						</svg>
						<span>Export Records</span>
					</button>
				</div>
			</div>
		</div>

		<!-- Dashboard View -->
		<div v-if="currentView === 'dashboard'">
			<!-- Quick Stats -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
									d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-neutral-500">Patient Search</p>
							<p class="text-2xl font-bold text-neutral-900">Ready</p>
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
									d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-neutral-500">Records Today</p>
							<p class="text-2xl font-bold text-neutral-900">-</p>
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
									d="M13 10V3L4 14h7v7l9-11h-7z"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-neutral-500">Quick Actions</p>
							<button
								@click="openAddModal"
								class="text-sm text-info hover:text-info/80 font-medium"
							>
								Add Record →
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Search section -->
			<div class="card mb-8">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Patient Lookup</h2>
					<p class="text-sm text-neutral-500 mt-1">
						Search for existing patient records by Student ID
					</p>
				</div>
				<div class="p-6">
					<div class="flex space-x-4">
						<div class="flex-1">
							<label for="search" class="form-label">
								Student ID (e.g., 2022100683)
							</label>
							<input
								id="search"
								v-model="searchQuery"
								type="text"
								placeholder="Enter 10-digit student ID"
								class="form-input"
								@keyup.enter="searchPatient"
							/>
						</div>
						<div class="flex items-end">
							<button @click="searchPatient" :disabled="isSearching" class="btn-primary">
								{{ isSearching ? "Searching..." : "Search Records" }}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Search Results View - only show if search was actually initiated -->
		<div
			v-if="currentView === 'search' && searchInitiated && (isSearching || hasSearched)"
		>
			<!-- Show loading state -->
			<div v-if="isSearching" class="card">
				<div class="px-6 py-8 text-center">
					<svg
						class="animate-spin w-8 h-8 text-primary mx-auto mb-4"
						fill="none"
						viewBox="0 0 24 24"
					>
						<circle
							class="opacity-25"
							cx="12"
							cy="12"
							r="10"
							stroke="currentColor"
							stroke-width="4"
						></circle>
						<path
							class="opacity-75"
							fill="currentColor"
							d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
						></path>
					</svg>
					<p class="text-neutral-600">Searching for records...</p>
				</div>
			</div>

			<!-- Show results if we have them and search is complete -->
			<div
				v-else-if="!isSearching && hasSearched && searchResults.length > 0"
				class="card"
			>
				<div class="px-6 py-4 border-b border-neutral-200">
					<div class="flex justify-between items-center">
						<h2 class="text-lg font-medium text-neutral-900">Search Results</h2>
						<button @click="quickAddRecord(searchQuery)" class="btn-success text-sm">
							+ Add New Record for {{ searchQuery }}
						</button>
					</div>
				</div>
				<div class="overflow-hidden">
					<table class="min-w-full divide-y divide-neutral-200">
						<thead class="bg-neutral-50">
							<tr>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Date
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Patient
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Visit Type
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Nurse
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Actions
								</th>
							</tr>
						</thead>
						<tbody class="bg-white divide-y divide-neutral-200">
							<tr v-for="record in searchResults" :key="record.id">
								<td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
									{{ new Date(record.date).toLocaleDateString() }}
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div>
										<div class="text-sm font-medium text-neutral-900">
											{{ record.student_name }}
										</div>
										<div class="text-sm text-neutral-500">{{ record.student_id }}</div>
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
									{{ record.visit_type }}
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
									{{ record.nurse_assigned }}
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
									<div class="flex space-x-3">
										<button
											@click="viewRecord(record)"
											class="text-primary hover:text-primary/80 font-medium"
										>
											View
										</button>
										<button
											@click="quickAddRecord(record.student_id)"
											class="text-success hover:text-success/80 font-medium"
										>
											+ Add New
										</button>
										<button
											@click="downloadPDF(record)"
											class="text-info hover:text-info/80 font-medium"
										>
											PDF
										</button>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- No results found - only show if search was completed successfully but returned no results -->
			<div
				v-else-if="
					!isSearching &&
					hasSearched &&
					searchSuccessful &&
					searchInitiated &&
					searchResults.length === 0
				"
				class="card"
			>
				<div class="px-6 py-8 text-center">
					<svg
						class="w-12 h-12 text-neutral-400 mx-auto mb-4"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
						/>
					</svg>
					<h3 class="text-lg font-medium text-neutral-900 mb-2">No records found</h3>
					<p class="text-neutral-500 mb-4">
						No existing records found for Student ID: {{ searchQuery }}
					</p>
					<div class="flex justify-center space-x-3">
						<button @click="quickAddRecord(searchQuery)" class="btn-primary">
							Create First Record for {{ searchQuery }}
						</button>
						<button @click="goToDashboard" class="btn-secondary">Back to Search</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Add New Record View -->
		<div v-if="currentView === 'add'">
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Patient Information</h2>
					<p class="text-sm text-neutral-500 mt-1">
						Please fill in all required fields marked with *
					</p>
				</div>

				<form @submit.prevent="submitForm" class="p-6 space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div>
							<label class="form-label">Student ID *</label>
							<input
								v-model="form.student_id"
								type="text"
								placeholder="e.g., 2022100683"
								class="form-input"
								required
							/>
						</div>
						<div>
							<label class="form-label">Student Name *</label>
							<input
								v-model="form.student_name"
								type="text"
								placeholder="Full name"
								class="form-input"
								required
							/>
						</div>
						<div>
							<label class="form-label">Date *</label>
							<input v-model="form.date" type="date" class="form-input" required />
						</div>
						<div>
							<label class="form-label">Visit Type *</label>
							<select v-model="form.visit_type" class="form-input" required>
								<option v-for="type in visitTypes" :key="type" :value="type">
									{{ type }}
								</option>
							</select>
						</div>
					</div>

					<div>
						<label class="form-label">Complaint/Diagnosis *</label>
						<textarea
							v-model="form.complaint_diagnosis"
							rows="3"
							placeholder="Describe the medical condition or complaint"
							class="form-input"
							required
						></textarea>
					</div>

					<div>
						<label class="form-label">Nurse Assigned *</label>
						<input
							v-model="form.nurse_assigned"
							type="text"
							placeholder="Nurse name"
							class="form-input"
							required
						/>
					</div>

					<div>
						<label class="form-label">Nurse Notes</label>
						<textarea
							v-model="form.nurse_notes"
							rows="3"
							placeholder="Additional notes from the nurse"
							class="form-input"
						></textarea>
					</div>

					<!-- Medicine Selection -->
					<div>
						<label class="form-label">Prescribed Medicines</label>
						<div class="space-y-4">
							<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
								<button
									v-for="medicine in medicines.filter(item => item.type !== 'Equipment')"
									:key="medicine.id"
									type="button"
									@click="addMedicine(medicine)"
									class="p-3 text-left border border-neutral-200 rounded-lg hover:border-primary hover:bg-primary/5 transition-colors"
								>
									<div class="font-medium text-neutral-900">{{ medicine.name }}</div>
									<div class="text-sm text-neutral-500">{{ medicine.type }}</div>
								</button>
							</div>

							<!-- Selected Medicines -->
							<div v-if="form.medicines.length > 0" class="mt-4">
								<h4 class="text-sm font-medium text-neutral-900 mb-2">
									Selected Medicines:
								</h4>
								<div class="space-y-2">
									<div
										v-for="(medicine, index) in form.medicines"
										:key="index"
										class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg"
									>
										<div class="flex-1">
											<span class="font-medium text-neutral-900">{{
												medicine.name
											}}</span>
										</div>
										<div class="flex items-center space-x-2">
											<label class="text-sm text-neutral-600">Qty:</label>
											<input
												:value="medicine.quantity"
												@input="updateMedicineQuantity(index, $event.target.value)"
												type="number"
												min="1"
												class="w-16 px-2 py-1 text-sm border border-neutral-300 rounded"
											/>
											<button
												type="button"
												@click="removeMedicine(index)"
												class="text-danger hover:text-danger/80"
											>
												<svg
													class="w-4 h-4"
													fill="none"
													stroke="currentColor"
													viewBox="0 0 24 24"
												>
													<path
														stroke-linecap="round"
														stroke-linejoin="round"
														stroke-width="2"
														d="M6 18L18 6M6 6l12 12"
													/>
												</svg>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Equipment Selection -->
					<div>
						<label class="form-label">Medical Equipments</label>
						<div class="space-y-4">
							<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
								<button
									v-for="equipment in medicines.filter(item => item.type === 'Equipment')"
									:key="equipment.id"
									type="button"
									@click="addEquipment(equipment)"
									class="p-3 text-left border border-neutral-200 rounded-lg hover:border-secondary hover:bg-secondary/5 transition-colors"
								>
									<div class="font-medium text-neutral-900">{{ equipment.name }}</div>
									<div class="text-sm text-neutral-500">{{ equipment.type }}</div>
								</button>
							</div>

							<!-- Selected Equipments -->
							<div v-if="form.equipments.length > 0" class="mt-4">
								<h4 class="text-sm font-medium text-neutral-900 mb-2">
									Selected Equipments:
								</h4>
								<div class="space-y-2">
									<div
										v-for="(equipment, index) in form.equipments"
										:key="index"
										class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg"
									>
										<div class="flex-1">
											<span class="font-medium text-neutral-900">{{
												equipment.name
											}}</span>
										</div>
										<div class="flex items-center space-x-2">
											<label class="text-sm text-neutral-600">Qty:</label>
											<input
												:value="equipment.quantity"
												@input="updateEquipmentQuantity(index, $event.target.value)"
												type="number"
												min="1"
												class="w-16 px-2 py-1 text-sm border border-neutral-300 rounded"
											/>
											<button
												type="button"
												@click="removeEquipment(index)"
												class="text-danger hover:text-danger/80"
											>
												<svg
													class="w-4 h-4"
													fill="none"
													stroke="currentColor"
													viewBox="0 0 24 24"
												>
													<path
														stroke-linecap="round"
														stroke-linejoin="round"
														stroke-width="2"
														d="M6 18L18 6M6 6l12 12"
													/>
												</svg>
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="flex justify-end space-x-3 pt-6 border-t border-neutral-200">
						<button type="button" @click="closeAddModal" class="btn-secondary">
							Cancel
						</button>
						<button type="submit" class="btn-primary">Save Record</button>
					</div>
				</form>
			</div>
		</div>

		<!-- View Record Modal -->
		<div v-if="showRecordModal && selectedRecord" class="fixed inset-0 z-50 overflow-y-auto">
			<!-- Overlay -->
			<div class="fixed inset-0 bg-black bg-opacity-50" @click="showRecordModal = false"></div>
			
			<!-- Modal Content -->
			<div class="flex items-center justify-center min-h-screen px-4 py-8">
				<div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
					<div class="p-6">
						<div class="flex justify-between items-center mb-6">
							<h3 class="text-xl font-semibold text-gray-900">EHR Record Details</h3>
							<button
								@click="showRecordModal = false"
								class="text-gray-400 hover:text-gray-600 transition-colors"
							>
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

						<div class="space-y-4">
							<div class="grid grid-cols-2 gap-4">
								<div>
									<label class="block text-sm font-medium text-gray-700">Student ID</label>
									<p class="mt-1 text-sm text-gray-900">{{ selectedRecord.student_id }}</p>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700">Student Name</label>
									<p class="mt-1 text-sm text-gray-900">{{ selectedRecord.student_name }}</p>
								</div>
							</div>

							<div class="grid grid-cols-2 gap-4">
								<div>
									<label class="block text-sm font-medium text-gray-700">Date</label>
									<p class="mt-1 text-sm text-gray-900">{{ new Date(selectedRecord.date).toLocaleDateString() }}</p>
								</div>
								<div>
									<label class="block text-sm font-medium text-gray-700">Visit Type</label>
									<p class="mt-1 text-sm text-gray-900">{{ selectedRecord.visit_type }}</p>
								</div>
							</div>

							<div>
								<label class="block text-sm font-medium text-gray-700">Complaint/Diagnosis</label>
								<p class="mt-1 text-sm text-gray-900">{{ selectedRecord.complaint_diagnosis }}</p>
							</div>

							<div v-if="selectedRecord.medicines && selectedRecord.medicines.length">
								<label class="block text-sm font-medium text-neutral-700">Medicines Prescribed</label>
								<div class="mt-2 space-y-2">
									<div
										v-for="medicine in selectedRecord.medicines"
										:key="typeof medicine === 'string' ? medicine : medicine.name"
										class="flex justify-between items-center p-2 bg-neutral-50 rounded-lg"
									>
										<span class="font-medium text-neutral-900">
											{{ typeof medicine === "string" ? medicine : medicine.name }}
										</span>
										<span
											v-if="typeof medicine === 'object' && medicine.quantity"
											class="text-sm text-neutral-600 bg-white px-2 py-1 rounded border"
										>
											{{ medicine.quantity }} pieces
										</span>
									</div>
								</div>
							</div>

							<div v-if="selectedRecord.equipments && selectedRecord.equipments.length">
								<label class="block text-sm font-medium text-neutral-700">Medical Equipments</label>
								<div class="mt-2 space-y-2">
									<div
										v-for="equipment in selectedRecord.equipments"
										:key="typeof equipment === 'string' ? equipment : equipment.name"
										class="flex justify-between items-center p-2 bg-blue-50 rounded-lg"
									>
										<span class="font-medium text-neutral-900">
											{{ typeof equipment === "string" ? equipment : equipment.name }}
										</span>
										<span
											v-if="typeof equipment === 'object' && equipment.quantity"
											class="text-sm text-neutral-600 bg-white px-2 py-1 rounded border"
										>
											{{ equipment.quantity }} pieces
										</span>
									</div>
								</div>
							</div>

							<div v-if="selectedRecord.nurse_notes">
								<label class="block text-sm font-medium text-gray-700">Nurse Notes</label>
								<p class="mt-1 text-sm text-gray-900">{{ selectedRecord.nurse_notes }}</p>
							</div>

							<div>
								<label class="block text-sm font-medium text-gray-700">Nurse Assigned</label>
								<p class="mt-1 text-sm text-gray-900">{{ selectedRecord.nurse_assigned }}</p>
							</div>

							<div class="flex justify-end space-x-4 mt-6">
								<button
									@click="downloadPDF(selectedRecord)"
									class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
								>
									Download PDF
								</button>
								<button
									@click="showRecordModal = false"
									class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
								>
									Close
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
</template>
