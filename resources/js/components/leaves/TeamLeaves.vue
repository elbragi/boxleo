<template>
    <v-container fluid class="pa-4">

        <!-- ── Page Header ───────────────────────────────────────────────── -->
        <div class="tl-page-header mb-5">
            <div>
                <h2 class="tl-page-title">
                    <v-icon color="primary" class="me-2" size="28">mdi-calendar-check</v-icon>
                    Team Leave Approvals
                </h2>
                <p class="tl-page-sub">Review and manage leave requests from your team members</p>
            </div>
            <v-btn color="primary" variant="tonal" rounded prepend-icon="mdi-refresh"
                :loading="loading" @click="fetchLeaves">
                Refresh Data
            </v-btn>
        </div>

        <!-- ── Stat Cards ────────────────────────────────────────────────── -->
        <v-row class="mb-5">
            <v-col cols="6" sm="3" v-for="card in statCards" :key="card.label">
                <v-card class="tl-stat-card" elevation="0" rounded="xl">
                    <div class="tl-stat-icon" :style="{ background: card.color }">
                        <v-icon :icon="card.icon" color="white" size="22"/>
                    </div>
                    <div class="tl-stat-body">
                        <div class="tl-stat-val">{{ card.value }}</div>
                        <div class="tl-stat-lbl">{{ card.label }}</div>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- ── Filters ───────────────────────────────────────────────────── -->
        <v-card elevation="0" rounded="xl" class="tl-filter-card mb-4">
            <v-card-text>
                <div class="tl-filter-title mb-3">
                    <v-icon size="18" class="me-1">mdi-filter-variant</v-icon>
                    Advanced Filters
                </div>
                <v-row align="center" dense>
                    <v-col cols="12" md="4">
                        <v-text-field v-model="search" prepend-inner-icon="mdi-magnify"
                            label="Search Employee or Leave Type" density="compact"
                            variant="outlined" rounded hide-details clearable/>
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select v-model="form.application_date" :items="applicationDateOptions"
                            label="Application Period" density="compact" variant="outlined"
                            rounded hide-details prepend-inner-icon="mdi-calendar-range"/>
                    </v-col>
                    <v-col cols="12" sm="6" md="3">
                        <v-select v-model="form.status" :items="statusOptions"
                            label="Filter by Status" density="compact" variant="outlined"
                            rounded hide-details prepend-inner-icon="mdi-filter-check-outline"/>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-btn color="primary" rounded block @click="filterLeaves">Apply Filters</v-btn>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- ── Table ─────────────────────────────────────────────────────── -->
        <v-card elevation="0" rounded="xl" class="tl-table-card">
            <v-data-table
                :headers="tableHeaders"
                :items="filteredLeaves"
                :search="search"
                :loading="loading"
                items-per-page-text="Items per page:"
                rounded="xl"
                hover>

                <template #item.index="{ index }">
                    <span class="text-caption text-grey">{{ index + 1 }}</span>
                </template>

                <template #item.employee="{ item }">
                    <div class="d-flex align-center gap-2 py-1">
                        <v-avatar size="36" :color="avatarColor(item.user)" class="tl-avatar">
                            <span class="text-caption font-weight-bold text-white">
                                {{ initials(item.user) }}
                            </span>
                        </v-avatar>
                        <div>
                            <div class="font-weight-semibold text-body-2">{{ fullName(item.user) }}</div>
                            <div class="text-caption text-grey">{{ item.user.department?.name || 'N/A' }}</div>
                        </div>
                    </div>
                </template>

                <template #item.leave_type="{ item }">
                    <v-chip size="small" color="primary" variant="tonal" rounded label>
                        {{ item.leave_type?.name?.replace('_', ' ') || 'N/A' }}
                    </v-chip>
                </template>

                <template #item.requested_on="{ item }">
                    <div class="text-body-2">{{ formatDate(item.created_at) }}</div>
                    <div class="text-caption text-grey">{{ formatTime(item.created_at) }}</div>
                </template>

                <template #item.period="{ item }">
                    <div class="d-flex align-center gap-1 text-body-2">
                        <div>
                            <div class="text-caption text-grey font-weight-medium">FROM</div>
                            <div class="font-weight-semibold">{{ item.from }}</div>
                        </div>
                        <v-icon size="16" color="grey" class="mx-1">mdi-arrow-right</v-icon>
                        <div>
                            <div class="text-caption text-grey font-weight-medium">TO</div>
                            <div class="font-weight-semibold">{{ item.to }}</div>
                        </div>
                    </div>
                </template>

                <template #item.status="{ item }">
                    <v-chip :color="statusColor(item.status)" size="small" rounded variant="flat">
                        <v-icon start size="12">{{ statusIcon(item.status) }}</v-icon>
                        {{ item.status.toUpperCase() }}
                    </v-chip>
                </template>

                <template #item.actions="{ item }">
                    <div class="d-flex align-center gap-1">
                        <v-tooltip text="View Logs" location="top">
                            <template #activator="{ props }">
                                <v-btn v-bind="props" icon size="x-small" variant="text" color="info"
                                    @click="openLogsModal(item)">
                                    <v-icon size="18">mdi-history</v-icon>
                                </v-btn>
                            </template>
                        </v-tooltip>
                        <v-tooltip text="View Details" location="top">
                            <template #activator="{ props }">
                                <v-btn v-bind="props" icon size="x-small" variant="text" color="primary"
                                    @click="viewLeave(item)">
                                    <v-icon size="18">mdi-eye-outline</v-icon>
                                </v-btn>
                            </template>
                        </v-tooltip>
                        <v-tooltip v-if="item.status === 'Pending'" text="Approve" location="top">
                            <template #activator="{ props }">
                                <v-btn v-bind="props" icon size="x-small" variant="text" color="success"
                                    @click="approveLeave(item)">
                                    <v-icon size="18">mdi-check-circle-outline</v-icon>
                                </v-btn>
                            </template>
                        </v-tooltip>
                        <v-tooltip v-if="item.status === 'Pending'" text="Reject" location="top">
                            <template #activator="{ props }">
                                <v-btn v-bind="props" icon size="x-small" variant="text" color="error"
                                    @click="cancelLeave(item)">
                                    <v-icon size="18">mdi-close-circle-outline</v-icon>
                                </v-btn>
                            </template>
                        </v-tooltip>
                    </div>
                </template>

                <template #no-data>
                    <div class="tl-empty py-10">
                        <v-icon size="52" color="grey-lighten-2">mdi-calendar-remove-outline</v-icon>
                        <p class="text-grey mt-2">No leave requests found.</p>
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- DIALOGS                                                        -->
        <!-- ══════════════════════════════════════════════════════════════ -->

        <!-- View Leave Details -->
        <v-dialog v-model="viewLeaveModal" max-width="520">
            <v-card v-if="selectedItem" rounded="xl">
                <div class="tl-dialog-header">
                    <v-avatar size="48" :color="avatarColor(selectedItem.user)">
                        <span class="text-white font-weight-bold">{{ initials(selectedItem.user) }}</span>
                    </v-avatar>
                    <div class="ms-3">
                        <div class="text-h6 font-weight-bold text-white">{{ fullName(selectedItem.user) }}</div>
                        <div class="text-caption" style="opacity:0.8">
                            {{ selectedItem.leave_type?.name?.replace('_', ' ') }}
                        </div>
                    </div>
                    <v-spacer/>
                    <v-btn icon="mdi-close" variant="text" color="white" size="small" @click="viewLeaveModal=false"/>
                </div>

                <v-card-text class="pa-5">
                    <v-row dense>
                        <v-col cols="6" v-for="detail in leaveDetails" :key="detail.label">
                            <div class="tl-detail-item">
                                <div class="tl-detail-label">
                                    <v-icon :color="detail.color" size="15" class="me-1">{{ detail.icon }}</v-icon>
                                    {{ detail.label }}
                                </div>
                                <div class="tl-detail-val">{{ detail.value }}</div>
                            </div>
                        </v-col>
                    </v-row>

                    <div v-if="selectedItem.comment" class="tl-comment-box mt-3">
                        <div class="text-caption font-weight-medium mb-1 text-grey">Employee Comment</div>
                        <p class="text-body-2">{{ selectedItem.comment }}</p>
                    </div>
                </v-card-text>

                <v-divider/>
                <v-card-actions class="pa-4 justify-space-between">
                    <div class="d-flex gap-2">
                        <v-btn v-if="selectedItem.status === 'Pending'" color="success" rounded size="small"
                            @click="viewLeaveModal=false; approveLeave(selectedItem)">
                            <v-icon start>mdi-check</v-icon>Approve
                        </v-btn>
                        <v-btn v-if="selectedItem.status === 'Pending'" color="error" variant="outlined" rounded size="small"
                            @click="viewLeaveModal=false; cancelLeave(selectedItem)">
                            <v-icon start>mdi-close</v-icon>Reject
                        </v-btn>
                    </div>
                    <v-btn variant="text" rounded @click="viewLeaveModal=false">Close</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Approve Leave -->
        <v-dialog v-model="approveLeaveModal" max-width="440">
            <v-card rounded="xl">
                <v-card-title class="pa-5 d-flex align-center gap-2">
                    <v-icon color="success" size="24">mdi-check-circle-outline</v-icon>
                    Approve Leave Request
                </v-card-title>
                <v-divider/>
                <v-card-text class="pa-5">
                    <p class="text-body-2 mb-4">
                        Approve leave for <strong>{{ selectedItem ? fullName(selectedItem.user) : '' }}</strong>?
                    </p>
                    <v-textarea v-model="approveNotes" label="Notes (optional)" variant="outlined"
                        density="compact" rounded rows="3" hint="Add any comments for the record"/>
                </v-card-text>
                <v-divider/>
                <v-card-actions class="pa-4">
                    <v-spacer/>
                    <v-btn variant="text" rounded @click="approveLeaveModal=false">Cancel</v-btn>
                    <v-btn color="success" rounded :loading="actioning" @click="approveLeaveAction">
                        Yes, Approve
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Cancel / Reject Leave -->
        <v-dialog v-model="cancelLeaveModal" max-width="440">
            <v-card rounded="xl">
                <v-card-title class="pa-5 d-flex align-center gap-2">
                    <v-icon color="error" size="24">mdi-close-circle-outline</v-icon>
                    Reject Leave Request
                </v-card-title>
                <v-divider/>
                <v-card-text class="pa-5">
                    <p class="text-body-2 mb-4">
                        Reject leave for <strong>{{ selectedItem ? fullName(selectedItem.user) : '' }}</strong>?
                    </p>
                    <v-textarea v-model="cancelNotes" label="Reason for rejection" variant="outlined"
                        density="compact" rounded rows="3" hint="This will be visible to the employee"/>
                </v-card-text>
                <v-divider/>
                <v-card-actions class="pa-4">
                    <v-spacer/>
                    <v-btn variant="text" rounded @click="cancelLeaveModal=false">Cancel</v-btn>
                    <v-btn color="error" rounded :loading="actioning" @click="cancelLeaveAction">
                        Yes, Reject
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- View Logs -->
        <v-dialog v-model="logsModal" max-width="500">
            <v-card rounded="xl">
                <v-card-title class="pa-5 d-flex align-center gap-2">
                    <v-icon color="primary">mdi-history</v-icon>
                    Activity Logs
                </v-card-title>
                <v-divider/>
                <v-card-text class="pa-4">
                    <div v-if="logs.length === 0" class="text-center text-grey py-6">
                        <v-icon size="40" color="grey-lighten-2">mdi-timeline-outline</v-icon>
                        <p class="mt-2">No logs available.</p>
                    </div>
                    <v-timeline v-else density="compact" side="end" truncate-line="both">
                        <v-timeline-item v-for="(log, i) in logs" :key="i" size="x-small" dot-color="primary">
                            <div class="text-body-2 font-weight-semibold">{{ log.action }}</div>
                            <div class="text-caption text-grey">{{ log.user }} &bull; {{ log.time }}</div>
                        </v-timeline-item>
                    </v-timeline>
                </v-card-text>
                <v-divider/>
                <v-card-actions class="pa-4 justify-end">
                    <v-btn variant="text" rounded @click="logsModal=false">Close</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Snackbar -->
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" rounded="xl" location="bottom right">
            <v-icon class="me-2">{{ snackbar.color === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
            {{ snackbar.text }}
        </v-snackbar>

    </v-container>
</template>

<script>
export default {
    props: {
        userId: { type: [Number, String], required: true }
    },

    data() {
        return {
            loading: false,
            actioning: false,
            leaves: [],
            allLeaves: [],
            search: '',
            form: { application_date: 'Last Week', status: 'Pending' },
            applicationDateOptions: ['All', 'Today', 'Current Week', 'Last Week', 'Current Month', 'Current Year'],
            statusOptions: ['All', 'Pending', 'Approved', 'Cancelled', 'Hr Approved'],
            viewLeaveModal: false,
            approveLeaveModal: false,
            cancelLeaveModal: false,
            logsModal: false,
            selectedItem: null,
            approveNotes: '',
            cancelNotes: '',
            logs: [],
            snackbar: { show: false, text: '', color: 'success' },
            tableHeaders: [
                { title: '#', key: 'index', sortable: false, width: '50px' },
                { title: 'Employee', key: 'employee', sortable: false },
                { title: 'Leave Category', key: 'leave_type', sortable: false },
                { title: 'Requested On', key: 'requested_on', sortable: false },
                { title: 'Period', key: 'period', sortable: false },
                { title: 'Status', key: 'status' },
                { title: 'Actions', key: 'actions', sortable: false, align: 'center' },
            ],
        }
    },

    computed: {
        filteredLeaves() {
            return this.leaves
        },

        statCards() {
            const total = this.allLeaves.length
            const pending = this.allLeaves.filter(l => l.status === 'Pending').length
            const approved = this.allLeaves.filter(l => l.status === 'Approved' || l.status === 'Hr Approved').length
            const rejected = this.allLeaves.filter(l => l.status === 'Cancelled').length
            return [
                { label: 'Pending Requests', value: pending, icon: 'mdi-clock-outline', color: '#f59e0b' },
                { label: 'Approved', value: approved, icon: 'mdi-check-circle-outline', color: '#10b981' },
                { label: 'Total Requests', value: total, icon: 'mdi-account-group-outline', color: '#6366f1' },
                { label: 'Rejected', value: rejected, icon: 'mdi-close-circle-outline', color: '#ef4444' },
            ]
        },

        leaveDetails() {
            if (!this.selectedItem) return []
            return [
                { label: 'From', value: this.selectedItem.from, icon: 'mdi-calendar-start', color: 'success' },
                { label: 'To', value: this.selectedItem.to, icon: 'mdi-calendar-end', color: 'error' },
                { label: 'Days', value: this.selectedItem.days ?? 'N/A', icon: 'mdi-calendar-star', color: 'indigo' },
                { label: 'Applied On', value: this.formatDate(this.selectedItem.created_at), icon: 'mdi-calendar-clock', color: 'primary' },
                { label: 'Status', value: this.selectedItem.status, icon: 'mdi-information-outline', color: this.statusColor(this.selectedItem.status) },
                { label: 'Department', value: this.selectedItem.user?.department?.name || 'N/A', icon: 'mdi-office-building', color: 'purple' },
            ]
        },
    },

    created() {
        this.fetchLeaves()
    },

    methods: {
        async fetchLeaves() {
            this.loading = true
            try {
                const { data } = await axios.post('api/v1/team-leaves', { userId: this.userId })
                this.allLeaves = data.leaves
                this.leaves = data.leaves
                this.filterLeaves()
            } catch {
                this.showSnack('Failed to load leave requests', 'error')
            }
            this.loading = false
        },

        filterLeaves() {
            let filtered = [...this.allLeaves]

            if (this.form.status && this.form.status !== 'All') {
                filtered = filtered.filter(l => l.status === this.form.status)
            }

            if (this.form.application_date && this.form.application_date !== 'All') {
                const now = new Date()
                filtered = filtered.filter(l => {
                    const d = new Date(l.created_at)
                    if (this.form.application_date === 'Today') return d.toDateString() === now.toDateString()
                    if (this.form.application_date === 'Current Week') {
                        const start = new Date(now); start.setDate(now.getDate() - now.getDay())
                        const end = new Date(start); end.setDate(start.getDate() + 6)
                        return d >= start && d <= end
                    }
                    if (this.form.application_date === 'Last Week') {
                        const start = new Date(now); start.setDate(now.getDate() - now.getDay() - 7)
                        const end = new Date(start); end.setDate(start.getDate() + 6)
                        return d >= start && d <= end
                    }
                    if (this.form.application_date === 'Current Month')
                        return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear()
                    if (this.form.application_date === 'Current Year')
                        return d.getFullYear() === now.getFullYear()
                    return true
                })
            }

            this.leaves = filtered
        },

        viewLeave(item) { this.selectedItem = item; this.viewLeaveModal = true },
        approveLeave(item) { this.selectedItem = item; this.approveNotes = ''; this.approveLeaveModal = true },
        cancelLeave(item) { this.selectedItem = item; this.cancelNotes = ''; this.cancelLeaveModal = true },

        async approveLeaveAction() {
            this.actioning = true
            try {
                await axios.put(`api/v1/leaves/${this.selectedItem.id}/approve`, { userId: this.userId })
                this.showSnack('Leave approved successfully', 'success')
                this.approveLeaveModal = false
                this.fetchLeaves()
            } catch { this.showSnack('Error approving leave', 'error') }
            this.actioning = false
        },

        async cancelLeaveAction() {
            this.actioning = true
            try {
                await axios.put(`api/v1/leaves/${this.selectedItem.id}/cancel`, { userId: this.userId })
                this.showSnack('Leave rejected', 'success')
                this.cancelLeaveModal = false
                this.fetchLeaves()
            } catch { this.showSnack('Error rejecting leave', 'error') }
            this.actioning = false
        },

        async openLogsModal(item) {
            this.logs = []
            this.logsModal = true
            try {
                const { data } = await axios.get(`api/v1/leaves/${item.id}/logs`)
                this.logs = data.logs
            } catch {}
        },

        // ── Helpers ──────────────────────────────────────────────────────
        fullName: (u) => u ? `${u.firstname} ${u.lastname}` : '',
        initials: (u) => u ? `${u.firstname?.[0] ?? ''}${u.lastname?.[0] ?? ''}`.toUpperCase() : '?',

        avatarColor(user) {
            const colors = ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#ef4444','#14b8a6']
            const str = user ? `${user.firstname}${user.lastname}` : ''
            let hash = 0
            for (let c of str) hash = c.charCodeAt(0) + ((hash << 5) - hash)
            return colors[Math.abs(hash) % colors.length]
        },

        statusColor(s) {
            return { Pending: 'warning', Approved: 'success', 'Hr Approved': 'info', Cancelled: 'error' }[s] || 'grey'
        },

        statusIcon(s) {
            return { Pending: 'mdi-clock-outline', Approved: 'mdi-check-circle', 'Hr Approved': 'mdi-check-all', Cancelled: 'mdi-close-circle' }[s] || 'mdi-help'
        },

        formatDate(d) {
            if (!d) return 'N/A'
            return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
        },

        formatTime(d) {
            if (!d) return ''
            return new Date(d).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' })
        },

        showSnack(text, color = 'success') { this.snackbar = { show: true, text, color } },
    },
}
</script>

<style scoped>
.tl-page-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; flex-wrap: wrap; gap: 12px;
}
.tl-page-title { font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; }
.tl-page-sub { color: #9e9e9e; font-size: 0.875rem; margin-top: 2px; }

/* ── Stat cards ──────────────────────────────────────────────────────────── */
.tl-stat-card {
    display: flex; align-items: center; gap: 14px; padding: 16px;
    border: 1px solid rgba(0,0,0,0.07);
}
.tl-stat-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.tl-stat-val { font-size: 1.7rem; font-weight: 800; line-height: 1; color: #1a1a2e; }
.tl-stat-lbl { font-size: 0.78rem; color: #757575; margin-top: 3px; }

/* ── Filter card ─────────────────────────────────────────────────────────── */
.tl-filter-card { border: 1px solid rgba(0,0,0,0.07); }
.tl-filter-title { font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; color: #424242; }

/* ── Table card ──────────────────────────────────────────────────────────── */
.tl-table-card { border: 1px solid rgba(0,0,0,0.07); overflow: hidden; }

.tl-avatar { font-size: 0.75rem; }

/* ── Leave detail dialog header ──────────────────────────────────────────── */
.tl-dialog-header {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    padding: 20px; display: flex; align-items: center;
}

/* ── Detail item ─────────────────────────────────────────────────────────── */
.tl-detail-item { margin-bottom: 14px; }
.tl-detail-label { font-size: 0.72rem; color: #9e9e9e; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; }
.tl-detail-val { font-size: 0.9rem; font-weight: 600; color: #212121; margin-top: 2px; }

/* ── Comment box ─────────────────────────────────────────────────────────── */
.tl-comment-box {
    background: rgba(0,0,0,0.03); border-radius: 10px; padding: 12px;
    border-left: 3px solid #6366f1;
}

/* ── Empty state ─────────────────────────────────────────────────────────── */
.tl-empty { display: flex; flex-direction: column; align-items: center; }

.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.font-weight-semibold { font-weight: 600; }
</style>
