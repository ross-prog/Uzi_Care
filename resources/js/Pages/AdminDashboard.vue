<template>
	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<!-- Header -->
			<div class="mb-8">
				<h2 class="text-3xl font-bold text-gray-900">Administrator Dashboard</h2>
				<p class="mt-2 text-gray-600">System overview and management tools</p>
			</div>

			<!-- Stats Grid -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
				<div class="bg-white overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="flex items-center justify-center h-8 w-8 rounded-md bg-blue-500 text-white">
									<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">
										Today's Visits
									</dt>
									<dd class="text-lg font-medium text-gray-900">
										{{ stats.todayVisits }}
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="flex items-center justify-center h-8 w-8 rounded-md bg-green-500 text-white">
									<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">
										Total Users
									</dt>
									<dd class="text-lg font-medium text-gray-900">
										{{ stats.totalUsers }}
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="flex items-center justify-center h-8 w-8 rounded-md bg-yellow-500 text-white">
									<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">
										Low Stock Alerts
									</dt>
									<dd class="text-lg font-medium text-gray-900">
										{{ stats.lowStockCount }}
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white overflow-hidden shadow rounded-lg">
					<div class="p-5">
						<div class="flex items-center">
							<div class="flex-shrink-0">
								<div class="flex items-center justify-center h-8 w-8 rounded-md bg-red-500 text-white">
									<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
									</svg>
								</div>
							</div>
							<div class="ml-5 w-0 flex-1">
								<dl>
									<dt class="text-sm font-medium text-gray-500 truncate">
										Expiring Soon
									</dt>
									<dd class="text-lg font-medium text-gray-900">
										{{ stats.nearingExpiryCount }}
									</dd>
								</dl>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Two Column Layout -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
				<!-- User Role Distribution -->
				<div class="bg-white overflow-hidden shadow rounded-lg">
					<div class="p-6">
						<h3 class="text-lg font-medium text-gray-900 mb-4">Users by Role</h3>
						<div class="space-y-3">
							<div v-for="(count, role) in stats.usersByRole" :key="role" class="flex items-center justify-between">
								<div class="flex items-center">
									<span
										:class="getRoleBadgeClass(role)"
										class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-3"
									>
										{{ getRoleDisplayName(role) }}
									</span>
								</div>
								<span class="text-sm font-medium text-gray-900">{{ count }}</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Recent Users -->
				<div class="bg-white overflow-hidden shadow rounded-lg">
					<div class="p-6">
						<div class="flex items-center justify-between mb-4">
							<h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
							<Link :href="route('users.index')" class="text-sm text-blue-600 hover:text-blue-900">
								View all
							</Link>
						</div>
						<div class="space-y-3">
							<div v-for="user in recentUsers" :key="user.id" class="flex items-center justify-between py-2">
								<div>
									<div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
									<div class="text-xs text-gray-500">{{ user.email }} • {{ getRoleDisplayName(user.role) }}</div>
								</div>
								<div class="text-xs text-gray-400">
									{{ formatDate(user.created_at) }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Weekly Activity Chart -->
			<div class="mt-8 bg-white overflow-hidden shadow rounded-lg">
				<div class="p-6">
					<h3 class="text-lg font-medium text-gray-900 mb-4">Weekly Consultation Activity</h3>
					<div class="space-y-2">
						<div v-for="consultation in weeklyConsultations" :key="consultation.date" class="flex items-center">
							<div class="w-20 text-sm text-gray-600">{{ formatDate(consultation.date) }}</div>
							<div class="flex-1 ml-4">
								<div class="bg-gray-200 rounded-full h-2">
									<div 
										class="bg-blue-500 h-2 rounded-full" 
										:style="{ width: (consultation.count / Math.max(...weeklyConsultations.map(c => c.count)) * 100) + '%' }"
									></div>
								</div>
							</div>
							<div class="w-12 text-sm text-gray-900 text-right">{{ consultation.count }}</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Quick Actions -->
			<div class="mt-8 bg-white overflow-hidden shadow rounded-lg">
				<div class="p-6">
					<h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
					<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
						<Link 
							:href="route('users.create')"
							class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50"
						>
							<div class="flex-shrink-0">
								<svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
								</svg>
							</div>
							<div class="ml-3">
								<div class="text-sm font-medium text-gray-900">Create User</div>
								<div class="text-xs text-gray-500">Add new staff member</div>
							</div>
						</Link>

						<Link 
							:href="route('ehr.index')"
							class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50"
						>
							<div class="flex-shrink-0">
								<svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
								</svg>
							</div>
							<div class="ml-3">
								<div class="text-sm font-medium text-gray-900">EHR System</div>
								<div class="text-xs text-gray-500">Patient records</div>
							</div>
						</Link>

						<Link 
							:href="route('inventory.index')"
							class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50"
						>
							<div class="flex-shrink-0">
								<svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
								</svg>
							</div>
							<div class="ml-3">
								<div class="text-sm font-medium text-gray-900">Inventory</div>
								<div class="text-xs text-gray-500">Manage supplies</div>
							</div>
						</Link>

						<Link 
							:href="route('reports.index')"
							class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50"
						>
							<div class="flex-shrink-0">
								<svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 00-2-2z" />
								</svg>
							</div>
							<div class="ml-3">
								<div class="text-sm font-medium text-gray-900">Reports</div>
								<div class="text-xs text-gray-500">View analytics</div>
							</div>
						</Link>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
	stats: Object,
	recentUsers: Array,
	weeklyConsultations: Array,
});

const formatDate = (dateString) => {
	return new Date(dateString).toLocaleDateString();
};

const getRoleDisplayName = (role) => {
	const roleMap = {
		admin: 'Administrator',
		nurse: 'Nurse',
		inventory_manager: 'Inventory Manager',
		account_manager: 'Account Manager',
	};
	return roleMap[role] || role;
};

const getRoleBadgeClass = (role) => {
	const roleClasses = {
		admin: 'bg-purple-100 text-purple-800',
		nurse: 'bg-blue-100 text-blue-800',
		inventory_manager: 'bg-green-100 text-green-800',
		account_manager: 'bg-yellow-100 text-yellow-800',
	};
	return roleClasses[role] || 'bg-gray-100 text-gray-800';
};
</script>