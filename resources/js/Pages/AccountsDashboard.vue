<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
	stats: Object,
	recentUsers: Array,
	pendingApprovals: Array,
});
</script>

<template>
	<Head title="Accounts Dashboard" />

	<div class="px-4 sm:px-6 lg:px-8">
		<!-- Page header -->
		<div class="page-header">
			<h1 class="page-title">Accounts Dashboard</h1>
			<p class="page-subtitle">{{ stats?.campus }} - User Management & Access Control</p>
		</div>

		<!-- Stats cards -->
		<div class="stats-grid mb-8">
			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
								<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
								<dd class="text-lg font-medium text-gray-900">{{ stats?.totalUsers || 0 }}</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<Link :href="route('users.index')" class="font-medium text-blue-700 hover:text-blue-900">
							Manage users
						</Link>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
								<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
								<dd class="text-lg font-medium text-gray-900">{{ stats?.activeUsers || 0 }}</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<Link :href="route('users.index', { status: 'active' })" class="font-medium text-green-700 hover:text-green-900">
							View active
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
								<dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
								<dd class="text-lg font-medium text-gray-900">{{ stats?.pendingUsers || 0 }}</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<Link :href="route('users.index', { status: 'pending' })" class="font-medium text-yellow-700 hover:text-yellow-900">
							Review pending
						</Link>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
								<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.586-3H4m0 6h16m0 6H4" />
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-gray-500 truncate">Roles</dt>
								<dd class="text-lg font-medium text-gray-900">{{ stats?.roleCount || 4 }}</dd>
							</dl>
						</div>
					</div>
				</div>
				<div class="bg-gray-50 px-5 py-3">
					<div class="text-sm">
						<span class="font-medium text-purple-700">Admin, Nurse, Inventory, Accounts</span>
					</div>
				</div>
			</div>
		</div>

		<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
			<!-- Recent Users -->
			<div class="card">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Users</h3>
					<div v-if="recentUsers?.length > 0" class="flow-root">
						<ul role="list" class="-my-5 divide-y divide-gray-200">
							<li v-for="user in recentUsers" :key="user.id" class="py-4">
								<div class="flex items-center justify-between">
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
										<p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
										<p class="text-xs text-gray-400">{{ user.campus }}</p>
									</div>
									<div class="flex-shrink-0 text-right">
										<span :class="[
											'inline-block px-2 py-1 text-xs font-medium rounded-full',
											user.role === 'admin' ? 'bg-red-100 text-red-800' :
											user.role === 'nurse' ? 'bg-green-100 text-green-800' :
											user.role === 'inventory_manager' ? 'bg-blue-100 text-blue-800' :
											user.role === 'account_manager' ? 'bg-purple-100 text-purple-800' :
											'bg-gray-100 text-gray-800'
										]">
											{{ user.role.replace('_', ' ') }}
										</span>
										<p class="text-xs text-gray-500 mt-1">
											{{ new Date(user.created_at).toLocaleDateString() }}
										</p>
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div v-else class="text-center py-8">
						<svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
						</svg>
						<p class="mt-2 text-sm text-gray-500">No recent users</p>
					</div>
					<div class="mt-6">
						<Link :href="route('users.create')" class="w-full flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
							Create New User
						</Link>
					</div>
				</div>
			</div>

			<!-- Pending Approvals -->
			<div class="card">
				<div class="px-4 py-5 sm:p-6">
					<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Pending Approvals</h3>
					<div v-if="pendingApprovals?.length > 0" class="flow-root">
						<ul role="list" class="-my-5 divide-y divide-gray-200">
							<li v-for="user in pendingApprovals" :key="user.id" class="py-4">
								<div class="flex items-center justify-between">
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
										<p class="text-sm text-gray-500 truncate">{{ user.email }}</p>
										<p class="text-xs text-gray-400">{{ user.campus }}</p>
									</div>
									<div class="flex-shrink-0 text-right">
										<span class="inline-block px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
											{{ user.role.replace('_', ' ') }}
										</span>
										<div class="flex space-x-2 mt-2">
											<Link :href="route('users.approve', user.id)" method="patch" class="text-xs text-green-600 hover:text-green-900">
												Approve
											</Link>
											<Link :href="route('users.reject', user.id)" method="patch" class="text-xs text-red-600 hover:text-red-900">
												Reject
											</Link>
										</div>
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div v-else class="text-center py-8">
						<svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
						</svg>
						<p class="mt-2 text-sm text-gray-500">No pending approvals!</p>
					</div>
					<div class="mt-6">
						<Link :href="route('users.index')" class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
							Manage All Users
						</Link>
					</div>
				</div>
			</div>
		</div>

		<!-- Quick Actions -->
		<div class="card">
			<div class="px-4 py-5 sm:p-6">
				<h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
					<Link :href="route('users.create')" class="group relative bg-white p-6 rounded-lg border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-200">
						<div>
							<span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-600 ring-4 ring-white">
								<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
								</svg>
							</span>
						</div>
						<div class="mt-4">
							<h3 class="text-lg font-medium text-gray-900 group-hover:text-blue-600">Create User</h3>
							<p class="text-sm text-gray-500">Add new staff member</p>
						</div>
					</Link>

					<Link :href="route('users.index', { role: 'nurse' })" class="group relative bg-white p-6 rounded-lg border border-gray-200 hover:border-green-300 hover:shadow-md transition-all duration-200">
						<div>
							<span class="rounded-lg inline-flex p-3 bg-green-50 text-green-600 ring-4 ring-white">
								<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
								</svg>
							</span>
						</div>
						<div class="mt-4">
							<h3 class="text-lg font-medium text-gray-900 group-hover:text-green-600">Nurses</h3>
							<p class="text-sm text-gray-500">Manage nursing staff</p>
						</div>
					</Link>

					<Link :href="route('users.index', { role: 'inventory_manager' })" class="group relative bg-white p-6 rounded-lg border border-gray-200 hover:border-yellow-300 hover:shadow-md transition-all duration-200">
						<div>
							<span class="rounded-lg inline-flex p-3 bg-yellow-50 text-yellow-600 ring-4 ring-white">
								<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
								</svg>
							</span>
						</div>
						<div class="mt-4">
							<h3 class="text-lg font-medium text-gray-900 group-hover:text-yellow-600">Inventory</h3>
							<p class="text-sm text-gray-500">Manage inventory staff</p>
						</div>
					</Link>

					<Link :href="route('users.index', { role: 'account_manager' })" class="group relative bg-white p-6 rounded-lg border border-gray-200 hover:border-purple-300 hover:shadow-md transition-all duration-200">
						<div>
							<span class="rounded-lg inline-flex p-3 bg-purple-50 text-purple-600 ring-4 ring-white">
								<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
								</svg>
							</span>
						</div>
						<div class="mt-4">
							<h3 class="text-lg font-medium text-gray-900 group-hover:text-purple-600">Accounts</h3>
							<p class="text-sm text-gray-500">Manage account staff</p>
						</div>
					</Link>
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