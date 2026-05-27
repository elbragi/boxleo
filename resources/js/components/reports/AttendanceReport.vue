<template>
  <v-container fluid class="pa-6">

    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h2 class="text-h5 font-weight-black">Attendance Report</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Filter, analyse and export attendance records</p>
      </div>
      <v-btn v-if="activeTab === 'monthly' && monthlyReport.length" color="success" prepend-icon="mdi-file-excel" variant="tonal" @click="generateExcel">
        Export Excel
      </v-btn>
    </div>

    <!-- Tab switcher -->
    <v-tabs v-model="activeTab" color="primary" class="mb-5" density="compact">
      <v-tab value="daily" prepend-icon="mdi-calendar-today">Daily Report</v-tab>
      <v-tab value="monthly" prepend-icon="mdi-calendar-month">Monthly Report</v-tab>
    </v-tabs>

    <v-tabs-window v-model="activeTab">

      <!-- ══════════════════ DAILY TAB ══════════════════ -->
      <v-tabs-window-item value="daily">

        <!-- Daily filters -->
        <v-card rounded="xl" elevation="2" class="mb-6">
          <v-card-text class="pa-6">
            <v-row dense align="center">
              <v-col cols="12" sm="6" md="3">
                <v-text-field v-model="daily.date" label="Date" type="date" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-autocomplete v-model="daily.unit_id" :items="branches" item-title="name" item-value="id"
                  label="Branch" variant="outlined" density="compact" clearable hide-details
                  prepend-inner-icon="mdi-office-building-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-autocomplete v-model="daily.department_id" :items="filteredDepartmentsDaily" item-title="name" item-value="id"
                  label="Department" variant="outlined" density="compact" clearable hide-details
                  prepend-inner-icon="mdi-briefcase-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-autocomplete v-model="daily.employee" :items="employees" item-title="fullname" item-value="id"
                  label="Employee" variant="outlined" density="compact" clearable hide-details
                  prepend-inner-icon="mdi-account-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-select v-model="daily.statusFilter"
                  :items="[{ title: 'All Statuses', value: null }, { title: 'In Time', value: 'In Time' }, { title: 'Late', value: 'Late' }, { title: 'On Leave', value: 'On Leave' }, { title: 'Absent', value: 'Absent' }]"
                  item-title="title" item-value="value"
                  label="Status" variant="outlined" density="compact" hide-details
                  prepend-inner-icon="mdi-filter-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3" class="d-flex align-center">
                <v-btn color="primary" :loading="daily.loading" prepend-icon="mdi-magnify" @click="fetchDaily" block>
                  Search
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Daily summary cards -->
        <v-row class="mb-6" dense>
          <v-col v-for="s in dailySummaryCards" :key="s.label" cols="6" sm="4" md="2">
            <v-card rounded="xl" elevation="1" class="text-center pa-3">
              <div class="text-h5 font-weight-black" :class="s.color">{{ s.value }}</div>
              <div class="text-caption text-medium-emphasis mt-1">{{ s.label }}</div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Daily table -->
        <v-card rounded="xl" elevation="2">
          <v-data-table
            :headers="dailyHeaders"
            :items="filteredDailyRows"
            :loading="daily.loading"
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
                <div class="text-body-2 text-disabled">Select a date and click Search</div>
              </div>
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
              <v-chip :color="dailyStatusColor(item.status)" size="x-small" variant="flat" class="font-weight-bold">
                {{ item.status }}
              </v-chip>
            </template>
            <template v-slot:[`item.leave_type`]="{ item }">
              <span v-if="item.leave_type" class="text-caption text-medium-emphasis">{{ item.leave_type }}</span>
              <span v-else class="text-disabled">—</span>
            </template>
          </v-data-table>
        </v-card>
      </v-tabs-window-item>

      <!-- ══════════════════ MONTHLY TAB ══════════════════ -->
      <v-tabs-window-item value="monthly">

        <!-- Monthly filters -->
        <v-card rounded="xl" elevation="2" class="mb-6">
          <v-card-text class="pa-6">
            <div class="d-flex flex-wrap gap-2 mb-4">
              <span class="text-caption text-medium-emphasis d-flex align-center mr-1">Quick range:</span>
              <v-chip v-for="preset in datePresets" :key="preset.label"
                size="small" variant="tonal" color="primary" class="cursor-pointer"
                :class="{ 'v-chip--selected': activePreset === preset.label }"
                @click="applyPreset(preset)">{{ preset.label }}</v-chip>
            </div>
            <v-row dense>
              <v-col cols="12" sm="6" md="3">
                <v-text-field v-model="monthly.start" label="Start Date" type="date" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-text-field v-model="monthly.end" label="End Date" type="date" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-autocomplete v-model="monthly.unit_id" :items="branches" item-title="name" item-value="id"
                  label="Branch" variant="outlined" density="compact" clearable hide-details
                  prepend-inner-icon="mdi-office-building-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-autocomplete v-model="monthly.department_id" :items="filteredDepartmentsMonthly" item-title="name" item-value="id"
                  label="Department" variant="outlined" density="compact" clearable hide-details
                  prepend-inner-icon="mdi-briefcase-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-autocomplete v-model="monthly.employee" :items="employees" item-title="fullname" item-value="id"
                  label="Employee" variant="outlined" density="compact" clearable hide-details
                  prepend-inner-icon="mdi-account-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3">
                <v-select v-model="monthly.attendanceStatus"
                  :items="[{ title: 'All Statuses', value: null }, { title: 'In Time', value: 'In Time' }, { title: 'Late', value: 'Late' }]"
                  item-title="title" item-value="value"
                  label="Status" variant="outlined" density="compact" hide-details
                  prepend-inner-icon="mdi-clock-outline" />
              </v-col>
              <v-col cols="12" sm="6" md="3" class="d-flex align-center">
                <v-btn color="primary" :loading="monthly.loading" prepend-icon="mdi-magnify" @click="fetchMonthly" block>
                  Search
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Monthly summary cards -->
        <v-row class="mb-6" dense>
          <v-col cols="6" sm="3">
            <v-card rounded="xl" elevation="1" class="text-center pa-4">
              <div class="text-h4 font-weight-black text-primary">{{ monthly.statuses.total }}</div>
              <div class="text-caption text-medium-emphasis mt-1">Total Records</div>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card rounded="xl" elevation="1" class="text-center pa-4">
              <div class="text-h4 font-weight-black text-success">{{ monthly.statuses.in_time }}</div>
              <div class="text-caption text-medium-emphasis mt-1">In Time</div>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card rounded="xl" elevation="1" class="text-center pa-4">
              <div class="text-h4 font-weight-black text-error">{{ monthly.statuses.late }}</div>
              <div class="text-caption text-medium-emphasis mt-1">Late</div>
            </v-card>
          </v-col>
          <v-col cols="6" sm="3">
            <v-card rounded="xl" elevation="1" class="text-center pa-4">
              <div class="text-h4 font-weight-black" :class="monthlyLateRate > 20 ? 'text-error' : 'text-warning'">
                {{ monthlyLateRate }}%
              </div>
              <div class="text-caption text-medium-emphasis mt-1">Late Rate</div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Monthly table -->
        <v-card rounded="xl" elevation="2">
          <v-data-table
            :headers="monthlyHeaders"
            :items="monthlyReport"
            :loading="monthly.loading"
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
              <v-chip :color="item.status === 'In Time' ? 'success' : 'error'"
                size="x-small" variant="flat" class="font-weight-bold">{{ item.status }}</v-chip>
            </template>
            <template v-slot:[`item.branch`]="{ item }">
              <span class="text-caption text-medium-emphasis">{{ item.branch || '—' }}</span>
            </template>
          </v-data-table>
        </v-card>

      </v-tabs-window-item>
    </v-tabs-window>

  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AttendanceReport',

  data() {
    const today = new Date().toISOString().slice(0, 10);
    return {
      activeTab: 'daily',
      employees: [],
      branches: [],
      departments: [],
      activePreset: 'This Month',

      daily: {
        loading: false,
        date: today,
        unit_id: null,
        department_id: null,
        employee: null,
        statusFilter: null,
        report: [],
        statuses: { total: 0, in_time: 0, late: 0, on_leave: 0, absent: 0 },
      },

      monthly: {
        loading: false,
        start: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10),
        end: today,
        unit_id: null,
        department_id: null,
        employee: null,
        attendanceStatus: null,
        statuses: { total: 0, in_time: 0, late: 0 },
      },
      monthlyReport: [],

      datePresets: [
        { label: 'Today',      start: 0,   end: 0 },
        { label: 'Yesterday',  start: -1,  end: -1 },
        { label: 'This Week',  start: -6,  end: 0 },
        { label: 'This Month', start: null, end: null, month: 'current' },
        { label: 'Last Month', start: null, end: null, month: 'last' },
      ],

      dailyHeaders: [
        { title: 'Employee',    value: 'name' },
        { title: 'Branch',      value: 'branch' },
        { title: 'Department',  value: 'department' },
        { title: 'Designation', value: 'designation' },
        { title: 'Clock In',    value: 'clock_in',   width: '100px' },
        { title: 'Clock Out',   value: 'clock_out',  width: '100px' },
        { title: 'Duration',    value: 'duration',   width: '100px' },
        { title: 'Status',      value: 'status',     width: '110px' },
        { title: 'Leave Type',  value: 'leave_type', width: '130px' },
      ],

      monthlyHeaders: [
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
    filteredDepartmentsDaily() {
      if (!this.daily.unit_id) return this.departments;
      return this.departments.filter(d => !d.unit_id || d.unit_id === this.daily.unit_id);
    },
    filteredDepartmentsMonthly() {
      if (!this.monthly.unit_id) return this.departments;
      return this.departments.filter(d => !d.unit_id || d.unit_id === this.monthly.unit_id);
    },
    filteredDailyRows() {
      if (!this.daily.statusFilter) return this.daily.report;
      return this.daily.report.filter(r => r.status === this.daily.statusFilter);
    },
    dailySummaryCards() {
      const s = this.daily.statuses;
      const attendance = s.total ? Math.round(((s.in_time + s.late + s.on_leave) / s.total) * 100) : 0;
      return [
        { label: 'Total',       value: s.total,          color: 'text-primary' },
        { label: 'On Time',     value: s.in_time,         color: 'text-success' },
        { label: 'Late',        value: s.late,            color: 'text-error' },
        { label: 'On Leave',    value: s.on_leave,        color: 'text-info' },
        { label: 'Absent',      value: s.absent,          color: 'text-warning' },
        { label: 'Attendance%', value: attendance + '%',  color: attendance >= 80 ? 'text-success' : 'text-error' },
      ];
    },
    monthlyLateRate() {
      if (!this.monthly.statuses.total) return 0;
      return Math.round((this.monthly.statuses.late / this.monthly.statuses.total) * 100);
    },
  },

  mounted() {
    this.fetchEmployees();
    this.fetchBranches();
    this.fetchDepartments();
    this.fetchDaily();
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

    fetchDaily() {
      this.daily.loading = true;
      axios.post('/api/v1/attendance-report/daily', {
        date:          this.daily.date,
        unit_id:       this.daily.unit_id,
        department_id: this.daily.department_id,
        employee:      this.daily.employee,
      })
        .then(res => {
          this.daily.report   = res.data.report;
          this.daily.statuses = res.data.statuses;
        })
        .catch(err => console.error('Daily report error:', err))
        .finally(() => { this.daily.loading = false; });
    },

    fetchMonthly() {
      this.monthly.loading = true;
      axios.post('/api/v1/attendance-report', {
        start:            this.monthly.start,
        end:              this.monthly.end,
        unit_id:          this.monthly.unit_id,
        department_id:    this.monthly.department_id,
        employee:         this.monthly.employee,
        attendanceStatus: this.monthly.attendanceStatus,
      })
        .then(res => {
          this.monthlyReport    = res.data.attendanceReport;
          this.monthly.statuses = res.data.attendanceReportStatuses;
        })
        .catch(err => console.error('Monthly report error:', err))
        .finally(() => { this.monthly.loading = false; });
    },

    applyPreset(preset) {
      this.activePreset = preset.label;
      const now = new Date();
      if (preset.month === 'current') {
        this.monthly.start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10);
        this.monthly.end   = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10);
      } else if (preset.month === 'last') {
        this.monthly.start = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().slice(0, 10);
        this.monthly.end   = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().slice(0, 10);
      } else {
        const s = new Date(now); s.setDate(now.getDate() + preset.start);
        const e = new Date(now); e.setDate(now.getDate() + preset.end);
        this.monthly.start = s.toISOString().slice(0, 10);
        this.monthly.end   = e.toISOString().slice(0, 10);
      }
      this.fetchMonthly();
    },

    generateExcel() {
      const today = new Date().toISOString().slice(0, 10);
      axios({
        url: '/api/v1/attendance-report/excel',
        method: 'POST',
        responseType: 'blob',
        data: { attendances: JSON.stringify(this.monthlyReport) },
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

    dailyStatusColor(status) {
      if (status === 'In Time')  return 'success';
      if (status === 'Late')     return 'error';
      if (status === 'On Leave') return 'info';
      return 'warning'; // Absent
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
