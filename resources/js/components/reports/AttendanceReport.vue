<template>
  <v-container fluid class="pa-6">

    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h2 class="text-h5 font-weight-black">Attendance Report</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Filter, analyse and export attendance records</p>
      </div>
      <v-btn v-if="activeTab === 'monthly' && monthly.report.length" color="success" prepend-icon="mdi-file-excel" variant="tonal" @click="exportMonthlyExcel">
        Export Excel
      </v-btn>
    </div>

    <!-- Tab switcher -->
    <v-tabs v-model="activeTab" color="primary" class="mb-5" density="compact">
      <v-tab value="daily"   prepend-icon="mdi-calendar-today">Daily Report</v-tab>
      <v-tab value="monthly" prepend-icon="mdi-table-large">Monthly Summary</v-tab>
    </v-tabs>

    <v-tabs-window v-model="activeTab">

      <!-- ══════════════════ DAILY TAB ══════════════════ -->
      <v-tabs-window-item value="daily">

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
                <v-btn color="primary" :loading="daily.loading" prepend-icon="mdi-magnify" @click="fetchDaily" block>Search</v-btn>
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

        <v-card rounded="xl" elevation="2">
          <v-data-table :headers="dailyHeaders" :items="filteredDailyRows" :loading="daily.loading"
            :items-per-page="25" :items-per-page-options="[10, 25, 50, 100]" class="rounded-xl" hover>
            <template v-slot:loading><v-skeleton-loader type="table-row@10" /></template>
            <template v-slot:no-data>
              <div class="text-center pa-10">
                <v-icon size="56" color="grey-lighten-2" class="mb-3">mdi-calendar-search</v-icon>
                <div class="text-subtitle-1 text-medium-emphasis">No records found</div>
                <div class="text-body-2 text-disabled">Select a date and click Search</div>
              </div>
            </template>
            <template v-slot:[`item.clock_in`]="{ item }"><span class="font-mono">{{ item.clock_in || '—' }}</span></template>
            <template v-slot:[`item.clock_out`]="{ item }"><span class="font-mono">{{ item.clock_out || '—' }}</span></template>
            <template v-slot:[`item.duration`]="{ item }"><span class="text-medium-emphasis">{{ item.duration || '—' }}</span></template>
            <template v-slot:[`item.status`]="{ item }">
              <v-chip :color="dailyStatusColor(item.status)" size="x-small" variant="flat" class="font-weight-bold">{{ item.status }}</v-chip>
            </template>
            <template v-slot:[`item.leave_type`]="{ item }">
              <span v-if="item.leave_type" class="text-caption text-medium-emphasis">{{ item.leave_type }}</span>
              <span v-else class="text-disabled">—</span>
            </template>
          </v-data-table>
        </v-card>
      </v-tabs-window-item>

      <!-- ══════════════════ MONTHLY SUMMARY TAB ══════════════════ -->
      <v-tabs-window-item value="monthly">

        <!-- Filters -->
        <v-card rounded="xl" elevation="2" class="mb-6">
          <v-card-text class="pa-6">
            <v-row dense align="center">
              <v-col cols="6" sm="3" md="2">
                <v-select v-model="monthly.month"
                  :items="monthOptions" item-title="label" item-value="value"
                  label="Month" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="6" sm="3" md="2">
                <v-select v-model="monthly.year"
                  :items="yearOptions"
                  label="Year" variant="outlined" density="compact" hide-details />
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
              <v-col cols="12" sm="6" md="2" class="d-flex align-center">
                <v-btn color="primary" :loading="monthly.loading" prepend-icon="mdi-magnify" @click="fetchMonthly" block>Generate</v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Report heading -->
        <div v-if="monthly.report.length" class="mb-4">
          <div class="text-h6 font-weight-black text-center text-uppercase">Boxleo Courier and Fulfilment Services</div>
          <div class="text-subtitle-2 font-weight-bold text-center text-medium-emphasis">{{ monthly.monthLabel }} Attendance Report</div>

          <!-- Legend -->
          <div class="d-flex flex-wrap gap-2 justify-center mt-2">
            <v-chip v-for="leg in legend" :key="leg.label" size="x-small" variant="tonal" :color="leg.color">
              {{ leg.symbol }} {{ leg.label }}
            </v-chip>
          </div>
        </div>

        <!-- Matrix table -->
        <v-card v-if="monthly.report.length || monthly.loading" rounded="xl" elevation="2" class="overflow-x-auto">
          <v-progress-linear v-if="monthly.loading" indeterminate color="primary" />
          <table class="summary-table" v-if="!monthly.loading && monthly.report.length">
            <thead>
              <tr class="header-row">
                <th class="sticky-col col-num">#</th>
                <th class="sticky-col col-name">Employee Name</th>
                <th class="col-num text-success">Present ✓</th>
                <th v-for="lt in monthly.leaveTypes" :key="lt.id" class="col-num">{{ leaveShortName(lt.name) }}</th>
                <th class="col-num text-orange">In Field £</th>
                <th class="col-num text-error">Late Φ</th>
                <th class="col-num text-warning">Absent ×</th>
                <th class="col-num text-info">Leave Bal.</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, i) in monthly.report" :key="row.id" :class="i % 2 === 0 ? 'row-even' : 'row-odd'">
                <td class="sticky-col col-num text-medium-emphasis">{{ i + 1 }}</td>
                <td class="sticky-col col-name font-weight-medium">{{ row.name }}</td>
                <td class="col-num">
                  <span :class="row.present > 0 ? 'text-success font-weight-bold' : 'text-disabled'">{{ row.present || 0 }}</span>
                </td>
                <td v-for="lt in monthly.leaveTypes" :key="lt.id" class="col-num">
                  <span v-if="row['lt_' + lt.id] > 0" class="text-primary font-weight-bold">{{ row['lt_' + lt.id] }}</span>
                  <span v-else class="text-disabled">0</span>
                </td>
                <td class="col-num">
                  <span v-if="row.in_field > 0" class="text-orange font-weight-bold">{{ row.in_field }}</span>
                  <span v-else class="text-disabled">0</span>
                </td>
                <td class="col-num">
                  <span v-if="row.late > 0" class="text-error font-weight-bold">{{ row.late }}</span>
                  <span v-else class="text-disabled">0</span>
                </td>
                <td class="col-num">
                  <span v-if="row.absent > 0" class="text-warning font-weight-bold">{{ row.absent }}</span>
                  <span v-else class="text-disabled">0</span>
                </td>
                <td class="col-num">
                  <span class="text-info font-weight-bold">{{ row.leave_balance }}</span>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="!monthly.loading && !monthly.report.length" class="text-center pa-10">
            <v-icon size="56" color="grey-lighten-2" class="mb-3">mdi-table-off</v-icon>
            <div class="text-subtitle-1 text-medium-emphasis">No data yet</div>
            <div class="text-body-2 text-disabled">Select a month and click Generate</div>
          </div>
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
    const today = new Date();
    return {
      activeTab: 'daily',
      employees: [],
      branches: [],
      departments: [],

      daily: {
        loading: false,
        date: today.toISOString().slice(0, 10),
        unit_id: null,
        department_id: null,
        employee: null,
        statusFilter: null,
        report: [],
        statuses: { total: 0, in_time: 0, late: 0, on_leave: 0, absent: 0 },
      },

      monthly: {
        loading: false,
        year:  today.getFullYear(),
        month: today.getMonth() + 1,
        unit_id: null,
        department_id: null,
        report: [],
        leaveTypes: [],
        monthLabel: '',
      },

      monthOptions: [
        { label: 'January',   value: 1  }, { label: 'February',  value: 2  },
        { label: 'March',     value: 3  }, { label: 'April',     value: 4  },
        { label: 'May',       value: 5  }, { label: 'June',      value: 6  },
        { label: 'July',      value: 7  }, { label: 'August',    value: 8  },
        { label: 'September', value: 9  }, { label: 'October',   value: 10 },
        { label: 'November',  value: 11 }, { label: 'December',  value: 12 },
      ],

      legend: [
        { symbol: '✓', label: 'Present',        color: 'success'  },
        { symbol: '⊙', label: 'Annual Leave',   color: 'primary'  },
        { symbol: 'M', label: 'Maternity',      color: 'pink'     },
        { symbol: 'P', label: 'Paternity',      color: 'blue'     },
        { symbol: '⊕', label: 'Sick',           color: 'warning'  },
        { symbol: '£', label: 'In Field',       color: 'orange'   },
        { symbol: 'Φ', label: 'Late',           color: 'error'    },
        { symbol: '×', label: 'Absent',         color: 'red'      },
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
    };
  },

  computed: {
    yearOptions() {
      const y = new Date().getFullYear();
      return [y - 2, y - 1, y, y + 1];
    },
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
      const att = s.total ? Math.round(((s.in_time + s.late + s.on_leave) / s.total) * 100) : 0;
      return [
        { label: 'Total',       value: s.total,       color: 'text-primary' },
        { label: 'On Time',     value: s.in_time,     color: 'text-success' },
        { label: 'Late',        value: s.late,        color: 'text-error'   },
        { label: 'On Leave',    value: s.on_leave,    color: 'text-info'    },
        { label: 'Absent',      value: s.absent,      color: 'text-warning' },
        { label: 'Attendance%', value: att + '%',     color: att >= 80 ? 'text-success' : 'text-error' },
      ];
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
      axios.get('/api/v1/branches').then(res => { this.branches = res.data.branches ?? res.data ?? []; });
    },
    fetchDepartments() {
      axios.get('/api/v1/departments').then(res => { this.departments = res.data.departments ?? res.data ?? []; });
    },

    fetchDaily() {
      this.daily.loading = true;
      axios.post('/api/v1/attendance-report/daily', {
        date: this.daily.date, unit_id: this.daily.unit_id,
        department_id: this.daily.department_id, employee: this.daily.employee,
      })
        .then(res => { this.daily.report = res.data.report; this.daily.statuses = res.data.statuses; })
        .catch(err => console.error(err))
        .finally(() => { this.daily.loading = false; });
    },

    fetchMonthly() {
      this.monthly.loading = true;
      axios.post('/api/v1/attendance-report/monthly-summary', {
        year: this.monthly.year, month: this.monthly.month,
        unit_id: this.monthly.unit_id, department_id: this.monthly.department_id,
      })
        .then(res => {
          const report     = res.data.report;
          const leaveTypes = res.data.leave_types;

          // Sort leave types by total usage across all employees — most data on the left
          const totals = {};
          leaveTypes.forEach(lt => {
            totals[lt.id] = report.reduce((sum, row) => sum + (row['lt_' + lt.id] || 0), 0);
          });
          leaveTypes.sort((a, b) => totals[b.id] - totals[a.id]);

          this.monthly.report     = report;
          this.monthly.leaveTypes = leaveTypes;
          this.monthly.monthLabel = res.data.month_label;
        })
        .catch(err => console.error(err))
        .finally(() => { this.monthly.loading = false; });
    },

    exportMonthlyExcel() {
      if (!this.monthly.report.length) return;

      // Build CSV-style data and trigger download
      const leaveTypes = this.monthly.leaveTypes;
      const headers = [
        '#', 'Employee Name', 'Present ✓',
        ...leaveTypes.map(lt => this.leaveShortName(lt.name)),
        'In Field £', 'Late Φ', 'Absent ×', 'Leave Balance',
      ];

      const rows = this.monthly.report.map((row, i) => [
        i + 1, row.name, row.present,
        ...leaveTypes.map(lt => row['lt_' + lt.id] || 0),
        row.in_field, row.late, row.absent, row.leave_balance,
      ]);

      const csvContent = [headers, ...rows]
        .map(r => r.map(c => `"${c}"`).join(','))
        .join('\n');

      const blob = new Blob([csvContent], { type: 'text/csv' });
      const url  = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href  = url;
      link.download = `${this.monthly.monthLabel.replace(' ', '_')}_Attendance_Report.csv`;
      link.click();
      URL.revokeObjectURL(url);
    },

    leaveShortName(name) {
      const map = {
        'Annual Leave':          'Annual ⊙',
        'Maternity Leave':       'Maternity M',
        'Paternity Leave':       'Paternity P',
        'Sick Leave':            'Sick ⊕',
        'Compassionate Leave':   'Compassionate φ',
        'Study Leave':           'Study ∞',
        'Monthly Saturday Off':  'Sat Off +',
        'Sunday Compensation':   'Sun Comp $',
        'Holiday compensation':  'Hol Comp ☆',
        'Unpaid Leave':          'Unpaid',
        'Rest Day':              'Rest Day',
      };
      return map[name] ?? name;
    },

    dailyStatusColor(status) {
      if (status === 'In Time')  return 'success';
      if (status === 'Late')     return 'error';
      if (status === 'On Leave') return 'info';
      return 'warning';
    },
  },
};
</script>

<style scoped>
.font-mono { font-family: monospace; }

/* ── Matrix table ── */
.summary-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
}
.summary-table th, .summary-table td {
  padding: 7px 10px;
  white-space: nowrap;
  border-bottom: 1px solid rgba(0,0,0,0.06);
}
.header-row th {
  background: #1a3c4e;
  color: #fff;
  font-weight: 700;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  position: sticky;
  top: 0;
  z-index: 2;
}
.sticky-col {
  position: sticky;
  background: inherit;
  z-index: 1;
}
.col-num { min-width: 54px; text-align: center; }
.col-name { min-width: 180px; text-align: left; left: 40px; }
.col-num:first-child { left: 0; min-width: 40px; }

.row-even { background: #fff; }
.row-odd  { background: #f8fafc; }
.summary-table tbody tr:hover { background: #eef6fb !important; }

.text-orange { color: #e65100 !important; }
</style>
