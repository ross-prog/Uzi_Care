<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const visitsData = ref([])
const inventoryData = ref({
    lowStock: [],
    nearingExpiry: [],
    totalValue: 0
})
const loading = ref(false)

const dateRange = reactive({
    start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0]
})

const loadPatientVisitsReport = async () => {
    loading.value = true
    try {
        const response = await axios.get('/reports/patient-visits', {
            params: dateRange
        })
        visitsData.value = response.data
    } catch (error) {
        console.error('Error loading visits report:', error)
    } finally {
        loading.value = false
    }
}

const loadInventoryReport = async () => {
    loading.value = true
    try {
        const response = await axios.get('/reports/inventory-report')
        inventoryData.value = response.data
    } catch (error) {
        console.error('Error loading inventory report:', error)
    } finally {
        loading.value = false
    }
}

const exportReport = (reportType) => {
    // Placeholder for export functionality
    alert(`Export ${reportType} report - Feature coming soon!`)
}

const printReport = (reportType) => {
    // Simple print functionality
    window.print()
}

onMounted(() => {
    loadPatientVisitsReport()
    loadInventoryReport()
})
</script>

<template>
    <Head title="Reports & Analytics" />
    
    <div class="page-container">
        <!-- Page header -->
        <div class="page-header">
            <h1 class="text-3xl font-bold text-neutral-900">Reports & Analytics</h1>
            <p class="mt-2 text-neutral-600">Generate and view clinic performance reports</p>
        </div>

        <!-- Report controls -->
        <div class="card mb-8">
            <div class="px-6 py-4 border-b border-neutral-200">
                <h2 class="text-lg font-medium text-neutral-900">Report Controls</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Start Date</label>
                        <input
                            v-model="dateRange.start_date"
                            type="date"
                            class="form-input"
                        />
                    </div>
                    <div>
                        <label class="form-label">End Date</label>
                        <input
                            v-model="dateRange.end_date"
                            type="date"
                            class="form-input"
                        />
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="loadPatientVisitsReport"
                            :disabled="loading"
                            class="w-full btn-primary"
                        >
                            {{ loading ? 'Loading...' : 'Generate Report' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Patient Visits Report -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Patient Visits Report</h3>
                    <div class="flex space-x-2">
                        <button
                            @click="exportReport('visits')"
                            class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                        >
                            Export
                        </button>
                        <button
                            @click="printReport('visits')"
                            class="text-sm bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700"
                        >
                            Print
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="visitsData.visits" class="space-y-4">
                        <!-- Summary stats -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ visitsData.visits.reduce((sum, item) => sum + item.count, 0) }}</div>
                                <div class="text-sm text-blue-800">Total Visits</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">{{ Math.round(visitsData.visits.reduce((sum, item) => sum + item.count, 0) / visitsData.visits.length) }}</div>
                                <div class="text-sm text-green-800">Daily Average</div>
                            </div>
                        </div>

                        <!-- Visit types chart -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Visit Types Distribution</h4>
                            <div class="space-y-2">
                                <div v-for="visitType in visitsData.visitTypes" :key="visitType.visit_type" class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">{{ visitType.visit_type }}</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-20 bg-gray-200 rounded-full h-2">
                                            <div 
                                                class="bg-blue-600 h-2 rounded-full" 
                                                :style="{ width: `${(visitType.count / visitsData.visitTypes.reduce((sum, item) => sum + item.count, 0)) * 100}%` }"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 w-8">{{ visitType.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daily visits chart -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Daily Visits Trend</h4>
                            <div class="h-32 flex items-end space-x-1">
                                <div
                                    v-for="visit in visitsData.visits.slice(-14)"
                                    :key="visit.visit_date"
                                    class="bg-blue-500 rounded-t flex-1 min-h-[4px] relative group"
                                    :style="{ height: `${(visit.count / Math.max(...visitsData.visits.map(v => v.count))) * 120}px` }"
                                >
                                    <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{ new Date(visit.visit_date).toLocaleDateString() }}: {{ visit.count }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center text-gray-500 py-8">
                        No visit data available for the selected period.
                    </div>
                </div>
            </div>

            <!-- Inventory Report -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Inventory Report</h3>
                    <div class="flex space-x-2">
                        <button
                            @click="exportReport('inventory')"
                            class="text-sm bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
                        >
                            Export
                        </button>
                        <button
                            @click="printReport('inventory')"
                            class="text-sm bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700"
                        >
                            Print
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div v-if="inventoryData.lowStock || inventoryData.nearingExpiry || inventoryData.totalValue" class="space-y-4">
                        <!-- Summary stats -->
                        <div class="grid grid-cols-1 gap-4">
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ inventoryData.lowStock?.length || 0 }}</div>
                                <div class="text-sm text-yellow-800">Low Stock Items</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ inventoryData.nearingExpiry?.length || 0 }}</div>
                                <div class="text-sm text-red-800">Items Nearing Expiry</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">₱{{ Number(inventoryData.totalValue || 0).toFixed(2) }}</div>
                                <div class="text-sm text-green-800">Total Inventory Value</div>
                            </div>
                        </div>

                        <!-- Low stock items -->
                        <div v-if="inventoryData.lowStock && inventoryData.lowStock.length > 0">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Low Stock Items</h4>
                            <div class="space-y-2">
                                <div v-for="item in inventoryData.lowStock.slice(0, 5)" :key="item.id" class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">{{ item.medicine?.name }}</span>
                                    <span class="text-red-600 font-medium">{{ item.quantity }} left</span>
                                </div>
                                <div v-if="inventoryData.lowStock.length > 5" class="text-xs text-gray-500">
                                    +{{ inventoryData.lowStock.length - 5 }} more items
                                </div>
                            </div>
                        </div>

                        <!-- Nearing expiry -->
                        <div v-if="inventoryData.nearingExpiry.length > 0">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Items Nearing Expiry</h4>
                            <div class="space-y-2">
                                <div v-for="item in inventoryData.nearingExpiry.slice(0, 5)" :key="item.id" class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700">{{ item.medicine?.name }}</span>
                                    <span class="text-red-600 font-medium">{{ new Date(item.expiry_date).toLocaleDateString() }}</span>
                                </div>
                                <div v-if="inventoryData.nearingExpiry.length > 5" class="text-xs text-gray-500">
                                    +{{ inventoryData.nearingExpiry.length - 5 }} more items
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center text-gray-500 py-8">
                        Loading inventory data...
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional reports placeholder -->
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Additional Reports</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button class="p-4 border border-gray-300 rounded-lg hover:bg-gray-50 text-left">
                        <div class="font-medium text-gray-900">Medicine Usage Report</div>
                        <div class="text-sm text-gray-500">Track most prescribed medicines</div>
                    </button>
                    <button class="p-4 border border-gray-300 rounded-lg hover:bg-gray-50 text-left">
                        <div class="font-medium text-gray-900">Nurse Performance Report</div>
                        <div class="text-sm text-gray-500">Analyze nurse workload and performance</div>
                    </button>
                    <button class="p-4 border border-gray-300 rounded-lg hover:bg-gray-50 text-left">
                        <div class="font-medium text-gray-900">Monthly Summary</div>
                        <div class="text-sm text-gray-500">Comprehensive monthly clinic report</div>
                    </button>
                </div>
                
                <div class="mt-4 text-center text-gray-500 text-sm">
                    Additional reporting features coming soon!
                </div>
            </div>
        </div>
    </div>
</template>
