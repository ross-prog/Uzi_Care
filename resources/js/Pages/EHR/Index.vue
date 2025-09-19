<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
	medicines: Array,
	equipment: Array,
});

const searchQuery = ref("");
const searchResults = ref([]);
const isSearching = ref(false);
const hasSearched = ref(false);
const searchSuccessful = ref(false);
const searchInitiated = ref(false);
const selectedRecord = ref(null);
const showRecordModal = ref(false);
const showNurseNotesModal = ref(false);
const showDetailedConsultationModal = ref(false);
const showTimelineModal = ref(false);
const nurseNotes = ref([]);
const consultationAuditLogs = ref([]);
const patientTimeline = ref([]);
const activeDetailTab = ref("vitals");

// Optional sections toggle
const showVitalSigns = ref(false);
const showEquipment = ref(false);

const currentView = ref("dashboard");

const form = reactive({
	// Basic Information
	student_employee_id: "",
	consultation_date_time: new Date().toISOString().slice(0, 16),

	// Personal Information
	last_name: "",
	first_name: "",
	middle_name: "",
	age: null, // Changed from "" to null for number input
	birthdate: "",
	civil_status: "Single",
	sex: "Male",
	address: "",
	department: "",
	department_course: "",
	contact_no: "",

	// Guardian Information
	guardian_name: "",
	guardian_relationship: "",
	guardian_contact_no: "",

	// Chief Complaints
	chief_complaints: "",

	// Medical History
	has_allergy: false,
	allergy_specify: "",
	has_hypertension: false,
	has_diabetes: false,
	has_asthma: false,
	asthma_last_attack: "",
	other_medical_history: "",

	// Vital Signs (Time 1)
	vital_signs_time_1: "",
	weight: "",
	height: "",
	bmi: "",
	last_menstrual_period: "",
	blood_pressure_1: "",
	heart_rate_1: "",
	respiratory_rate_1: "",
	temperature_1: "",
	oxygen_saturation_1: "",

	// Vital Signs (Time 2)
	vital_signs_time_2: "",
	blood_pressure_2: "",
	heart_rate_2: "",
	respiratory_rate_2: "",
	temperature_2: "",
	oxygen_saturation_2: "",

	// Diagnosis and Staff
	diagnosis: "",
	nurse_on_duty: "",
	physician_on_duty: "",

	// Medicines and Equipment
	medicines: [],
	equipment: [],
});

const nurseNoteForm = reactive({
	nurse_notes: "",
	doctor_orders: "",
	entered_by_nurse: "",
	relationship: "",
});

const civilStatusOptions = ["Single", "Married", "Divorced", "Widowed", "Separated"];

const departmentOptions = [
	"SCHOOL OF MEDICINE",
	"SCHOOL OF DENTISTRY",
	"SCHOOL OF CRIMINAL JUSTICE",
	"SCHOOL OF ENGINEERING INFORMATION COMPUTER AND TECHNOLOGY",
	"SCHOOL OF EDUCATION",
	"SCHOOL OF LIBERAL ARTS",
];

// Computed BMI calculation
const calculatedBMI = computed(() => {
	if (form.weight && form.height) {
		const heightInMeters = form.height / 100;
		return (form.weight / (heightInMeters * heightInMeters)).toFixed(2);
	}
	return "";
});

// Computed properties for date validation
const maxBirthdate = computed(() => {
	return new Date().toISOString().split("T")[0]; // Today's date
});

const minBirthdate = computed(() => {
	// 120 years ago
	const date = new Date();
	date.setFullYear(date.getFullYear() - 120);
	return date.toISOString().split("T")[0];
});

const birthdateFormatted = computed(() => {
	if (form.birthdate) {
		const date = new Date(form.birthdate);
		return date.toLocaleDateString("en-US", {
			year: "numeric",
			month: "long",
			day: "numeric",
		});
	}
	return "";
});

const ageFromBirthdate = computed(() => {
	if (form.birthdate) {
		const today = new Date();
		const birthDate = new Date(form.birthdate);
		let age = today.getFullYear() - birthDate.getFullYear();
		const monthDiff = today.getMonth() - birthDate.getMonth();

		if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
			age--;
		}

		return age >= 0 ? age : 0;
	}
	return null;
});

// Function to determine consultation type
const getConsultationType = (record) => {
	// Check if this is a nurse consultation (diagnosis contains "Nurse consultation" or physician_on_duty is null)
	if (record.diagnosis && record.diagnosis.includes("Nurse consultation")) {
		return {
			type: "Nurse Consultation",
			label: "Nurse Consultation",
			class: "bg-green-100 text-green-800",
			badgeClass:
				"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800",
			icon: "🩺",
		};
	}
	// Check if it has vital signs or detailed medical data (indicating full medical consultation)
	if (
		record.vital_signs_time_1 ||
		record.weight ||
		record.height ||
		record.physician_on_duty
	) {
		return {
			type: "Medical Consultation",
			label: "Medical Consultation",
			class: "bg-blue-100 text-blue-800",
			badgeClass:
				"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800",
			icon: "🏥",
		};
	}
	// Default to general consultation
	return {
		type: "General Consultation",
		label: "General Consultation",
		class: "bg-gray-100 text-gray-800",
		badgeClass:
			"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800",
		icon: "📋",
	};
};

// Watch for BMI calculation
watch([() => form.weight, () => form.height], () => {
	form.bmi = calculatedBMI.value;
});

// Watch for birthdate changes and auto-calculate age
watch(
	() => form.birthdate,
	(newBirthdate) => {
		if (newBirthdate) {
			form.age = ageFromBirthdate.value;
		}
	}
);

// Watch for age changes and auto-suggest birthdate year (only if no birthdate set)
watch(
	() => form.age,
	(newAge) => {
		if (newAge && newAge > 0 && !form.birthdate) {
			const currentYear = new Date().getFullYear();
			const estimatedBirthYear = currentYear - newAge;
			// Set suggested birthdate to January 1st of estimated birth year for easy adjustment
			form.birthdate = `${estimatedBirthYear}-01-01`;
		}
	}
);

const searchPatient = async () => {
	if (!searchQuery.value) {
		alert("Please enter a student/employee ID");
		return;
	}

	hasSearched.value = false;
	searchSuccessful.value = false;
	searchResults.value = [];
	searchInitiated.value = true;
	isSearching.value = true;
	currentView.value = "search";

	try {
		const response = await axios.post("/ehr/search", {
			student_employee_id: searchQuery.value,
		});
		searchResults.value = response.data.records || [];
		hasSearched.value = true;
		searchSuccessful.value = true;
	} catch (error) {
		console.error("Search error:", error);
		alert("Error searching for patient records");
		currentView.value = "dashboard";
		searchResults.value = [];
		hasSearched.value = false;
		searchSuccessful.value = false;
		searchInitiated.value = false;
	} finally {
		isSearching.value = false;
	}
};

const openAddModal = () => {
	if (searchQuery.value) {
		form.student_employee_id = searchQuery.value;
	}
	currentView.value = "add";
};

const quickAddRecord = (studentId = "") => {
	resetForm();
	if (studentId) {
		form.student_employee_id = studentId;
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
		student_employee_id: "",
		consultation_date_time: new Date().toISOString().slice(0, 16),
		last_name: "",
		first_name: "",
		middle_name: "",
		age: null, // Changed from "" to null for number input
		birthdate: "",
		civil_status: "Single",
		sex: "Male",
		address: "",
		department: "", // Add missing department field
		department_course: "",
		contact_no: "",
		guardian_name: "",
		guardian_relationship: "",
		guardian_contact_no: "",
		chief_complaints: "",
		has_allergy: false,
		allergy_specify: "",
		has_hypertension: false,
		has_diabetes: false,
		has_asthma: false,
		asthma_last_attack: "",
		other_medical_history: "",
		vital_signs_time_1: "",
		weight: "",
		height: "",
		bmi: "",
		last_menstrual_period: "",
		blood_pressure_1: "",
		heart_rate_1: "",
		respiratory_rate_1: "",
		temperature_1: "",
		oxygen_saturation_1: "",
		vital_signs_time_2: "",
		blood_pressure_2: "",
		heart_rate_2: "",
		respiratory_rate_2: "",
		temperature_2: "",
		oxygen_saturation_2: "",
		diagnosis: "",
		nurse_on_duty: "",
		physician_on_duty: "",
		medicines: [],
		equipment: [],
	});
};

// New medicine and equipment forms
const newMedicine = reactive({
	name: "",
	dosage: "",
	frequency: "",
	duration: "",
});

const newEquipment = reactive({
	name: "",
	purpose: "",
});

const addMedicine = (medicine) => {
	const existingIndex = form.medicines.findIndex((m) => m.name === medicine.name);
	if (existingIndex !== -1) {
		form.medicines[existingIndex].quantity += 1;
	} else {
		form.medicines.push({
			id: medicine.id,
			name: medicine.name,
			quantity: 1,
		});
	}
};

const addEquipment = (equipment) => {
	const existingIndex = form.equipment.findIndex((e) => e.name === equipment.name);
	if (existingIndex !== -1) {
		form.equipment[existingIndex].quantity += 1;
	} else {
		form.equipment.push({
			id: equipment.id,
			name: equipment.name,
			quantity: 1,
		});
	}
};

const removeMedicine = (index) => {
	form.medicines.splice(index, 1);
};

const removeEquipment = (index) => {
	form.equipment.splice(index, 1);
};

const updateMedicineQuantity = (index, quantity) => {
	if (quantity <= 0) {
		removeMedicine(index);
	} else {
		form.medicines[index].quantity = parseInt(quantity);
	}
};

const updateEquipmentQuantity = (index, quantity) => {
	if (quantity <= 0) {
		removeEquipment(index);
	} else {
		form.equipment[index].quantity = parseInt(quantity);
	}
};

// New methods for comprehensive form structure
const addMedicineFromInventory = (medicine) => {
	// Debug: Check medicine object structure
	console.log("Medicine object:", medicine);
	console.log("Dosage strength:", medicine.dosage_strength);
	console.log("Form:", medicine.form);

	// Check if medicine is in stock
	if (medicine.available_quantity <= 0) {
		alert(`${medicine.name} is out of stock and cannot be added.`);
		return;
	}

	// Auto-populate dosage with strength info if available
	let suggestedDosage = "";
	if (medicine.dosage_strength && medicine.form) {
		// Create a comprehensive dosage suggestion
		if (
			medicine.form.toLowerCase().includes("tablet") ||
			medicine.form.toLowerCase().includes("capsule")
		) {
			suggestedDosage = `1 ${medicine.form} (${medicine.dosage_strength})`;
		} else if (
			medicine.form.toLowerCase().includes("syrup") ||
			medicine.form.toLowerCase().includes("liquid")
		) {
			// For liquids, suggest 5ml or extract ml amount from dosage_strength
			const mlMatch = medicine.dosage_strength.match(/(\d+)ml/i);
			if (mlMatch) {
				suggestedDosage = `5ml from ${medicine.dosage_strength} bottle`;
			} else {
				suggestedDosage = `5ml (${medicine.dosage_strength})`;
			}
		} else {
			suggestedDosage = `1 ${medicine.form} (${medicine.dosage_strength})`;
		}
	} else if (medicine.dosage_strength) {
		// If only dosage strength is available
		suggestedDosage = medicine.dosage_strength;
	} else if (medicine.form) {
		// If only form is available
		suggestedDosage = `1 ${medicine.form}`;
	} else {
		// Default suggestion
		suggestedDosage = "1 unit";
	}

	console.log("Suggested dosage:", suggestedDosage);

	form.medicines.push({
		id: medicine.id,
		name: medicine.name,
		quantity: 1,
		dosage: suggestedDosage,
		frequency: "",
		duration: "",
	});
};

const addCustomMedicine = () => {
	if (newMedicine.name) {
		form.medicines.push({
			name: newMedicine.name,
			quantity: 1,
			dosage: newMedicine.dosage || "",
			frequency: newMedicine.frequency || "",
			duration: newMedicine.duration || "",
		});
		// Reset form
		newMedicine.name = "";
		newMedicine.dosage = "";
		newMedicine.frequency = "";
		newMedicine.duration = "";
	}
};

const addEquipmentFromInventory = (equipment) => {
	// Check if equipment is in stock
	if (equipment.available_quantity <= 0) {
		alert(`${equipment.name} is out of stock and cannot be added.`);
		return;
	}

	form.equipment.push({
		name: equipment.name,
		purpose: "",
	});
};

const addCustomEquipment = () => {
	if (newEquipment.name) {
		form.equipment.push({
			name: newEquipment.name,
			purpose: newEquipment.purpose || "",
		});
		// Reset form
		newEquipment.name = "";
		newEquipment.purpose = "";
	}
};

const submitForm = async () => {
	// Debug: Check which fields are empty
	const requiredFields = {
		student_employee_id: form.student_employee_id,
		first_name: form.first_name,
		last_name: form.last_name,
		age: form.age,
		birthdate: form.birthdate,
		address: form.address,
		department: form.department, // Add department field
		department_course: form.department_course,
		contact_no: form.contact_no,
		chief_complaints: form.chief_complaints,
		diagnosis: form.diagnosis,
		nurse_on_duty: form.nurse_on_duty,
	};

	console.log("Form validation check:", requiredFields);

	// Find empty required fields
	const emptyFields = Object.keys(requiredFields).filter((key) => {
		const value = requiredFields[key];
		// Special handling for age field (number)
		if (key === "age") {
			return !value || value <= 0;
		}
		// For other fields, check for empty strings
		return !value || (typeof value === "string" && value.trim() === "");
	});

	if (emptyFields.length > 0) {
		console.log("Empty required fields:", emptyFields);
		console.log("Field values debug:");
		emptyFields.forEach((field) => {
			console.log(
				`${field}:`,
				`"${requiredFields[field]}"`,
				typeof requiredFields[field]
			);
		});

		alert(
			`Please fill in all required fields. Missing: ${emptyFields.join(
				", "
			)}\n\nRequired fields: Student/Employee ID, First Name, Last Name, Age, Birthdate, Address, Department/Course, Contact Number, Chief Complaints, Diagnosis, and Nurse on Duty`
		);
		return;
	}

	try {
		// Check if record exists for this student ID
		const existingRecordsResponse = await axios.post("/ehr/search", {
			student_employee_id: form.student_employee_id,
		});

		const existingRecords = existingRecordsResponse.data.records || [];

		if (existingRecords.length > 0) {
			// Record exists - ask user if they want to add a new consultation
			const shouldAddConsultation = confirm(
				`A record already exists for Student/Employee ID: ${form.student_employee_id}\n\n` +
					`Do you want to add a new consultation for this student?\n\n` +
					`Click OK to add new consultation\n` +
					`Click Cancel to update existing record`
			);

			if (shouldAddConsultation) {
				// Add as new consultation (current behavior)
				await axios.post("/ehr", form);
				alert("New consultation added successfully!");
			} else {
				// Update existing record
				const latestRecord = existingRecords[0]; // Get the most recent record
				await axios.put(`/ehr/${latestRecord.id}`, form);
				alert("Patient record updated successfully!");
			}
		} else {
			// No existing record - create new one
			await axios.post("/ehr", form);
			alert("Patient consultation record created successfully!");
		}

		// Store the student ID before resetting the form
		const submittedStudentId = form.student_employee_id;
		closeAddModal();

		// Check if we should refresh the search results
		if (searchQuery.value === submittedStudentId) {
			searchPatient();
		}
	} catch (error) {
		console.error("Submit error:", error);
		alert("Error creating record: " + (error.response?.data?.message || "Unknown error"));
	}
};

const viewRecord = async (record) => {
	selectedRecord.value = record;
	showRecordModal.value = true;

	// Fetch nurse notes for this record
	try {
		const response = await axios.get(`/ehr/${record.id}/nurse-notes`);
		nurseNotes.value = response.data;
	} catch (error) {
		console.error("Error fetching nurse notes:", error);
		nurseNotes.value = []; // Clear any previous notes on error
	}
};

const openNurseNotes = async (record) => {
	selectedRecord.value = record;
	try {
		const response = await axios.get(`/ehr/${record.id}/nurse-notes`);
		nurseNotes.value = response.data;
		showNurseNotesModal.value = true;
	} catch (error) {
		console.error("Error fetching nurse notes:", error);
		alert("Error loading nurse notes");
	}
};

const addNurseNote = async () => {
	if (!nurseNoteForm.nurse_notes || !nurseNoteForm.entered_by_nurse) {
		alert("Please fill in required fields");
		return;
	}

	try {
		// Create a new consultation record instead of a nurse note
		const consultationData = {
			// Copy basic info from the selected record
			student_employee_id: selectedRecord.value.student_employee_id,
			consultation_date_time: new Date().toISOString(),
			first_name: selectedRecord.value.first_name,
			last_name: selectedRecord.value.last_name,
			middle_name: selectedRecord.value.middle_name,
			age: selectedRecord.value.age,
			birthdate: selectedRecord.value.birthdate,
			civil_status: selectedRecord.value.civil_status,
			sex: selectedRecord.value.sex,
			address: selectedRecord.value.address,
			department_course: selectedRecord.value.department_course,
			contact_no: selectedRecord.value.contact_no,
			guardian_name: selectedRecord.value.guardian_name,
			guardian_relationship: selectedRecord.value.guardian_relationship,
			guardian_contact_no: selectedRecord.value.guardian_contact_no,

			// Medical history from previous record
			has_allergy: selectedRecord.value.has_allergy,
			allergy_specify: selectedRecord.value.allergy_specify,
			has_hypertension: selectedRecord.value.has_hypertension,
			has_diabetes: selectedRecord.value.has_diabetes,
			has_asthma: selectedRecord.value.has_asthma,
			asthma_last_attack: selectedRecord.value.asthma_last_attack,
			other_medical_history: selectedRecord.value.other_medical_history,

			// New nurse consultation data
			chief_complaints: nurseNoteForm.nurse_notes,
			diagnosis: nurseNoteForm.doctor_orders || "Nurse consultation - follow-up care",
			nurse_on_duty: nurseNoteForm.entered_by_nurse,
			physician_on_duty: null,

			// Empty vital signs for nurse consultation
			vital_signs_time_1: null,
			weight: null,
			height: null,
			last_menstrual_period: null,
			blood_pressure_1: null,
			heart_rate_1: null,
			respiratory_rate_1: null,
			temperature_1: null,
			oxygen_saturation_1: null,
			vital_signs_time_2: null,
			blood_pressure_2: null,
			heart_rate_2: null,
			respiratory_rate_2: null,
			temperature_2: null,
			oxygen_saturation_2: null,

			// Empty medicines and equipment
			medicines: [],
			equipment: [],
		};

		await axios.post("/ehr", consultationData);
		alert("Nurse consultation added successfully!");

		// Reset form
		Object.assign(nurseNoteForm, {
			nurse_notes: "",
			doctor_orders: "",
			entered_by_nurse: "",
			relationship: "",
		});

		// Close modal and refresh search if needed
		showNurseNotesModal.value = false;
		if (searchQuery.value === selectedRecord.value.student_employee_id) {
			searchPatient();
		}
	} catch (error) {
		console.error("Error adding nurse consultation:", error);
		alert("Error adding nurse consultation");
	}
};

const downloadPDF = async (record) => {
	try {
		const response = await axios.get(`/ehr/${record.id}/pdf`, {
			responseType: "blob",
		});

		const url = window.URL.createObjectURL(new Blob([response.data]));
		const link = document.createElement("a");
		link.href = url;
		link.setAttribute(
			"download",
			`ehr-record-${record.student_employee_id}-${
				new Date(record.consultation_date_time).toISOString().split("T")[0]
			}.pdf`
		);
		document.body.appendChild(link);
		link.click();
		link.remove();
		window.URL.revokeObjectURL(url);
	} catch (error) {
		console.error("PDF download error:", error);
		alert("Error downloading PDF");
	}
};

const downloadNurseNotesPDF = async (record) => {
	try {
		const response = await axios.get(`/ehr/${record.id}/nurse-notes-pdf`, {
			responseType: "blob",
		});

		const url = window.URL.createObjectURL(new Blob([response.data]));
		const link = document.createElement("a");
		link.href = url;
		link.setAttribute(
			"download",
			`nurse-notes-${record.student_employee_id}-${
				new Date(record.consultation_date_time).toISOString().split("T")[0]
			}.pdf`
		);
		document.body.appendChild(link);
		link.click();
		link.remove();
		window.URL.revokeObjectURL(url);
	} catch (error) {
		console.error("PDF download error:", error);
		alert("Error downloading PDF");
	}
};

const openDetailedConsultationView = async (record) => {
	selectedRecord.value = record;
	activeDetailTab.value = "vitals";

	try {
		// Fetch audit logs for this consultation
		const auditResponse = await axios.get(`/ehr/${record.id}/audit-logs`);
		consultationAuditLogs.value = auditResponse.data;

		// Fetch related nurse notes
		const notesResponse = await axios.get(`/ehr/${record.id}/nurse-notes`);
		nurseNotes.value = notesResponse.data;

		showDetailedConsultationModal.value = true;
	} catch (error) {
		console.error("Error fetching consultation details:", error);
		alert("Error loading consultation details");
	}
};

const openPatientTimeline = async (record) => {
	selectedRecord.value = record;

	try {
		// Fetch all consultations for this patient ID
		const timelineResponse = await axios.get(
			`/ehr/timeline/${record.student_employee_id}`
		);
		patientTimeline.value = timelineResponse.data.sort(
			(a, b) => new Date(b.consultation_date_time) - new Date(a.consultation_date_time)
		);

		showTimelineModal.value = true;
	} catch (error) {
		console.error("Error fetching patient timeline:", error);
		alert("Error loading patient timeline");
	}
};

const goToDashboard = () => {
	currentView.value = "dashboard";
	searchQuery.value = "";
	searchResults.value = [];
	hasSearched.value = false;
	searchSuccessful.value = false;
	searchInitiated.value = false;
};

// Helper functions for date/time formatting
const formatDateTime = (dateTime) => {
	if (!dateTime) return "";
	const date = new Date(dateTime);
	return date.toLocaleString("en-US", {
		year: "numeric",
		month: "2-digit",
		day: "2-digit",
		hour: "2-digit",
		minute: "2-digit",
		hour12: true,
	});
};

const formatDate = (date) => {
	if (!date) return "";
	const d = new Date(date);
	return d.toLocaleDateString("en-US", {
		year: "numeric",
		month: "2-digit",
		day: "2-digit",
	});
};

const formatTime = (time) => {
	if (!time) return "";
	// If it's already a time string (HH:mm), return it
	if (typeof time === "string" && time.includes(":")) {
		return time;
	}
	// If it's a date/datetime, extract the time portion
	const date = new Date(time);
	return date.toLocaleTimeString("en-US", {
		hour: "2-digit",
		minute: "2-digit",
		hour12: true,
	});
};
</script>

<template>
	<Head title="Electronic Health Records (EHR)" />

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
							<span class="ml-4 text-sm font-medium text-primary"
								>Add New EHR Record</span
							>
						</div>
					</li>
				</ol>
			</nav>
		</div>

		<div class="page-header">
			<div class="flex justify-between items-center">
				<div>
					<h1 class="text-3xl font-bold text-neutral-900">
						<span v-if="currentView === 'dashboard'"
							>Electronic Health Records (EHR)</span
						>
						<span v-else-if="currentView === 'search'">Search Results</span>
						<span v-else-if="currentView === 'add'">Add New EHR Record</span>
					</h1>
					<p class="mt-2 text-neutral-600">
						<span v-if="currentView === 'dashboard'"
							>Comprehensive patient consultation records and medical documentation</span
						>
						<span v-else-if="currentView === 'search'"
							>Found {{ searchResults.length }} record(s) for ID: {{ searchQuery }}</span
						>
						<span v-else-if="currentView === 'add'"
							>Create a comprehensive patient consultation record</span
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
						Search for existing patient consultation records by Student/Employee ID
					</p>
				</div>
				<div class="p-6">
					<div class="flex space-x-4">
						<div class="flex-1">
							<label for="search" class="form-label">Student/Employee ID</label>
							<input
								id="search"
								v-model="searchQuery"
								type="text"
								placeholder="Enter student or employee ID"
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

		<!-- Search Results View -->
		<div
			v-if="currentView === 'search' && searchInitiated && (isSearching || hasSearched)"
		>
			<!-- Loading state -->
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

			<!-- Results -->
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
				<div class="overflow-x-auto">
					<table class="min-w-full divide-y divide-neutral-200">
						<thead class="bg-neutral-50">
							<tr>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Date & Time
								</th>
								<th
									class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Type
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Patient
								</th>
								<th
									class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider hidden md:table-cell"
								>
									Chief Complaints
								</th>
								<th
									class="px-4 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider hidden lg:table-cell"
								>
									Nurse on Duty
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider min-w-[280px]"
								>
									Actions
								</th>
							</tr>
						</thead>
						<tbody class="bg-white divide-y divide-neutral-200">
							<tr v-for="record in searchResults" :key="record.id">
								<td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
									<div class="text-sm">
										{{ new Date(record.consultation_date_time).toLocaleString() }}
									</div>
								</td>
								<td class="px-4 py-4 whitespace-nowrap">
									<span
										:class="getConsultationType(record).class"
										class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
									>
										{{ getConsultationType(record).icon }}
										{{ getConsultationType(record).type }}
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div>
										<div class="text-sm font-medium text-neutral-900">
											{{ record.first_name }} {{ record.middle_name }}
											{{ record.last_name }}
										</div>
										<div class="text-sm text-neutral-500">
											{{ record.student_employee_id }}
										</div>
									</div>
								</td>
								<td class="px-4 py-4 text-sm text-neutral-900 hidden md:table-cell">
									<div class="max-w-xs truncate">{{ record.chief_complaints }}</div>
								</td>
								<td
									class="px-4 py-4 whitespace-nowrap text-sm text-neutral-900 hidden lg:table-cell"
								>
									{{ record.nurse_on_duty }}
								</td>
								<td class="px-6 py-4 text-sm font-medium min-w-[280px]">
									<div class="flex flex-wrap gap-1 items-center">
										<button
											@click="viewRecord(record)"
											class="text-xs px-2 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded font-medium"
										>
											View
										</button>
										<button
											@click="openDetailedConsultationView(record)"
											class="text-xs px-2 py-1 bg-purple-50 text-purple-600 hover:bg-purple-100 rounded font-medium"
										>
											Details
										</button>
										<button
											@click="openPatientTimeline(record)"
											class="text-xs px-2 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded font-medium"
										>
											Timeline
										</button>
										<button
											@click="openNurseNotes(record)"
											class="text-xs px-2 py-1 bg-green-50 text-green-600 hover:bg-green-100 rounded font-medium"
										>
											+ Note
										</button>
										<button
											@click="downloadPDF(record)"
											class="text-xs px-2 py-1 bg-gray-50 text-gray-600 hover:bg-gray-100 rounded font-medium"
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

			<!-- No results -->
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
						No existing records found for ID: {{ searchQuery }}
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
		<div v-if="currentView === 'add'" class="space-y-6">
			<!-- Basic Information Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Basic Information</h2>
					<p class="text-sm text-neutral-500 mt-1">
						Patient consultation record details (FO-UHS-032)
					</p>
				</div>
				<div class="p-6 space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div>
							<label class="form-label">Student/Employee ID * (10 digits)</label>
							<input
								v-model="form.student_employee_id"
								type="text"
								maxlength="10"
								class="form-input"
								required
							/>
						</div>
						<div>
							<label class="form-label">Consultation Date and Time *</label>
							<input
								v-model="form.consultation_date_time"
								type="datetime-local"
								class="form-input"
								required
							/>
						</div>
					</div>
				</div>
			</div>

			<!-- Personal Information Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Personal Information</h2>
				</div>
				<div class="p-6 space-y-6">
					<!-- Name Fields -->
					<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<div>
							<label class="form-label">Last Name *</label>
							<input
								v-model="form.last_name"
								type="text"
								class="form-input"
								required
								placeholder="Enter last name"
							/>
						</div>
						<div>
							<label class="form-label">First Name *</label>
							<input
								v-model="form.first_name"
								type="text"
								class="form-input"
								required
								placeholder="Enter first name"
							/>
						</div>
						<div>
							<label class="form-label">Middle Name</label>
							<input
								v-model="form.middle_name"
								type="text"
								class="form-input"
								placeholder="Enter middle name (optional)"
							/>
						</div>
					</div>

					<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
						<div>
							<label class="form-label">Age *</label>
							<input
								v-model="form.age"
								type="number"
								min="0"
								max="150"
								class="form-input"
								required
								placeholder="Enter age"
								:class="{ 'bg-green-50 border-green-300': ageFromBirthdate !== null }"
							/>
							<div class="mt-1">
								<p v-if="ageFromBirthdate !== null" class="text-xs text-green-600">
									✓ Auto-calculated from birthdate
								</p>
								<p v-else class="text-xs text-gray-500">
									Age will auto-calculate from birthdate
								</p>
							</div>
						</div>
						<div>
							<label class="form-label">Birthdate *</label>
							<div class="relative">
								<input
									v-model="form.birthdate"
									type="date"
									class="form-input pl-10 pr-4"
									required
									:max="maxBirthdate"
									:min="minBirthdate"
									placeholder="yyyy-mm-dd"
								/>
								<div
									class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
								>
									<svg
										class="h-5 w-5 text-gray-400"
										fill="none"
										viewBox="0 0 24 24"
										stroke="currentColor"
									>
										<path
											stroke-linecap="round"
											stroke-linejoin="round"
											stroke-width="2"
											d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
										/>
									</svg>
								</div>
							</div>
							<div class="mt-1 space-y-1">
								<p v-if="birthdateFormatted" class="text-sm text-green-600">
									📅 {{ birthdateFormatted }}
								</p>
								<p v-if="ageFromBirthdate !== null" class="text-sm text-blue-600">
									🎂 Age: {{ ageFromBirthdate }} years old
								</p>
								<p v-if="!form.birthdate" class="text-xs text-gray-500">
									Select birthdate to auto-calculate age
								</p>
							</div>
						</div>
						<div>
							<label class="form-label">Civil Status</label>
							<select v-model="form.civil_status" class="form-input">
								<option
									v-for="status in civilStatusOptions"
									:key="status"
									:value="status"
								>
									{{ status }}
								</option>
							</select>
						</div>
						<div>
							<label class="form-label">Sex</label>
							<select v-model="form.sex" class="form-input">
								<option value="Male">Male</option>
								<option value="Female">Female</option>
							</select>
						</div>
					</div>
					<div>
						<label class="form-label">Address *</label>
						<textarea
							v-model="form.address"
							rows="2"
							class="form-input"
							required
						></textarea>
					</div>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<div>
							<label class="form-label">Department</label>
							<select v-model="form.department" class="form-input">
								<option value="">Select Department</option>
								<option
									v-for="department in departmentOptions"
									:key="department"
									:value="department"
								>
									{{ department }}
								</option>
							</select>
						</div>
						<div>
							<label class="form-label">Course/Program</label>
							<input
								v-model="form.department_course"
								type="text"
								class="form-input"
								placeholder="e.g., Bachelor of Science in Computer Science"
							/>
						</div>
						<div>
							<label class="form-label">Contact No. *</label>
							<input v-model="form.contact_no" type="text" class="form-input" required />
						</div>
					</div>
				</div>
			</div>

			<!-- Guardian Information Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Guardian Information</h2>
				</div>
				<div class="p-6 space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<div>
							<label class="form-label">Name of Guardian</label>
							<input v-model="form.guardian_name" type="text" class="form-input" />
						</div>
						<div>
							<label class="form-label">Relationship</label>
							<input
								v-model="form.guardian_relationship"
								type="text"
								class="form-input"
							/>
						</div>
						<div>
							<label class="form-label">Guardian Contact No.</label>
							<input v-model="form.guardian_contact_no" type="text" class="form-input" />
						</div>
					</div>
				</div>
			</div>

			<!-- Chief Complaints Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">
						Chief Complaints / Reasons for Consultation
					</h2>
				</div>
				<div class="p-6">
					<textarea
						v-model="form.chief_complaints"
						rows="4"
						class="form-input"
						placeholder="Describe the chief complaints or reasons for consultation"
						required
					></textarea>
				</div>
			</div>

			<!-- Medical History Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Medical History</h2>
				</div>
				<div class="p-6 space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<input
									v-model="form.has_allergy"
									type="checkbox"
									id="allergy"
									class="form-checkbox"
								/>
								<label for="allergy" class="form-label">Allergy</label>
							</div>
							<div v-if="form.has_allergy">
								<input
									v-model="form.allergy_specify"
									type="text"
									placeholder="Specify allergy (e.g., Penicillin, shellfish)"
									class="form-input"
								/>
							</div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<input
									v-model="form.has_hypertension"
									type="checkbox"
									id="hypertension"
									class="form-checkbox"
								/>
								<label for="hypertension" class="form-label">Hypertension</label>
							</div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<input
									v-model="form.has_diabetes"
									type="checkbox"
									id="diabetes"
									class="form-checkbox"
								/>
								<label for="diabetes" class="form-label">Diabetes</label>
							</div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<input
									v-model="form.has_asthma"
									type="checkbox"
									id="asthma"
									class="form-checkbox"
								/>
								<label for="asthma" class="form-label">Asthma</label>
							</div>
							<div v-if="form.has_asthma">
								<label class="form-label">Last Attack Date</label>
								<input v-model="form.asthma_last_attack" type="date" class="form-input" />
							</div>
						</div>
					</div>
					<div>
						<label class="form-label">Other Medical History</label>
						<textarea
							v-model="form.other_medical_history"
							rows="3"
							class="form-input"
							placeholder="Other relevant medical history, surgeries, hospitalizations, etc."
						></textarea>
					</div>
				</div>
			</div>

			<!-- Vital Signs Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<div class="flex items-center justify-between">
						<h2 class="text-lg font-medium text-neutral-900">
							Vital Signs & Physical Measurements
						</h2>
						<button
							type="button"
							@click="showVitalSigns = !showVitalSigns"
							class="text-sm text-blue-600 hover:text-blue-800 font-medium"
						>
							{{ showVitalSigns ? "Hide" : "Add Vital Signs" }}
						</button>
					</div>
					<p class="text-sm text-neutral-500 mt-1">
						Optional: Record patient's vital signs and physical measurements
					</p>
				</div>
				<div v-show="showVitalSigns" class="p-6 space-y-6">
					<!-- Basic Measurements -->
					<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
						<div>
							<label class="form-label">Weight (kg)</label>
							<input v-model="form.weight" type="number" step="0.1" class="form-input" />
						</div>
						<div>
							<label class="form-label">Height (cm)</label>
							<input v-model="form.height" type="number" step="0.1" class="form-input" />
						</div>
						<div>
							<label class="form-label">BMI (auto-calculated)</label>
							<input
								:value="calculatedBMI"
								type="text"
								class="form-input bg-gray-50"
								readonly
							/>
						</div>
						<div v-if="form.sex === 'Female'">
							<label class="form-label">Last Menstrual Period</label>
							<input
								v-model="form.last_menstrual_period"
								type="date"
								class="form-input"
							/>
						</div>
					</div>

					<!-- Time 1 Vital Signs -->
					<div class="border border-gray-200 rounded-lg p-4">
						<h3 class="text-md font-medium text-neutral-900 mb-4">Time 1 Measurements</h3>
						<div class="grid grid-cols-1 md:grid-cols-6 gap-4">
							<div>
								<label class="form-label">Time</label>
								<input v-model="form.vital_signs_time_1" type="time" class="form-input" />
							</div>
							<div>
								<label class="form-label">Blood Pressure</label>
								<input
									v-model="form.blood_pressure_1"
									type="text"
									placeholder="120/80"
									class="form-input"
								/>
							</div>
							<div>
								<label class="form-label">Heart Rate (bpm)</label>
								<input v-model="form.heart_rate_1" type="number" class="form-input" />
							</div>
							<div>
								<label class="form-label">Respiratory Rate</label>
								<input
									v-model="form.respiratory_rate_1"
									type="number"
									class="form-input"
								/>
							</div>
							<div>
								<label class="form-label">Temperature (°C)</label>
								<input
									v-model="form.temperature_1"
									type="number"
									step="0.1"
									class="form-input"
								/>
							</div>
							<div>
								<label class="form-label">O2 Saturation (%)</label>
								<input
									v-model="form.oxygen_saturation_1"
									type="number"
									min="0"
									max="100"
									class="form-input"
								/>
							</div>
						</div>
					</div>

					<!-- Time 2 Vital Signs -->
					<div class="border border-gray-200 rounded-lg p-4">
						<h3 class="text-md font-medium text-neutral-900 mb-4">
							Time 2 Measurements (Optional)
						</h3>
						<div class="grid grid-cols-1 md:grid-cols-6 gap-4">
							<div>
								<label class="form-label">Time</label>
								<input v-model="form.vital_signs_time_2" type="time" class="form-input" />
							</div>
							<div>
								<label class="form-label">Blood Pressure</label>
								<input
									v-model="form.blood_pressure_2"
									type="text"
									placeholder="120/80"
									class="form-input"
								/>
							</div>
							<div>
								<label class="form-label">Heart Rate (bpm)</label>
								<input v-model="form.heart_rate_2" type="number" class="form-input" />
							</div>
							<div>
								<label class="form-label">Respiratory Rate</label>
								<input
									v-model="form.respiratory_rate_2"
									type="number"
									class="form-input"
								/>
							</div>
							<div>
								<label class="form-label">Temperature (°C)</label>
								<input
									v-model="form.temperature_2"
									type="number"
									step="0.1"
									class="form-input"
								/>
							</div>
							<div>
								<label class="form-label">O2 Saturation (%)</label>
								<input
									v-model="form.oxygen_saturation_2"
									type="number"
									min="0"
									max="100"
									class="form-input"
								/>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Diagnosis Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Diagnosis</h2>
				</div>
				<div class="p-6">
					<textarea
						v-model="form.diagnosis"
						rows="4"
						class="form-input"
						placeholder="Enter clinical diagnosis, assessment, and treatment plan"
						required
					></textarea>
				</div>
			</div>

			<!-- Staff Information Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">
						Healthcare Staff Information
					</h2>
				</div>
				<div class="p-6 space-y-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div>
							<label class="form-label">Nurse on Duty *</label>
							<input
								v-model="form.nurse_on_duty"
								type="text"
								placeholder="e.g., Nurse Jane Doe, RN"
								class="form-input"
								required
							/>
						</div>
						<div>
							<label class="form-label">Physician on Duty</label>
							<input
								v-model="form.physician_on_duty"
								type="text"
								placeholder="e.g., Dr. John Smith, MD"
								class="form-input"
							/>
						</div>
					</div>
				</div>
			</div>

			<!-- Medicines Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h2 class="text-lg font-medium text-neutral-900">Prescribed Medicines</h2>
					<p class="text-sm text-neutral-500 mt-1">
						Add medicines with dosage, frequency, and duration details
					</p>
				</div>
				<div class="p-6 space-y-4">
					<!-- Quick Add from Available Medicines -->
					<div v-if="medicines && medicines.length > 0">
						<h4 class="text-sm font-medium text-neutral-900 mb-2">
							Quick Add from Inventory:
						</h4>
						<p class="text-xs text-neutral-600 mb-3">
							Click any medicine below to add it to the prescription. Dosage will be
							automatically filled based on medicine strength.
						</p>
						<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
							<button
								v-for="medicine in medicines"
								:key="medicine.id"
								type="button"
								@click="addMedicineFromInventory(medicine)"
								:disabled="medicine.available_quantity <= 0"
								:class="[
									'p-3 text-left border rounded-lg transition-colors',
									medicine.available_quantity <= 0
										? 'border-red-200 bg-red-50 cursor-not-allowed opacity-60'
										: 'border-neutral-200 hover:border-primary hover:bg-primary/5',
								]"
							>
								<div class="font-medium text-neutral-900">{{ medicine.name }}</div>
								<div class="text-sm text-neutral-500">
									{{ medicine.type }}
									<span v-if="medicine.dosage_strength">
										• {{ medicine.dosage_strength }}</span
									>
									<span v-if="medicine.form"> • {{ medicine.form }}</span>
								</div>
								<div
									v-if="medicine.dosage_strength || medicine.form"
									class="text-xs text-blue-600 mt-1"
								>
									Dosage will auto-fill
								</div>
								<div
									class="text-xs mt-1"
									:class="
										medicine.available_quantity <= 0 ? 'text-red-600' : 'text-green-600'
									"
								>
									Stock: {{ medicine.available_quantity || 0 }}
									<span v-if="medicine.available_quantity <= 0" class="font-medium"
										>(Out of Stock)</span
									>
								</div>
							</button>
						</div>
					</div>

					<!-- Manual Add Medicine -->
					<div class="border-t border-gray-200 pt-4">
						<h4 class="text-sm font-medium text-neutral-900 mb-3">
							Add Medicine Manually:
						</h4>
						<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-3">
							<input
								v-model="newMedicine.name"
								type="text"
								placeholder="Medicine name"
								class="form-input"
							/>
							<input
								v-model="newMedicine.dosage"
								type="text"
								placeholder="Dosage prescribed (e.g., 1 tablet, 5ml)"
								class="form-input"
							/>
							<input
								v-model="newMedicine.frequency"
								type="text"
								placeholder="Frequency (e.g., 3x daily)"
								class="form-input"
							/>
							<input
								v-model="newMedicine.duration"
								type="text"
								placeholder="Duration (e.g., 5 days)"
								class="form-input"
							/>
						</div>
						<button
							@click="addCustomMedicine"
							type="button"
							class="btn-secondary text-sm"
						>
							Add Medicine
						</button>
					</div>

					<!-- Selected Medicines List -->
					<div v-if="form.medicines.length > 0" class="mt-4">
						<h4 class="text-sm font-medium text-neutral-900 mb-3">
							Prescribed Medicines:
						</h4>
						<div class="space-y-3">
							<div
								v-for="(medicine, index) in form.medicines"
								:key="index"
								class="flex items-start justify-between p-4 bg-green-50 rounded-lg border border-green-200"
							>
								<div class="flex-1 grid grid-cols-1 md:grid-cols-5 gap-3">
									<div>
										<label class="text-xs text-gray-600">Name:</label>
										<div class="font-medium text-neutral-900">{{ medicine.name }}</div>
									</div>
									<div>
										<label class="text-xs text-gray-600">Quantity:</label>
										<input
											v-model.number="medicine.quantity"
											type="number"
											min="1"
											class="form-input text-sm mt-1"
										/>
									</div>
									<div>
										<label class="text-xs text-gray-600">Dosage:</label>
										<input
											v-model="medicine.dosage"
											type="text"
											class="form-input text-sm mt-1"
										/>
									</div>
									<div>
										<label class="text-xs text-gray-600">Frequency:</label>
										<input
											v-model="medicine.frequency"
											type="text"
											class="form-input text-sm mt-1"
										/>
									</div>
									<div>
										<label class="text-xs text-gray-600">Duration:</label>
										<input
											v-model="medicine.duration"
											type="text"
											class="form-input text-sm mt-1"
										/>
									</div>
								</div>
								<button
									type="button"
									@click="removeMedicine(index)"
									class="ml-3 text-danger hover:text-danger/80"
								>
									<svg
										class="w-5 h-5"
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

			<!-- Equipment Card -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<div class="flex items-center justify-between">
						<h2 class="text-lg font-medium text-neutral-900">
							Medical Equipment/Supplies Used
						</h2>
						<button
							type="button"
							@click="showEquipment = !showEquipment"
							class="text-sm text-blue-600 hover:text-blue-800 font-medium"
						>
							{{ showEquipment ? "Hide" : "Add Equipment" }}
						</button>
					</div>
					<p class="text-sm text-neutral-500 mt-1">
						Optional: Record medical equipment and supplies utilized during consultation
					</p>
				</div>
				<div v-show="showEquipment" class="p-6 space-y-4">
					<!-- Quick Add from Available Equipment -->
					<div v-if="equipment && equipment.length > 0">
						<h4 class="text-sm font-medium text-neutral-900 mb-3">
							Quick Add from Inventory:
						</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
							<button
								v-for="equipmentItem in equipment"
								:key="equipmentItem.id"
								type="button"
								@click="addEquipmentFromInventory(equipmentItem)"
								:disabled="equipmentItem.available_quantity <= 0"
								:class="[
									'p-3 text-left border rounded-lg transition-colors',
									equipmentItem.available_quantity <= 0
										? 'border-red-200 bg-red-50 cursor-not-allowed opacity-60'
										: 'border-neutral-200 hover:border-info hover:bg-info/5',
								]"
							>
								<div class="font-medium text-neutral-900">{{ equipmentItem.name }}</div>
								<div class="text-sm text-neutral-500">{{ equipmentItem.type }}</div>
								<div
									class="text-xs"
									:class="
										equipmentItem.available_quantity <= 0
											? 'text-red-600'
											: 'text-green-600'
									"
								>
									Stock: {{ equipmentItem.available_quantity || 0 }}
									<span v-if="equipmentItem.available_quantity <= 0" class="font-medium"
										>(Out of Stock)</span
									>
								</div>
							</button>
						</div>
					</div>

					<!-- Manual Add Equipment -->
					<div class="border-t border-gray-200 pt-4">
						<h4 class="text-sm font-medium text-neutral-900 mb-3">
							Add Equipment Manually:
						</h4>
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
							<input
								v-model="newEquipment.name"
								type="text"
								placeholder="Equipment/Supply name"
								class="form-input"
							/>
							<input
								v-model="newEquipment.purpose"
								type="text"
								placeholder="Purpose/Use (e.g., Blood pressure monitoring)"
								class="form-input"
							/>
						</div>
						<button
							@click="addCustomEquipment"
							type="button"
							class="btn-secondary text-sm"
						>
							Add Equipment
						</button>
					</div>

					<!-- Selected Equipment List -->
					<div v-if="form.equipment.length > 0" class="mt-4">
						<h4 class="text-sm font-medium text-neutral-900 mb-3">
							Equipment/Supplies Used:
						</h4>
						<div class="space-y-3">
							<div
								v-for="(equipmentItem, index) in form.equipment"
								:key="index"
								class="flex items-start justify-between p-4 bg-blue-50 rounded-lg border border-blue-200"
							>
								<div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
									<div>
										<label class="text-xs text-gray-600">Equipment/Supply:</label>
										<div class="font-medium text-neutral-900">
											{{ equipmentItem.name }}
										</div>
									</div>
									<div>
										<label class="text-xs text-gray-600">Purpose:</label>
										<input
											v-model="equipmentItem.purpose"
											type="text"
											class="form-input text-sm mt-1"
										/>
									</div>
								</div>
								<button
									type="button"
									@click="removeEquipment(index)"
									class="ml-3 text-danger hover:text-danger/80"
								>
									<svg
										class="w-5 h-5"
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

			<!-- Submit Button -->
			<div class="flex justify-end space-x-3 pt-6">
				<button type="button" @click="closeAddModal" class="btn-secondary">Cancel</button>
				<button @click="submitForm" class="btn-primary">
					Save Patient Consultation Record
				</button>
			</div>
		</div>

		<!-- View Record Modal -->
		<div
			v-if="showRecordModal && selectedRecord"
			class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
		>
			<div
				class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto"
			>
				<div class="mt-3">
					<div class="flex justify-between items-center mb-4">
						<h3 class="text-lg font-medium text-gray-900">
							Patient Consultation Record Details
						</h3>
						<button
							@click="showRecordModal = false"
							class="text-gray-400 hover:text-gray-600"
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

					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<!-- Left Column -->
						<div class="space-y-4">
							<div>
								<h4 class="font-medium text-gray-900 mb-2">Basic Information</h4>
								<div class="text-sm space-y-1">
									<p>
										<span class="font-medium">ID:</span>
										{{ selectedRecord.student_employee_id }}
									</p>
									<p>
										<span class="font-medium">Date & Time:</span>
										{{ new Date(selectedRecord.consultation_date_time).toLocaleString() }}
									</p>
								</div>
							</div>

							<div>
								<h4 class="font-medium text-gray-900 mb-2">Personal Information</h4>
								<div class="text-sm space-y-1">
									<p>
										<span class="font-medium">Name:</span>
										{{ selectedRecord.first_name }} {{ selectedRecord.middle_name }}
										{{ selectedRecord.last_name }}
									</p>
									<p><span class="font-medium">Age:</span> {{ selectedRecord.age }}</p>
									<p><span class="font-medium">Sex:</span> {{ selectedRecord.sex }}</p>
									<p>
										<span class="font-medium">Civil Status:</span>
										{{ selectedRecord.civil_status }}
									</p>
									<p>
										<span class="font-medium">Department/Course:</span>
										{{ selectedRecord.department_course }}
									</p>
									<p>
										<span class="font-medium">Contact:</span>
										{{ selectedRecord.contact_no }}
									</p>
								</div>
							</div>

							<div v-if="selectedRecord.guardian_name">
								<h4 class="font-medium text-gray-900 mb-2">Guardian Information</h4>
								<div class="text-sm space-y-1">
									<p>
										<span class="font-medium">Name:</span>
										{{ selectedRecord.guardian_name }}
									</p>
									<p>
										<span class="font-medium">Relationship:</span>
										{{ selectedRecord.guardian_relationship }}
									</p>
									<p>
										<span class="font-medium">Contact:</span>
										{{ selectedRecord.guardian_contact_no }}
									</p>
								</div>
							</div>
						</div>

						<!-- Right Column -->
						<div class="space-y-4">
							<div>
								<h4 class="font-medium text-gray-900 mb-2">Chief Complaints</h4>
								<p class="text-sm">{{ selectedRecord.chief_complaints }}</p>
							</div>

							<div>
								<h4 class="font-medium text-gray-900 mb-2">Diagnosis</h4>
								<p class="text-sm">{{ selectedRecord.diagnosis }}</p>
							</div>

							<div>
								<h4 class="font-medium text-gray-900 mb-2">Staff</h4>
								<div class="text-sm space-y-1">
									<p>
										<span class="font-medium">Nurse on Duty:</span>
										{{ selectedRecord.nurse_on_duty }}
									</p>
									<p v-if="selectedRecord.physician_on_duty">
										<span class="font-medium">Physician on Duty:</span>
										{{ selectedRecord.physician_on_duty }}
									</p>
								</div>
							</div>
						</div>
					</div>

					<div class="mt-6 space-y-4">
						<!-- Medicines -->
						<div v-if="selectedRecord.medicines && selectedRecord.medicines.length">
							<h4 class="font-medium text-gray-900 mb-2">Medicines Prescribed</h4>
							<div class="grid grid-cols-1 gap-2">
								<div
									v-for="medicine in selectedRecord.medicines"
									:key="medicine.name"
									class="p-3 bg-green-50 rounded-lg border border-green-200"
								>
									<div class="flex justify-between items-start">
										<div class="flex-1">
											<div class="font-medium text-gray-900">{{ medicine.name }}</div>
											<div v-if="medicine.dosage" class="text-sm text-gray-600 mt-1">
												<span class="font-medium">Dosage:</span> {{ medicine.dosage }}
											</div>
											<div v-if="medicine.frequency" class="text-sm text-gray-600">
												<span class="font-medium">Frequency:</span>
												{{ medicine.frequency }}
											</div>
											<div v-if="medicine.duration" class="text-sm text-gray-600">
												<span class="font-medium">Duration:</span> {{ medicine.duration }}
											</div>
										</div>
										<span
											class="text-sm text-gray-600 bg-white px-2 py-1 rounded border font-medium"
											>Qty: {{ medicine.quantity || 1 }}</span
										>
									</div>
								</div>
							</div>
						</div>

						<!-- Equipment -->
						<div v-if="selectedRecord.equipment && selectedRecord.equipment.length">
							<h4 class="font-medium text-gray-900 mb-2">Equipment/Supplies Used</h4>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-2">
								<div
									v-for="equipmentItem in selectedRecord.equipment"
									:key="equipmentItem.name"
									class="flex justify-between items-center p-2 bg-blue-50 rounded-lg border border-blue-200"
								>
									<span class="font-medium text-gray-900">{{ equipmentItem.name }}</span>
									<span class="text-sm text-gray-600 bg-white px-2 py-1 rounded border"
										>{{ equipmentItem.quantity }} pieces</span
									>
								</div>
							</div>
						</div>

						<!-- Nurse Notes -->
						<div v-if="nurseNotes && nurseNotes.length > 0">
							<h4 class="font-medium text-gray-900 mb-2">Nurse Notes</h4>
							<div class="space-y-3 max-h-48 overflow-y-auto">
								<div
									v-for="note in nurseNotes"
									:key="note.id"
									class="p-3 bg-yellow-50 rounded-lg border border-yellow-200"
								>
									<div class="flex justify-between items-start mb-2">
										<div class="text-xs text-gray-500">
											<span class="font-medium">{{ note.entered_by_nurse }}</span>
											<span class="mx-1">•</span>
											<span>{{ new Date(note.entry_date_time).toLocaleString() }}</span>
										</div>
									</div>
									<p class="text-sm text-gray-900">{{ note.nurse_notes }}</p>
									<div
										v-if="note.doctor_orders"
										class="mt-2 pt-2 border-t border-yellow-300"
									>
										<span class="text-xs font-medium text-gray-600"
											>Doctor's Orders:</span
										>
										<p class="text-sm text-gray-800 mt-1">{{ note.doctor_orders }}</p>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="flex justify-end space-x-4 mt-6 pt-4 border-t border-gray-200">
						<button
							@click="openNurseNotes(selectedRecord)"
							class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
						>
							Nurse Notes
						</button>
						<button
							@click="downloadPDF(selectedRecord)"
							class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
						>
							Download PDF
						</button>
						<button
							@click="showRecordModal = false"
							class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
						>
							Close
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Nurse Notes Modal -->
		<div
			v-if="showNurseNotesModal && selectedRecord"
			class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
		>
			<div
				class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto"
			>
				<div class="mt-3">
					<div class="flex justify-between items-center mb-4">
						<h3 class="text-lg font-medium text-gray-900">Add New Consultation</h3>
						<button
							@click="showNurseNotesModal = false"
							class="text-gray-400 hover:text-gray-600"
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

					<!-- Patient Info Header -->
					<div class="bg-gray-50 p-4 rounded-lg mb-6">
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
							<div>
								<span class="font-medium">Name:</span> {{ selectedRecord.first_name }}
								{{ selectedRecord.middle_name }} {{ selectedRecord.last_name }}
							</div>
							<div><span class="font-medium">Age:</span> {{ selectedRecord.age }}</div>
							<div>
								<span class="font-medium">Department:</span>
								{{ selectedRecord.department_course }}
							</div>
							<div>
								<span class="font-medium">ID:</span>
								{{ selectedRecord.student_employee_id }}
							</div>
							<div>
								<span class="font-medium">Contact:</span> {{ selectedRecord.contact_no }}
							</div>
						</div>
					</div>

					<!-- Add New Consultation Form -->
					<div class="border-b border-gray-200 pb-6 mb-6">
						<h4 class="text-md font-medium text-gray-900 mb-4">Add New Consultation</h4>
						<div class="space-y-4">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div>
									<label class="form-label">Nurse on Duty *</label>
									<input
										v-model="nurseNoteForm.entered_by_nurse"
										type="text"
										class="form-input"
										required
									/>
								</div>
								<div>
									<label class="form-label">Relationship</label>
									<input
										v-model="nurseNoteForm.relationship"
										type="text"
										class="form-input"
									/>
								</div>
							</div>
							<div>
								<label class="form-label">Chief Complaints / Notes *</label>
								<textarea
									v-model="nurseNoteForm.nurse_notes"
									rows="3"
									class="form-input"
									required
									placeholder="Enter the reason for consultation or follow-up notes"
								></textarea>
							</div>
							<div>
								<label class="form-label">Diagnosis / Treatment Plan</label>
								<textarea
									v-model="nurseNoteForm.doctor_orders"
									rows="2"
									class="form-input"
									placeholder="Enter diagnosis, treatment, or follow-up instructions"
								></textarea>
							</div>
							<div class="flex justify-end">
								<button @click="addNurseNote" class="btn-primary">
									Add Consultation
								</button>
							</div>
						</div>
					</div>

					<div class="flex justify-end space-x-4 mt-6 pt-4 border-t border-gray-200">
						<button
							@click="showNurseNotesModal = false"
							class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
						>
							Close
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Detailed Consultation View Modal -->
		<div
			v-if="showDetailedConsultationModal && selectedRecord"
			class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
		>
			<div
				class="relative top-10 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto"
			>
				<div class="mt-3">
					<div class="flex justify-between items-center mb-4">
						<h3 class="text-lg font-medium text-gray-900">Consultation Details</h3>
						<button
							@click="showDetailedConsultationModal = false"
							class="text-gray-400 hover:text-gray-600"
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

					<!-- Patient Info Header -->
					<div class="bg-gray-50 p-4 rounded-lg mb-6">
						<div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
							<div>
								<span class="font-medium">Name:</span> {{ selectedRecord.first_name }}
								{{ selectedRecord.middle_name }} {{ selectedRecord.last_name }}
							</div>
							<div><span class="font-medium">Age:</span> {{ selectedRecord.age }}</div>
							<div>
								<span class="font-medium">Department:</span>
								{{ selectedRecord.department_course }}
							</div>
							<div>
								<span class="font-medium">ID:</span>
								{{ selectedRecord.student_employee_id }}
							</div>
							<div>
								<span class="font-medium">Date:</span>
								{{ formatDateTime(selectedRecord.consultation_date_time) }}
							</div>
							<div>
								<span class="font-medium">Type:</span>
								<span :class="getConsultationType(selectedRecord).badgeClass">
									{{ getConsultationType(selectedRecord).label }}
								</span>
							</div>
						</div>
					</div>

					<!-- Tab Navigation -->
					<div class="border-b border-gray-200 mb-6">
						<nav class="-mb-px flex space-x-8">
							<button
								@click="activeDetailTab = 'vitals'"
								:class="[
									activeDetailTab === 'vitals'
										? 'border-blue-500 text-blue-600'
										: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
									'py-2 px-1 border-b-2 font-medium text-sm',
								]"
							>
								Vital Signs
							</button>
							<button
								@click="activeDetailTab = 'diagnosis'"
								:class="[
									activeDetailTab === 'diagnosis'
										? 'border-blue-500 text-blue-600'
										: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
									'py-2 px-1 border-b-2 font-medium text-sm',
								]"
							>
								Diagnosis & Treatment
							</button>
							<button
								@click="activeDetailTab = 'history'"
								:class="[
									activeDetailTab === 'history'
										? 'border-blue-500 text-blue-600'
										: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
									'py-2 px-1 border-b-2 font-medium text-sm',
								]"
							>
								Medical History
							</button>
							<button
								@click="activeDetailTab = 'notes'"
								:class="[
									activeDetailTab === 'notes'
										? 'border-blue-500 text-blue-600'
										: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
									'py-2 px-1 border-b-2 font-medium text-sm',
								]"
							>
								Notes & Orders
							</button>
							<button
								@click="activeDetailTab = 'audit'"
								:class="[
									activeDetailTab === 'audit'
										? 'border-blue-500 text-blue-600'
										: 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
									'py-2 px-1 border-b-2 font-medium text-sm',
								]"
							>
								Audit Log
							</button>
						</nav>
					</div>

					<!-- Tab Content -->
					<div v-if="activeDetailTab === 'vitals'" class="space-y-6">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<!-- First Set of Vital Signs -->
							<div
								v-if="selectedRecord.vital_signs_time_1"
								class="bg-blue-50 p-4 rounded-lg"
							>
								<h4 class="font-medium text-gray-900 mb-3">
									First Reading - {{ formatTime(selectedRecord.vital_signs_time_1) }}
								</h4>
								<div class="grid grid-cols-2 gap-3 text-sm">
									<div v-if="selectedRecord.blood_pressure_1">
										<span class="font-medium">Blood Pressure:</span>
										{{ selectedRecord.blood_pressure_1 }}
									</div>
									<div v-if="selectedRecord.heart_rate_1">
										<span class="font-medium">Heart Rate:</span>
										{{ selectedRecord.heart_rate_1 }} bpm
									</div>
									<div v-if="selectedRecord.respiratory_rate_1">
										<span class="font-medium">Respiratory Rate:</span>
										{{ selectedRecord.respiratory_rate_1 }} /min
									</div>
									<div v-if="selectedRecord.temperature_1">
										<span class="font-medium">Temperature:</span>
										{{ selectedRecord.temperature_1 }}°C
									</div>
									<div v-if="selectedRecord.oxygen_saturation_1">
										<span class="font-medium">Oxygen Saturation:</span>
										{{ selectedRecord.oxygen_saturation_1 }}%
									</div>
								</div>
							</div>

							<!-- Second Set of Vital Signs -->
							<div
								v-if="selectedRecord.vital_signs_time_2"
								class="bg-green-50 p-4 rounded-lg"
							>
								<h4 class="font-medium text-gray-900 mb-3">
									Second Reading - {{ formatTime(selectedRecord.vital_signs_time_2) }}
								</h4>
								<div class="grid grid-cols-2 gap-3 text-sm">
									<div v-if="selectedRecord.blood_pressure_2">
										<span class="font-medium">Blood Pressure:</span>
										{{ selectedRecord.blood_pressure_2 }}
									</div>
									<div v-if="selectedRecord.heart_rate_2">
										<span class="font-medium">Heart Rate:</span>
										{{ selectedRecord.heart_rate_2 }} bpm
									</div>
									<div v-if="selectedRecord.respiratory_rate_2">
										<span class="font-medium">Respiratory Rate:</span>
										{{ selectedRecord.respiratory_rate_2 }} /min
									</div>
									<div v-if="selectedRecord.temperature_2">
										<span class="font-medium">Temperature:</span>
										{{ selectedRecord.temperature_2 }}°C
									</div>
									<div v-if="selectedRecord.oxygen_saturation_2">
										<span class="font-medium">Oxygen Saturation:</span>
										{{ selectedRecord.oxygen_saturation_2 }}%
									</div>
								</div>
							</div>
						</div>

						<!-- Physical Measurements -->
						<div class="bg-gray-50 p-4 rounded-lg">
							<h4 class="font-medium text-gray-900 mb-3">Physical Measurements</h4>
							<div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
								<div v-if="selectedRecord.weight">
									<span class="font-medium">Weight:</span> {{ selectedRecord.weight }} kg
								</div>
								<div v-if="selectedRecord.height">
									<span class="font-medium">Height:</span> {{ selectedRecord.height }} cm
								</div>
								<div v-if="selectedRecord.bmi">
									<span class="font-medium">BMI:</span> {{ selectedRecord.bmi }}
								</div>
								<div v-if="selectedRecord.last_menstrual_period">
									<span class="font-medium">Last Menstrual Period:</span>
									{{ formatDate(selectedRecord.last_menstrual_period) }}
								</div>
							</div>
						</div>
					</div>

					<div v-if="activeDetailTab === 'diagnosis'" class="space-y-6">
						<!-- Chief Complaints -->
						<div class="bg-red-50 p-4 rounded-lg">
							<h4 class="font-medium text-gray-900 mb-2">Chief Complaints</h4>
							<p class="text-sm text-gray-700">
								{{ selectedRecord.chief_complaints || "Not specified" }}
							</p>
						</div>

						<!-- Diagnosis -->
						<div class="bg-blue-50 p-4 rounded-lg">
							<h4 class="font-medium text-gray-900 mb-2">Diagnosis</h4>
							<p class="text-sm text-gray-700">
								{{ selectedRecord.diagnosis || "Not specified" }}
							</p>
						</div>

						<!-- Staff Information -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div class="bg-green-50 p-4 rounded-lg">
								<h4 class="font-medium text-gray-900 mb-2">Nurse on Duty</h4>
								<p class="text-sm text-gray-700">
									{{ selectedRecord.nurse_on_duty || "Not specified" }}
								</p>
							</div>
							<div class="bg-purple-50 p-4 rounded-lg">
								<h4 class="font-medium text-gray-900 mb-2">Physician on Duty</h4>
								<p class="text-sm text-gray-700">
									{{ selectedRecord.physician_on_duty || "Not specified" }}
								</p>
							</div>
						</div>

						<!-- Medicines and Equipment -->
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div class="bg-yellow-50 p-4 rounded-lg">
								<h4 class="font-medium text-gray-900 mb-3">Prescribed Medicines</h4>
								<div
									v-if="selectedRecord.medicines && selectedRecord.medicines.length > 0"
									class="space-y-2"
								>
									<div
										v-for="medicine in selectedRecord.medicines"
										:key="medicine.id"
										class="text-sm"
									>
										<span class="font-medium">{{ medicine.name }}</span>
										<span v-if="medicine.quantity" class="text-gray-600">
											- Qty: {{ medicine.quantity }}</span
										>
									</div>
								</div>
								<p v-else class="text-sm text-gray-600">No medicines prescribed</p>
							</div>
							<div class="bg-indigo-50 p-4 rounded-lg">
								<h4 class="font-medium text-gray-900 mb-3">Equipment Used</h4>
								<div
									v-if="selectedRecord.equipment && selectedRecord.equipment.length > 0"
									class="space-y-2"
								>
									<div
										v-for="equipment in selectedRecord.equipment"
										:key="equipment.id"
										class="text-sm"
									>
										<span class="font-medium">{{ equipment.name }}</span>
										<span v-if="equipment.quantity" class="text-gray-600">
											- Qty: {{ equipment.quantity }}</span
										>
									</div>
								</div>
								<p v-else class="text-sm text-gray-600">No equipment used</p>
							</div>
						</div>
					</div>

					<div v-if="activeDetailTab === 'history'" class="space-y-6">
						<!-- Allergies -->
						<div class="bg-red-50 p-4 rounded-lg">
							<h4 class="font-medium text-gray-900 mb-2">Allergies</h4>
							<p v-if="selectedRecord.has_allergy" class="text-sm text-red-700">
								<span class="font-medium">Yes:</span>
								{{ selectedRecord.allergy_specify || "Not specified" }}
							</p>
							<p v-else class="text-sm text-gray-600">No known allergies</p>
						</div>

						<!-- Medical Conditions -->
						<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
							<div class="bg-orange-50 p-4 rounded-lg">
								<h4 class="font-medium text-gray-900 mb-2">Hypertension</h4>
								<p
									class="text-sm"
									:class="
										selectedRecord.has_hypertension ? 'text-orange-700' : 'text-gray-600'
									"
								>
									{{ selectedRecord.has_hypertension ? "Yes" : "No" }}
								</p>
							</div>
							<div class="bg-blue-50 p-4 rounded-lg">
								<h4 class="font-medium text-gray-900 mb-2">Diabetes</h4>
								<p
									class="text-sm"
									:class="selectedRecord.has_diabetes ? 'text-blue-700' : 'text-gray-600'"
								>
									{{ selectedRecord.has_diabetes ? "Yes" : "No" }}
								</p>
							</div>
							<div class="bg-green-50 p-4 rounded-lg">
								<h4 class="font-medium text-gray-900 mb-2">Asthma</h4>
								<p
									class="text-sm"
									:class="selectedRecord.has_asthma ? 'text-green-700' : 'text-gray-600'"
								>
									{{ selectedRecord.has_asthma ? "Yes" : "No" }}
								</p>
								<p
									v-if="selectedRecord.has_asthma && selectedRecord.asthma_last_attack"
									class="text-xs text-gray-600 mt-1"
								>
									Last attack: {{ formatDate(selectedRecord.asthma_last_attack) }}
								</p>
							</div>
						</div>

						<!-- Other Medical History -->
						<div class="bg-gray-50 p-4 rounded-lg">
							<h4 class="font-medium text-gray-900 mb-2">Other Medical History</h4>
							<p class="text-sm text-gray-700">
								{{ selectedRecord.other_medical_history || "None specified" }}
							</p>
						</div>
					</div>

					<div v-if="activeDetailTab === 'notes'" class="space-y-6">
						<!-- Related Nurse Notes -->
						<div class="bg-blue-50 p-4 rounded-lg">
							<h4 class="font-medium text-gray-900 mb-3">Related Nurse Notes</h4>
							<div v-if="nurseNotes.length > 0" class="space-y-3">
								<div
									v-for="note in nurseNotes"
									:key="note.id"
									class="bg-white p-3 rounded border"
								>
									<div class="flex justify-between items-start mb-2">
										<span class="text-sm font-medium">{{
											formatDateTime(note.entry_date_time)
										}}</span>
										<span class="text-xs text-gray-500"
											>by {{ note.entered_by_nurse }}</span
										>
									</div>
									<div class="space-y-2 text-sm">
										<div v-if="note.nurse_notes">
											<span class="font-medium">Notes:</span>
											<p class="mt-1 text-gray-700">{{ note.nurse_notes }}</p>
										</div>
										<div v-if="note.doctor_orders">
											<span class="font-medium">Doctor Orders:</span>
											<p class="mt-1 text-gray-700">{{ note.doctor_orders }}</p>
										</div>
									</div>
								</div>
							</div>
							<p v-else class="text-sm text-gray-600">No related nurse notes</p>
						</div>
					</div>

					<div v-if="activeDetailTab === 'audit'" class="space-y-6">
						<!-- Audit Log -->
						<div class="bg-gray-50 p-4 rounded-lg">
							<h4 class="font-medium text-gray-900 mb-3">Consultation Audit Trail</h4>
							<div v-if="consultationAuditLogs.length > 0" class="space-y-3">
								<div
									v-for="log in consultationAuditLogs"
									:key="log.id"
									class="bg-white p-3 rounded border"
								>
									<div class="flex justify-between items-start mb-2">
										<div class="flex items-center space-x-2">
											<span class="text-sm font-medium capitalize">{{ log.event }}</span>
											<span
												class="text-xs px-2 py-1 rounded-full"
												:class="{
													'bg-green-100 text-green-800': log.event === 'created',
													'bg-blue-100 text-blue-800': log.event === 'updated',
													'bg-red-100 text-red-800': log.event === 'deleted',
													'bg-gray-100 text-gray-800': log.event === 'viewed',
												}"
											>
												{{ log.event }}
											</span>
										</div>
										<div class="text-right">
											<div class="text-xs text-gray-500">
												{{ formatDateTime(log.created_at) }}
											</div>
											<div class="text-xs text-gray-400">
												{{ log.user_name }} ({{ log.user_role }})
											</div>
										</div>
									</div>
									<p class="text-sm text-gray-700 mb-2">{{ log.description }}</p>
									<div
										v-if="log.changes && Object.keys(log.changes).length > 0"
										class="text-xs"
									>
										<div class="font-medium text-gray-600 mb-1">Changes:</div>
										<div class="space-y-1">
											<div
												v-for="(change, field) in log.changes"
												:key="field"
												class="pl-2"
											>
												<span class="font-medium">{{ field }}:</span>
												<span class="text-red-600">{{ change.old || "null" }}</span>
												→
												<span class="text-green-600">{{ change.new || "null" }}</span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<p v-else class="text-sm text-gray-600">No audit logs available</p>
						</div>
					</div>

					<!-- Modal Footer -->
					<div
						class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200"
					>
						<div class="flex space-x-2">
							<button @click="downloadPDF(selectedRecord)" class="btn-secondary">
								Download PDF
							</button>
							<button @click="openNurseNotes(selectedRecord)" class="btn-secondary">
								Add Note
							</button>
						</div>
						<button
							@click="showDetailedConsultationModal = false"
							class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
						>
							Close
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Patient Timeline Modal -->
		<div
			v-if="showTimelineModal && selectedRecord"
			class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
		>
			<div
				class="relative top-10 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto"
			>
				<div class="mt-3">
					<div class="flex justify-between items-center mb-4">
						<h3 class="text-lg font-medium text-gray-900">
							Patient Medical Timeline - {{ selectedRecord.first_name }}
							{{ selectedRecord.middle_name }} {{ selectedRecord.last_name }}
						</h3>
						<button
							@click="showTimelineModal = false"
							class="text-gray-400 hover:text-gray-600"
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

					<!-- Patient Info Header -->
					<div class="bg-gray-50 p-4 rounded-lg mb-6">
						<div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
							<div>
								<span class="font-medium">Patient ID:</span>
								{{ selectedRecord.student_employee_id }}
							</div>
							<div>
								<span class="font-medium">Name:</span> {{ selectedRecord.first_name }}
								{{ selectedRecord.middle_name }} {{ selectedRecord.last_name }}
							</div>
							<div><span class="font-medium">Age:</span> {{ selectedRecord.age }}</div>
							<div>
								<span class="font-medium">Department:</span>
								{{ selectedRecord.department_course }}
							</div>
						</div>
						<div class="mt-2 text-sm text-gray-600">
							<span class="font-medium">Total Consultations:</span>
							{{ patientTimeline.length }}
						</div>
					</div>

					<!-- Timeline -->
					<div class="space-y-4">
						<div v-if="patientTimeline.length === 0" class="text-center py-8">
							<svg
								class="w-12 h-12 text-gray-400 mx-auto mb-4"
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
							<p class="text-gray-500">No consultation records found for this patient</p>
						</div>

						<div v-else class="relative">
							<!-- Timeline line -->
							<div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-300"></div>

							<!-- Timeline entries -->
							<div
								v-for="(consultation, index) in patientTimeline"
								:key="consultation.id"
								class="relative"
							>
								<!-- Timeline marker -->
								<div class="flex items-start">
									<div class="flex-shrink-0 relative">
										<div
											class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow relative z-10"
										></div>
									</div>

									<!-- Content -->
									<div class="ml-6 pb-8">
										<!-- Card -->
										<div
											class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow"
										>
											<!-- Header -->
											<div
												class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg"
											>
												<div class="flex justify-between items-start">
													<div>
														<h4 class="text-sm font-medium text-gray-900">
															{{ formatDateTime(consultation.consultation_date_time) }}
														</h4>
														<div class="flex items-center space-x-2 mt-1">
															<span :class="getConsultationType(consultation).badgeClass">
																{{ getConsultationType(consultation).label }}
															</span>
															<span
																v-if="consultation.nurse_notes_count > 0"
																class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
															>
																{{ consultation.nurse_notes_count }} note(s)
															</span>
														</div>
													</div>
													<div class="flex space-x-2">
														<button
															@click="openDetailedConsultationView(consultation)"
															class="text-xs px-2 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded"
														>
															View Details
														</button>
														<button
															@click="downloadPDF(consultation)"
															class="text-xs px-2 py-1 bg-gray-50 text-gray-600 hover:bg-gray-100 rounded"
														>
															PDF
														</button>
													</div>
												</div>
											</div>

											<!-- Content -->
											<div class="px-4 py-3">
												<!-- Chief Complaints -->
												<div v-if="consultation.chief_complaints" class="mb-3">
													<div
														class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1"
													>
														Chief Complaints
													</div>
													<p class="text-sm text-gray-700">
														{{ consultation.chief_complaints }}
													</p>
												</div>

												<!-- Diagnosis -->
												<div v-if="consultation.diagnosis" class="mb-3">
													<div
														class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1"
													>
														Diagnosis
													</div>
													<p class="text-sm text-gray-700">
														{{ consultation.diagnosis }}
													</p>
												</div>

												<!-- Quick Vital Signs -->
												<div v-if="consultation.vital_signs_time_1" class="mb-3">
													<div
														class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1"
													>
														Vital Signs
													</div>
													<div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
														<div v-if="consultation.blood_pressure_1">
															<span class="font-medium">BP:</span>
															{{ consultation.blood_pressure_1 }}
														</div>
														<div v-if="consultation.heart_rate_1">
															<span class="font-medium">HR:</span>
															{{ consultation.heart_rate_1 }} bpm
														</div>
														<div v-if="consultation.temperature_1">
															<span class="font-medium">Temp:</span>
															{{ consultation.temperature_1 }}°C
														</div>
														<div v-if="consultation.respiratory_rate_1">
															<span class="font-medium">RR:</span>
															{{ consultation.respiratory_rate_1 }}/min
														</div>
													</div>
												</div>

												<!-- Staff -->
												<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
													<div v-if="consultation.nurse_on_duty">
														<span class="font-medium text-gray-500">Nurse:</span>
														<span class="text-gray-700">{{
															consultation.nurse_on_duty
														}}</span>
													</div>
													<div v-if="consultation.physician_on_duty">
														<span class="font-medium text-gray-500">Physician:</span>
														<span class="text-gray-700">{{
															consultation.physician_on_duty
														}}</span>
													</div>
												</div>

												<!-- Medicines & Equipment Summary -->
												<div
													v-if="
														(consultation.medicines &&
															consultation.medicines.length > 0) ||
														(consultation.equipment && consultation.equipment.length > 0)
													"
													class="mt-3 pt-3 border-t border-gray-100"
												>
													<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
														<div
															v-if="
																consultation.medicines &&
																consultation.medicines.length > 0
															"
														>
															<span class="font-medium text-gray-500">Medicines:</span>
															<span class="text-gray-700"
																>{{ consultation.medicines.length }} prescribed</span
															>
														</div>
														<div
															v-if="
																consultation.equipment &&
																consultation.equipment.length > 0
															"
														>
															<span class="font-medium text-gray-500">Equipment:</span>
															<span class="text-gray-700"
																>{{ consultation.equipment.length }} used</span
															>
														</div>
													</div>
												</div>
											</div>

											<!-- Footer with timestamps -->
											<div
												class="px-4 py-2 bg-gray-50 rounded-b-lg text-xs text-gray-500"
											>
												<div class="flex justify-between">
													<span
														>Created: {{ formatDateTime(consultation.created_at) }}</span
													>
													<span
														v-if="consultation.updated_at !== consultation.created_at"
													>
														Updated: {{ formatDateTime(consultation.updated_at) }}
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Modal Footer -->
					<div class="flex justify-end space-x-4 mt-6 pt-4 border-t border-gray-200">
						<button
							@click="showTimelineModal = false"
							class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
						>
							Close
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
