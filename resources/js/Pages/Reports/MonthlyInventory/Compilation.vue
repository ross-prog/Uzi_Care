<script setup>
import { Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
	month: Number,
	year: Number,
	campusData: Object,
	allMedicines: Array,
	itemsByCategory: Object,
});

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

const reportPeriod = computed(() => {
	return `${monthNames[props.month - 1]} ${props.year}`;
});

const getCampusData = (campus, medicineName) => {
	if (!props.campusData[campus] || !props.campusData[campus].inventory_data) {
		return { current_stock: "-", quantity_to_order: "-" };
	}

	const item = props.campusData[campus].inventory_data.find(
		(item) => item.medicine_name === medicineName
	);

	return item
		? {
				current_stock: item.current_stock,
				quantity_to_order: item.quantity_to_order,
		  }
		: { current_stock: "-", quantity_to_order: "-" };
};

const getTotalQuantityNeeded = (medicineName) => {
	let total = 0;
	Object.keys(props.campusData).forEach((campus) => {
		const data = getCampusData(campus, medicineName);
		if (data.quantity_to_order !== "-" && !isNaN(data.quantity_to_order)) {
			total += parseInt(data.quantity_to_order) || 0;
		}
	});
	return total;
};

const getTotalStock = (itemName) => {
	let total = 0;
	Object.keys(props.campusData).forEach((campus) => {
		const data = getCampusData(campus, itemName);
		if (data.current_stock !== "-" && !isNaN(data.current_stock)) {
			total += parseInt(data.current_stock) || 0;
		}
	});
	return total;
};

// Campus supply status helper functions
const getCampusSupplyCount = (campus) => {
	if (!props.itemsByCategory?.Supplies) return 0;
	let count = 0;
	props.itemsByCategory.Supplies.forEach((supplyName) => {
		const data = getCampusData(campus, supplyName);
		if (data.current_stock !== "-") {
			count++;
		}
	});
	return count;
};

const getCampusTotalStock = (campus) => {
	if (!props.itemsByCategory?.Supplies) return 0;
	let total = 0;
	props.itemsByCategory.Supplies.forEach((supplyName) => {
		const data = getCampusData(campus, supplyName);
		if (data.current_stock !== "-" && !isNaN(data.current_stock)) {
			total += parseInt(data.current_stock) || 0;
		}
	});
	return total;
};

const getCampusAverageStock = (campus) => {
	const count = getCampusSupplyCount(campus);
	const total = getCampusTotalStock(campus);
	return count > 0 ? Math.round(total / count) : 0;
};

const getOverallTotalStock = () => {
	if (!props.itemsByCategory?.Supplies) return 0;
	let total = 0;
	props.itemsByCategory.Supplies.forEach((supplyName) => {
		total += getTotalStock(supplyName);
	});
	return total;
};

const getOverallAverageStock = () => {
	const itemCount = props.itemsByCategory?.Supplies?.length || 0;
	const totalStock = getOverallTotalStock();
	const campusCount = Object.keys(props.campusData).length;
	return itemCount > 0 && campusCount > 0
		? Math.round(totalStock / (itemCount * campusCount))
		: 0;
};

// Export as Excel/XLSX with rich formatting
const exportToExcel = () => {
	const params = new URLSearchParams({
		month: props.month,
		year: props.year,
	});

	window.location.href = `/monthly-reports/compilation/export-excel?${params}`;
};
</script>

<template>
	<Head title="Monthly Inventory Reports Compilation" />

	<div class="px-4 sm:px-6 lg:px-8">
		<!-- Header -->
		<div class="sm:flex sm:items-center">
			<div class="sm:flex-auto">
				<h1 class="text-3xl font-bold text-gray-900">
					Monthly Inventory Reports Compilation
				</h1>
				<p class="mt-2 text-sm text-gray-700">{{ reportPeriod }} - All Campus Reports</p>
			</div>
			<div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
				<button
					@click="exportToExcel"
					class="inline-flex items-center justify-center rounded-md border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-purple-700"
				>
					Export Excel (.xlsx)
				</button>
			</div>
		</div>

		<!-- Campus Reports Summary -->
		<div class="mt-8">
			<div class="bg-white shadow overflow-hidden sm:rounded-lg">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
						Campus Report Status
					</h3>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
						<div
							v-for="(data, campus) in campusData"
							:key="campus"
							class="bg-green-50 p-4 rounded-lg border border-green-200"
						>
							<div class="font-medium text-green-800">{{ campus }}</div>
							<div class="text-sm text-green-600">Submitted: {{ data.generated_at }}</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Compilation Table -->
		<div class="mt-8">
			<div class="bg-white shadow overflow-hidden sm:rounded-lg">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
						Medicine Inventory Compilation
					</h3>

					<div v-if="Object.keys(campusData).length > 0" class="space-y-8">
						<!-- Main Compilation Table (All Items) -->
						<div class="overflow-x-auto">
							<table class="min-w-full divide-y divide-gray-300">
								<thead class="bg-gray-50">
									<tr>
										<th
											rowspan="2"
											class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300"
										>
											Item Name
										</th>
										<th
											v-for="(data, campus) in campusData"
											:key="campus"
											colspan="2"
											class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300"
										>
											{{ campus }}
											<div class="text-xs text-gray-400 normal-case">
												{{ data.generated_at }}
											</div>
										</th>
										<th
											rowspan="2"
											class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-blue-50"
										>
											Total Needed<br />Across Campus
										</th>
									</tr>
									<tr>
										<template
											v-for="(data, campus) in campusData"
											:key="`${campus}-header`"
										>
											<th
												class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200"
											>
												Current Stock
											</th>
											<th
												class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300"
											>
												Quantity to Order
											</th>
										</template>
									</tr>
								</thead>
								<tbody class="bg-white divide-y divide-gray-200">
									<tr
										v-for="medicine in itemsByCategory?.Medicines || allMedicines"
										:key="medicine"
									>
										<td
											class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300"
										>
											{{ medicine }}
										</td>
										<template
											v-for="(data, campus) in campusData"
											:key="`${medicine}-${campus}`"
										>
											<td
												class="px-3 py-4 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-200"
											>
												{{ getCampusData(campus, medicine).current_stock }}
											</td>
											<td
												class="px-3 py-4 whitespace-nowrap text-sm text-center text-gray-900 border-r border-gray-300"
											>
												{{ getCampusData(campus, medicine).quantity_to_order }}
											</td>
										</template>
										<td
											class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-blue-900 bg-blue-50"
										>
											{{ getTotalQuantityNeeded(medicine) }}
										</td>
									</tr>
								</tbody>
							</table>
						</div>

						<!-- Supplies Total Quantity Status Table -->
						<div class="mt-8">
							<h4 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
								<span class="w-2 h-2 rounded-full mr-2 bg-blue-500"></span>
								Overall Supplies Stock Status
								<span class="ml-2 text-sm text-gray-600">
									({{ itemsByCategory?.Supplies?.length || 0 }} items)
								</span>
							</h4>

							<div v-if="itemsByCategory?.Supplies?.length > 0">
								<div class="overflow-x-auto">
									<table
										class="min-w-full divide-y divide-gray-300 border border-gray-200 rounded-lg bg-blue-50"
									>
										<thead class="bg-blue-100">
											<tr>
												<th
													class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider border-r border-blue-200"
												>
													Supply Item
												</th>
												<th
													class="px-6 py-3 text-center text-xs font-medium text-blue-800 uppercase tracking-wider"
												>
													Total Stock Across All Campuses
												</th>
											</tr>
										</thead>
										<tbody class="bg-white divide-y divide-gray-200">
											<tr
												v-for="supplyName in itemsByCategory.Supplies"
												:key="`total-supply-${supplyName}`"
											>
												<td
													class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200"
												>
													{{ supplyName }}
												</td>
												<td
													class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold text-blue-900"
												>
													{{ getTotalStock(supplyName) }}
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<div
								v-else
								class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200"
							>
								<p class="text-gray-500">
									No supplies data available for {{ reportPeriod }}.
								</p>
							</div>
						</div>
					</div>

					<div v-else class="text-center py-8">
						<p class="text-gray-500">
							No submitted reports found for {{ reportPeriod }}.
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
