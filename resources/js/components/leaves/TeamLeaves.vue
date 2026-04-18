<template>
    <v-container fluid class="pa-6 bg-grey-lighten-4 min-vh-100">
        <!-- Header Section -->
        <v-row class="mb-6 align-center">
            <v-col>
                <div class="d-flex align-center mb-1">
                    <v-icon color="primary" size="32" class="mr-3">mdi-calendar-check</v-icon>
                    <h1 class="text-h4 font-weight-bold grey--text text--darken-3">Team Leave Approvals</h1>
                </div>
                <p class="text-subtitle-1 text-medium-emphasis ml-11">Review and manage leave requests from your team members</p>
            </v-col>
            <v-col cols="auto">
                <v-btn color="primary" prepend-icon="mdi-refresh" variant="tonal" @click="fetchLeaves" :loading="isLoading">
                    Refresh Data
                </v-btn>
            </v-col>
        </v-row>

        <!-- Stats Overview (Optional but premium) -->
        <v-row class="mb-6">
            <v-col cols="12" sm="6" md="3">
                <v-card theme="dark" color="info" class="rounded-xl elevation-4 glass-card">
                    <v-card-text class="d-flex align-center">
                        <v-avatar color="white" rounded="lg" class="mr-4">
                            <v-icon color="info">mdi-clock-outline</v-icon>
                        </v-avatar>
                        <div>
                            <div class="text-h4 font-weight-bold">{{ pendingCount }}</div>
                            <div class="text-caption text-uppercase font-weight-bold">Pending Requests</div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card theme="dark" color="success" class="rounded-xl elevation-4 glass-card">
                    <v-card-text class="d-flex align-center">
                        <v-avatar color="white" rounded="lg" class="mr-4">
                            <v-icon color="success">mdi-check-all</v-icon>
                        </v-avatar>
                        <div>
                            <div class="text-h4 font-weight-bold">{{ approvedTodayCount }}</div>
                            <div class="text-caption text-uppercase font-weight-bold">Approved Today</div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card theme="dark" color="warning" class="rounded-xl elevation-4 glass-card">
                    <v-card-text class="d-flex align-center">
                        <v-avatar color="white" rounded="lg" class="mr-4">
                            <v-icon color="warning">mdi-account-multiple</v-icon>
                        </v-avatar>
                        <div>
                            <div class="text-h4 font-weight-bold">{{ distinctEmployeesCount }}</div>
                            <div class="text-caption text-uppercase font-weight-bold">Active Employees</div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="3">
                <v-card theme="dark" color="error" class="rounded-xl elevation-4 glass-card">
                    <v-card-text class="d-flex align-center">
                        <v-avatar color="white" rounded="lg" class="mr-4">
                            <v-icon color="error">mdi-close-circle-outline</v-icon>
                        </v-avatar>
                        <div>
                            <div class="text-h4 font-weight-bold">{{ cancelledCount }}</div>
                            <div class="text-caption text-uppercase font-weight-bold">Cancelled</div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Filter & Search Section -->
        <v-card class="mb-6 rounded-xl elevation-2 overflow-hidden border-0">
            <v-toolbar flat color="white" class="px-4 py-2 border-b">
                <v-icon color="primary" class="mr-2">mdi-filter-variant</v-icon>
                <v-toolbar-title class="font-weight-bold text-body-1">Advanced Filters</v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon="mdi-close-circle-outline" variant="text" color="grey" @click="resetFilters" v-if="hasActiveFilters">
                    Clear Filters
                </v-btn>
            </v-toolbar>
            
            <v-card-text class="py-6 px-8">
                <v-row dense>
                    <v-col cols="12" md="4">
                        <v-text-field
                            v-model="search"
                            prepend-inner-icon="mdi-magnify"
                            label="Search Employee or Leave Type"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            hide-details
                            class="modern-input"
                            clearable
                        ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select
                            v-model="filters.application_date"
                            :items="applicationDateOptions"
                            label="Application Period"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            hide-details
                            prepend-inner-icon="mdi-calendar-range"
                            class="modern-input"
                            @update:model-value="submitFilters"
                        ></v-select>
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select
                            v-model="filters.status"
                            :items="statusOptions"
                            label="Filter by Status"
                            variant="outlined"
                            density="comfortable"
                            rounded="lg"
                            hide-details
                            prepend-inner-icon="mdi-list-status"
                            class="modern-input"
                            @update:model-value="submitFilters"
                        >
                            <template v-slot:item="{ props, item }">
                                <v-list-item v-bind="props">
                                    <template v-slot:prepend>
                                        <v-icon :color="getStatusColor(item.raw)" size="18" class="mr-2">
                                            {{ getStatusIcon(item.raw) }}
                                        </v-icon>
                                    </template>
                                </v-list-item>
                            </template>
                        </v-select>
                    </v-col>
                    <v-col cols="12" md="2" class="d-flex align-center">
                        <v-btn block color="primary" height="48" rounded="lg" elevation="2" @click="submitFilters" class="font-weight-bold">
                            Apply Filters
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Data Table Section -->
        <v-card class="rounded-xl elevation-4 border-0 overflow-hidden">
            <v-data-table
                :headers="tableHeaders"
                :items="leaves"
                :search="search"
                :loading="isLoading"
                hover
                class="modern-table"
                id="team-leaves-table"
            >
                <!-- Index Column -->
                <template v-slot:item.id="{ index }">
                    <span class="text-caption font-weight-bold grey--text">{{ index + 1 }}</span>
                </template>

                <!-- Employee Column -->
                <template v-slot:item.user.firstname="{ item }">
                    <div class="d-flex align-center py-3">
                        <v-avatar size="38" color="primary" class="mr-3 elevation-1">
                            <span class="text-subtitle-2 font-weight-bold text-white">{{ getInitials(item.user) }}</span>
                        </v-avatar>
                        <div>
                            <div class="font-weight-bold text-body-1">{{ getUserFullName(item.user) }}</div>
                            <div class="text-caption text-medium-emphasis">{{ item.user.department || 'Operations' }}</div>
                        </div>
                    </div>
                </template>

                <!-- Leave Type Column -->
                <template v-slot:item.leave_type.name="{ item }">
                    <v-chip size="small" variant="tonal" class="font-weight-medium">
                        {{ formatLeaveType(item.leave_type.name) }}
                    </v-chip>
                </template>

                <!-- Date Columns -->
                <template v-slot:item.created_at="{ item }">
                    <div class="text-body-2">{{ formatDateSimple(item.created_at) }}</div>
                    <div class="text-caption text-disabled">{{ formatTime(item.created_at) }}</div>
                </template>

                <template v-slot:item.period="{ item }">
                    <div class="d-flex align-center">
                        <div class="text-center mr-2">
                            <div class="text-caption font-weight-bold text-uppercase grey--text">From</div>
                            <div class="text-body-2">{{ item.from }}</div>
                        </div>
                        <v-icon color="grey-lighten-1" size="20">mdi-arrow-right</v-icon>
                        <div class="text-center ml-2">
                            <div class="text-caption font-weight-bold text-uppercase grey--text">To</div>
                            <div class="text-body-2">{{ item.to }}</div>
                        </div>
                    </div>
                </template>

                <!-- Status Column -->
                <template v-slot:item.status="{ item }">
                    <v-chip
                        :color="getStatusColor(item.status)"
                        size="small"
                        class="font-weight-bold text-uppercase"
                        variant="flat"
                    >
                        <v-icon start size="14">{{ getStatusIcon(item.status) }}</v-icon>
                        {{ item.status }}
                    </v-chip>
                </template>

                <!-- Actions Column -->
                <template v-slot:item.actions="{ item }">
                    <div class="d-flex justify-center">
                        <v-tooltip text="View Logs" location="top">
                            <template v-slot:activator="{ props }">
                                <v-btn v-bind="props" icon="mdi-history" size="x-small" color="info" variant="text" class="mr-1" @click="openLogsModal(item)"></v-btn>
                            </template>
                        </v-tooltip>
                        
                        <v-tooltip text="Approve Preview" location="top">
                            <template v-slot:activator="{ props }">
                                <v-btn v-bind="props" icon="mdi-eye-outline" size="x-small" color="primary" variant="text" class="mr-1" @click="viewLeave(item)"></v-btn>
                            </template>
                        </v-tooltip>

                        <v-fade-transition>
                            <div v-if="item.status === 'Pending'" class="d-flex">
                                <v-tooltip text="Quick Approve" location="top">
                                    <template v-slot:activator="{ props }">
                                        <v-btn v-bind="props" icon="mdi-check-circle" size="x-small" color="success" variant="tonal" class="mr-1 shadow-sm" @click="approveLeave(item)"></v-btn>
                                    </template>
                                </v-tooltip>
                                <v-tooltip text="Reject/Cancel" location="top">
                                    <template v-slot:activator="{ props }">
                                        <v-btn v-bind="props" icon="mdi-close-circle" size="x-small" color="error" variant="tonal" @click="cancelLeave(item)"></v-btn>
                                    </template>
                                </v-tooltip>
                            </div>
                        </v-fade-transition>
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Modals/Dialogs -->
        <!-- View Leave Dialog -->
        <v-dialog v-model="viewLeaveModal" max-width="650px" transition="dialog-bottom-transition">
            <v-card class="rounded-xl overflow-hidden elevation-24">
                <v-toolbar color="primary" theme="dark" flat>
                    <v-icon start class="ml-4">mdi-card-text-outline</v-icon>
                    <v-toolbar-title class="font-weight-bold">Leave Request Details</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon="mdi-close" variant="text" @click="closeLeaveViewModal"></v-btn>
                </v-toolbar>
                
                <v-card-text class="pa-8" v-if="selectedItem">
                    <div class="d-flex align-center mb-8 bg-blue-lighten-5 pa-4 rounded-lg">
                        <v-avatar size="64" color="primary" class="mr-4 elevation-2">
                            <span class="text-h5 font-weight-bold text-white">{{ getInitials(selectedItem.user) }}</span>
                        </v-avatar>
                        <div>
                            <h3 class="text-h5 font-weight-bold text-grey-darken-3">{{ getUserFullName(selectedItem.user) }}</h3>
                            <div class="text-subtitle-1 text-primary">{{ selectedItem.user.department || 'Operations' }}</div>
                        </div>
                        <v-spacer></v-spacer>
                        <v-chip :color="getStatusColor(selectedItem.status)" variant="flat" class="elevation-1">
                            {{ selectedItem.status }}
                        </v-chip>
                    </div>

                    <v-row>
                        <v-col cols="12" sm="6">
                            <div class="mb-6">
                                <div class="text-overline text-grey-darken-1 mb-1">Leave Category</div>
                                <div class="text-body-1 font-weight-bold d-flex align-center">
                                    <v-icon start color="primary" size="20">mdi-beach</v-icon>
                                    {{ formatLeaveType(selectedItem.leave_type.name) }}
                                </div>
                            </div>
                            <div class="mb-6">
                                <div class="text-overline text-grey-darken-1 mb-1">Duration</div>
                                <div class="text-body-1 font-weight-bold d-flex align-center">
                                    <v-icon start color="indigo" size="20">mdi-calendar-range</v-icon>
                                    {{ selectedItem.days }} Working Days
                                </div>
                            </div>
                        </v-col>
                        <v-col cols="12" sm="6">
                            <div class="mb-6">
                                <div class="text-overline text-grey-darken-1 mb-1">Period</div>
                                <div class="text-body-1 font-weight-bold d-flex align-center">
                                    <v-icon start color="success" size="20">mdi-clock-out</v-icon>
                                    {{ selectedItem.from }} ➜ {{ selectedItem.to }}
                                </div>
                            </div>
                            <div class="mb-6">
                                <div class="text-overline text-grey-darken-1 mb-1">Contact Phone</div>
                                <div class="text-body-1 font-weight-bold d-flex align-center">
                                    <v-icon start color="teal" size="20">mdi-phone-outline</v-icon>
                                    {{ selectedItem.phone || 'N/A' }}
                                </div>
                            </div>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <div class="mt-4">
                        <div class="text-overline text-grey-darken-1 mb-1">Employee Comment</div>
                        <v-alert
                            variant="tonal"
                            color="grey-darken-2"
                            rounded="lg"
                            class="border-opacity-10"
                        >
                            {{ selectedItem.comment || 'No comment provided by the employee.' }}
                        </v-alert>
                    </div>
                </v-card-text>

                <v-divider></v-divider>
                
                <v-card-actions class="pa-6 justify-end bg-grey-lighten-5">
                    <v-btn variant="text" color="grey-darken-1" @click="closeLeaveViewModal" class="px-6 mr-2 font-weight-bold">Dismiss</v-btn>
                    <v-fade-transition>
                        <div v-if="selectedItem && selectedItem.status === 'Pending'">
                            <v-btn color="error" variant="tonal" class="px-6 mr-2 font-weight-bold" @click="closeLeaveViewModal(); cancelLeave(selectedItem)">Reject</v-btn>
                            <v-btn color="primary" class="px-8 font-weight-bold elevation-2" @click="closeLeaveViewModal(); approveLeave(selectedItem)">Approve Request</v-btn>
                        </div>
                    </v-fade-transition>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Log Dialog -->
        <v-dialog v-model="logsModal" max-width="500px" transition="dialog-fade-transition">
            <v-card class="rounded-xl elevation-24">
                <v-card-title class="pa-6 bg-info text-white d-flex align-center">
                    <v-icon size="32" class="mr-4">mdi-timeline-text-outline</v-icon>
                    <div>
                        <div class="text-h6 font-weight-bold">Audit Logs</div>
                        <div class="text-caption text-white-opacity-80">Track the lifecycle of this leave request</div>
                    </div>
                </v-card-title>
                
                <v-card-text class="pa-0">
                    <v-list class="pa-4 bg-transparent" lines="three">
                        <v-list-item v-for="(log, index) in logs" :key="index" class="mb-2 log-item">
                            <template v-slot:prepend>
                                <v-avatar color="info lighten-5" size="40">
                                    <v-icon color="info" size="20">mdi-account-clock</v-icon>
                                </v-avatar>
                            </template>
                            <v-list-item-title class="font-weight-bold text-body-1">{{ log.action }}</v-list-item-title>
                            <v-list-item-subtitle class="mt-1">
                                <div class="d-flex align-center mb-1">
                                    <v-icon size="14" class="mr-1 text-info">mdi-account-outline</v-icon>
                                    <span>{{ log.user }}</span>
                                </div>
                                <div class="d-flex align-center">
                                    <v-icon size="14" class="mr-1 text-grey">mdi-clock-outline</v-icon>
                                    <span>{{ log.time }}</span>
                                </div>
                            </v-list-item-subtitle>
                            <v-divider class="mt-4" v-if="index < logs.length - 1"></v-divider>
                        </v-list-item>
                        <div v-if="logs.length === 0" class="text-center py-12">
                            <v-icon size="64" color="grey-lighten-2">mdi-history-off</v-icon>
                            <div class="text-body-2 text-medium-emphasis mt-2">No activity logs found</div>
                        </div>
                    </v-list>
                </v-card-text>
                
                <v-divider></v-divider>
                <v-card-actions class="pa-4 bg-grey-lighten-5">
                    <v-spacer></v-spacer>
                    <v-btn color="grey-darken-1" variant="text" class="px-6" @click="closeLogsModal">Close</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Dynamic Action Confirmations -->
        <v-dialog v-model="approveLeaveModal" max-width="500px">
            <v-card class="rounded-xl elevation-24 overflow-hidden">
                <v-card-title class="bg-success text-white py-6 text-center">
                    <v-icon size="48" class="mb-2">mdi-check-decagram</v-icon>
                    <h3 class="text-h5 font-weight-bold">Confirm Approval</h3>
                </v-card-title>
                <v-card-text class="pa-8 text-center">
                    <p class="text-body-1 mb-6">Are you sure you want to approve the leave request from <br><strong>{{ selectedItem?.user ? getUserFullName(selectedItem.user) : '' }}</strong>?</p>
                    <v-textarea v-model="approveNotes" label="Add a note (optional)" variant="outlined" rounded="lg" hide-details class="modern-input"></v-textarea>
                </v-card-text>
                <v-card-actions class="px-8 pb-8 pt-0 d-flex gap-2">
                    <v-btn block size="large" variant="flat" color="success" class="font-weight-bold rounded-lg elevation-2 flex-grow-1" :loading="isSubmittingAction" @click="approveLeaveAction">
                        Yes, Approve
                    </v-btn>
                    <v-btn block size="large" variant="tonal" color="grey" class="font-weight-bold rounded-lg flex-grow-1" @click="closeApproveLeaveModal">
                        Cancel
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="cancelLeaveModal" max-width="500px">
            <v-card class="rounded-xl elevation-24 overflow-hidden">
                <v-card-title class="bg-error text-white py-6 text-center">
                    <v-icon size="48" class="mb-2">mdi-alert-circle</v-icon>
                    <h3 class="text-h5 font-weight-bold">Confirm Rejection</h3>
                </v-card-title>
                <v-card-text class="pa-8 text-center">
                    <p class="text-body-1 mb-6">Provide a reason for cancelling the leave request for <br><strong>{{ selectedItem?.user ? getUserFullName(selectedItem.user) : '' }}</strong>.</p>
                    <v-textarea v-model="cancelNotes" label="Reason for cancellation" variant="outlined" rounded="lg" persistent-placeholder placeholder="e.g., Short-staffed on these dates" class="modern-input"></v-textarea>
                </v-card-text>
                <v-card-actions class="px-8 pb-8 pt-0 d-flex gap-2">
                    <v-btn block size="large" variant="flat" color="error" class="font-weight-bold rounded-lg elevation-2 flex-grow-1" :loading="isSubmittingAction" :disabled="!cancelNotes" @click="cancelLeaveAction">
                        Confirm Rejection
                    </v-btn>
                    <v-btn block size="large" variant="tonal" color="grey" class="font-weight-bold rounded-lg flex-grow-1" @click="closeCancelLeaveModal">
                        Cancel
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Notification Snackbar -->
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000" rounded="pill" elevation="12">
            <div class="d-flex align-center">
                <v-icon start size="20" class="mr-2">{{ snackbar.icon }}</v-icon>
                <span class="font-weight-bold">{{ snackbar.text }}</span>
            </div>
        </v-snackbar>
    </v-container>
</template>

<script>
import axios from 'axios';

export default {
    name: 'TeamLeaves',
    props: {
        userId: {
            type: [Number, String],
            required: true
        }
    },

    data() {
        return {
            base_url: '/',
            isLoading: false,
            isSubmittingAction: false,
            search: '',
            viewLeaveModal: false,
            approveLeaveModal: false,
            cancelLeaveModal: false,
            logsModal: false,
            selectedItem: null,
            approveNotes: '',
            cancelNotes: '',
            logs: [],
            leaves: [],
            allLeaves: [],
            filters: {
                application_date: 'All',
                status: 'All'
            },
            applicationDateOptions: ['All', 'Today', 'Current Week', 'Last Week', 'Current Month', 'Current Year'],
            statusOptions: ['All', 'Approved', 'Pending', 'Cancelled', 'Hr Approved'],
            snackbar: {
                show: false,
                text: '',
                color: 'success',
                icon: 'mdi-check-circle'
            },
            tableHeaders: [
                { title: '#', key: 'id', sortable: false, width: '50px' },
                { title: 'Employee', key: 'user.firstname', align: 'start', sortable: true },
                { title: 'Leave Category', key: 'leave_type.name', sortable: true },
                { title: 'Requested On', key: 'created_at', sortable: true },
                { title: 'Period', key: 'period', sortable: false },
                { title: 'Status', key: 'status', align: 'center', sortable: true },
                { title: 'Actions', key: 'actions', align: 'center', sortable: false }
            ]
        };
    },

    created() {
        this.fetchLeaves();
    },

    computed: {
        pendingCount() {
            return this.allLeaves.filter(l => l.status === 'Pending').length;
        },
        approvedTodayCount() {
            const today = new Date().toDateString();
            return this.allLeaves.filter(l => 
                l.status === 'Approved' && 
                new Date(l.updated_at || l.created_at).toDateString() === today
            ).length;
        },
        cancelledCount() {
            return this.allLeaves.filter(l => l.status === 'Cancelled').length;
        },
        distinctEmployeesCount() {
            return new Set(this.allLeaves.map(l => l.user_id)).size;
        },
        hasActiveFilters() {
            return this.filters.application_date !== 'All' || this.filters.status !== 'All' || this.search !== '';
        }
    },

    methods: {
        async fetchLeaves() {
            this.isLoading = true;
            try {
                const formData = new FormData();
                formData.append('userId', this.userId);
                const response = await axios.post(`${this.base_url}api/v1/team-leaves`, formData);
                this.allLeaves = response.data.leaves || [];
                this.applyClientFilters();
            } catch (error) {
                this.showNotify('Error loading leave data', 'error', 'mdi-alert');
            } finally {
                this.isLoading = false;
            }
        },

        submitFilters() {
            this.applyClientFilters();
        },

        resetFilters() {
            this.filters.application_date = 'All';
            this.filters.status = 'All';
            this.search = '';
            this.applyClientFilters();
        },

        applyClientFilters() {
            let filtered = [...this.allLeaves];

            if (this.filters.status !== 'All') {
                filtered = filtered.filter(l => l.status === this.filters.status);
            }

            if (this.filters.application_date !== 'All') {
                const now = new Date();
                filtered = filtered.filter(leave => {
                    const leaveDate = new Date(leave.created_at);
                    switch (this.filters.application_date) {
                        case 'Today': return leaveDate.toDateString() === now.toDateString();
                        case 'Current Week': 
                            const ws = new Date(now.setDate(now.getDate() - now.getDay()));
                            const we = new Date(now.setDate(ws.getDate() + 6));
                            return leaveDate >= ws && leaveDate <= we;
                        case 'Current Month': return leaveDate.getMonth() === now.getMonth() && leaveDate.getFullYear() === now.getFullYear();
                        case 'Current Year': return leaveDate.getFullYear() === now.getFullYear();
                        default: return true;
                    }
                });
            }

            this.leaves = filtered;
        },

        getUserFullName(user) {
            return user ? `${user.firstname} ${user.lastname}` : 'Unknown';
        },

        getInitials(user) {
            if (!user) return '?';
            const f = user.firstname ? user.firstname.charAt(0) : '';
            const l = user.lastname ? user.lastname.charAt(0) : '';
            return (f + l).toUpperCase() || '?';
        },

        formatLeaveType(name) {
            return name ? name.replace(/_/g, ' ') : 'N/A';
        },

        formatDateSimple(date) {
            return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        formatTime(date) {
            return new Date(date).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        },

        getStatusColor(status) {
            const colors = {
                'Approved': 'success',
                'Pending': 'warning',
                'Cancelled': 'error',
                'Hr Approved': 'info',
                'Manager Approved': 'primary'
            };
            return colors[status] || 'grey';
        },

        getStatusIcon(status) {
            const icons = {
                'Approved': 'mdi-check-decagram',
                'Pending': 'mdi-clock-outline',
                'Cancelled': 'mdi-close-octagon',
                'Hr Approved': 'mdi-account-check',
                'Manager Approved': 'mdi-shield-check'
            };
            return icons[status] || 'mdi-help-circle';
        },

        viewLeave(item) {
            this.selectedItem = item;
            this.viewLeaveModal = true;
        },

        closeLeaveViewModal() {
            this.viewLeaveModal = false;
        },

        async openLogsModal(item) {
            try {
                const response = await axios.get(`${this.base_url}api/v1/leaves/${item.id}/logs`);
                this.logs = response.data.logs || [];
                this.logsModal = true;
            } catch (error) {
                this.showNotify('Failed to fetch audit logs', 'error', 'mdi-history-off');
            }
        },

        closeLogsModal() {
            this.logsModal = false;
        },

        approveLeave(item) {
            this.selectedItem = item;
            this.approveNotes = '';
            this.approveLeaveModal = true;
        },

        closeApproveLeaveModal() {
            this.approveLeaveModal = false;
        },

        async approveLeaveAction() {
            this.isSubmittingAction = true;
            try {
                await axios.put(`${this.base_url}api/v1/leaves/${this.selectedItem.id}/approve`, { 
                    userId: this.userId,
                    notes: this.approveNotes 
                });
                this.showNotify('Leave request approved successfully', 'success', 'mdi-check-circle');
                this.fetchLeaves();
                this.closeApproveLeaveModal();
            } catch (error) {
                this.showNotify('Approval failed. Please try again.', 'error', 'mdi-alert');
            } finally {
                this.isSubmittingAction = false;
            }
        },

        cancelLeave(item) {
            this.selectedItem = item;
            this.cancelNotes = '';
            this.cancelLeaveModal = true;
        },

        closeCancelLeaveModal() {
            this.cancelLeaveModal = false;
        },

        async cancelLeaveAction() {
            this.isSubmittingAction = true;
            try {
                await axios.put(`${this.base_url}api/v1/leaves/${this.selectedItem.id}/cancel`, { 
                    userId: this.userId,
                    comment: this.cancelNotes 
                });
                this.showNotify('Leave request rejected', 'warning', 'mdi-close-circle');
                this.fetchLeaves();
                this.closeCancelLeaveModal();
            } catch (error) {
                this.showNotify('Cancellation failed', 'error', 'mdi-alert');
            } finally {
                this.isSubmittingAction = false;
            }
        },

        showNotify(text, color, icon) {
            this.snackbar = { show: true, text, color, icon };
        }
    }
};
</script>

<style scoped>
.glass-card {
    background: rgba(255, 255, 255, 0.1) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.modern-table :deep(.v-data-table-header) {
    background-color: #f8fafc;
    text-transform: uppercase;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
}

.modern-table :deep(tr:hover) {
    background-color: #f1f5f9 !important;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.modern-input :deep(.v-field__outline) {
    border-color: #e2e8f0 !important;
}

.modern-input :deep(.v-field--focused .v-field__outline) {
    border-color: #3b82f6 !important;
    border-width: 2px !important;
}

.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
}

.min-vh-100 {
    min-height: 100vh;
}

.log-item:hover {
    background-color: #f8fafc;
    border-radius: 8px;
}
</style>
