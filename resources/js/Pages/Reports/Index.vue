<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, reactive, onMounted, computed } from "vue";
import axios from "axios";

const activeTab = ref("statistics");
const loading = ref(false);

// Statistical data
const statisticsData = ref({
	patients: {
		total: 0,
		thisMonth: 0,
		today: 0,
		dailyTrend: [],
	},
	visits: {
		total: 0,
		byType: [],
		byMonth: [],
		recentVisits: [],
	},
	medicines: {
		totalPrescribed: 0,
		mostPrescribed: [],
		byCategory: [],
	},
	nurses: {
		workload: [],
		performance: [],
	},
});

// Patients report data
const patientsReportData = ref([]);
const patientsFilters = reactive({
	start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split("T")[0],
	end_date: new Date().toISOString().split("T")[0],
	search: "",
	visit_type: "all",
	nurse: "all",
	department: "all",
});

// Department options
const departments = [
	{ value: "all", label: "All Departments" },
	{
		value: "School of Hospitality Management",
		label: "School of Hospitality Management",
	},
	{
		value: "School of Computing and Information Technology",
		label: "School of Computing and Information Technology",
	},
	{
		value: "School of Human Resource Development",
		label: "School of Human Resource Development",
	},
	{
		value: "School of Entrepreneurship and Business Sciences",
		label: "School of Entrepreneurship and Business Sciences",
	},
	{
		value: "School of Creative Arts, Media and Social Sciences",
		label: "School of Creative Arts, Media and Social Sciences",
	},
	{ value: "School of Technician Studies", label: "School of Technician Studies" },
];

const paginationInfo = ref({
	current_page: 1,
	per_page: 15,
	total: 0,
	last_page: 1,
});

// Load statistical report data
const loadStatistics = async () => {
	loading.value = true;
	try {
		const response = await axios.get("/reports/statistics");
		statisticsData.value = response.data;
	} catch (error) {
		console.error("Error loading statistics:", error);
		alert("Error loading statistics. Please try again.");
	} finally {
		loading.value = false;
	}
};

// Load patients report data
const loadPatientsReport = async (page = 1) => {
	loading.value = true;
	try {
		const params = {
			...patientsFilters,
			page,
			per_page: paginationInfo.value.per_page,
		};
		const response = await axios.get("/reports/patients", { params });
		patientsReportData.value = response.data.data;
		paginationInfo.value = {
			current_page: response.data.current_page,
			per_page: response.data.per_page,
			total: response.data.total,
			last_page: response.data.last_page,
		};
	} catch (error) {
		console.error("Error loading patients report:", error);
		alert("Error loading patients report. Please try again.");
	} finally {
		loading.value = false;
	}
};

// Computed properties for statistics
const totalPatients = computed(() => statisticsData.value.patients?.total || 0);
const totalVisitsThisMonth = computed(
	() => statisticsData.value.patients?.thisMonth || 0
);
const todayVisits = computed(() => statisticsData.value.patients?.today || 0);

// Export functions
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());
const exportFormat = ref("pdf");

const months = [
	{ value: 1, label: "January" },
	{ value: 2, label: "February" },
	{ value: 3, label: "March" },
	{ value: 4, label: "April" },
	{ value: 5, label: "May" },
	{ value: 6, label: "June" },
	{ value: 7, label: "July" },
	{ value: 8, label: "August" },
	{ value: 9, label: "September" },
	{ value: 10, label: "October" },
	{ value: 11, label: "November" },
	{ value: 12, label: "December" },
];

const years = [];
const currentYear = new Date().getFullYear();
for (let year = currentYear - 5; year <= currentYear + 1; year++) {
	years.push(year);
}

const exportPatientsReport = async (format = "pdf") => {
	try {
		if (format === "pdf") {
			console.log("Starting patients PDF export via server-side generation...");
			// Use server-side PDF generation instead of client-side
			const params = new URLSearchParams({
				...patientsFilters,
				month: selectedMonth.value,
				year: selectedYear.value,
				format: 'pdf'
			});

			console.log("Initiating server-side PDF download...");
			// Create a direct download link to the server endpoint
			const url = `/reports/patients/export?${params}`;
			const link = document.createElement('a');
			link.href = url;
			link.style.display = 'none';
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			console.log("Patients PDF download initiated successfully!");
		} else {
			// Handle Excel export - server-side
			const params = new URLSearchParams({
				...patientsFilters,
				format: format,
				month: selectedMonth.value,
				year: selectedYear.value,
			});

			const response = await axios.get(`/reports/patients/export?${params}`, {
				responseType: "blob",
			});

			const monthName = months.find((m) => m.value === selectedMonth.value)?.label;
			const filename = `patients-report-${monthName.toLowerCase()}-${
				selectedYear.value
			}.xlsx`;

			const url = window.URL.createObjectURL(new Blob([response.data]));
			const link = document.createElement("a");
			link.href = url;
			link.setAttribute("download", filename);
			document.body.appendChild(link);
			link.click();
			link.remove();
			window.URL.revokeObjectURL(url);
		}
	} catch (error) {
		console.error("Export error:", error);
		alert("Error exporting patients report: " + (error.message || error));
	}
};

const exportStatisticalReport = async (format = "pdf") => {
	try {
		if (format === "pdf") {
			console.log("Starting statistical PDF export via server-side generation...");
			// Use server-side PDF generation instead of client-side
			const params = new URLSearchParams({
				month: selectedMonth.value,
				year: selectedYear.value,
				format: 'pdf'
			});

			console.log("Initiating server-side PDF download...");
			// Create a direct download link to the server endpoint
			const url = `/reports/statistics/export?${params}`;
			const link = document.createElement('a');
			link.href = url;
			link.style.display = 'none';
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			console.log("Statistical PDF download initiated successfully!");
		} else {
			// Handle Excel export - server-side
			const params = new URLSearchParams({
				format: format,
				month: selectedMonth.value,
				year: selectedYear.value,
			});

			const response = await axios.get(`/reports/statistics/export?${params}`, {
				responseType: "blob",
			});

			const monthName = months.find((m) => m.value === selectedMonth.value)?.label;
			const filename = `statistics-report-${monthName.toLowerCase()}-${
				selectedYear.value
			}.xlsx`;

			const url = window.URL.createObjectURL(new Blob([response.data]));
			const link = document.createElement("a");
			link.href = url;
			link.setAttribute("download", filename);
			document.body.appendChild(link);
			link.click();
			link.remove();
			window.URL.revokeObjectURL(url);
		}
	} catch (error) {
		console.error("Export error:", error);
		alert("Error exporting statistical report: " + (error.message || error));
	}
};

const printReport = () => {
	window.print();
};

// Filter functions
const clearFilters = () => {
	patientsFilters.start_date = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000)
		.toISOString()
		.split("T")[0];
	patientsFilters.end_date = new Date().toISOString().split("T")[0];
	patientsFilters.search = "";
	patientsFilters.visit_type = "all";
	patientsFilters.nurse = "all";
	patientsFilters.department = "all";
	loadPatientsReport(1);
};

onMounted(() => {
	loadStatistics();
	loadPatientsReport();
});
</script>
<template>
	<Head title="Reports & Analytics" />

	<div class="page-container">
		<!-- Page header -->
		<div class="page-header">
			<div class="flex justify-between items-center">
				<div>
					<h1 class="text-3xl font-bold text-neutral-900">Reports & Analytics</h1>
					<p class="mt-2 text-neutral-600">
						Comprehensive clinic performance and patient reports
					</p>
				</div>
				<div class="flex space-x-3">
					<!-- Export Controls -->
					<div class="flex items-center space-x-3 border-r border-neutral-200 pr-4">
						<div class="flex flex-col space-y-1">
							<label class="text-xs text-neutral-600">Month</label>
							<select
								v-model="selectedMonth"
								class="text-sm border-neutral-300 rounded-md"
							>
								<option v-for="month in months" :key="month.value" :value="month.value">
									{{ month.label }}
								</option>
							</select>
						</div>
						<div class="flex flex-col space-y-1">
							<label class="text-xs text-neutral-600">Year</label>
							<select
								v-model="selectedYear"
								class="text-sm border-neutral-300 rounded-md"
							>
								<option v-for="year in years" :key="year" :value="year">
									{{ year }}
								</option>
							</select>
						</div>
					</div>

					<!-- Export Buttons -->
					<div class="flex space-x-2">
						<div class="relative" v-if="activeTab === 'statistics'">
							<button
								@click="exportStatisticalReport('pdf')"
								class="btn-success flex items-center space-x-2"
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
										d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"
									/>
								</svg>
								<span>PDF</span>
							</button>
							<button
								@click="exportStatisticalReport('excel')"
								class="btn-primary flex items-center space-x-2"
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
										d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
									/>
								</svg>
								<span>Excel</span>
							</button>
						</div>

						<div class="relative" v-if="activeTab === 'patients'">
							<button
								@click="exportPatientsReport('pdf')"
								class="btn-success flex items-center space-x-2"
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
										d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"
									/>
								</svg>
								<span>PDF</span>
							</button>
							<button
								@click="exportPatientsReport('excel')"
								class="btn-primary flex items-center space-x-2"
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
										d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
									/>
								</svg>
								<span>Excel</span>
							</button>
						</div>
					</div>

					<button @click="printReport" class="btn-secondary flex items-center space-x-2">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
							/>
						</svg>
						<span>Print</span>
					</button>
				</div>
			</div>
		</div>

		<!-- Report Tabs -->
		<div class="card mb-8">
			<div class="border-b border-neutral-200">
				<nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
					<button
						@click="activeTab = 'statistics'"
						:class="[
							'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
							activeTab === 'statistics'
								? 'border-primary text-primary'
								: 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300',
						]"
					>
						<div class="flex items-center space-x-2">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
								/>
							</svg>
							<span>Statistical Report</span>
						</div>
					</button>
					<button
						@click="activeTab = 'patients'"
						:class="[
							'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
							activeTab === 'patients'
								? 'border-primary text-primary'
								: 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300',
						]"
					>
						<div class="flex items-center space-x-2">
							<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
								/>
							</svg>
							<span>Patients Report</span>
						</div>
					</button>
				</nav>
			</div>
		</div>

		<!-- Statistical Report Tab -->
		<div v-if="activeTab === 'statistics'">
			<!-- Overview Stats -->
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
									d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-neutral-500">Total Patients</p>
							<p class="text-2xl font-bold text-neutral-900">{{ totalPatients }}</p>
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
							<p class="text-sm font-medium text-neutral-500">This Month</p>
							<p class="text-2xl font-bold text-neutral-900">
								{{ totalVisitsThisMonth }}
							</p>
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
									d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-neutral-500">Today's Visits</p>
							<p class="text-2xl font-bold text-neutral-900">{{ todayVisits }}</p>
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
									d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<p class="text-sm font-medium text-neutral-500">Medicines Prescribed</p>
							<p class="text-2xl font-bold text-neutral-900">
								{{ statisticsData.medicines?.totalPrescribed || 0 }}
							</p>
						</div>
					</div>
				</div>
			</div>

			<!-- Charts Row -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
				<!-- Daily Trend Chart -->
				<div class="card">
					<div class="px-6 py-4 border-b border-neutral-200">
						<h3 class="text-lg font-medium text-neutral-900">
							Daily Patient Visits Trend
						</h3>
						<p class="text-sm text-neutral-500">Last 30 days</p>
					</div>
					<div class="p-6">
						<div v-if="statisticsData.patients?.dailyTrend?.length > 0" class="h-64">
							<div class="h-full flex items-end space-x-1">
								<div
									v-for="(day, index) in (
										statisticsData.patients?.dailyTrend || []
									).slice(-30)"
									:key="index"
									class="bg-primary rounded-t flex-1 min-h-[4px] relative group transition-all hover:bg-primary/80"
									:style="{
										height: `${
											(day.count /
												Math.max(
													...(statisticsData.patients?.dailyTrend || []).map(
														(d) => d.count
													)
												)) *
											240
										}px`,
									}"
								>
									<div
										class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap"
									>
										{{ new Date(day.date).toLocaleDateString() }}: {{ day.count }} visits
									</div>
								</div>
							</div>
						</div>
						<div v-else class="h-64 flex items-center justify-center text-neutral-500">
							<div class="text-center">
								<svg
									class="w-12 h-12 mx-auto mb-4 text-neutral-300"
									fill="none"
									stroke="currentColor"
									viewBox="0 0 24 24"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2"
										d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
									/>
								</svg>
								<p>No visit data available</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Visit Types Distribution -->
				<div class="card">
					<div class="px-6 py-4 border-b border-neutral-200">
						<h3 class="text-lg font-medium text-neutral-900">Visit Types Distribution</h3>
						<p class="text-sm text-neutral-500">Current period breakdown</p>
					</div>
					<div class="p-6">
						<div v-if="statisticsData.visits?.byType?.length > 0" class="space-y-4">
							<div
								v-for="(type, index) in statisticsData.visits?.byType || []"
								:key="type.type"
								class="flex items-center justify-between"
							>
								<div class="flex items-center space-x-3">
									<div
										:class="[
											'w-4 h-4 rounded-full',
											index === 0
												? 'bg-primary'
												: index === 1
												? 'bg-success'
												: index === 2
												? 'bg-info'
												: index === 3
												? 'bg-warning'
												: 'bg-neutral-400',
										]"
									></div>
									<span class="text-sm font-medium text-neutral-700">{{
										type.type
									}}</span>
								</div>
								<div class="flex items-center space-x-3">
									<div class="w-32 bg-neutral-200 rounded-full h-2">
										<div
											:class="[
												'h-2 rounded-full transition-all',
												index === 0
													? 'bg-primary'
													: index === 1
													? 'bg-success'
													: index === 2
													? 'bg-info'
													: index === 3
													? 'bg-warning'
													: 'bg-neutral-400',
											]"
											:style="{
												width: `${Math.min(
													(type.count /
														(statisticsData.value?.visits?.byType?.reduce(
															(sum, item) => sum + item.count,
															0
														) || 1)) *
														100,
													100
												)}%`,
											}"
										></div>
									</div>
									<span class="text-sm font-bold text-neutral-900 w-8">{{
										type.count
									}}</span>
								</div>
							</div>
						</div>
						<div v-else class="h-32 flex items-center justify-center text-neutral-500">
							<div class="text-center">
								<svg
									class="w-12 h-12 mx-auto mb-4 text-neutral-300"
									fill="none"
									stroke="currentColor"
									viewBox="0 0 24 24"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2"
										d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"
									/>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2"
										d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"
									/>
								</svg>
								<p>No visit type data available</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Additional Statistics -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
				<!-- Most Prescribed Medicines -->
				<div class="card">
					<div class="px-6 py-4 border-b border-neutral-200">
						<h3 class="text-lg font-medium text-neutral-900">
							Most Prescribed Medicines
						</h3>
						<p class="text-sm text-neutral-500">Top 10 this month</p>
					</div>
					<div class="p-6">
						<div
							v-if="statisticsData.medicines?.mostPrescribed?.length > 0"
							class="space-y-3"
						>
							<div
								v-for="(medicine, index) in (
									statisticsData.medicines?.mostPrescribed || []
								).slice(0, 10)"
								:key="medicine.name"
								class="flex items-center justify-between"
							>
								<div class="flex items-center space-x-3">
									<div
										class="flex-shrink-0 w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center"
									>
										<span class="text-xs font-bold text-primary">{{ index + 1 }}</span>
									</div>
									<div>
										<p class="text-sm font-medium text-neutral-900">
											{{ medicine.name }}
										</p>
										<p class="text-xs text-neutral-500">{{ medicine.type }}</p>
									</div>
								</div>
								<span class="text-sm font-bold text-primary"
									>{{ medicine.prescribed_count }}x</span
								>
							</div>
						</div>
						<div v-else class="h-32 flex items-center justify-center text-neutral-500">
							<div class="text-center">
								<svg
									class="w-12 h-12 mx-auto mb-4 text-neutral-300"
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
								<p>No medicine data available</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Nurse Workload -->
				<div class="card">
					<div class="px-6 py-4 border-b border-neutral-200">
						<h3 class="text-lg font-medium text-neutral-900">Nurse Workload</h3>
						<p class="text-sm text-neutral-500">Patients handled this month</p>
					</div>
					<div class="p-6">
						<div v-if="statisticsData.nurses?.workload?.length > 0" class="space-y-3">
							<div
								v-for="nurse in statisticsData.nurses?.workload || []"
								:key="nurse.name"
								class="flex items-center justify-between"
							>
								<div class="flex items-center space-x-3">
									<div
										class="w-8 h-8 bg-success/10 rounded-full flex items-center justify-center"
									>
										<svg
											class="w-4 h-4 text-success"
											fill="none"
											stroke="currentColor"
											viewBox="0 0 24 24"
										>
											<path
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="2"
												d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
											/>
										</svg>
									</div>
									<span class="text-sm font-medium text-neutral-900">{{
										nurse.name
									}}</span>
								</div>
								<div class="flex items-center space-x-3">
									<div class="w-20 bg-neutral-200 rounded-full h-2">
										<div
											class="bg-success h-2 rounded-full transition-all"
											:style="{
												width: `${
													(nurse.patient_count /
														Math.max(
															...(statisticsData.nurses?.workload || []).map(
																(n) => n.patient_count
															)
														)) *
													100
												}%`,
											}"
										></div>
									</div>
									<span class="text-sm font-bold text-neutral-900 w-8">{{
										nurse.patient_count
									}}</span>
								</div>
							</div>
						</div>
						<div v-else class="h-32 flex items-center justify-center text-neutral-500">
							<div class="text-center">
								<svg
									class="w-12 h-12 mx-auto mb-4 text-neutral-300"
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
								<p>No nurse data available</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Patients Report Tab -->
		<div v-if="activeTab === 'patients'">
			<!-- Filters -->
			<div class="card mb-8">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h3 class="text-lg font-medium text-neutral-900">Filter Options</h3>
					<p class="text-sm text-neutral-500">Customize your patient report</p>
				</div>
				<div class="p-6">
					<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
						<div>
							<label class="form-label">Start Date</label>
							<input
								v-model="patientsFilters.start_date"
								type="date"
								class="form-input"
								@change="loadPatientsReport(1)"
							/>
						</div>
						<div>
							<label class="form-label">End Date</label>
							<input
								v-model="patientsFilters.end_date"
								type="date"
								class="form-input"
								@change="loadPatientsReport(1)"
							/>
						</div>
						<div>
							<label class="form-label">Search</label>
							<input
								v-model="patientsFilters.search"
								type="text"
								placeholder="Student/Employee name or ID..."
								class="form-input"
								@input="loadPatientsReport(1)"
							/>
						</div>
						<div>
							<label class="form-label">Visit Type</label>
							<select
								v-model="patientsFilters.visit_type"
								class="form-input"
								@change="loadPatientsReport(1)"
							>
								<option value="all">All Types</option>
								<option value="Check-up">Check-up</option>
								<option value="Emergency">Emergency</option>
								<option value="Follow-up">Follow-up</option>
								<option value="Consultation">Consultation</option>
								<option value="Vaccination">Vaccination</option>
							</select>
						</div>
						<div>
							<label class="form-label">Department</label>
							<select
								v-model="patientsFilters.department"
								class="form-input"
								@change="loadPatientsReport(1)"
							>
								<option v-for="dept in departments" :key="dept.value" :value="dept.value">
									{{ dept.label }}
								</option>
							</select>
						</div>
						<div class="flex items-end">
							<button @click="clearFilters" class="w-full btn-secondary">
								Clear Filters
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Patients Table -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<div class="flex justify-between items-center">
						<h3 class="text-lg font-medium text-neutral-900">
							Patient Records
							<span class="text-sm text-neutral-500 font-normal">
								({{ paginationInfo.total }} total records)
							</span>
						</h3>
					</div>
				</div>
				<div class="overflow-x-auto">
					<table class="min-w-full divide-y divide-neutral-200">
						<thead class="bg-neutral-50">
							<tr>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Patient Info
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Visit Details
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Diagnosis
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Prescribed Medicines
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Nurse Notes
								</th>
								<th
									class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider"
								>
									Nurse Assigned
								</th>
							</tr>
						</thead>
						<tbody class="bg-white divide-y divide-neutral-200">
							<tr
								v-for="patient in patientsReportData"
								:key="patient.id"
								class="hover:bg-neutral-50"
							>
								<td class="px-6 py-4 whitespace-nowrap">
									<div>
										<div class="text-sm font-medium text-neutral-900">
											{{
												patient.full_name || `${patient.first_name} ${patient.last_name}`
											}}
										</div>
										<div class="text-sm text-neutral-500">
											ID: {{ patient.student_employee_id }}
										</div>
										<div v-if="patient.department" class="text-xs text-neutral-400 mt-1">
											{{ patient.department }}
										</div>
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div>
										<div class="text-sm text-neutral-900">
											{{ new Date(patient.consultation_date_time).toLocaleDateString() }}
										</div>
										<div class="text-sm text-neutral-500">
											{{ patient.visit_type || "General" }}
										</div>
									</div>
								</td>
								<td class="px-6 py-4">
									<div class="text-sm text-neutral-900 max-w-xs">
										{{ patient.chief_complaints || "No diagnosis recorded" }}
									</div>
								</td>
								<td class="px-6 py-4">
									<div
										v-if="patient.medicines && patient.medicines.length > 0"
										class="space-y-1"
									>
										<div
											v-for="medicine in patient.medicines.slice(0, 3)"
											:key="medicine.id || medicine"
											class="text-sm"
										>
											<span class="text-neutral-900 font-medium">
												{{ typeof medicine === "string" ? medicine : medicine.name }}
											</span>
											<span
												v-if="typeof medicine === 'object' && medicine.quantity"
												class="text-neutral-500 text-xs"
											>
												({{ medicine.quantity }})
											</span>
										</div>
										<div
											v-if="patient.medicines.length > 3"
											class="text-xs text-neutral-500"
										>
											+{{ patient.medicines.length - 3 }} more...
										</div>
									</div>
									<div v-else class="text-sm text-neutral-500">
										No medicines prescribed
									</div>
								</td>
								<td class="px-6 py-4">
									<div class="text-sm text-neutral-900 max-w-xs">
										<div v-if="patient.nurse_notes && patient.nurse_notes.length > 0">
											<div class="space-y-1">
												<div
													v-for="note in patient.nurse_notes.slice(0, 2)"
													:key="note.id"
													class="text-xs"
												>
													{{ (note.nurse_notes || "").substring(0, 80)
													}}{{ (note.nurse_notes || "").length > 80 ? "..." : "" }}
												</div>
												<div
													v-if="patient.nurse_notes.length > 2"
													class="text-xs text-neutral-400"
												>
													+{{ patient.nurse_notes.length - 2 }} more notes
												</div>
											</div>
										</div>
										<div v-else class="text-neutral-500">No notes recorded</div>
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-neutral-900">
										{{ patient.nurse_on_duty || "Not assigned" }}
									</div>
								</td>
							</tr>
						</tbody>
					</table>

					<div v-if="patientsReportData.length === 0" class="text-center py-12">
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
								d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
							/>
						</svg>
						<h3 class="mt-2 text-sm font-medium text-gray-900">
							No patient records found
						</h3>
						<p class="mt-1 text-sm text-gray-500">
							Try adjusting your date range or search filters.
						</p>
					</div>
				</div>

				<!-- Pagination -->
				<div
					v-if="patientsReportData.length > 0"
					class="px-6 py-4 border-t border-neutral-200"
				>
					<div class="flex items-center justify-between">
						<div class="text-sm text-gray-700">
							Showing
							{{ (paginationInfo.current_page - 1) * paginationInfo.per_page + 1 }} to
							{{
								Math.min(
									paginationInfo.current_page * paginationInfo.per_page,
									paginationInfo.total
								)
							}}
							of {{ paginationInfo.total }} results
						</div>
						<div class="flex space-x-2">
							<button
								@click="loadPatientsReport(paginationInfo.current_page - 1)"
								:disabled="paginationInfo.current_page <= 1"
								class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
							>
								Previous
							</button>
							<span class="px-3 py-1 text-sm bg-primary text-white rounded">
								{{ paginationInfo.current_page }} of {{ paginationInfo.last_page }}
							</span>
							<button
								@click="loadPatientsReport(paginationInfo.current_page + 1)"
								:disabled="paginationInfo.current_page >= paginationInfo.last_page"
								class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
							>
								Next
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
