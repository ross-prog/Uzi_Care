<template>
	<div class="py-12">
		<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
			<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
				<div class="p-6 bg-white border-b border-gray-200">
					<div class="flex justify-between items-center mb-6">
						<h2 class="text-2xl font-semibold text-gray-900">User Management</h2>
						<a
							:href="route('users.create')"
							class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
						>
							Create New User
						</a>
					</div>

					<!-- Users Table -->
					<div class="overflow-x-auto">
						<table class="min-w-full divide-y divide-gray-200">
							<thead class="bg-gray-50">
								<tr>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										User
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Employee ID
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Role
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Department
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Campus
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Status
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Last Login
									</th>
									<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
										Actions
									</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<tr v-for="user in users.data" :key="user.id">
									<td class="px-6 py-4 whitespace-nowrap">
										<div>
											<div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
											<div class="text-sm text-gray-500">{{ user.email }}</div>
										</div>
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ user.employee_id }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap">
										<span
											:class="getRoleBadgeClass(user.role)"
											class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
										>
											{{ getRoleDisplayName(user.role) }}
										</span>
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ user.department }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
										{{ user.campus || 'Not assigned' }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap">
										<span
											:class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
											class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
										>
											{{ user.is_active ? 'Active' : 'Inactive' }}
										</span>
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
										{{ user.last_login_at ? formatDateTime(user.last_login_at) : 'Never' }}
									</td>
									<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
										<div class="flex space-x-2">
											<a
												:href="route('users.edit', user.id)"
												class="text-indigo-600 hover:text-indigo-900"
											>
												Edit
											</a>
											<button
												@click="toggleUserStatus(user)"
												:class="user.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
											>
												{{ user.is_active ? 'Deactivate' : 'Activate' }}
											</button>
											<button
												@click="showResetPasswordModal(user)"
												class="text-blue-600 hover:text-blue-900"
											>
												Reset Password
											</button>
											<button
												@click="deleteUser(user)"
												class="text-red-600 hover:text-red-900"
												:disabled="user.role === 'admin'"
											>
												Delete
											</button>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<!-- Pagination -->
					<div class="mt-6">
						<div class="flex items-center justify-between">
							<div class="text-sm text-gray-700">
								Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
							</div>
							<div class="flex space-x-2">
								<a
									v-for="link in users.links"
									:key="link.label"
									:href="link.url"
									v-html="link.label"
									:class="[
										'px-3 py-2 text-sm border rounded',
										link.active 
											? 'bg-blue-500 text-white border-blue-500' 
											: 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
									]"
								></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Reset Password Modal -->
		<div v-if="showPasswordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
			<div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
				<div class="mt-3 text-center">
					<h3 class="text-lg font-medium text-gray-900">Reset Password</h3>
					<div class="mt-2 px-7 py-3">
						<p class="text-sm text-gray-500">
							Reset password for {{ selectedUser?.name }}
						</p>
						<form @submit.prevent="resetPassword" class="mt-4">
							<div class="mb-4">
								<label class="block text-gray-700 text-sm font-bold mb-2">
									New Password
								</label>
								<input
									v-model="passwordForm.password"
									type="password"
									class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
									required
								>
							</div>
							<div class="mb-4">
								<label class="block text-gray-700 text-sm font-bold mb-2">
									Confirm Password
								</label>
								<input
									v-model="passwordForm.password_confirmation"
									type="password"
									class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
									required
								>
							</div>
							<div class="flex justify-end space-x-2">
								<button
									type="button"
									@click="closePasswordModal"
									class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
								>
									Cancel
								</button>
								<button
									type="submit"
									:disabled="passwordForm.processing"
									class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
								>
									Reset Password
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
	users: Object,
});

const showPasswordModal = ref(false);
const selectedUser = ref(null);

const passwordForm = useForm({
	password: '',
	password_confirmation: '',
});

const formatDateTime = (dateTime) => {
	return new Date(dateTime).toLocaleString();
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

const toggleUserStatus = (user) => {
	if (confirm(`Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} ${user.name}?`)) {
		router.post(route('users.toggle-status', user.id), {}, {
			preserveScroll: true,
		});
	}
};

const showResetPasswordModal = (user) => {
	selectedUser.value = user;
	showPasswordModal.value = true;
};

const closePasswordModal = () => {
	showPasswordModal.value = false;
	selectedUser.value = null;
	passwordForm.reset();
};

const resetPassword = () => {
	passwordForm.post(route('users.reset-password', selectedUser.value.id), {
		onSuccess: () => {
			closePasswordModal();
		},
	});
};

const deleteUser = (user) => {
	if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
		router.delete(route('users.destroy', user.id));
	}
};
</script>