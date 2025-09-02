<script setup>
import { Head } from "@inertiajs/vue3";
</script>

<template>
	<Head title="AI Forecasting" />

	<div class="page-container">
		<!-- Page header -->
		<div class="page-header">
			<h1 class="text-3xl font-bold text-neutral-900">AI Forecasting</h1>
			<p class="mt-2 text-neutral-600">
				Predictive analytics for health trends and inventory management
			</p>
		</div>

		<!-- Coming Soon Card -->
		<div class="card">
			<div class="px-6 py-8 text-center">
				<div
					class="w-16 h-16 bg-info/10 rounded-full flex items-center justify-center mx-auto mb-4"
				>
					<svg
						class="w-8 h-8 text-info"
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

				<h2 class="text-2xl font-bold text-neutral-900 mb-4">AI Forecasting Module</h2>
				<p class="text-lg text-neutral-600 mb-6">Coming Soon!</p>

				<div class="max-w-2xl mx-auto text-left">
					<h3 class="text-lg font-semibold text-neutral-900 mb-4">Planned Features:</h3>
					<ul class="space-y-3 text-neutral-600">
						<li class="flex items-start">
							<svg
								class="w-5 h-5 text-success mt-0.5 mr-3 flex-shrink-0"
								fill="none"
								stroke="currentColor"
								viewBox="0 0 24 24"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M5 13l4 4L19 7"
								/>
							</svg>
							<span
								><strong>Disease Outbreak Prediction:</strong> Early warning system for
								potential health crises in the campus community</span
							>
						</li>
						<li class="flex items-start">
							<svg
								class="w-5 h-5 text-success mt-0.5 mr-3 flex-shrink-0"
								fill="none"
								stroke="currentColor"
								viewBox="0 0 24 24"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M5 13l4 4L19 7"
								/>
							</svg>
							<span
								><strong>Seasonal Health Trends:</strong> Predict flu seasons, allergy
								spikes, and other seasonal health patterns</span
							>
						</li>
						<li class="flex items-start">
							<svg
								class="w-5 h-5 text-success mt-0.5 mr-3 flex-shrink-0"
								fill="none"
								stroke="currentColor"
								viewBox="0 0 24 24"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									stroke-width="2"
									d="M5 13l4 4L19 7"
								/>
							</svg>
							<span
								><strong>Inventory Demand Forecasting:</strong> Predict medicine and
								supply needs based on historical data</span
							>
						</li>
					</ul>
				</div>

				<!-- Placeholder charts/visualizations -->
				<div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="bg-neutral-50 rounded-lg p-6">
						<h4 class="text-sm font-medium text-neutral-900 mb-4">Predicted Visit Trends</h4>
						<div
							class="h-32 bg-gradient-to-r from-primary/10 to-primary/20 rounded flex items-center justify-center"
						>
							<span class="text-neutral-500">Chart Placeholder</span>
						</div>
					</div>

					<div class="bg-neutral-50 rounded-lg p-6">
						<h4 class="text-sm font-medium text-neutral-900 mb-4">
							Disease Risk Assessment
						</h4>
						<div
							class="h-32 bg-gradient-to-r from-success/10 to-success/20 rounded flex items-center justify-center"
						>
							<span class="text-neutral-500">Chart Placeholder</span>
						</div>
					</div>
				</div>

				<!-- Development status -->
				<div class="mt-8 bg-info/10 rounded-lg p-6">
					<div class="flex items-center justify-center">
						<div class="flex items-center space-x-2">
							<div class="w-3 h-3 bg-info rounded-full animate-pulse"></div>
							<span class="text-info font-medium">In Development</span>
						</div>
					</div>
					<p class="text-info text-sm mt-2">
						This module is currently under development. Machine learning models are being
						trained with historical health data to provide accurate predictions.
					</p>
				</div>
			</div>
		</div>
	</div>
</template>
