<template>
	<div class="py-12">
		<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 bg-white border-b border-gray-200">
					<div class="flex justify-between items-center mb-6">
						<h2 class="text-2xl font-semibold text-gray-900">Distribution Details</h2>
						<Link
							:href="route('medicine-distributions.index')"
							class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
						>
							Back to Distributions
						</Link>
					</div>

					<!-- Distribution Information Card -->
					<div class="bg-white shadow overflow-hidden sm:rounded-lg">
						<div class="px-4 py-5 sm:px-6">
							<h3 class="text-lg leading-6 font-medium text-gray-900">
								Distribution Information
							</h3>
							<p class="mt-1 max-w-2xl text-sm text-gray-500">
								Reference: {{ distribution.reference_number }}
							</p>
						</div>
						<div class="border-t border-gray-200">
							<dl>
								<div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Status</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
										<span :class="getStatusClass(distribution.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
											{{ distribution.status.charAt(0).toUpperCase() + distribution.status.slice(1) }}
										</span>
									</dd>
								</div>
								<div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Medicine</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
										{{ distribution.medicine.name }}
										<span v-if="distribution.medicine.dosage_strength" class="text-gray-500">
											({{ distribution.medicine.dosage_strength }})
										</span>
									</dd>
								</div>
								<div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">From Campus</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ distribution.from_campus }}</dd>
								</div>
								<div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">To Campus</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ distribution.to_campus }}</dd>
								</div>
								<div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Department</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ distribution.to_department || 'N/A' }}</dd>
								</div>
								<div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Quantity Distributed</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ distribution.quantity_distributed }}</dd>
								</div>
								<div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Batch Number</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ distribution.batch_number || 'N/A' }}</dd>
								</div>
								<div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
										{{ distribution.expiry_date ? formatDate(distribution.expiry_date) : 'N/A' }}
									</dd>
								</div>
								<div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Distribution Date</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
										{{ formatDateTime(distribution.distribution_date) }}
									</dd>
								</div>
								<div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Distributed By</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
										{{ distribution.distributed_by ? distribution.distributed_by.name : 'N/A' }}
									</dd>
								</div>
								<div v-if="distribution.notes" class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
									<dt class="text-sm font-medium text-gray-500">Notes</dt>
									<dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ distribution.notes }}</dd>
								</div>
							</dl>
						</div>
					</div>

					<!-- Status Update Section (if user can manage distributions) -->
					<div v-if="canManageDistributions && distribution.status !== 'completed'" class="mt-6">
						<div class="bg-white shadow sm:rounded-lg">
							<div class="px-4 py-5 sm:p-6">
								<h3 class="text-lg leading-6 font-medium text-gray-900">Update Status</h3>
								<div class="mt-2 max-w-xl text-sm text-gray-500">
									<p>Change the status of this distribution.</p>
								</div>
								<div class="mt-5">
									<form @submit.prevent="updateStatus">
										<div class="flex items-center space-x-4">
											<select v-model="statusForm.status" class="block w-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
												<option value="pending">Pending</option>
												<option value="approved">Approved</option>
												<option value="completed">Completed</option>
												<option value="cancelled">Cancelled</option>
											</select>
											<button
												type="submit"
												:disabled="statusForm.processing"
												class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
											>
												Update Status
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import { Link, useForm } from '@inertiajs/vue3';

export default {
	name: 'MedicineDistributionShow',
	components: {
		Link,
	},
	props: {
		distribution: {
			type: Object,
			required: true,
		},
	},
	setup(props) {
		const statusForm = useForm({
			status: props.distribution.status,
		});

		const updateStatus = () => {
			statusForm.patch(route('medicine-distributions.update-status', props.distribution.id), {
				preserveScroll: true,
				onSuccess: () => {
					// Status updated successfully
				},
			});
		};

		return {
			statusForm,
			updateStatus,
		};
	},
	computed: {
		canManageDistributions() {
			return ['admin', 'inventory_manager'].includes(this.$page.props.auth.user.role);
		},
	},
	methods: {
		getStatusClass(status) {
			const classes = {
				pending: 'bg-yellow-100 text-yellow-800',
				approved: 'bg-blue-100 text-blue-800',
				completed: 'bg-green-100 text-green-800',
				cancelled: 'bg-red-100 text-red-800',
			};
			return classes[status] || 'bg-gray-100 text-gray-800';
		},
		formatDate(date) {
			if (!date) return 'N/A';
			return new Date(date).toLocaleDateString();
		},
		formatDateTime(datetime) {
			if (!datetime) return 'N/A';
			return new Date(datetime).toLocaleString();
		},
	},
};
</script>