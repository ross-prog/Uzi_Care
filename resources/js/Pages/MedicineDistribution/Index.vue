<template>
	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 bg-white border-b border-gray-200">
					<div class="flex justify-between items-center mb-6">
						<h2 class="text-2xl font-semibold text-gray-900">Medicine Distribution</h2>
						<div v-if="canCreateDistribution">
							<Link
								:href="route('medicine-distributions.create')"
								class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
							>
								Create Distribution
							</Link>
						</div>
					</div>

					<!-- Campus Filter Info -->
					<div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
						<p class="text-sm text-blue-700">
							<strong>Current Campus:</strong> {{ userCampus }}
							<span v-if="$page.props.auth.user.role === 'admin'" class="ml-2 text-xs">
								(Viewing all campuses as Admin)
							</span>
						</p>
					</div>

					<!-- Notifications Section -->
					<div v-if="notifications && notifications.length > 0" class="mb-6">
						<div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
							<div class="flex justify-between items-start mb-3">
								<h3 class="text-lg font-medium text-yellow-800">
									Distribution Notifications ({{ unreadNotifications.length }} unread)
								</h3>
								<div class="flex space-x-2">
									<button
										v-if="unreadNotifications.length > 0"
										@click="markAllNotificationsRead"
										class="text-sm bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-1 rounded-md"
									>
										Mark All Read
									</button>
								</div>
							</div>
							
							<div class="space-y-2 max-h-64 overflow-y-auto">
								<div
									v-for="notification in notifications"
									:key="notification.id"
									:class="[
										'p-3 rounded-md border',
										notification.is_read
											? 'bg-gray-50 border-gray-200'
											: 'bg-white border-yellow-300'
									]"
								>
									<div class="flex justify-between items-start">
										<div class="flex-1">
											<p class="text-sm font-medium text-gray-900">
												{{ notification.title }}
											</p>
											<p class="text-sm text-gray-600 mt-1">
												{{ notification.message }}
											</p>
											<p class="text-xs text-gray-500 mt-2">
												{{ formatDateTime(notification.created_at) }}
											</p>
										</div>
										<div class="flex items-center space-x-2 ml-4">
											<span
												v-if="!notification.is_read"
												class="inline-block w-2 h-2 bg-yellow-400 rounded-full"
												title="Unread"
											></span>
											<button
												v-if="!notification.is_read"
												@click="markNotificationRead(notification.id)"
												class="text-xs text-yellow-600 hover:text-yellow-800"
											>
												Mark Read
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Distributions Table -->
					<div class="overflow-x-auto">
						<table class="min-w-full divide-y divide-gray-200">
							<thead class="bg-gray-50">
								<tr>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Reference
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Medicine
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										From Campus
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										To Campus
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Department
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Quantity
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Status
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Date
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Actions
									</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr v-for="distribution in distributions.data" :key="distribution.id">
									<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
										{{ distribution.reference_number }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap">
										<div class="text-sm font-medium text-gray-900">{{ distribution.medicine.name }}</div>
										<div class="text-sm text-gray-500">{{ distribution.medicine.type }}</div>
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ distribution.from_campus }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ distribution.to_campus }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ distribution.to_department }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ distribution.quantity_distributed }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap">
										<span
											:class="getStatusBadgeClass(distribution.status)"
											class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
										>
											{{ getStatusDisplayName(distribution.status) }}
										</span>
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
										{{ formatDateTime(distribution.distribution_date) }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
										<Link
											:href="route('medicine-distributions.show', distribution.id)"
											class="text-indigo-600 hover:text-indigo-900"
										>
											View
										</Link>
									</td>
								</tr>
								<tr v-if="distributions.data.length === 0">
									<td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
										No distributions found.
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- Pagination -->
					<div v-if="distributions.links" class="mt-6">
						<nav class="flex items-center justify-between">
							<div class="text-sm text-gray-700">
								Showing {{ distributions.from }} to {{ distributions.to }} of {{ distributions.total }} results
							</div>
							<div class="flex space-x-1">
								<template v-for="link in distributions.links" :key="link.label">
									<Link
										v-if="link.url"
										:href="link.url"
										:class="[
											'px-3 py-2 text-sm rounded-md',
											link.active
												? 'bg-blue-500 text-white'
												: 'bg-gray-200 text-gray-700 hover:bg-gray-300'
										]"
										v-html="link.label"
									/>
									<span
										v-else
										:class="[
											'px-3 py-2 text-sm rounded-md',
											'bg-gray-100 text-gray-400 cursor-not-allowed'
										]"
										v-html="link.label"
									/>
								</template>
							</div>
						</nav>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { computed } from 'vue';

const props = defineProps({
	distributions: Object,
	userCampus: String,
	canCreateDistribution: Boolean,
	notifications: Array,
});

const unreadNotifications = computed(() => {
	return props.notifications ? props.notifications.filter(n => !n.is_read) : [];
});

const markNotificationRead = (notificationId) => {
	router.post(route('medicine-distributions.notifications.mark-read'), {
		notification_id: notificationId
	}, {
		preserveScroll: true,
		onSuccess: () => {
			// The page will be refreshed with updated notification data
		}
	});
};

const markAllNotificationsRead = () => {
	router.post(route('medicine-distributions.notifications.mark-all-read'), {}, {
		preserveScroll: true,
		onSuccess: () => {
			// The page will be refreshed with updated notification data
		}
	});
};

const getStatusBadgeClass = (status) => {
	const classes = {
		pending: 'bg-yellow-100 text-yellow-800',
		approved: 'bg-blue-100 text-blue-800',
		completed: 'bg-green-100 text-green-800',
		cancelled: 'bg-red-100 text-red-800',
	};
	return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusDisplayName = (status) => {
	const names = {
		pending: 'Pending',
		approved: 'Approved',
		completed: 'Completed',
		cancelled: 'Cancelled',
	};
	return names[status] || status;
};

const formatDateTime = (dateTime) => {
	return new Date(dateTime).toLocaleString();
};
</script>