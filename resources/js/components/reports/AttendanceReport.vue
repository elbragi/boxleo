<template>
  <v-container fluid class="pa-6">

    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h2 class="text-h5 font-weight-black">Attendance Report</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Filter, analyse and export attendance records</p>
      </div>
      <v-btn v-if="report.length" color="success" prepend-icon="mdi-file-excel" variant="tonal" @click="generateExcel">
        Export Excel
      </v-btn>
    </div>

    <!-- Filter Card -->
    <v-card rounded="xl" elevation="2" class="mb-6">
      <v-card-text class="pa-6">

        <!-- Quick date presets -->
        <div class="d-flex flex-wrap gap-2 mb-4">
          <span class="text-caption text-medium-emphasis d-flex align-center mr-1">Quick range:</span>
          <v-chip
            v-for="preset in datePresets" :key="preset.label"
            size="small" variant="tonal" color="primary" class="cursor-pointer"
            :class="{ 'v-chip--selected': activePreset === preset.label }"
            @click="applyPreset(preset)"
          >{{ preset.label }}</v-chip>
        </div>

        <v-row dense>
          <v-col cols="12" sm="6" md="3">
            <v-text-field v-model="filters.start" label="Start Date" type="date" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-text-field v-model="filters.end" label="End Date" type="date" variant="outlined" density="compact" hide-details />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-autocomplete
              v-model="filters.unit_id" :items="branches" item-title="name" item-value="id"
              label="Branch" variant="outlined" density="compact" clearable hide-details
              prepend-inner-icon="mdi-office-building-outline"
            />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-autocomplete
              v-model="filters.department_id" :items="filteredDepartments" item-title="name" item-value="id"
              label="Department" variant="outlined" density="compact" clearable hide-details
              prepend-inner-icon="mdi-briefcase-outline"
            />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-autocomplete
              v-model="filters.employee" :items="employees" item-title="fullname" item-value="id"
              label="Employee" variant="outlined" density="compact" clearable hide-details
              prepend-inner-icon="mdi-account-outline"
            />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-select
              v-model="filters.attendanceStatus"
              :items="[{ title: 'All Statuses', value: null }, { title: 'In Time', value: 'In Time' }, { title: 'Late', value: 'Late' }]"
              item-title="title" item-value="value"
              label="Status" variant="outlined" density="compact" hide-details
              prepend-inner-icon="mdi-clock-outline"
            />
          </v-col>
          <v-col cols="12" sm="6" md="3" class="d-flex align-center gap-3">
            <v-btn color="primary" :loading="loading" prepend-icon="mdi-magnify" @click="fetchReport" block>
              Search
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Summary Cards -->
    <v-row class="mb-6" dense>
      <v-col cols="6" sm="3">
        <v-card rounded="xl" elevation="1" class="text-center pa-4">
          <div class="text-h4 font-weight-black text-primary">{{ statuses.total }}</div>
          <div class="text-caption text-medium-emphasis mt-1">Total Records</div>
        </v-card>
      </v-col>
      <v-col cols="6" sm="3">
        <v-card rounded="xl" elevation="1" class="text-center pa-4">
          <div class="text-h4 font-weight-black text-success">{{ statuses.in_time }}</div>
          <div class="text-caption text-medium-emphasis mt-1">In Time</div>
        </v-card>
      </v-col>
      <v-col cols="6" sm="3">
        <v-card rounded="xl" elevation="1" class="text-center pa-4">
          <div class="text-h4 font-weight-black text-error">{{ statuses.late }}</div>
          <div class="text-caption text-medium-emphasis mt-1">Late</div>
        </v-card>
      </v-col>
      <v-col cols="6" sm="3">
        <v-card rounded="xl" elevation="1" class="text-center pa-4">
          <div class="text-h4 font-weight-black" :class="lateRate > 20 ? 'text-error' : 'text-warning'">
            {{ lateRate }}%
          </div>
          <div class="text-caption text-medium-emphasis mt-1">Late Rate</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Table -->
    <v-card rounded="xl" elevation="2">
      <v-data-table
        :headers="tableHeaders"
        :items="report"
        :loading="loading"
        :items-per-page="25"
        :items-per-page-options="[10, 25, 50, 100]"
        class="rounded-xl"
        hover
      >
        <template v-slot:loading>
          <v-skeleton-loader type="table-row@10" />
        </template>

        <template v-slot:no-data>
          <div class="text-center pa-10">
            <v-icon size="56" color="grey-lighten-2" class="mb-3">mdi-calendar-search</v-icon>
            <div class="text-subtitle-1 text-medium-emphasis">No records found</div>
            <div class="text-body-2 text-disabled">Adjust your filters and search again</div>
          </div>
        </template>

        <template v-slot:[`item.attendance_date`]="{ item }">
          <span class="font-weight-medium">{{ formatDate(item.attendance_date) }}</span>
        </template>

        <template v-slot:[`item.clock_in`]="{ item }">
          <span class="font-mono">{{ item.clock_in || '—' }}</span>
        </template>

        <template v-slot:[`item.clock_out`]="{ item }">
          <span class="font-mono">{{ item.clock_out || '—' }}</span>
        </template>

        <template v-slot:[`item.duration`]="{ item }">
          <span class="text-medium-emphasis">{{ item.duration || '—' }}</span>
        </template>

        <template v-slot:[`item.status`]="{ item }">
          <v-chip
            :color="item.status === 'In Time' ? 'success' : 'error'"
            size="x-small" variant="flat" class="font-weight-bold"
          >{{ item.status }}</v-chip>
        </template>

        <template v-slot:[`item.branch`]="{ item }">
          <span class="text-caption text-medium-emphasis">{{ item.branch || '—' }}</span>
        </template>
      </v-data-table>
    </v-card>

  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AttendanceReport',

  data() {
    const today = new Date().toISOString().slice(0, 10);
    return {
      loading: false,
      employees: [],
      branches: [],
      departments: [],
      activePreset: 'Today',
      filters: {
        employee: null,
        attendanceStatus: null,
        unit_id: null,
        department_id: null,
        start: today,
        end: today,
      },
      statuses: { total: 0, in_time: 0, late: 0 },
      report: [],
      datePresets: [
        { label: 'Today',      start: 0,   end: 0 },
        { label: 'Yesterday',  start: -1,  end: -1 },
        { label: 'This Week',  start: -6,  end: 0 },
        { label: 'This Month', start: null, end: null, month: 'current' },
        { label: 'Last Month', start: null, end: null, month: 'last' },
      ],
      tableHeaders: [
        { title: 'Date',       value: 'attendance_date', width: '120px' },
        { title: 'Employee',   value: 'name' },
        { title: 'Branch',     value: 'branch' },
        { title: 'Department', value: 'department' },
        { title: 'Clock In',   value: 'clock_in',  width: '100px' },
        { title: 'Clock Out',  value: 'clock_out', width: '100px' },
        { title: 'Duration',   value: 'duration',  width: '100px' },
        { title: 'Status',     value: 'status',    width: '100px' },
      ],
    };
  },

  computed: {
    lateRate() {
      if (!this.statuses.total) return 0;
      return Math.round((this.statuses.late / this.statuses.total) * 100);
    },
    filteredDepartments() {
      if (!this.filters.unit_id) return this.departments;
      return this.departments.filter(d => !d.unit_id || d.unit_id === this.filters.unit_id);
    },
  },

  mounted() {
    this.fetchEmployees();
    this.fetchBranches();
    this.fetchDepartments();
    this.fetchReport(); // auto-load today
  },

  methods: {
    fetchEmployees() {
      axios.get('/api/v1/users').then(res => {
        const list = Array.isArray(res.data) ? res.data : (res.data.users ?? []);
        this.employees = list.map(u => ({ ...u, fullname: `${u.firstname} ${u.lastname}` }));
      });
    },

    fetchBranches() {
      axios.get('/api/v1/branches').then(res => {
        this.branches = res.data.branches ?? res.data ?? [];
      });
    },

    fetchDepartments() {
      axios.get('/api/v1/departments').then(res => {
        this.departments = res.data.departments ?? res.data ?? [];
      });
    },

    applyPreset(preset) {
      this.activePreset = preset.label;
      const now = new Date();
      if (preset.month === 'current') {
        this.filters.start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10);
        this.filters.end   = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10);
      } else if (preset.month === 'last') {
        this.filters.start = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().slice(0, 10);
        this.filters.end   = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().slice(0, 10);
      } else {
        const s = new Date(now); s.setDate(now.getDate() + preset.start);
        const e = new Date(now); e.setDate(now.getDate() + preset.end);
        this.filters.start = s.toISOString().slice(0, 10);
        this.filters.end   = e.toISOString().slice(0, 10);
      }
      this.fetchReport();
    },

    fetchReport() {
      this.loading = true;
      axios.post('/api/v1/attendance-report', this.filters)
        .then(res => {
          this.report   = res.data.attendanceReport;
          this.statuses = res.data.attendanceReportStatuses;
        })
        .catch(err => console.error('Attendance report error:', err))
        .finally(() => { this.loading = false; });
    },

    generateExcel() {
      const today = new Date().toISOString().slice(0, 10);
      axios({
        url: '/api/v1/attendance-report/excel',
        method: 'POST',
        responseType: 'blob',
        data: { attendances: JSON.stringify(this.report) },
      }).then(res => {
        const url  = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href  = url;
        link.setAttribute('download', `attendance_report_${today}.xlsx`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      });
    },

    formatDate(d) {
      if (!d) return '—';
      return new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    },
  },
};
</script>

<style scoped>
.font-mono { font-family: monospace; }
.cursor-pointer { cursor: pointer; }
.v-chip--selected { opacity: 1 !important; font-weight: 700; }
</style>
