<template>
  <div class="dashboard-container pa-4">
    <!-- Animated background layers -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>

    <v-card class="glass-card header-banner mb-6 animate-fade-in-up" style="animation-delay: 0.1s;">
      <div class="header-overlay"></div>
      <v-card-text class="pa-6">
        <v-row align="center">
          <v-col cols="12" md="8">
            <div class="d-flex align-center">
              <v-avatar size="64" class="mr-4 header-avatar">
                <v-img :src="`https://ui-avatars.com/api/?name=${user.firstname}+${user.lastname}&background=0D8ABC&color=fff`"></v-img>
              </v-avatar>
              <div>
                <h1 class="text-h4 font-weight-black text-white mb-1 shadow-text">Welcome Back, {{ user.firstname }}!</h1>
                <p class="text-subtitle-1 text-white opacity-80 mb-0 d-flex align-center">
                  <v-icon size="18" class="mr-2">mdi-clock-outline</v-icon>
                  {{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </p>
              </div>
            </div>
          </v-col>
          <v-col cols="12" md="4" class="text-md-right">
             <v-btn
              color="white"
              variant="elevated"
              class="premium-header-btn text-none font-weight-bold px-6"
              size="large"
              rounded="xl"
              prepend-icon="mdi-calendar-check"
              href="/holidays"
            >
              Smart Calendar
              <v-chip size="x-small" color="primary" class="ml-2 px-1">NEW</v-chip>
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <v-row class="compact-stats-row" no-gutters>
      <v-col cols="12" sm="6" md="3" v-for="(stat, index) in statistics" :key="index" class="pa-2">
        <v-card 
          class="glass-card premium-stat-card animate-fade-in-up" 
          :style="{ 'animation-delay': `${0.2 + index * 0.1}s` }"
          :href="stat.label.toLowerCase() === 'announcements' ? '/announcements' : undefined"
        >
          <div class="d-flex align-center pa-3">
            <div :class="`icon-glow-box bg-${getStatColor(index)}`" class="mr-3">
              <v-icon :icon="stat.icon" size="18" color="white"></v-icon>
            </div>
            <div class="flex-grow-1 overflow-hidden">
               <div class="stat-label-small text-uppercase">{{ stat.label }}</div>
               <div class="stat-value-compact">{{ stat.value }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-row class="mt-2" no-gutters>
      <v-col cols="12" sm="6" md="3" v-for="(status, index) in employeeStatus.slice(0, 4)" :key="'status-' + index" class="pa-2">
        <v-card class="glass-card compact-status-card animate-fade-in-up" :style="{ 'animation-delay': `${0.4 + index * 0.1}s` }">
          <div class="pa-3">
            <div class="d-flex justify-space-between align-center mb-1">
              <span class="text-caption font-weight-bold text-medium-emphasis">{{ status.label }}</span>
              <span class="text-caption font-weight-black" :class="`text-${getProgressColor(status.percentage)}`">{{ status.percentage }}%</span>
            </div>
            <v-progress-linear 
              :model-value="status.percentage"
              :color="getProgressColor(status.percentage)" 
              height="3"
              rounded
              class="mb-0"
            ></v-progress-linear>
            <div class="text-h6 font-weight-black mt-1">{{ status.value }}</div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" lg="8">
        <v-card class="glass-card chart-card mb-4 animate-fade-in-up" height="480" style="animation-delay: 0.6s;">
          <div class="pa-4 h-100">
            <AttendanceGraph />
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" lg="4">
        <v-card class="glass-card chart-card mb-4 animate-fade-in-up" height="480" style="animation-delay: 0.7s;">
          <v-progress-linear v-if="loading" color="primary" height="4" indeterminate rounded class="premium-progress loader-progress"></v-progress-linear>
          <div class="pa-4 h-100 d-flex flex-column">
            <div class="text-subtitle-1 font-weight-bold text-dark-emphasis mb-2">Today's Presence</div>
            <div class="flex-grow-1">
              <AttendancePieChart />
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" lg="6">
        <v-card class="glass-card list-card mb-4 animate-fade-in-up" style="animation-delay: 0.6s;">
          <v-card-title class="premium-card-title px-5 pt-5 pb-3 d-flex align-center">
            <v-icon color="primary" class="mr-2" size="20">mdi-clipboard-text-clock</v-icon>
            Recent Leaves
          </v-card-title>
          <v-card-text class="pa-0">
            <div class="px-5 py-2 bg-light-primary border-bottom-light">
              <v-row class="text-caption text-uppercase font-weight-bold text-primary">
                <v-col cols="6">Employee</v-col>
                <v-col cols="6" class="text-right">Status</v-col>
              </v-row>
            </div>
            <v-list class="bg-transparent pa-0">
              <v-list-item v-for="(item, index) in recentLeaves" :key="item.id" class="premium-list-item py-2 px-5">
                <v-row align="center" no-gutters>
                  <v-col cols="6" class="font-weight-medium text-body-2">
                    <div class="d-flex align-center">
                      <v-avatar color="primary" variant="tonal" size="24" class="mr-2 text-caption font-weight-bold">
                        {{ item.user.firstname.charAt(0) }}
                      </v-avatar>
                      {{ item.user.firstname }} {{ item.user.lastname }}
                    </div>
                  </v-col>
                  <v-col cols="6" class="text-right">
                    <v-chip :color="getStatusColor(item.status)" variant="flat" size="x-small" class="premium-chip font-weight-bold text-uppercase">
                      {{ item.status }}
                    </v-chip>
                  </v-col>
                </v-row>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" lg="6">
        <v-card class="glass-card calendar-card mb-4 animate-fade-in-up overflow-hidden" style="animation-delay: 0.7s;">
          <v-card-title class="d-flex justify-space-between align-center px-5 py-4 bg-light-primary border-bottom-light">
            <div class="d-flex align-center">
              <div class="icon-box-small bg-primary text-white mr-3">
                <v-icon size="16">mdi-calendar-star</v-icon>
              </div>
              <span class="text-subtitle-1 font-weight-bold text-primary">Upcoming Events</span>
            </div>
            <v-btn icon="mdi-open-in-new" variant="tonal" color="primary" size="x-small" href="/holidays" class="rounded-lg shadow-sm"></v-btn>
          </v-card-title>
          
          <div class="px-4 py-3 upcoming-events-wrapper">
            <v-list dense class="bg-transparent pa-0">
              <v-list-item v-for="(item, index) in upcomingEvents" :key="item.id" class="upcoming-event-item-compact mb-2 rounded-lg pa-2 shadow-sm">
                <template v-slot:prepend>
                  <div class="mini-date-badge-small mr-3 text-center">
                    <div class="text-xxs font-weight-bold text-primary text-uppercase">{{ new Date(item.date).toLocaleString('default', { month: 'short' }) }}</div>
                    <div class="text-subtitle-2 font-weight-black tracking-tight" style="line-height: 1;">{{ new Date(item.date).getDate() }}</div>
                  </div>
                </template>
                <v-list-item-title class="text-caption font-weight-bold text-dark-emphasis mb-0">
                  {{ item.name }}
                </v-list-item-title>
                <div class="d-flex align-center mt-0">
                  <span class="event-dot-small mr-1" :style="{ backgroundColor: getCategory(item.name).color }"></span>
                  <span class="text-xxs text-medium-emphasis">{{ getCategory(item.name).label }}</span>
                </div>
              </v-list-item>
            </v-list>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" lg="6">
        <v-card class="glass-card chart-card mb-4 animate-fade-in-up" style="animation-delay: 0.8s;">
          <DepartmentChart />
        </v-card>
      </v-col>
      <v-col cols="12" lg="6">
        <DailyCheerWidget />
      </v-col>
    </v-row>
  </div>
</template>

<script>
import DepartmentChart from '@/components/charts/DepartmentChart.vue';
import GenderChart from '@/components/charts/GenderChart.vue';
import AttendanceGraph from '@/components/charts/AttendanceGraph.vue';
import AttendancePieChart from '@/components/charts/AttendancePieChart.vue';
import DailyCheerWidget from '@/components/dashboards/DailyCheerWidget.vue';

export default {
  name: 'Admin Dashboard',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  components: {
    AttendanceGraph,
    DepartmentChart,
    GenderChart,
    AttendancePieChart,
    DailyCheerWidget
  },
  data() {
    return {
      base_url: '/',
      statistics: [],
      loading: false,
      employeeStatus: [],
      attendanceHeaders: [
        { title: '#', value: 'index' },
        { title: 'Date', value: 'clockin' },
        { title: 'Name', value: 'user' },
        { title: 'Status', value: 'status' },
      ],
      leaveHeaders: [
        { title: '#', value: 'index' },
        { title: 'Employee', value: 'user_id' },
        { title: 'Leave Type', value: 'leave_type_id' },
        { title: 'Status', value: 'status' },
      ],
      newEmployeeHeaders: [
        { title: '#', value: 'index' },
        { title: 'Employee', value: 'name' },
        { title: 'Department', value: 'department' },
        { title: 'Position', value: 'position' },
      ],
      recentAttendances: [],
      recentLeaves: [],
      newEmployees: [],
      holidays: [],
    };
  },
  mounted() {
    this.fetchStatistics();
    this.fetchLeaves();
    this.fetchUsers();
    this.fetchAttendaces();
    this.fetchHolidays();
  },
  methods: {
    getStatColor(index) {
      const colors = ['primary', 'success', 'warning', 'info', 'purple', 'error'];
      return colors[index % colors.length];
    },
    getStatusColor(status) {
      switch (status) {
        case 'Approved':
          return 'success';
        case 'Pending':
          return 'warning';
        case 'Cancelled':
          return 'error';
        default:
          return 'primary';
      }
    },
    statusColor(status) {
      switch (status) {
        case 'In Time':
          return 'success';
        case 'Late':
          return 'danger';
        case 'Absent':
          return 'error';
        default:
          return 'primary';
      }
    },
    fetchAttendaces() {
      this.loading = true;
      const uri = `/web-api/attendances`;
      axios.get(uri)
        .then(response => {
          this.recentAttendances = response.data.attendances.slice(0, 3);
        })
        .catch(error => {
          console.error('Error fetching attendances:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    },
    fetchLeaves() {
      const uri = `/web-api/leaves`;
      axios.get(uri)
        .then(response => {
          this.recentLeaves = response.data.leaves.slice(0, 3);
        })
        .catch(error => {
          console.error('Error fetching Leaves:', error);
        });
    },
    fetchUsers() {
      const uri = `/web-api/users`;
      axios.get(uri)
        .then(response => {
          this.newEmployees = response.data.users.slice(-3);
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },
    fetchStatistics() {
      const uri = `/web-api/dashboard-stats`;
      axios.get(uri)
        .then(response => {
          this.statistics = response.data.statistics || response.data;
          this.employeeStatus = response.data.employeeStatus || [];
          if (response.data.hr) { this.hr = response.data.hr; }
        })
        .catch(error => {
          console.error('Error fetching statistics:', error);
        });
    },
    getProgressColor(percentage) {
      if (percentage >= 80) {
        return 'success';
      } else if (percentage >= 50) {
        return 'warning';
      } else {
        return 'red';
      }
    },
    fetchHolidays() {
      const uri = `/web-api/holidays`;
      axios.get(uri)
        .then(response => {
          this.holidays = response.data.holidays || response.data || [];
        });
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
    },
    getEventColor(name) {
      const lower = name.toLowerCase();
      if (lower.includes('birthday')) return '#9C27B0';
      if (lower.includes('meeting')) return '#2196F3';
      if (lower.includes('training')) return '#3F51B5';
      if (lower.includes('holiday') || lower.includes('day')) return '#FF5252';
      return '#FF9800';
    },
    getCategory(name) {
      const lower = name.toLowerCase();
      if (lower.includes('birthday')) return { key: 'birthdays', label: 'Birthdays', color: '#9C27B0' };
      if (lower.includes('meeting')) return { key: 'meetings', label: 'Meetings', color: '#2196F3' };
      if (lower.includes('training')) return { key: 'training', label: 'Training', color: '#3F51B5' };
      if (lower.includes('holiday') || lower.includes('day')) return { key: 'holidays', label: 'Holidays', color: '#FF5252' };
      return { key: 'other', label: 'Other', color: '#FF9800' };
    }
  },
  computed: {
    calendarAttributes() {
      return this.holidays.map(h => ({
        key: h.id,
        highlight: {
          color: this.getEventColor(h.name),
          fillMode: 'light',
        },
        dates: new Date(h.date),
      }));
    },
    upcomingEvents() {
      const today = new Date();
      return this.holidays
        .filter(h => new Date(h.date) >= today)
        .sort((a, b) => new Date(a.date) - new Date(b.date))
        .slice(0, 3);
    }
  }
};
</script>

<style scoped>
/* Animated Background Elements */
.dashboard-container {
  position: relative;
  min-height: 100vh;
  z-index: 1;
}

.bg-shape {
  position: absolute;
  filter: blur(80px);
  z-index: -1;
  opacity: 0.5;
  border-radius: 50%;
  animation: float 20s infinite ease-in-out alternate;
}

.shape-1 {
  top: -10%;
  left: -5%;
  width: 400px;
  height: 400px;
  background: rgba(var(--v-theme-primary), 0.4);
  animation-delay: 0s;
}

.shape-2 {
  bottom: 20%;
  right: -5%;
  width: 500px;
  height: 500px;
  background: rgba(var(--v-theme-info), 0.3);
  animation-delay: -5s;
}

.shape-3 {
  top: 40%;
  left: 30%;
  width: 300px;
  height: 300px;
  background: rgba(var(--v-theme-purple), 0.2);
  animation-delay: -10s;
}

@keyframes float {
  0% { transform: translate(0, 0) rotate(0deg) scale(1); }
  33% { transform: translate(30px, -50px) rotate(10deg) scale(1.1); }
  66% { transform: translate(-20px, 20px) rotate(-5deg) scale(0.9); }
  100% { transform: translate(0, 0) rotate(0deg) scale(1); }
}

/* Glassmorphism Classes */
.glass-card {
  background: rgba(255, 255, 255, 0.75) !important;
  backdrop-filter: blur(12px) saturate(180%);
  -webkit-backdrop-filter: blur(12px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.6) !important;
  border-radius: 16px !important;
  box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07) !important;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  overflow: hidden;
}

.glass-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(31, 38, 135, 0.12) !important;
  background: rgba(255, 255, 255, 0.85) !important;
  border: 1px solid rgba(255, 255, 255, 0.8) !important;
}

.glass-btn {
  background: rgba(255, 255, 255, 0.9) !important;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.8);
  font-weight: 600;
  transition: all 0.2s;
}

.glass-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(var(--v-theme-primary), 0.3) !important;
}

/* Typography & Layout */
.premium-title {
  font-size: 1.75rem;
  font-weight: 800;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)), rgb(var(--v-theme-info)));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.5px;
}

.premium-subtitle {
  font-size: 1.1rem;
  font-weight: 500;
  color: rgba(var(--v-theme-on-surface), 0.7);
}

.premium-card-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: rgb(var(--v-theme-on-surface));
  letter-spacing: -0.2px;
}

/* Header Banner */
.header-banner {
  background: linear-gradient(135deg, #0D8ABC 0%, #61DAFB 100%) !important;
  position: relative;
  overflow: hidden;
  border: none !important;
}

.header-banner:hover {
  background: linear-gradient(135deg, #0D8ABC 0%, #61DAFB 100%) !important;
  transform: translateY(-2px);
  box-shadow: 0 12px 40px rgba(13, 138, 188, 0.25) !important;
}

.header-overlay {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background-image: radial-gradient(circle at 20% 150%, rgba(255,255,255,0.2) 0%, transparent 50%),
                    radial-gradient(circle at 80% -50%, rgba(255,255,255,0.1) 0%, transparent 50%);
  pointer-events: none;
}

.header-avatar {
  border: 4px solid rgba(255,255,255,0.2);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.shadow-text {
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.opacity-80 {
  opacity: 0.8;
}

.premium-header-btn {
  box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
  transition: all 0.3s ease;
  color: #0D8ABC !important;
}

.premium-header-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

/* Premium Compact Stat Cards */
.compact-stats-row {
  margin: -8px;
}

.premium-stat-card {
  height: 72px;
  background: rgba(255, 255, 255, 0.8) !important;
  border: 1px solid rgba(255, 255, 255, 0.5) !important;
  box-shadow: 0 4px 15px rgba(0,0,0,0.03) !important;
}

.icon-glow-box {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 16px -4px currentColor;
  position: relative;
}

.icon-glow-box::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: inherit;
  background: inherit;
  filter: blur(8px);
  opacity: 0.4;
  z-index: -1;
}

.stat-label-small {
  font-size: 0.65rem;
  font-weight: 800;
  color: #64748b;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}

.stat-value-compact {
  font-size: 1.4rem;
  font-weight: 900;
  color: #1e293b;
  line-height: 1;
}

.compact-status-card {
  background: rgba(255, 255, 255, 0.85) !important;
  border-radius: 12px !important;
}

.compact-status-card .v-progress-linear {
  border-radius: 10px;
  background-color: rgba(0,0,0,0.04);
}

/* Stat Cards */
.stat-card-inner {
  padding: 20px;
}
.stat-avatar {
  transition: transform 0.3s ease;
}
.glass-card:hover .stat-avatar {
  transform: scale(1.1) rotate(5deg);
}

/* Progress Bars */
.premium-progress {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}
.loader-progress {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  opacity: 0.7;
}

/* Lists and Tables */
.premium-list-item {
  border-bottom: 1px solid rgba(0,0,0,0.03);
  transition: background-color 0.2s;
}
.premium-list-item:last-child {
  border-bottom: none;
}
.premium-list-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.03);
}

.premium-chip {
  font-weight: 700 !important;
  letter-spacing: 0.5px;
  font-size: 0.7rem !important;
}

/* Calendar & Events */
.icon-box {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)), rgb(var(--v-theme-info)));
  box-shadow: 0 4px 10px rgba(var(--v-theme-primary), 0.3);
}

.bg-light-primary {
  background-color: rgba(var(--v-theme-primary), 0.04) !important;
}

.border-bottom-light {
  border-bottom: 1px solid rgba(0,0,0,0.05);
}
.border-top-light {
  border-top: 1px solid rgba(0,0,0,0.05);
}

.calendar-wrapper {
  position: relative;
}
.calendar-wrapper::after {
  content: '';
  position: absolute;
  top: 10%;
  right: 10%;
  width: 120px;
  height: 120px;
  background: radial-gradient(circle, rgba(var(--v-theme-primary), 0.1) 0%, transparent 70%);
  z-index: 0;
  filter: blur(10px);
}
.custom-mini-calendar {
  position: relative;
  z-index: 1;
}
.custom-mini-calendar :deep(.vc-pane-container) {
  background: transparent !important;
  border: none;
}
.custom-mini-calendar :deep(.vc-header) {
  margin-bottom: 12px;
}
.custom-mini-calendar :deep(.vc-title) {
  font-size: 1rem;
  font-weight: 800;
  color: rgb(var(--v-theme-primary));
}
.custom-mini-calendar :deep(.vc-weekday) {
  color: rgba(var(--v-theme-on-surface), 0.5);
  font-weight: 600;
  font-size: 0.75rem;
}
.custom-mini-calendar :deep(.vc-day-content:hover) {
  background-color: rgba(var(--v-theme-primary), 0.1);
}

.mini-date-badge {
  min-width: 48px;
  height: 52px;
  background: linear-gradient(to bottom, #ffffff, #f8fafc);
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  border: 1px solid rgba(0,0,0,0.06);
  box-shadow: 0 4px 10px rgba(0,0,0,0.04);
}

.upcoming-event-item {
  background: rgba(255, 255, 255, 0.6) !important;
  border: 1px solid rgba(255, 255, 255, 0.8);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  position: relative;
  overflow: hidden;
}

.upcoming-event-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: rgba(var(--v-theme-primary), 0.5);
  border-radius: 3px 0 0 3px;
  opacity: 0;
  transition: opacity 0.3s;
}

.upcoming-event-item:hover {
  transform: translateX(6px);
  background: rgba(255, 255, 255, 0.9) !important;
  box-shadow: 0 6px 15px rgba(0,0,0,0.05) !important;
}

.upcoming-event-item:hover::before {
  opacity: 1;
}

.event-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.tracking-wide {
  letter-spacing: 1px;
}
.tracking-tight {
  letter-spacing: -0.5px;
}
.text-xxs {
  font-size: 0.65rem !important;
}
.text-xxs {
  font-size: 0.65rem !important;
}
.truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}

.bg-light-slate {
  background-color: rgba(248, 250, 252, 0.7);
}

.icon-box-small {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)), rgb(var(--v-theme-info)));
}

.mini-date-badge-small {
  min-width: 36px;
  height: 40px;
  background: white;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  border: 1px solid rgba(0,0,0,0.05);
}

.upcoming-event-item-compact {
  background: rgba(255, 255, 255, 0.5) !important;
  border: 1px solid rgba(255, 255, 255, 0.8);
  transition: all 0.2s ease;
}

.upcoming-event-item-compact:hover {
  background: white !important;
  transform: translateX(4px);
}

.event-dot-small {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}

/* Animations */
.animate-fade-in-up {
  opacity: 0;
  animation: fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Scrollbar styling for a premium feel */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-track {
  background: rgba(0,0,0,0.02);
  border-radius: 4px;
}
::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.15);
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(0,0,0,0.25);
}
</style>
