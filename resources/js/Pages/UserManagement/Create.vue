<template>
	<div class="py-12">
		<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 bg-white border-b border-gray-200">
					<div class="flex justify-between items-center mb-6">
						<h2 class="text-2xl font-semibold text-gray-900">Create New User</h2>
						<Link
							:href="route('users.index')"
							class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
						>
							Back to Users
						</Link>
					</div>

					<form @submit.prevent="submit" class="space-y-6">
						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<!-- Basic Information -->
							<div class="space-y-4">
								<h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
								
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Full Name *
									</label>
									<input
										v-model="form.name"
										type="text"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.name }"
										required
									/>
									<div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
										{{ form.errors.name }}
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Email *
									</label>
									<input
										v-model="form.email"
										type="email"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.email }"
										required
									/>
									<div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
										{{ form.errors.email }}
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Employee ID *
									</label>
									<input
										v-model="form.employee_id"
										type="text"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.employee_id }"
										required
									/>
									<div v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600">
										{{ form.errors.employee_id }}
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Contact Number
									</label>
									<input
										v-model="form.contact_number"
										type="text"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.contact_number }"
									/>
									<div v-if="form.errors.contact_number" class="mt-1 text-sm text-red-600">
										{{ form.errors.contact_number }}
									</div>
								</div>
							</div>

							<!-- Role and Department -->
							<div class="space-y-4">
								<h3 class="text-lg font-medium text-gray-900">Role & Department</h3>
								
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Role *
									</label>
									<select
										v-model="form.role"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.role }"
										required
									>
										<option value="">Select Role</option>
										<option value="admin">Administrator</option>
										<option value="nurse">Nurse</option>
										<option value="inventory_manager">Inventory Manager</option>
										<option value="account_manager">Account Manager</option>
									</select>
									<div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
										{{ form.errors.role }}
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Department *
									</label>
									<input
										v-model="form.department"
										type="text"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.department }"
										placeholder="e.g., Medical, Administration, Supply Management"
										required
									/>
									<div v-if="form.errors.department" class="mt-1 text-sm text-red-600">
										{{ form.errors.department }}
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Campus/Location *
									</label>
									<select
										v-model="form.campus"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.campus }"
										required
									>
										<option value="">Select Campus</option>
										<option value="Main Campus">Main Campus</option>
										<option value="North Campus">North Campus</option>
										<option value="South Campus">South Campus</option>
										<option value="East Campus">East Campus</option>
										<option value="West Campus">West Campus</option>
										<option value="Downtown Campus">Downtown Campus</option>
										<option value="Satellite Clinic A">Satellite Clinic A</option>
										<option value="Satellite Clinic B">Satellite Clinic B</option>
									</select>
									<div v-if="form.errors.campus" class="mt-1 text-sm text-red-600">
										{{ form.errors.campus }}
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Status
									</label>
									<select
										v-model="form.is_active"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
									>
										<option :value="true">Active</option>
										<option :value="false">Inactive</option>
									</select>
								</div>
							</div>
						</div>

						<!-- Password Section -->
						<div class="space-y-4 border-t pt-6">
							<h3 class="text-lg font-medium text-gray-900">Password</h3>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Password *
									</label>
									<input
										v-model="form.password"
										type="password"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										:class="{ 'border-red-500': form.errors.password }"
										required
									/>
									<div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
										{{ form.errors.password }}
									</div>
								</div>

								<div>
									<label class="block text-sm font-medium text-gray-700 mb-1">
										Confirm Password *
									</label>
									<input
										v-model="form.password_confirmation"
										type="password"
										class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
										required
									/>
								</div>
							</div>
						</div>

						<!-- Role Permissions Info -->
						<div v-if="form.role" class="bg-blue-50 border border-blue-200 rounded-md p-4">
							<h4 class="text-sm font-medium text-blue-800 mb-2">Role Permissions:</h4>
							<div class="text-sm text-blue-700">
								<div v-if="form.role === 'admin'">
									<strong>Administrator:</strong> Full system access including user management, all reports, AI forecasting, and system settings.
								</div>
								<div v-else-if="form.role === 'nurse'">
									<strong>Nurse:</strong> Access to patient records (EHR), consultation management, inventory viewing, and reports.
								</div>
								<div v-else-if="form.role === 'inventory_manager'">
									<strong>Inventory Manager:</strong> Full inventory management, medicine tracking, stock alerts, and inventory reports.
								</div>
								<div v-else-if="form.role === 'account_manager'">
									<strong>Account Manager:</strong> User account creation, management, and basic system access.
								</div>
							</div>
						</div>

						<!-- Submit Buttons -->
						<div class="flex justify-end space-x-3 pt-6 border-t">
							<Link
								:href="route('users.index')"
								class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500"
							>
								Cancel
							</Link>
							<button
								type="submit"
								:disabled="form.processing"
								class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
							>
								{{ form.processing ? 'Creating...' : 'Create User' }}
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
	name: '',
	email: '',
	employee_id: '',
	role: '',
	department: '',
	campus: '',
	contact_number: '',
	is_active: true,
	password: '',
	password_confirmation: '',
});

const submit = () => {
	form.post(route('users.store'), {
		onSuccess: () => {
			// Redirect handled by controller
		},
	});
};
</script>