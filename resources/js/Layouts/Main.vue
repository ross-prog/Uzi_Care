<script setup>
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const showNotifications = ref(false)
const showMobileMenu = ref(false)

// Get auth user data
const authUser = computed(() => page.props.auth?.user || null)

// Get notification data from shared data
const notificationData = computed(() => page.props.notificationData || {
    lowStockCount: 0,
    nearingExpiryCount: 0
})

// Calculate total notification count
const totalNotifications = computed(() => 
    notificationData.value.lowStockCount + notificationData.value.nearingExpiryCount
)

const logout = () => {
    router.post(route('logout'))
}
</script>

<template>
    <div class="min-h-screen bg-neutral-50">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg border-b border-neutral-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo and main nav -->
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <Link :href="route('dashboard')" class="text-2xl font-bold text-primary">
                                UZI Care
                            </Link>
                        </div>
                        
                        <!-- Desktop Navigation -->
                        <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                            <Link 
                                :href="route('dashboard')" 
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    route().current('dashboard') 
                                        ? 'border-primary text-neutral-900' 
                                        : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'
                                ]"
                            >
                                Dashboard
                            </Link>
                            
                            <!-- AI Forecasting - Admin only -->
                            <Link 
                                v-if="authUser?.permissions?.isAdmin"
                                :href="route('ai.forecasting')" 
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    route().current('ai.forecasting') 
                                        ? 'border-primary text-neutral-900' 
                                        : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300'
                                ]"
                            >
                                AI Forecasting
                            </Link>
                            
                            <!-- EHR - Admin and Nurse -->
                            <Link 
                                v-if="authUser?.permissions?.canManageRecords"
                                :href="route('ehr.index')" 
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    route().current('ehr.*') || route().current('patient-consultation.*')
                                        ? 'border-blue-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                EHR
                            </Link>
                            
                            <!-- Inventory - Admin, Nurse, Inventory Manager -->
                            <Link 
                                v-if="authUser?.permissions?.canViewInventory"
                                :href="route('inventory.index')" 
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    route().current('inventory.*') 
                                        ? 'border-blue-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Inventory
                            </Link>
                            
                            <!-- Medicine Distribution - Admin and Inventory Manager -->
                            <Link 
                                v-if="authUser?.role === 'admin' || authUser?.role === 'inventory_manager'"
                                :href="route('medicine-distributions.index')" 
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    route().current('medicine-distributions.*') 
                                        ? 'border-blue-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Distribution
                            </Link>
                            
                            <!-- Reports - Admin and Nurse -->
                            <Link 
                                v-if="authUser?.permissions?.canDownloadReports"
                                :href="route('reports.index')" 
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    route().current('reports.*') 
                                        ? 'border-blue-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Reports
                            </Link>

                            <!-- User Management - Admin and Account Manager -->
                            <Link 
                                v-if="authUser?.permissions?.canManageAccounts"
                                :href="route('users.index')" 
                                :class="[
                                    'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                                    route().current('users.*') 
                                        ? 'border-blue-500 text-gray-900' 
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                ]"
                            >
                                Users
                            </Link>
                        </div>
                    </div>

                    <!-- Right side buttons -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <!-- Notifications -->
                        <div class="relative">
                            <button 
                                @click="showNotifications = !showNotifications"
                                class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5L15 17zm0 0l-8.5-8.5M12 3V1m0 2a9 9 0 110 18 9 9 0 010-18z" />
                                </svg>
                                <!-- Show notification badge only if there are notifications -->
                                <span 
                                    v-if="totalNotifications > 0" 
                                    class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full min-w-[1.25rem]"
                                >
                                    {{ totalNotifications > 99 ? '99+' : totalNotifications }}
                                </span>
                            </button>
                            
                            <!-- Notifications dropdown -->
                            <div v-show="showNotifications" class="origin-top-right absolute right-0 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                                <div class="py-1">
                                    <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                        <h3 class="font-medium">Notifications</h3>
                                        <p class="text-xs text-gray-500 mt-1">{{ totalNotifications }} total alerts</p>
                                    </div>
                                    
                                    <!-- Low stock notification -->
                                    <div v-if="notificationData.lowStockCount > 0" class="px-4 py-3 text-sm text-gray-600 hover:bg-gray-50 border-b">
                                        <div class="flex items-center space-x-3">
                                            <span class="w-2 h-2 bg-yellow-400 rounded-full flex-shrink-0"></span>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900">{{ notificationData.lowStockCount }} items low in stock</p>
                                                <p class="text-xs text-gray-500">Items below threshold level</p>
                                            </div>
                                            <Link :href="route('inventory.index')" class="text-xs text-blue-600 hover:text-blue-800">
                                                View
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <!-- Nearing expiry notification -->
                                    <div v-if="notificationData.nearingExpiryCount > 0" class="px-4 py-3 text-sm text-gray-600 hover:bg-gray-50 border-b">
                                        <div class="flex items-center space-x-3">
                                            <span class="w-2 h-2 bg-red-400 rounded-full flex-shrink-0"></span>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900">{{ notificationData.nearingExpiryCount }} medicines nearing expiry</p>
                                                <p class="text-xs text-gray-500">Expiring within 30 days</p>
                                            </div>
                                            <Link :href="route('inventory.index')" class="text-xs text-blue-600 hover:text-blue-800">
                                                View
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <!-- No notifications -->
                                    <div v-if="totalNotifications === 0" class="px-4 py-8 text-center text-sm text-gray-500">
                                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p>All good! No alerts at this time.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User info and logout -->
                        <div v-if="authUser" class="flex items-center space-x-3">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">{{ authUser.name }}</div>
                                <div class="text-xs text-gray-500">{{ authUser.roleDisplayName }} • {{ authUser.employee_id }}</div>
                            </div>
                        </div>

                        <!-- Logout button -->
                        <button 
                            @click="logout"
                            class="ml-4 bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            Logout
                        </button>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="sm:hidden flex items-center">
                        <button 
                            @click="showMobileMenu = !showMobileMenu"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div v-show="showMobileMenu" class="sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <Link :href="route('dashboard')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-blue-500 text-blue-700 bg-blue-50">
                        Dashboard
                    </Link>
                    <Link :href="route('ai.forecasting')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">
                        AI Forecasting
                    </Link>
                    <Link :href="route('ehr.index')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">
                        EHR
                    </Link>
                    <Link :href="route('inventory.index')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">
                        Inventory
                    </Link>
                    <Link 
                        v-if="authUser?.role === 'admin' || authUser?.role === 'inventory_manager'"
                        :href="route('medicine-distributions.index')" 
                        class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300"
                    >
                        Distribution
                    </Link>
                    <Link :href="route('reports.index')" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300">
                        Reports
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>
