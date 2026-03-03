<script>
export default {
    data() {
        return {
            search: '',
            loading: false,
            viewMode: 'calendar', // 'calendar' or 'timeline'
            addHolidayDialog: false,
            editHolidayDialog: false,
            editingHoliday: null,
            newHoliday: {
                name: '',
                date: new Date().toISOString().substr(0, 10),
                unit_id: null
            },
            holidays: [],
            branches: [],
            filters: {
                holidays: true,
                training: true,
                meetings: true,
                birthdays: true,
                other: true
            },
            categories: [
                { key: 'holidays', label: 'Holidays', color: '#FF5252', pattern: ['holiday', 'day'] },
                { key: 'training', label: 'Training', color: '#3F51B5', pattern: ['training'] },
                { key: 'meetings', label: 'Meetings', color: '#2196F3', pattern: ['meeting', 'quarterly'] },
                { key: 'birthdays', label: 'Birthdays', color: '#9C27B0', pattern: ['birthday'] },
                { key: 'other', label: 'Other', color: '#FF9800', pattern: ['party', 'building'] }
            ],
            currentCalendarPage: {
                month: new Date().getMonth() + 1,
                year: new Date().getFullYear()
            }
        };
    },
    props: {
        user: {
            type: Object,
            required: true
        }
    },
    computed: {
        isAdmin() {
            // Check for admin role name or a known admin role ID
            // Based on typical Laravel setups in this project
            return this.user && (this.user.role === 'admin' || this.user.role_id === 1);
        },
        filteredHolidays() {
            return this.holidays.filter(holiday => {
                const name = holiday.name.toLowerCase();
                const matchesSearch = name.includes(this.search.toLowerCase());
                
                let matchesFilter = false;
                if (this.filters.holidays && this.categories[0].pattern.some(p => name.includes(p))) matchesFilter = true;
                else if (this.filters.training && this.categories[1].pattern.some(p => name.includes(p))) matchesFilter = true;
                else if (this.filters.meetings && this.categories[2].pattern.some(p => name.includes(p))) matchesFilter = true;
                else if (this.filters.birthdays && this.categories[3].pattern.some(p => name.includes(p))) matchesFilter = true;
                else if (this.filters.other) {
                    const known = [].concat(...this.categories.slice(0, 4).map(c => c.pattern));
                    if (!known.some(p => name.includes(p))) matchesFilter = true;
                }

                return matchesSearch && matchesFilter;
            });
        },
        monthFilteredHolidays() {
            if (this.viewMode !== 'calendar') return [...this.filteredHolidays].sort((a, b) => new Date(a.date) - new Date(b.date));
            return this.filteredHolidays
                .filter(holiday => {
                    const d = new Date(holiday.date);
                    return (d.getMonth() + 1) === this.currentCalendarPage.month && 
                           d.getFullYear() === this.currentCalendarPage.year;
                })
                .sort((a, b) => new Date(a.date) - new Date(b.date));
        },
        formattedAttributes() {
            return this.filteredHolidays.map(holiday => {
                const category = this.getCategory(holiday.name);
                return {
                    key: holiday.id,
                    highlight: {
                        color: category.color,
                        fillMode: 'light',
                    },
                    dates: new Date(holiday.date),
                    popover: {
                        label: holiday.name,
                        visibility: 'hover',
                    },
                    customData: holiday
                };
            });
        },
        stats() {
            const now = new Date();
            const thisMonth = now.getMonth();
            const thisYear = now.getFullYear();
            
            const monthHolidays = this.holidays.filter(h => {
                const d = new Date(h.date);
                return d.getMonth() === thisMonth && d.getFullYear() === thisYear;
            });

            const nextEvent = this.holidays
                .filter(h => new Date(h.date) >= now)
                .sort((a, b) => new Date(a.date) - new Date(b.date))[0];

            return {
                thisMonth: monthHolidays.length,
                nextEvent: nextEvent ? nextEvent.name : 'None',
                birthdays: monthHolidays.filter(h => h.name.toLowerCase().includes('birthday')).length
            };
        },
        timelineGroups() {
            const groups = {};
            this.filteredHolidays.forEach(holiday => {
                const date = new Date(holiday.date);
                const monthYear = date.toLocaleString('default', { month: 'long', year: 'numeric' });
                if (!groups[monthYear]) groups[monthYear] = [];
                groups[monthYear].push(holiday);
            });
            return Object.keys(groups).map(key => ({
                month: key,
                events: groups[key].sort((a, b) => new Date(a.date) - new Date(b.date))
            })).sort((a, b) => {
                const dateA = new Date(a.events[0].date);
                const dateB = new Date(b.events[0].date);
                return dateA - dateB;
            });
        }
    },
    created() {
        this.fetchHolidays();
        this.fetchBranches();
    },
    methods: {
        fetchHolidays() {
            this.loading = true;
            axios.get('/api/v1/holidays')
                .then(response => {
                    this.holidays = response.data.holidays || response.data || [];
                })
                .catch(error => {
                    this.$toastr.error("Failed to fetch holidays");
                })
                .finally(() => {
                    this.loading = false;
                });
        },
        fetchBranches() {
            axios.get('/api/v1/branches')
                .then(response => {
                    this.branches = response.data.branches || response.data || [];
                });
        },
        getCategory(name) {
            const lower = name.toLowerCase();
            for (const cat of this.categories) {
                if (cat.pattern.some(p => lower.includes(p))) return cat;
            }
            return this.categories[4]; // Other
        },
        getStatusColor(name) {
            return this.getCategory(name).color;
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
        },
        addHoliday() {
            axios.post('/api/v1/holidays', this.newHoliday)
                .then(response => {
                    this.holidays.push(response.data.holiday || response.data);
                    this.$toastr.success("Event created!");
                    this.addHolidayDialog = false;
                });
        },
        editEvent(event) {
            this.editingHoliday = { ...event };
            this.editHolidayDialog = true;
        },
        updateHoliday() {
            axios.put(`/api/v1/holidays/${this.editingHoliday.id}`, this.editingHoliday)
                .then(response => {
                    const index = this.holidays.findIndex(h => h.id === this.editingHoliday.id);
                    if (index !== -1) {
                        this.holidays.splice(index, 1, response.data.holiday || response.data);
                    }
                    this.$toastr.success("Event updated!");
                    this.editHolidayDialog = false;
                });
        },
        deleteHoliday(id) {
            if (confirm("Delete this event?")) {
                axios.delete(`/api/v1/holidays/${id}`).then(() => {
                    this.holidays = this.holidays.filter(h => h.id !== id);
                    this.$toastr.success("Event deleted");
                });
            }
        },
        onPageUpdate(pages) {
            if (pages && pages.length > 0) {
                this.currentCalendarPage = {
                    month: pages[0].month,
                    year: pages[0].year
                };
            }
        }
    }
};
</script>

<template>
    <v-container fluid class="calendar-app-container pa-0">
        <v-row no-gutters class="fill-height">
            <!-- Sidebar -->
            <v-col cols="12" md="3" class="sidebar-col pa-6">
                <div class="glass-sidebar">
                    <div class="d-flex align-center mb-8">
                        <v-icon size="32" color="primary" class="mr-3">mdi-calendar-heart</v-icon>
                        <h1 class="text-h4 font-weight-bold primary--text">Calendar</h1>
                    </div>

                    <!-- Search -->
                    <v-text-field
                        v-model="search"
                        placeholder="Search events..."
                        prepend-inner-icon="mdi-magnify"
                        variant="solo"
                        rounded="lg"
                        class="mb-6 elevation-1"
                        hide-details
                    ></v-text-field>

                    <!-- Stats Cards -->
                    <div class="stats-grid mb-8">
                        <v-card class="stat-card pa-4 rounded-xl elevation-2 mb-4" color="primary" theme="dark">
                            <div class="text-caption">Events this Month</div>
                            <div class="text-h3 font-weight-black">{{ stats.thisMonth }}</div>
                        </v-card>
                        <v-row dense>
                            <v-col cols="6">
                                <v-card class="stat-card pa-4 rounded-xl elevation-2" color="secondary" theme="dark">
                                    <div class="text-caption">Birthdays</div>
                                    <div class="text-h4 font-weight-black">{{ stats.birthdays }}</div>
                                </v-card>
                            </v-col>
                            <v-col cols="6">
                                <v-card class="stat-card pa-4 rounded-xl elevation-2" color="info" theme="dark">
                                    <div class="text-caption">Next Up</div>
                                    <div class="text-subtitle-2 font-weight-bold truncate">{{ stats.nextEvent }}</div>
                                </v-card>
                            </v-col>
                        </v-row>
                    </div>

                    <!-- Filters -->
                    <div class="filter-section">
                        <h3 class="text-subtitle-1 font-weight-bold mb-4">Categories</h3>
                        <v-checkbox
                            v-for="cat in categories"
                            :key="cat.key"
                            v-model="filters[cat.key]"
                            :label="cat.label"
                            :color="cat.color"
                            density="comfortable"
                            hide-details
                            class="mb-1"
                        ></v-checkbox>
                    </div>

                    <v-btn
                        v-if="isAdmin"
                        block
                        color="primary"
                        size="large"
                        rounded="pill"
                        class="mt-8 elevation-4"
                        prepend-icon="mdi-plus"
                        @click="addHolidayDialog = true"
                    >
                        New Event
                    </v-btn>
                </div>
            </v-col>

            <!-- Main Content -->
            <v-col cols="12" md="9" class="main-content-col pa-6">
                <v-card class="main-card glass-morphism rounded-xl elevation-12">
                    <v-toolbar flat color="transparent" class="px-4">
                        <v-tabs v-model="viewMode" color="primary" class="mt-2">
                            <v-tab value="calendar">
                                <v-icon start>mdi-calendar-month</v-icon>
                                Calendar
                            </v-tab>
                            <v-tab value="timeline">
                                <v-icon start>mdi-format-list-bulleted-type</v-icon>
                                Timeline
                            </v-tab>
                        </v-tabs>
                        <v-spacer></v-spacer>
                        <v-btn icon @click="fetchHolidays" :loading="loading">
                            <v-icon>mdi-refresh</v-icon>
                        </v-btn>
                    </v-toolbar>

                    <v-divider></v-divider>

                    <v-window v-model="viewMode" class="pa-6" style="overflow: visible !important;">
                        <v-window-item value="calendar" style="overflow: visible !important;">
                            <div class="calendar-wrapper d-flex flex-column align-center pb-24">
                                <VCalendar
                                    expanded
                                    borderless
                                    transparent
                                    title-position="left"
                                    :attributes="formattedAttributes"
                                    class="premium-vcalendar mb-8"
                                    @update:pages="onPageUpdate"
                                >
                                    <template #day-popover="{ attributes }">
                                        <div class="popover-content pa-3">
                                            <div v-for="attr in attributes" :key="attr.key" class="mb-2">
                                                <v-chip size="x-small" :color="getStatusColor(attr.customData.name)" label class="mb-1">
                                                    {{ attr.customData.name }}
                                                </v-chip>
                                                <div class="text-caption grey--text">
                                                    {{ formatDate(attr.customData.date) }}
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </VCalendar>

                                <!-- Events List Section -->
                                <v-card class="events-summary-card w-100 mt-8 rounded-xl elevation-4 glass-morphism border-primary-light">
                                    <v-card-title class="d-flex align-center px-6 py-4 bg-light-primary">
                                        <v-icon color="primary" class="mr-2">mdi-format-list-bulleted</v-icon>
                                        <span class="text-h6 font-weight-bold primary--text">Events Summary</span>
                                        <v-spacer></v-spacer>
                                        <v-chip size="small" variant="flat" color="primary" class="font-weight-bold">
                                            {{ monthFilteredHolidays.length }} events in {{ new Date(currentCalendarPage.year, currentCalendarPage.month - 1).toLocaleString('default', { month: 'long', year: 'numeric' }) }}
                                        </v-chip>
                                    </v-card-title>
                                    
                                    <v-divider></v-divider>

                                    <v-card-text class="pa-4 scrollable-events-area custom-scrollbar pb-10">
                                        <div v-if="monthFilteredHolidays.length === 0" class="text-center py-12 grey--text text-body-1 d-flex flex-column align-center">
                                            <v-icon size="48" color="grey-lighten-2" class="mb-2">mdi-calendar-blank</v-icon>
                                            No events found for this month.
                                        </div>

                                        <v-list v-else bg-color="transparent" class="pa-0">
                                            <v-list-item
                                                v-for="(event, index) in monthFilteredHolidays.slice(0, 50)"
                                                :key="'list-' + event.id"
                                                class="event-list-item mb-3 rounded-xl elevation-1 py-3"
                                                :style="{ borderLeft: `6px solid ${getStatusColor(event.name)}` }"
                                            >
                                                <template v-slot:prepend>
                                                    <div class="d-flex align-center">
                                                        <div class="text-caption font-weight-black grey--text mr-3" style="min-width: 20px;">#{{ index + 1 }}</div>
                                                        <div class="date-badge-premium mr-4 text-center d-flex flex-column justify-center align-center shadow-sm">
                                                            <div class="text-caption font-weight-black primary--text text-uppercase line-height-1 mb-1">{{ new Date(event.date).toLocaleString('default', { month: 'short' }) }}</div>
                                                            <div class="text-h5 font-weight-black line-height-1">{{ new Date(event.date).getDate() }}</div>
                                                        </div>
                                                    </div>
                                                </template>

                                                <v-list-item-title class="text-h6 font-weight-bold mb-1">{{ event.name }}</v-list-item-title>
                                                <v-list-item-subtitle class="text-body-2 d-flex align-center">
                                                    <v-icon size="16" class="mr-1 grey--text">mdi-map-marker-radius-outline</v-icon>
                                                    <span class="grey--text font-weight-medium">{{ event.branch ? event.branch.name : 'All Branches' }}</span>
                                                </v-list-item-subtitle>

                                                <template v-slot:append>
                                                    <div class="d-flex align-center">
                                                        <v-chip
                                                            size="small"
                                                            :color="getStatusColor(event.name)"
                                                            variant="flat"
                                                            class="mr-3 text-white font-weight-bold px-4 elevation-2 rounded-lg"
                                                        >
                                                            {{ getCategory(event.name).label }}
                                                        </v-chip>
                                                        <v-menu v-if="isAdmin" transition="slide-y-transition">
                                                            <template v-slot:activator="{ props }">
                                                                <v-btn icon="mdi-dots-vertical" variant="tonal" color="primary" size="small" v-bind="props" class="rounded-lg"></v-btn>
                                                            </template>
                                                            <v-list density="compact" class="rounded-lg">
                                                                <v-list-item prepend-icon="mdi-pencil-outline" title="Edit Event" @click="editEvent(event)" class="py-2"></v-list-item>
                                                                <v-divider></v-divider>
                                                                <v-list-item prepend-icon="mdi-delete-outline" title="Delete Event" base-color="error" @click="deleteHoliday(event.id)" class="py-2"></v-list-item>
                                                            </v-list>
                                                        </v-menu>
                                                    </div>
                                                </template>
                                            </v-list-item>
                                        </v-list>
                                        
                                        <div v-if="monthFilteredHolidays.length > 50" class="text-center py-4 text-caption grey--text font-italic">
                                            <v-icon size="14" class="mr-1">mdi-information-outline</v-icon>
                                            Showing first 50 results.
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </div>
                        </v-window-item>

                        <v-window-item value="timeline">
                            <div class="timeline-container pr-4">
                                <div v-for="group in timelineGroups" :key="group.month" class="mb-8">
                                    <h2 class="text-h5 font-weight-bold mb-4 primary--text">{{ group.month }}</h2>
                                    <v-row>
                                        <v-col v-for="event in group.events" :key="event.id" cols="12" sm="6" lg="4">
                                            <v-card class="event-card pa-4 rounded-lg elevation-2 border-left-thick" :style="{ borderColor: getStatusColor(event.name) }">
                                                <div class="d-flex justify-space-between align-start">
                                                    <div>
                                                        <div class="text-h6 font-weight-bold truncate-2" style="max-height: 3.6em; line-height: 1.2;">{{ event.name }}</div>
                                                        <div class="text-caption grey--text mt-1">
                                                            <v-icon size="14">mdi-clock-outline</v-icon>
                                                            {{ formatDate(event.date) }}
                                                        </div>
                                                    </div>
                                                    <v-menu v-if="isAdmin">
                                                        <template v-slot:activator="{ props }">
                                                            <v-btn icon="mdi-dots-vertical" variant="text" size="small" v-bind="props"></v-btn>
                                                        </template>
                                                        <v-list>
                                                            <v-list-item prepend-icon="mdi-pencil" title="Edit" @click="editEvent(event)"></v-list-item>
                                                            <v-list-item prepend-icon="mdi-delete" title="Delete" base-color="error" @click="deleteHoliday(event.id)"></v-list-item>
                                                        </v-list>
                                                    </v-menu>
                                                </div>
                                            </v-card>
                                        </v-col>
                                    </v-row>
                                </div>
                            </div>
                        </v-window-item>
                    </v-window>
                </v-card>
            </v-col>
        </v-row>

        <!-- Dialogs -->
        <v-dialog v-model="addHolidayDialog" max-width="500px">
            <v-card class="rounded-xl pa-4">
                <v-card-title class="text-h5 font-weight-bold">Create New Event</v-card-title>
                <v-card-text>
                    <v-text-field v-model="newHoliday.name" label="Event Name" variant="outlined" class="mb-4"></v-text-field>
                    <v-text-field v-model="newHoliday.date" label="Date" type="date" variant="outlined" class="mb-4"></v-text-field>
                    <v-select v-model="newHoliday.unit_id" :items="branches" item-title="name" item-value="id" label="Branch" variant="outlined"></v-select>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn variant="text" @click="addHolidayDialog = false">Cancel</v-btn>
                    <v-btn color="primary" rounded="pill" variant="elevated" @click="addHoliday">Save Event</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="editHolidayDialog" max-width="500px">
            <v-card class="rounded-xl pa-4">
                <v-card-title class="text-h5 font-weight-bold">Edit Event</v-card-title>
                <v-card-text v-if="editingHoliday">
                    <v-text-field v-model="editingHoliday.name" label="Event Name" variant="outlined" class="mb-4"></v-text-field>
                    <v-text-field v-model="editingHoliday.date" label="Date" type="date" variant="outlined" class="mb-4"></v-text-field>
                    <v-select v-model="editingHoliday.unit_id" :items="branches" item-title="name" item-value="id" label="Branch" variant="outlined"></v-select>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn variant="text" @click="editHolidayDialog = false">Cancel</v-btn>
                    <v-btn color="primary" rounded="pill" variant="elevated" @click="updateHoliday">Update</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<style scoped>
.calendar-app-container {
    min-height: 100vh;
    background-color: #f8fafc;
    position: relative;
}

.sidebar-col {
    background-color: #ffffff;
    border-right: 1px solid #e2e8f0;
    z-index: 10;
}

.main-content-col {
    background: radial-gradient(circle at top right, #e2e8f0 0%, #f8fafc 100%);
    overflow-y: visible;
}

.glass-morphism {
    background: rgba(255, 255, 255, 0.7) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-card {
    transition: transform 0.2s;
}
.stat-card:hover {
    transform: translateY(-2px);
}

.premium-vcalendar {
    width: 100% !important;
    max-width: 900px;
    background: transparent !important;
}

.timeline-container {
    max-height: 70vh;
    overflow-y: auto;
}

.border-left-thick {
    border-left: 6px solid transparent;
}

.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.event-card {
    transition: all 0.2s;
}
.event-card:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.event-list-item {
    background: white !important;
    transition: all 0.2s ease;
    border: 1px solid #f1f5f9;
}

.event-list-item:hover {
    background: #f8fafc !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;
}

.date-badge {
    min-width: 50px;
    background: #eff6ff;
    padding: 8px;
    border-radius: 12px;
    line-height: 1;
}

.w-100 {
    width: 100% !important;
}

.scrollable-events-area {
    max-height: 600px;
    overflow-y: auto;
    padding-bottom: 40px !important;
}

.bg-light-primary {
    background-color: rgba(var(--v-theme-primary), 0.05) !important;
}

.border-primary-light {
    border: 1px solid rgba(var(--v-theme-primary), 0.1) !important;
}

.date-badge-premium {
    min-width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.line-height-1 {
    line-height: 1 !important;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>