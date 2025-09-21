<script setup>
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
	stats: Object,
	lowStockItems: Array,
	nearingExpiry: Array,
	recentPatients: Array,
});

// Debug log to see what data we're receiving
console.log("=== DASHBOARD DEBUG DATA ===");
console.log("Stats:", props.stats);
console.log("Low stock items count:", props.lowStockItems?.length);
console.log("Low stock items:", props.lowStockItems);
console.log("Nearing expiry count:", props.nearingExpiry?.length);
console.log("Nearing expiry items:", props.nearingExpiry);
</script>

<template>
	<Head title="Dashboard" />

	<div class="px-4 sm:px-6 lg:px-8">
		<!-- Page header -->
		<div class="page-header">
			<h1 class="page-title">Dashboard</h1>
			<p class="page-subtitle">
				Welcome to UZI Care - Clinic Management System
				<span
					v-if="stats?.campus && stats?.userRole !== 'admin'"
					class="text-primary font-medium"
				>
					({{ stats.campus }})
				</span>
				<span v-if="stats?.userRole === 'admin'" class="text-primary font-medium">
					(System Administrator - All Campuses)
				</span>
			</p>
		</div>

		<!-- Stats cards -->
		<div class="stats-grid mb-8">
			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div
								class="w-8 h-8 bg-primary rounded-full flex items-center justify-center"
							>
								<svg
									class="w-5 h-5 text-white"
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
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-neutral-500 truncate">
									Today's Visits
								</dt>
								<dd class="text-3xl font-bold text-neutral-900">
									{{ stats.todayVisits }}
								</dd>
							</dl>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div
								class="w-8 h-8 bg-warning rounded-full flex items-center justify-center"
							>
								<svg
									class="w-5 h-5 text-white"
									fill="none"
									stroke="currentColor"
									viewBox="0 0 24 24"
								>
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2"
										d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
									/>
								</svg>
							</div>
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-neutral-500 truncate">
									Low Stock Items
								</dt>
								<dd class="text-3xl font-bold text-neutral-900">
									{{ stats.lowStockCount }}
								</dd>
							</dl>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div
								class="w-8 h-8 bg-danger rounded-full flex items-center justify-center"
							>
								<svg
									class="w-5 h-5 text-white"
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
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-neutral-500 truncate">
									Nearing Expiry
								</dt>
								<dd class="text-3xl font-bold text-neutral-900">
									{{ stats.nearingExpiryCount }}
								</dd>
							</dl>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="p-5">
					<div class="flex items-center">
						<div class="flex-shrink-0">
							<div
								class="w-8 h-8 bg-success rounded-full flex items-center justify-center"
							>
								<svg
									class="w-5 h-5 text-white"
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
						</div>
						<div class="ml-5 w-0 flex-1">
							<dl>
								<dt class="text-sm font-medium text-neutral-500 truncate">
									Total Patients
								</dt>
								<dd class="text-3xl font-bold text-neutral-900">
									{{ stats.totalPatients }}
								</dd>
							</dl>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Quick actions -->
		<div class="mb-8">
			<h2 class="text-lg font-medium text-neutral-900 mb-4">Quick Actions</h2>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
				<Link :href="route('ehr.index')" class="card-hover p-6">
					<div class="flex items-center">
						<div
							class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center"
						>
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
									d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<h3 class="text-sm font-medium text-neutral-900">Add New Record</h3>
							<p class="text-sm text-neutral-500">Create patient EHR</p>
						</div>
					</div>
				</Link>

				<Link :href="route('inventory.index')" class="card-hover p-6">
					<div class="flex items-center">
						<div
							class="w-10 h-10 bg-success/10 rounded-lg flex items-center justify-center"
						>
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
									d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
								/>
							</svg>
						</div>
						<div class="ml-4">
							<h3 class="text-sm font-medium text-neutral-900">Manage Inventory</h3>
							<p class="text-sm text-neutral-500">Update stock levels</p>
						</div>
					</div>
				</Link>

				<Link :href="route('reports.index')" class="card-hover p-6">
					<div class="flex items-center">
						<div
							class="w-10 h-10 bg-secondary/10 rounded-lg flex items-center justify-center"
						>
							<svg
								class="w-6 h-6 text-secondary"
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
						</div>
						<div class="ml-4">
							<h3 class="text-sm font-medium text-neutral-900">View Reports</h3>
							<p class="text-sm text-neutral-500">Analytics & insights</p>
						</div>
					</div>
				</Link>

				<Link :href="route('ai.forecasting')" class="card-hover p-6">
					<div class="flex items-center">
						<div class="w-10 h-10 bg-info/10 rounded-lg flex items-center justify-center">
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
							<h3 class="text-sm font-medium text-neutral-900">AI Forecasting</h3>
							<p class="text-sm text-neutral-500">Predictive analytics</p>
						</div>
					</div>
				</Link>
			</div>
		</div>

		<!-- Recent activity and alerts -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
			<!-- Recent patients -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h3 class="text-lg font-medium text-neutral-900">Recent Patients</h3>
				</div>
				<div class="p-6">
					<div class="space-y-4">
						<div
							v-for="patient in recentPatients.slice(0, 5)"
							:key="patient.id"
							class="flex items-center justify-between"
						>
							<div>
								<p class="text-sm font-medium text-neutral-900">
									{{ patient.full_name }}
								</p>
								<p class="text-sm text-neutral-500">
									{{ patient.student_employee_id }} - {{ patient.department_course }}
								</p>
								<p class="text-xs text-neutral-400 mt-1" v-if="patient.chief_complaints">
									{{
										patient.chief_complaints.length > 50
											? patient.chief_complaints.substring(0, 50) + "..."
											: patient.chief_complaints
									}}
								</p>
							</div>
							<div class="text-right">
								<p class="text-sm text-neutral-900">
									{{ new Date(patient.consultation_date_time).toLocaleDateString() }}
								</p>
								<p class="text-xs text-neutral-500">
									{{
										new Date(patient.consultation_date_time).toLocaleTimeString([], {
											hour: "2-digit",
											minute: "2-digit",
										})
									}}
								</p>
							</div>
						</div>
						<div
							v-if="recentPatients.length === 0"
							class="text-center text-neutral-500 py-4"
						>
							No recent patients
						</div>
					</div>
				</div>
			</div>

			<!-- Alerts -->
			<div class="card">
				<div class="px-6 py-4 border-b border-neutral-200">
					<h3 class="text-lg font-medium text-neutral-900">Alerts & Notifications</h3>
				</div>
				<div class="p-6">
					<div class="space-y-4">
						<!-- Low stock alerts -->
						<div
							v-for="item in lowStockItems.slice(0, 3)"
							:key="item.id"
							class="flex items-center space-x-3"
						>
							<div class="w-2 h-2 bg-warning rounded-full"></div>
							<div class="flex-1">
								<p class="text-sm text-neutral-900">
									{{ item.medicine?.name }} is low in stock
								</p>
								<p class="text-xs text-neutral-500">{{ item.quantity }} remaining</p>
							</div>
						</div>

						<!-- Expiry alerts -->
						<div
							v-for="item in nearingExpiry.slice(0, 3)"
							:key="'expiry-' + item.id"
							class="flex items-center space-x-3"
						>
							<div class="w-2 h-2 bg-danger rounded-full"></div>
							<div class="flex-1">
								<p class="text-sm text-neutral-900">
									{{ item.medicine?.name }} expires soon
								</p>
								<p class="text-xs text-neutral-500">
									Expires: {{ new Date(item.expiry_date).toLocaleDateString() }}
								</p>
							</div>
						</div>

						<!-- Placeholder AI alert -->
						<div class="flex items-center space-x-3">
							<div class="w-2 h-2 bg-info rounded-full"></div>
							<div class="flex-1">
								<p class="text-sm text-neutral-900">
									AI Forecasting: this is a placeholder for Predictions
								</p>
								<p class="text-xs text-neutral-500">sample text</p>
							</div>
						</div>

						<div
							v-if="lowStockItems.length === 0 && nearingExpiry.length === 0"
							class="text-center text-neutral-500 py-4"
						>
							No alerts at the moment
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
