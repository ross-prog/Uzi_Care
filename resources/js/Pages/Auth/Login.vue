<template>
	<div
		class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8"
	>
		<div class="max-w-md w-full space-y-8">
			<div>
				<div
					class="mx-auto h-20 w-20 flex items-center justify-center rounded-full bg-blue-100"
				></div>
				<h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
					Sign in to UziCare
				</h2>
				<p class="mt-2 text-center text-sm text-gray-600">
					Medical Records Management System
				</p>
			</div>
			<form class="mt-8 space-y-6" @submit.prevent="submit">
				<div class="rounded-md shadow-sm -space-y-px">
					<div>
						<label for="email" class="sr-only">Email address</label>
						<input
							id="email"
							v-model="form.email"
							name="email"
							type="email"
							autocomplete="email"
							required
							class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
							:class="{ 'border-red-500': form.errors.email }"
							placeholder="Email address"
						/>
						<div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
							{{ form.errors.email }}
						</div>
					</div>
					<div>
						<label for="password" class="sr-only">Password</label>
						<input
							id="password"
							v-model="form.password"
							name="password"
							type="password"
							autocomplete="current-password"
							required
							class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
							:class="{ 'border-red-500': form.errors.password }"
							placeholder="Password"
						/>
						<div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
							{{ form.errors.password }}
						</div>
					</div>
				</div>

				<div class="flex items-center justify-between">
					<div class="flex items-center">
						<input
							id="remember-me"
							v-model="form.remember"
							name="remember-me"
							type="checkbox"
							class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
						/>
						<label for="remember-me" class="ml-2 block text-sm text-gray-900">
							Remember me
						</label>
					</div>
				</div>

				<div>
					<button
						type="submit"
						:disabled="form.processing"
						class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
					>
						<span class="absolute left-0 inset-y-0 flex items-center pl-3">
							<svg
								v-if="!form.processing"
								class="h-5 w-5 text-blue-500 group-hover:text-blue-400"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path
									fill-rule="evenodd"
									d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
									clip-rule="evenodd"
								/>
							</svg>
							<svg
								v-else
								class="animate-spin h-5 w-5 text-blue-500"
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
						</span>
						{{ form.processing ? "Signing in..." : "Sign in" }}
					</button>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
import { Head, useForm } from "@inertiajs/vue3";

defineOptions({
	layout: false, // No layout for login page
});

const form = useForm({
	email: "",
	password: "",
	remember: false,
});

const submit = () => {
	form.post(route("login"), {
		onFinish: () => form.reset("password"),
	});
};
</script>
