<template>
    <v-container-fluid class="app-container pa-6">
        <!-- Mesh Background Elements -->
        <div class="mesh-bg">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        </div>

        <!-- Header Stats Cards -->
        <v-row class="mb-8 mt-2 relative-z">
            <v-col cols="12" md="3">
                <v-card class="glass-card stat-card pa-4 overflow-hidden" elevation="0">
                    <div class="d-flex align-center justify-space-between relative-z">
                        <div>
                            <div class="text-overline text-indigo-darken-4 font-weight-black mb-1 opacity-70">Requests Pool</div>
                            <div class="text-h4 font-weight-black tracking-tighter">{{ stats.total }}</div>
                        </div>
                        <v-btn icon color="indigo-lighten-4" variant="tonal" class="rounded-lg shadow-sm">
                            <v-icon color="indigo-darken-4" size="24">mdi-database-outline</v-icon>
                        </v-btn>
                    </div>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card class="glass-card stat-card pa-4 overflow-hidden" elevation="0">
                    <div class="d-flex align-center justify-space-between relative-z">
                        <div>
                            <div class="text-overline text-orange-darken-4 font-weight-black mb-1 opacity-70">Awaiting Action</div>
                            <div class="text-h4 font-weight-black text-orange-darken-3 tracking-tighter">{{ stats.pending }}</div>
                        </div>
                        <v-btn icon color="orange-lighten-4" variant="tonal" class="rounded-lg shadow-sm">
                            <v-icon color="orange-darken-4" size="24">mdi-timer-sand-complete</v-icon>
                        </v-btn>
                    </div>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card class="glass-card stat-card pa-4 overflow-hidden" elevation="0">
                    <div class="d-flex align-center justify-space-between relative-z">
                        <div>
                            <div class="text-overline text-blue-darken-4 font-weight-black mb-1 opacity-70">Active Sprints</div>
                            <div class="text-h4 font-weight-black text-blue-darken-3 tracking-tighter">{{ stats.in_progress }}</div>
                        </div>
                        <v-btn icon color="blue-lighten-4" variant="tonal" class="rounded-lg shadow-sm">
                            <v-icon color="blue-darken-4" size="24">mdi-layers-triple-outline</v-icon>
                        </v-btn>
                    </div>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card class="glass-card stat-card pa-4 overflow-hidden" elevation="0">
                    <div class="d-flex align-center justify-space-between relative-z">
                        <div>
                            <div class="text-overline text-green-darken-4 font-weight-black mb-1 opacity-70">Delivered</div>
                            <div class="text-h4 font-weight-black text-green-darken-3 tracking-tighter">{{ stats.completed }}</div>
                        </div>
                        <v-btn icon color="green-lighten-4" variant="tonal" class="rounded-lg shadow-sm">
                            <v-icon color="green-darken-4" size="24">mdi-auto-fix</v-icon>
                        </v-btn>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Main Tracker Card -->
        <v-card class="glass-card main-content-card relative-z" elevation="0">
            <!-- Header Section -->
            <div class="px-8 py-8 d-flex align-center border-bottom-glass">
                <div class="d-flex flex-column">
                    <h1 class="font-weight-black text-h4 tracking-tighter indigo-gradient-text mb-1">
                        Task Tracking
                    </h1>
                    <span class="text-overline text-indigo-darken-2 font-weight-black opacity-60" style="line-height:1">
                        IT DEPT. PROGRESS MATRIX
                    </span>
                </div>
                
                <v-spacer></v-spacer>
                
                <div class="d-flex align-center toolbar-actions">
                    <v-text-field
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        label="Quick filtering..."
                        single-line
                        hide-details
                        density="comfortable"
                        variant="solo"
                        class="search-bar-modern mr-4"
                        rounded="lg"
                        flat
                        bg-color="rgba(255,255,255,0.4)"
                    ></v-text-field>
                    
                    <v-btn
                        color="indigo-darken-4"
                        class="rounded-lg px-8 text-none font-weight-black shadow-soft"
                        height="48"
                        @click="openDialog()"
                        prepend-icon="mdi-plus-circle"
                    >
                        NEW TICKET
                    </v-btn>
                    <v-btn icon @click="fetchRequests" class="ml-3 glass-btn" size="large" elevation="0">
                        <v-icon size="20" color="indigo-darken-4">mdi-sync</v-icon>
                    </v-btn>
                </div>
            </div>

            <v-data-table
                :headers="headers"
                :items="requests"
                :search="search"
                :loading="loading"
                hover
                class="glass-table-modern"
            >
                <template v-slot:item.title="{ item }">
                    <div class="font-weight-bold text-slate-800 text-body-2 py-2 title-cell">{{ item.title }}</div>
                </template>

                <template v-slot:item.priority="{ item }">
                    <v-chip
                        :color="getPriorityColor(item.priority)"
                        size="x-small"
                        class="text-uppercase font-weight-black priority-badge"
                        variant="flat"
                    >
                        {{ item.priority }}
                    </v-chip>
                </template>

                <template v-slot:item.status="{ item }">
                    <div class="d-flex align-center">
                        <div class="status-dot mr-2" :style="{ backgroundColor: getStatusColorHex(item.status) }"></div>
                        <span class="text-caption font-weight-black text-uppercase tracking-wider" :style="{ color: getStatusColorHex(item.status) }">
                            {{ item.status.replace('_', ' ') }}
                        </span>
                    </div>
                </template>

                <template v-slot:item.requested_by="{ item }">
                    <div class="text-caption font-weight-black text-indigo-darken-3">
                        {{ item.requested_by || '-' }}
                    </div>
                </template>

                <template v-slot:item.developer_name="{ item }">
                    <v-tooltip location="top" :text="item.developer_name || 'Unassigned'">
                        <template v-slot:activator="{ props }">
                            <v-avatar v-bind="props" size="26" class="elevation-1 cursor-pointer bg-indigo-darken-4">
                                <span class="text-caption text-white font-weight-bold">{{ item.developer_name ? item.developer_name.charAt(0) : '?' }}</span>
                            </v-avatar>
                        </template>
                    </v-tooltip>
                </template>

                <template v-slot:item.reported_at="{ item }">
                    <div class="text-caption font-weight-bold opacity-60">
                        {{ formatDateShort(item.reported_at) }}
                    </div>
                </template>

                <template v-slot:item.actions="{ item }">
                    <div class="d-flex gap-2">
                        <v-btn icon size="x-small" variant="text" color="indigo-darken-2" class="glass-btn-sm" @click="openDialog(item)">
                            <v-icon size="14">mdi-pencil-box-multiple-outline</v-icon>
                        </v-btn>
                        <v-btn icon size="x-small" variant="text" color="red-darken-2" class="glass-btn-sm" @click="confirmDelete(item)">
                            <v-icon size="14">mdi-close-circle-outline</v-icon>
                        </v-btn>
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Upsert Dialog -->
        <v-dialog v-model="dialog" max-width="700px" persistent class="glass-overlay">
            <v-card class="glass-modal-modern rounded-xl overflow-hidden">
                <v-card-title class="pa-6 border-bottom-glass d-flex align-center bg-indigo-darken-4 text-white">
                    <v-icon color="white" class="mr-3">mdi-clipboard-edit-outline</v-icon>
                    <span class="text-h6 font-weight-black tracking-tight">{{ formTitle.toUpperCase() }}</span>
                    <v-spacer></v-spacer>
                    <v-btn icon variant="text" color="white" size="small" @click="close"><v-icon>mdi-close</v-icon></v-btn>
                </v-card-title>
                
                <v-card-text class="pa-8">
                    <v-form ref="form" v-model="valid">
                        <v-row>
                            <v-col cols="12">
                                <v-text-field
                                    v-model="editedItem.title"
                                    label="Objective*"
                                    required
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                    :rules="[v => !!v || 'Objective is required']"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-textarea
                                    v-model="editedItem.description"
                                    label="Technical Context*"
                                    required
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                    rows="3"
                                    :rules="[v => !!v || 'Context is required']"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.priority"
                                    :items="['low', 'medium', 'high', 'urgent']"
                                    label="Priority"
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.status"
                                    :items="['pending', 'in_progress', 'completed', 'on_hold', 'cancelled']"
                                    label="Lifecycle Stage"
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-select
                                    v-model="editedItem.department_id"
                                    :items="departments"
                                    item-title="name"
                                    item-value="id"
                                    label="Requesting Dept"
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                    clearable
                                ></v-select>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.requested_by"
                                    label="Requested By"
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                    prepend-inner-icon="mdi-account-question-outline"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.developer_name"
                                    label="Assigned Dev"
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                    prepend-inner-icon="mdi-account-star-outline"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.reported_at"
                                    label="Log Date"
                                    type="date"
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-text-field
                                    v-model="editedItem.effort_hours"
                                    label="Est. Cycle (Hrs)"
                                    type="number"
                                    variant="outlined"
                                    density="comfortable"
                                    color="indigo"
                                    class="modern-input"
                                ></v-text-field>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                
                <v-card-actions class="pa-8 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn variant="text" class="text-none font-weight-black" @click="close">ABORT</v-btn>
                    <v-btn color="indigo-darken-4" variant="flat" class="text-none font-weight-black px-10 rounded-lg shadow-sm" height="48" :loading="saving" :disabled="!valid" @click="save">
                        EXECUTE COMMIT
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation -->
        <v-dialog v-model="deleteDialog" max-width="400px" persistent>
            <v-card class="glass-modal-modern rounded-xl overflow-hidden">
                <div class="pa-6 bg-red-darken-4 text-white d-flex align-center">
                    <v-icon color="white" class="mr-2">mdi-alert-octagon</v-icon>
                    <span class="font-weight-black text-h6">DESTRUCTIVE ACTION</span>
                </div>
                <v-card-text class="pa-8 text-indigo-darken-4 font-weight-medium">
                    Permanent deletion of this record is irreversible. Proceed with caution.
                </v-card-text>
                <v-card-actions class="pa-6 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn variant="text" class="text-none font-weight-black" @click="deleteDialog = false">CANCEL</v-btn>
                    <v-btn color="red-darken-4" variant="flat" class="text-none font-weight-black px-6 rounded-lg" @click="deleteItem" :loading="deleting">CONFIRM DELETE</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container-fluid>
</template>

<script>
export default {
    data() {
        return {
            loading: false,
            saving: false,
            deleting: false,
            dialog: false,
            deleteDialog: false,
            valid: true,
            search: '',
            requests: [],
            departments: [],
            stats: {
                total: 0,
                pending: 0,
                in_progress: 0,
                completed: 0
            },
            headers: [
                { title: 'Task Objective', key: 'title', align: 'start' },
                { title: 'Requester', key: 'requested_by', width: '120px' },
                { title: 'Priority', key: 'priority', width: '90px' },
                { title: 'Status', key: 'status', width: '130px' },
                { title: 'Dev', key: 'developer_name', width: '50px', align: 'center' },
                { title: 'Logged', key: 'reported_at', width: '100px' },
                { title: 'Actions', key: 'actions', sortable: false, width: '100px', align: 'end' }
            ],
            editedIndex: -1,
            editedItem: {
                title: '',
                description: '',
                priority: 'medium',
                status: 'pending',
                department_id: null,
                requested_by: '',
                reported_at: new Date().toISOString().substr(0, 10),
                target_due_date: null,
                effort_hours: 0,
                developer_name: 'Mohammed',
                comments: ''
            },
            defaultItem: {
                title: '',
                description: '',
                priority: 'medium',
                status: 'pending',
                department_id: null,
                requested_by: '',
                reported_at: new Date().toISOString().substr(0, 10),
                target_due_date: null,
                effort_hours: 0,
                developer_name: 'Mohammed',
                comments: ''
            },
            itemToDelete: null
        };
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'Generate New Log' : 'Update Log Sequence';
        }
    },
    mounted() {
        this.fetchRequests();
        this.fetchDepartments();
    },
    methods: {
        async fetchRequests() {
            this.loading = true;
            try {
                const response = await axios.get('/api/v1/system-requests');
                this.requests = response.data.data;
                this.calculateStats();
            } catch (error) {
                this.$toastr.error('API Handshake Failed');
            } finally {
                this.loading = false;
            }
        },
        async fetchDepartments() {
            try {
                const response = await axios.get('/api/v1/departments');
                this.departments = response.data.departments;
            } catch (error) {
                console.error('Dept Fetch Error');
            }
        },
        calculateStats() {
            this.stats.total = this.requests.length;
            this.stats.pending = this.requests.filter(r => r.status === 'pending').length;
            this.stats.in_progress = this.requests.filter(r => r.status === 'in_progress').length;
            this.stats.completed = this.requests.filter(r => r.status === 'completed').length;
        },
        getPriorityColor(priority) {
            const colors = {
                low: '#94a3b8',
                medium: '#3b82f6',
                high: '#f59e0b',
                urgent: '#ef4444'
            };
            return colors[priority] || '#94a3b8';
        },
        getStatusColorHex(status) {
            const colors = {
                pending: '#f59e0b',
                in_progress: '#3b82f6',
                completed: '#10b981',
                on_hold: '#64748b',
                cancelled: '#ef4444'
            };
            return colors[status] || '#94a3b8';
        },
        formatDateShort(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-KE', {
                month: 'short',
                day: 'numeric',
                year: '2-digit'
            });
        },
        openDialog(item = null) {
            if (item) {
                this.editedIndex = this.requests.indexOf(item);
                this.editedItem = Object.assign({}, item);
                if (this.editedItem.reported_at) {
                    this.editedItem.reported_at = this.editedItem.reported_at.substr(0, 10);
                }
                if (!this.editedItem.developer_name) {
                    this.editedItem.developer_name = 'Mohammed';
                }
            } else {
                this.editedIndex = -1;
                this.editedItem = Object.assign({}, this.defaultItem);
            }
            this.dialog = true;
        },
        close() {
            this.dialog = false;
            this.$nextTick(() => {
                this.editedItem = Object.assign({}, this.defaultItem);
                this.editedIndex = -1;
            });
        },
        async save() {
            if (!this.$refs.form.validate()) return;

            this.saving = true;
            try {
                if (this.editedIndex > -1) {
                    await axios.put(`/api/v1/system-requests/${this.editedItem.id}`, this.editedItem);
                    this.$toastr.success('Registry Serial Updated');
                } else {
                    await axios.post('/api/v1/system-requests', this.editedItem);
                    this.$toastr.success('New Registry Serial Logged');
                }
                this.fetchRequests();
                this.close();
            } catch (error) {
                this.$toastr.error('System Integrity Error');
            } finally {
                this.saving = false;
            }
        },
        confirmDelete(item) {
            this.itemToDelete = item;
            this.deleteDialog = true;
        },
        async deleteItem() {
            this.deleting = true;
            try {
                await axios.delete(`/api/v1/system-requests/${this.itemToDelete.id}`);
                this.$toastr.success('Registry Entry Purged');
                this.fetchRequests();
                this.deleteDialog = false;
            } catch (error) {
                this.$toastr.error('Purge Failed');
            } finally {
                this.deleting = false;
            }
        }
    }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

.app-container {
    font-family: 'Outfit', sans-serif !important;
    background: #f1f5f9;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
}

/* Mesh Background */
.mesh-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
}

.blob {
    position: absolute;
    width: 600px;
    height: 600px;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.15;
    animation: blobify 20s infinite alternate;
}

.blob-1 { background: #4f46e5; top: -100px; left: -100px; }
.blob-2 { background: #0ea5e9; bottom: -100px; right: -100px; animation-delay: -5s; }
.blob-3 { background: #8b5cf6; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-delay: -10s; }

@keyframes blobify {
    0% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0, 0) scale(1); }
}

.relative-z { position: relative; z-index: 1; }

.glass-card {
    background: rgba(255, 255, 255, 0.7) !important;
    backdrop-filter: blur(12px) saturate(160%) !important;
    -webkit-backdrop-filter: blur(12px) saturate(160%) !important;
    border: 1px solid rgba(255, 255, 255, 0.4) !important;
    border-radius: 20px !important;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.85) !important;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08) !important;
}

.indigo-gradient-text {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.search-bar-modern {
    width: 280px !important;
    border: 1px solid rgba(0,0,0,0.05);
}

.shadow-soft {
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.25) !important;
}

.glass-btn {
    background: rgba(255, 255, 255, 0.5) !important;
    border: 1px solid rgba(255, 255, 255, 0.6) !important;
}

.glass-btn-sm {
    background: rgba(255, 255, 255, 0.3) !important;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
    transition: all 0.2s ease;
}

.glass-btn-sm:hover {
    background: rgba(255, 255, 255, 0.8) !important;
    transform: scale(1.1);
}

.glass-table-modern {
    background: transparent !important;
}

.glass-table-modern >>> thead th {
    background: rgba(255,255,255,0.4) !important;
    color: #1e1b4b !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    font-size: 0.65rem !important;
    letter-spacing: 0.1em !important;
    border-bottom: 2px solid rgba(0,0,0,0.05) !important;
}

.glass-table-modern >>> tbody td {
    border-bottom: 1px solid rgba(0,0,0,0.03) !important;
}

.title-cell {
    color: #1e293b;
    line-height: 1.4;
    max-width: 450px;
}

.priority-badge {
    letter-spacing: 0.05em;
    border-radius: 4px !important;
    font-size: 0.6rem !important;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    box-shadow: 0 0 8px currentColor;
}

/* Modal Styling */
.glass-modal-modern {
    background: rgba(255, 255, 255, 0.9) !important;
    backdrop-filter: blur(20px) !important;
    border: 1px solid rgba(255,255,255,0.6) !important;
}

.modern-input >>> .v-field--outline__start,
.modern-input >>> .v-field--outline__end {
    border-color: rgba(0,0,0,0.1) !important;
}

.tracking-tighter {
    letter-spacing: -2px;
}

.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
}

.cursor-pointer { cursor: pointer; }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }
</style>
