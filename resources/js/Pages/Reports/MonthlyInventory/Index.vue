<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
	reports: Object,
	filters: Object,
	canGenerateReport: Boolean,
	isAdmin: Boolean,
});

const generateForm = useForm({
	month: new Date().getMonth() + 1,
	year: new Date().getFullYear(),
});

const deleteForm = useForm({});

const showConfirmDialog = ref(false);
const reportToDelete = ref(null);

const submitGenerate = () => {
	generateForm.post(route("monthly-reports.generate"));
};

const confirmDelete = (report) => {
	reportToDelete.value = report;
	showConfirmDialog.value = true;
};

const cancelDelete = () => {
	showConfirmDialog.value = false;
	reportToDelete.value = null;
};

const deleteReport = () => {
	if (reportToDelete.value) {
		deleteForm.delete(route("monthly-reports.destroy", reportToDelete.value.id), {
			onSuccess: () => {
				showConfirmDialog.value = false;
				reportToDelete.value = null;
			},
			onError: () => {
				console.error("Failed to delete report");
			},
		});
	}
};

const getMonthName = (month) => {
	const months = [
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
	return months[month - 1] || "Unknown";
};

const submittedCount = computed(() => {
	return props.reports.data
		? props.reports.data.filter((report) => report.status === "submitted").length
		: 0;
});

const draftCount = computed(() => {
	return props.reports.data
		? props.reports.data.filter((report) => report.status === "draft").length
		: 0;
});
</script>

<template>
	<Head title="Monthly Inventory Reports" />

	<div class="px-4 sm:px-6 lg:px-8">
		<!-- Header -->
		<div class="sm:flex sm:items-center">
			<div class="sm:flex-auto">
				<h1 class="text-3xl font-bold text-gray-900">Monthly Inventory Reports</h1>
				<p class="mt-2 text-sm text-gray-700">
					Generate and manage monthly inventory reports for your campus.
				</p>
			</div>
			<div v-if="canGenerateReport" class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
				<div class="flex items-center space-x-3">
					<select
						v-model="generateForm.month"
						class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
					>
						<option
							v-for="(month, index) in [
								'January',
								'February',
								'March',
								'April',
								'May',
								'June',
								'July',
								'August',
								'September',
								'October',
								'November',
								'December',
							]"
							:key="index"
							:value="index + 1"
						>
							{{ month }}
						</option>
					</select>
					<select
						v-model="generateForm.year"
						class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
					>
						<option v-for="year in [2024, 2025, 2026]" :key="year" :value="year">
							{{ year }}
						</option>
					</select>
					<button
						@click="submitGenerate"
						:disabled="generateForm.processing"
						class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
					>
						<svg
							v-if="generateForm.processing"
							class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
							xmlns="http://www.w3.org/2000/svg"
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
						{{ generateForm.processing ? "Generating..." : "Generate Report" }}
					</button>
				</div>
			</div>
		</div>

		<!-- Filters -->
		<div v-if="reports.data.length > 0" class="mt-6">
			<div class="bg-white shadow rounded-lg p-4">
				<h3 class="text-lg font-medium text-gray-900 mb-4">Report History</h3>
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div class="bg-blue-50 p-3 rounded-lg">
						<div class="text-2xl font-bold text-blue-600">{{ reports.total || 0 }}</div>
						<div class="text-sm text-blue-600">Total Reports</div>
					</div>
					<div class="bg-green-50 p-3 rounded-lg">
						<div class="text-2xl font-bold text-green-600">{{ submittedCount }}</div>
						<div class="text-sm text-green-600">Submitted Reports</div>
					</div>
					<div class="bg-yellow-50 p-3 rounded-lg">
						<div class="text-2xl font-bold text-yellow-600">{{ draftCount }}</div>
						<div class="text-sm text-yellow-600">Draft Reports</div>
					</div>
				</div>
			</div>

			<!-- Admin Compilation Link -->
			<div v-if="isAdmin" class="mt-6">
				<Link
					:href="route('monthly-reports.compilation')"
					class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
				>
					<svg
						class="-ml-1 mr-2 h-5 w-5"
						xmlns="http://www.w3.org/2000/svg"
						fill="none"
						viewBox="0 0 24 24"
						stroke="currentColor"
					>
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
						/>
					</svg>
					View All Campus Reports Compilation
				</Link>
			</div>
		</div>

		<!-- Reports Table -->
		<div class="mt-8 flow-root">
			<div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
				<div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
					<div
						class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
					>
						<table class="min-w-full divide-y divide-gray-300">
							<thead class="bg-gray-50">
								<tr>
									<th
										scope="col"
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Report Period
									</th>
									<th
										scope="col"
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Campus
									</th>
									<th
										scope="col"
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Status
									</th>
									<th
										scope="col"
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Generated By
									</th>
									<th
										scope="col"
										class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
									>
										Created Date
									</th>
									<th scope="col" class="relative px-6 py-3">
										<span class="sr-only">Actions</span>
									</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr v-for="report in reports.data" :key="report.id">
									<td
										class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
									>
										{{ getMonthName(report.report_month) }} {{ report.report_year }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
										{{ report.campus }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap">
										<span
											:class="[
												'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
												report.status === 'draft'
													? 'bg-yellow-100 text-yellow-800'
													: report.status === 'submitted'
													? 'bg-green-100 text-green-800'
													: 'bg-gray-100 text-gray-800',
											]"
										>
											{{ report.status.charAt(0).toUpperCase() + report.status.slice(1) }}
										</span>
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
										{{ report.generated_by?.name || "N/A" }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
										{{ new Date(report.created_at).toLocaleDateString() }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
										<div class="flex justify-end space-x-2">
											<Link
												:href="route('monthly-reports.show', report.id)"
												class="text-blue-600 hover:text-blue-900"
											>
												View
											</Link>
											<button
												v-if="report.status === 'draft'"
												@click="confirmDelete(report)"
												class="text-red-600 hover:text-red-900"
												:disabled="deleteForm.processing"
											>
												Cancel
											</button>
										</div>
									</td>
								</tr>
								<tr v-if="reports.data.length === 0">
									<td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
										No reports found. Generate your first monthly report to get started.
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<!-- Pagination -->
		<div
			v-if="reports.links"
			class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4"
		>
			<div class="flex flex-1 justify-between sm:hidden">
				<Link
					v-if="reports.prev_page_url"
					:href="reports.prev_page_url"
					class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
				>
					Previous
				</Link>
				<Link
					v-if="reports.next_page_url"
					:href="reports.next_page_url"
					class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
				>
					Next
				</Link>
			</div>
			<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
				<div>
					<p class="text-sm text-gray-700">
						Showing
						<span class="font-medium">{{ reports.from || 0 }}</span>
						to
						<span class="font-medium">{{ reports.to || 0 }}</span>
						of
						<span class="font-medium">{{ reports.total || 0 }}</span>
						results
					</p>
				</div>
				<div>
					<nav
						class="isolate inline-flex -space-x-px rounded-md shadow-sm"
						aria-label="Pagination"
					>
						<Link
							v-if="reports.prev_page_url"
							:href="reports.prev_page_url"
							class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50"
						>
							<span class="sr-only">Previous</span>
							<svg
								class="h-5 w-5"
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 20 20"
								fill="currentColor"
								aria-hidden="true"
							>
								<path
									fill-rule="evenodd"
									d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
									clip-rule="evenodd"
								/>
							</svg>
						</Link>

						<template v-for="link in reports.links" :key="link.label">
							<Link
								v-if="
									link.url &&
									link.label !== '&laquo; Previous' &&
									link.label !== 'Next &raquo;'
								"
								:href="link.url"
								:class="[
									'relative inline-flex items-center border px-4 py-2 text-sm font-medium',
									link.active
										? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
										: 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
								]"
								v-html="link.label"
							></Link>
						</template>

						<Link
							v-if="reports.next_page_url"
							:href="reports.next_page_url"
							class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50"
						>
							<span class="sr-only">Next</span>
							<svg
								class="h-5 w-5"
								xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 20 20"
								fill="currentColor"
								aria-hidden="true"
							>
								<path
									fill-rule="evenodd"
									d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
									clip-rule="evenodd"
								/>
							</svg>
						</Link>
					</nav>
				</div>
			</div>
		</div>

		<!-- Confirmation Dialog -->
		<div
			v-if="showConfirmDialog"
			class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
		>
			<div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
				<div class="mt-3 text-center">
					<div
						class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100"
					>
						<svg
							class="h-6 w-6 text-red-600"
							fill="none"
							viewBox="0 0 24 24"
							stroke="currentColor"
						>
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M6 18L18 6M6 6l12 12"
							/>
						</svg>
					</div>
					<h3 class="text-lg font-medium text-gray-900 mt-2">Cancel Report</h3>
					<div class="mt-2 px-7 py-3">
						<p class="text-sm text-gray-500">
							Are you sure you want to cancel this draft report? This action cannot be
							undone and all draft data will be lost.
						</p>
						<div v-if="reportToDelete" class="mt-3 text-sm text-gray-700">
							<strong
								>{{ getMonthName(reportToDelete.report_month) }}
								{{ reportToDelete.report_year }}</strong
							>
							- {{ reportToDelete.campus }}
						</div>
					</div>
					<div class="flex justify-center space-x-4 mt-4">
						<button
							@click="cancelDelete"
							class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
						>
							Keep Report
						</button>
						<button
							@click="deleteReport"
							:disabled="deleteForm.processing"
							class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
						>
							{{ deleteForm.processing ? "Canceling..." : "Cancel Report" }}
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
