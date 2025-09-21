<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
	stats: Object,
	lowStockItems: Array,
	nearingExpiry: Array,
});
</script>

<template>
	<Head title="Inventory Dashboard" />

	<div class="px-4 sm:px-6 lg:px-8">
		<!-- Page header -->
		<div class="page-header">
			<h1 class="page-title">Inventory Dashboard</h1>
			<p class="page-subtitle">{{ stats?.campus }} - Medicine & Supply Management</p>
		</div>

		<!-- Stats cards -->
		<div class="stats-grid mb-8">
			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
								<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Total Items</dt>
								<dd class="text-lg font-medium text-gray-900">{{ stats?.totalItems || 0 }}</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<Link :href="route('inventory.index')" class="font-medium text-green-700 hover:text-green-900">
							View inventory
						</Link>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
								<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Low Stock Items</dt>
								<dd class="text-lg font-medium text-gray-900">{{ stats?.lowStockCount || 0 }}</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<Link :href="route('inventory.index', { type: 'low_stock' })" class="font-medium text-yellow-700 hover:text-yellow-900">
							View low stock
						</Link>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
								<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Expiring Soon</dt>
								<dd class="text-lg font-medium text-gray-900">{{ stats?.nearingExpiryCount || 0 }}</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<Link :href="route('inventory.index', { type: 'expiring' })" class="font-medium text-red-700 hover:text-red-900">
							View expiring
						</Link>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
								<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Distributions</dt>
								<dd class="text-lg font-medium text-gray-900">Manage</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<Link :href="route('medicine-distributions.index')" class="font-medium text-blue-700 hover:text-blue-900">
							View distributions
						</Link>
					</div>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
			<!-- Low Stock Items -->
			<div class="card">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Low Stock Alert</h3>
					<div v-if="lowStockItems?.length > 0" class="flow-root">
						<ul role="list" class="-my-5 divide-y divide-gray-200">
							<li v-for="item in lowStockItems" :key="item.id" class="py-4">
								<div class="flex items-center justify-between">
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 truncate">{{ item.medicine?.name }}</p>
										<p class="text-sm text-gray-500 truncate">{{ item.medicine?.type }}</p>
										<p class="text-xs text-gray-400">Batch: {{ item.batch_number }}</p>
									</div>
									<div class="flex-shrink-0 text-right">
										<span class="inline-block px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
											{{ item.quantity }} left
										</span>
										<p class="text-xs text-gray-500 mt-1">Threshold: {{ item.low_stock_threshold }}</p>
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div v-else class="text-center py-8">
						<svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<p class="mt-2 text-sm text-gray-500">All items are well stocked!</p>
					</div>
					<div class="mt-6">
						<Link :href="route('monthly-reports.index')" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
							Generate Monthly Report
						</Link>
					</div>
				</div>
			</div>

			<!-- Expiring Items -->
			<div class="card">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Expiring Soon</h3>
					<div v-if="nearingExpiry?.length > 0" class="flow-root">
						<ul role="list" class="-my-5 divide-y divide-gray-200">
							<li v-for="item in nearingExpiry" :key="item.id" class="py-4">
								<div class="flex items-center justify-between">
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 truncate">{{ item.medicine?.name }}</p>
										<p class="text-sm text-gray-500 truncate">{{ item.medicine?.type }}</p>
										<p class="text-xs text-gray-400">Batch: {{ item.batch_number }}</p>
									</div>
									<div class="flex-shrink-0 text-right">
										<span class="inline-block px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
											{{ new Date(item.expiry_date).toLocaleDateString() }}
										</span>
										<p class="text-xs text-gray-500 mt-1">{{ item.quantity }} units</p>
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div v-else class="text-center py-8">
						<svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<p class="mt-2 text-sm text-gray-500">No items expiring soon!</p>
					</div>
					<div class="mt-6">
						<Link :href="route('medicine-distributions.create')" class="w-full flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
							Create Distribution
						</Link>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.page-header {
	@apply mb-8;
}

.page-title {
	@apply text-3xl font-bold text-gray-900;
}

.page-subtitle {
	@apply mt-2 text-sm text-gray-600;
}

.stats-grid {
	@apply grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4;
}

.card {
	@apply bg-white overflow-hidden shadow rounded-lg;
}
</style>