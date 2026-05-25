<template>
  <div class="emp-dashboard pa-4 pa-md-6">

    <!-- Ambient background shapes -->
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>
    <div class="bg-orb orb-3"></div>

    <!-- ── Hero Banner ── -->
    <v-card class="hero-card mb-6 overflow-hidden animate-up" style="animation-delay:0.05s">
      <div class="hero-gradient"></div>
      <v-card-text class="pa-6 position-relative" style="z-index:1">
        <v-row align="center">
          <v-col cols="12" md="8">
            <div class="d-flex align-center gap-4">
              <v-avatar size="72" class="hero-avatar flex-shrink-0">
                <v-img :src="user.gender === 'Male' ? '/assets/img/male-avatar.svg' : '/assets/img/female-avatar.png'" cover />
              </v-avatar>
              <div>
                <div class="text-caption text-white opacity-70 font-weight-medium mb-1 text-uppercase tracking-wide">
                  {{ greeting }}, welcome back
                </div>
                <h1 class="text-h4 font-weight-black text-white mb-1">{{ user.firstname }} {{ user.lastname }}</h1>
                <div class="d-flex align-center gap-3 flex-wrap">
                  <v-chip size="small" color="white" variant="tonal" class="text-white font-weight-bold">
                    <v-icon start size="14">mdi-account-tie</v-icon>
                    {{ user.designation?.name }}
                  </v-chip>
                  <v-chip size="small" color="white" variant="tonal" class="text-white font-weight-bold">
                    <v-icon start size="14">mdi-domain</v-icon>
                    {{ user.department?.name }}
                  </v-chip>
                </div>
              </div>
            </div>
          </v-col>
          <v-col cols="12" md="4" class="d-flex justify-md-end gap-3 flex-wrap mt-3 mt-md-0">
            <v-btn color="white" variant="elevated" rounded="xl" prepend-icon="mdi-calendar-plus"
              class="text-primary font-weight-bold text-none shadow-glow"
              @click="applyLeaveModal = true">
              Apply Leave
            </v-btn>
            <v-btn color="white" variant="tonal" rounded="xl" prepend-icon="mdi-calendar-star"
              class="text-white font-weight-bold text-none" href="/holidays">
              Calendar
            </v-btn>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- ── Leave Stat Cards ── -->
    <v-row no-gutters class="mb-2">
      <v-col v-for="(s, i) in leaveStats" :key="i" cols="6" sm="3" class="pa-2">
        <v-card class="glass-card stat-card animate-up h-100" :style="`animation-delay:${0.1 + i * 0.07}s`">
          <div class="pa-4 d-flex align-center gap-3">
            <div class="icon-pill" :style="`background:${s.bg}`">
              <v-icon :icon="s.icon" size="20" color="white" />
            </div>
            <div>
              <div class="text-h5 font-weight-black" :class="s.color">{{ s.value }}</div>
              <div class="text-caption text-medium-emphasis font-weight-medium">{{ s.label }}</div>
            </div>
          </div>
          <div class="stat-bar" :style="`background:${s.bg};width:${s.pct}%`"></div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ── Manager team row (visible only for privileged roles) ── -->
    <v-row v-if="isManager" no-gutters class="mb-2">
      <v-col v-for="(t, i) in teamStats" :key="'t'+i" cols="6" sm="3" class="pa-2">
        <v-card class="glass-card stat-card animate-up h-100" :style="`animation-delay:${0.35 + i * 0.07}s`">
          <div class="pa-4 d-flex align-center gap-3">
            <div class="icon-pill" :style="`background:${t.bg}`">
              <v-icon :icon="t.icon" size="20" color="white" />
            </div>
            <div>
              <div class="text-h5 font-weight-black" :class="t.color">{{ t.value }}</div>
              <div class="text-caption text-medium-emphasis font-weight-medium">{{ t.label }}</div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- ── Daily Cheer + Leave Balances ── -->
    <v-row no-gutters class="mb-4">
      <v-col cols="12" md="7" class="pa-2">
        <daily-cheer-widget class="animate-up h-100" style="animation-delay:0.42s" />
      </v-col>
      <v-col cols="12" md="5" class="pa-2">
        <v-card class="glass-card animate-up h-100" style="animation-delay:0.46s">
          <div class="d-flex align-center gap-2 px-5 pt-4 pb-2">
            <div class="icon-pill" style="background:linear-gradient(135deg,#00b894,#55efc4);width:32px;height:32px">
              <v-icon icon="mdi-calendar-heart" size="16" color="white" />
            </div>
            <span class="text-subtitle-1 font-weight-black">Leave Balances</span>
          </div>
          <v-card-text class="pa-4 pt-2 leave-scroll">
            <div v-if="!leaveBalances.length" class="text-caption text-disabled text-center py-4">
              <v-icon size="32" color="grey-lighten-2">mdi-calendar-blank-outline</v-icon>
              <div class="mt-1">No leave balances found</div>
            </div>
            <div v-for="lb in leaveBalances" :key="lb.id" class="lb-row mb-4">
              <div class="d-flex justify-space-between align-center mb-1">
                <span class="text-body-2 font-weight-bold">{{ lb.leave_type?.name }}</span>
                <v-chip size="x-small" variant="tonal"
                  :color="lb.balance > 0 ? 'success' : 'error'"
                  class="font-weight-black">
                  {{ lb.balance }} day{{ lb.balance !== 1 ? 's' : '' }} left
                </v-chip>
              </div>
              <v-progress-linear
                :model-value="lb.allocated ? Math.min(Math.round((lb.balance / lb.allocated) * 100), 100) : 0"
                :color="lb.balance / lb.allocated > 0.5 ? 'success' : lb.balance > 0 ? 'warning' : 'error'"
                rounded height="5" bg-color="rgba(0,0,0,0.06)"
              />
              <div class="d-flex justify-space-between mt-1">
                <span class="text-xxs text-disabled">Allocated: {{ lb.allocated }}</span>
                <span class="text-xxs text-disabled">Taken: {{ lb.taken }}</span>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- ── Main grid: Profile + Calendar ── -->
    <v-row class="mt-0">

      <!-- Profile card -->
      <v-col cols="12" md="5" lg="4">
        <v-card class="glass-card animate-up h-100" style="animation-delay:0.25s">
          <div class="profile-header-strip"></div>
          <v-card-text class="pa-6">
            <div class="d-flex align-center gap-4 mb-4">
              <v-avatar size="80" class="profile-avatar">
                <v-img :src="user.gender === 'Male' ? '/assets/img/male-avatar.svg' : '/assets/img/female-avatar.png'" cover />
              </v-avatar>
              <div>
                <div class="text-h6 font-weight-black">{{ user.firstname }} {{ user.lastname }}</div>
                <div class="text-body-2 text-medium-emphasis">{{ user.designation?.name }}</div>
                <v-chip size="x-small" color="success" variant="flat" class="mt-1 font-weight-bold">Active</v-chip>
              </div>
            </div>

            <v-divider class="mb-4" />

            <div v-for="row in profileRows" :key="row.label" class="info-row">
              <v-icon :icon="row.icon" size="18" :color="row.color" class="flex-shrink-0" />
              <div>
                <div class="text-caption text-medium-emphasis">{{ row.label }}</div>
                <div class="text-body-2 font-weight-medium">{{ row.value || '—' }}</div>
              </div>
            </div>

            <v-divider class="my-4" />

            <!-- HR Contact inline -->
            <div class="text-caption font-weight-black text-medium-emphasis mb-2 text-uppercase">HR Contact</div>
            <div class="d-flex align-center gap-3">
              <v-avatar size="36" color="primary">
                <v-icon color="white" size="18">mdi-account-tie</v-icon>
              </v-avatar>
              <div>
                <div class="text-body-2 font-weight-bold">{{ hr.firstname }} {{ hr.lastname }}</div>
                <div class="text-caption text-medium-emphasis">{{ hr.email }}</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Right column: Calendar + Quick access -->
      <v-col cols="12" md="7" lg="8">

        <!-- Smart Calendar card -->
        <v-card class="glass-card animate-up mb-4" style="animation-delay:0.3s">
          <div class="d-flex align-center justify-space-between px-5 pt-4 pb-2">
            <div class="d-flex align-center gap-2">
              <div class="icon-pill" style="background:linear-gradient(135deg,#6c5ce7,#a29bfe);width:32px;height:32px">
                <v-icon icon="mdi-calendar-star" size="16" color="white" />
              </div>
              <span class="text-subtitle-1 font-weight-black">Smart Calendar</span>
            </div>
            <v-btn icon="mdi-open-in-new" variant="tonal" color="primary" size="x-small" rounded href="/holidays" />
          </div>

          <v-row no-gutters>
            <v-col cols="12" sm="7" class="d-flex justify-center pa-3">
              <VCalendar borderless transparent mini :attributes="calendarAttributes" class="mini-cal" />
            </v-col>
            <v-col cols="12" sm="5" class="pa-4 border-s">
              <div class="text-caption font-weight-black text-medium-emphasis mb-3 text-uppercase">Upcoming</div>
              <div v-if="upcomingEvents.length === 0" class="text-caption text-disabled">No upcoming events</div>
              <div v-for="ev in upcomingEvents" :key="ev.id" class="upcoming-item mb-3">
                <div class="date-badge">
                  <div class="text-xxs font-weight-black text-primary">{{ fmtMonth(ev.date) }}</div>
                  <div class="text-subtitle-2 font-weight-black">{{ fmtDay(ev.date) }}</div>
                </div>
                <div class="text-caption font-weight-bold text-truncate">{{ ev.name }}</div>
              </div>
            </v-col>
          </v-row>
        </v-card>

        <!-- Quick access grid -->
        <v-card class="glass-card animate-up" style="animation-delay:0.35s">
          <div class="px-5 pt-4 pb-2">
            <span class="text-subtitle-1 font-weight-black">Quick Access</span>
          </div>
          <v-card-text class="pa-4 pt-2">
            <v-row dense>
              <v-col v-for="q in quickLinks" :key="q.label" cols="4" sm="3" md="2">
                <a :href="q.href || undefined" class="quick-tile" @click="q.action ? q.action() : null">
                  <div class="tile-icon" :style="`background:${q.bg}`">
                    <v-icon :icon="q.icon" size="22" color="white" />
                  </div>
                  <div class="text-caption font-weight-bold mt-2 text-center">{{ q.label }}</div>
                </a>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

      </v-col>
    </v-row>

    <!-- ── Apply Leave Dialog ── (unchanged logic) -->
    <v-dialog v-model="applyLeaveModal" max-width="800" persistent>
      <v-card rounded="xl">
        <v-toolbar color="primary" density="compact" rounded="0">
          <v-toolbar-title class="font-weight-black">Apply for Leave</v-toolbar-title>
          <v-spacer />
          <v-btn icon @click="applyLeaveModal = false"><v-icon>mdi-close</v-icon></v-btn>
        </v-toolbar>
        <v-card-text class="pt-4">
          <v-form ref="createLeaveForm">
            <v-row>
              <v-col cols="12" sm="6">
                <v-select v-model="newLeave.leave_type_id" :items="leaveTypes" label="Type of Leave"
                  item-value="id" item-title="name" variant="outlined" clearable
                  :rules="[v => !!v || 'Leave type is required']"
                  prepend-inner-icon="mdi-format-list-bulleted" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="newLeave.days" label="Number of Days" type="number" variant="outlined"
                  :rules="[v => !!v || 'Days are required', v => v > 0 || 'Must be > 0']"
                  prepend-inner-icon="mdi-numeric" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="newLeave.from" label="Start Date" type="date" variant="outlined"
                  :rules="[v => !!v || 'Start date is required']" prepend-inner-icon="mdi-calendar-start" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="newLeave.to" label="End Date" type="date" variant="outlined"
                  :rules="[v => !!v || 'End date is required']" prepend-inner-icon="mdi-calendar-end" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-select v-model="newLeave.manager" :items="managers" label="Line Manager"
                  item-value="id" item-title="fullname" variant="outlined" clearable
                  :rules="[v => !!v || 'Line manager is required']"
                  prepend-inner-icon="mdi-account-supervisor" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-select v-model="newLeave.hod" :items="hods" label="Head of Department"
                  item-value="id" item-title="fullname" variant="outlined" clearable
                  :rules="[v => !!v || 'HOD is required']" prepend-inner-icon="mdi-account-tie" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-text-field v-model="newLeave.phone" label="Contact Phone" placeholder="254..."
                  variant="outlined"
                  :rules="[v => !!v || 'Phone required', v => /^[+254]\d{9,12}$/.test(v) || 'Invalid phone']"
                  prepend-inner-icon="mdi-phone" />
              </v-col>
              <v-col cols="12" sm="6">
                <v-file-input v-model="newLeave.document" label="Supporting Document (Optional)"
                  accept=".pdf,.doc,.docx,.jpeg,.jpg,.png" variant="outlined"
                  prepend-icon="mdi-paperclip" clearable show-size />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="newLeave.comment" label="Additional Comments" variant="outlined"
                  counter clearable prepend-inner-icon="mdi-comment-text" auto-grow rows="3" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-divider />
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn color="error" variant="text" @click="applyLeaveModal = false" class="me-2">Cancel</v-btn>
          <v-btn color="primary" variant="elevated" :loading="isLoading" :disabled="isLoading"
            @click="submitNewLeave">Submit Application</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </div>
</template>

<script>
import DailyCheerWidget from './DailyCheerWidget.vue';

export default {
  name: 'EmployeeDashboard',
  components: { DailyCheerWidget },
  props: {
    user: { type: Object, required: true },
  },

  data() {
    return {
      applyLeaveModal: false,
      statistics: {
        teamMembers: 0, earlyAttendances: 0, lateAttendances: 0,
        annualLeaveDaysAssigned: 0, leaveBalance: 0, leaveTaken: 0,
        leaveRequests: 0, notifications: 0, totalNotifications: 0,
        teamMembersOnLeave: 0, totalAppliedLeaves: 0, totalApprovedLeaves: 0, recentLeave: '',
      },
      hr: { firstname: '', lastname: '', email: '', phone: '' },
      users: [], managers: [], hods: [], leaveTypes: [], holidays: [],
      newLeave: {
        leave_type_id: null, from: null, to: null, phone: null,
        days: null, manager: null, hod: null, document: null, comment: null,
      },
      isLoading: false,
      leaveBalances: [],
    };
  },

  computed: {
    greeting() {
      const h = new Date().getHours();
      if (h < 12) return 'Good morning';
      if (h < 17) return 'Good afternoon';
      return 'Good evening';
    },

    isManager() {
      return this.user.is_hod || this.user.is_hr || this.user.is_coo
          || this.user.is_finance_manager || this.user.is_cfo || this.user.super_admin;
    },

    leaveStats() {
      const total = this.statistics.annualLeaveDaysAssigned || 0;
      const taken = this.statistics.leaveTaken || 0;
      const bal   = this.statistics.leaveBalance ?? 0;
      const pend  = this.statistics.leaveRequests || 0;
      return [
        { label: 'Allocated', value: total, icon: 'mdi-calendar-check',  bg: 'linear-gradient(135deg,#0D8ABC,#00b4d8)', color: 'text-info',    pct: 100 },
        { label: 'Available', value: bal,   icon: 'mdi-calendar-heart',  bg: 'linear-gradient(135deg,#00b894,#55efc4)', color: 'text-success', pct: total ? Math.round((bal/total)*100) : 0 },
        { label: 'Taken',     value: taken, icon: 'mdi-calendar-minus',  bg: 'linear-gradient(135deg,#e17055,#fd79a8)', color: 'text-error',   pct: total ? Math.round((taken/total)*100) : 0 },
        { label: 'Pending',   value: pend,  icon: 'mdi-clock-outline',   bg: 'linear-gradient(135deg,#fdcb6e,#e17055)', color: 'text-warning', pct: 0 },
      ];
    },

    teamStats() {
      return [
        { label: 'Team Members',   value: this.statistics.teamMembers        || 0, icon: 'mdi-account-group',   bg: 'linear-gradient(135deg,#6c5ce7,#a29bfe)', color: 'text-purple' },
        { label: 'On Leave',       value: this.statistics.teamMembersOnLeave || 0, icon: 'mdi-beach',           bg: 'linear-gradient(135deg,#0D8ABC,#00b4d8)', color: 'text-info' },
        { label: 'Early Today',    value: this.statistics.earlyAttendances   || 0, icon: 'mdi-clock-check',     bg: 'linear-gradient(135deg,#00b894,#55efc4)', color: 'text-success' },
        { label: 'Late Today',     value: this.statistics.lateAttendances    || 0, icon: 'mdi-clock-alert',     bg: 'linear-gradient(135deg,#e17055,#fd79a8)', color: 'text-error' },
      ];
    },

    profileRows() {
      return [
        { label: 'Email',       value: this.user.email,              icon: 'mdi-email-outline',    color: 'blue' },
        { label: 'Phone',       value: this.user.phone,              icon: 'mdi-phone-outline',    color: 'green' },
        { label: 'Branch',      value: this.user.unit?.name,         icon: 'mdi-map-marker-outline', color: 'orange' },
        { label: 'Anniversary', value: this.user.work_anniversary,   icon: 'mdi-calendar-star',   color: 'purple' },
      ];
    },

    quickLinks() {
      const self = this;
      return [
        { label: 'Attendance', icon: 'mdi-calendar-clock',       bg: 'linear-gradient(135deg,#0D8ABC,#00b4d8)', href: '/attendance' },
        { label: 'Leaves',     icon: 'mdi-calendar-multiple',    bg: 'linear-gradient(135deg,#00b894,#55efc4)', action: () => { self.applyLeaveModal = true; } },
        { label: 'Payslips',   icon: 'mdi-cash-multiple',        bg: 'linear-gradient(135deg,#6c5ce7,#a29bfe)', href: '/payslips' },
        { label: 'Training',   icon: 'mdi-school-outline',       bg: 'linear-gradient(135deg,#fdcb6e,#e17055)', href: '/staff-development' },
        { label: 'Team',       icon: 'mdi-account-group-outline', bg: 'linear-gradient(135deg,#fd79a8,#e84393)', href: '/team' },
        { label: 'Policies',   icon: 'mdi-notebook-outline',     bg: 'linear-gradient(135deg,#636e72,#b2bec3)', href: '/policies' },
        { label: 'Calendar',   icon: 'mdi-calendar-multiselect', bg: 'linear-gradient(135deg,#e17055,#fd79a8)', href: '/holidays' },
        { label: 'Settings',   icon: 'mdi-cog-outline',          bg: 'linear-gradient(135deg,#2d3436,#636e72)', href: '/settings' },
      ];
    },

    calendarAttributes() {
      return this.holidays.map(h => ({
        key: h.id,
        highlight: { color: this.getEventColor(h.name), fillMode: 'light' },
        dates: new Date(h.date),
      }));
    },

    upcomingEvents() {
      const today = new Date();
      return this.holidays
        .filter(h => new Date(h.date) >= today)
        .sort((a, b) => new Date(a.date) - new Date(b.date))
        .slice(0, 4);
    },
  },

  mounted() {
    this.fetchDashboardStatistics();
    this.fetchUsers();
    this.fetchLeaveTypes();
    this.fetchHolidays();
    this.fetchLeaveBalances();
  },

  methods: {
    fetchUsers() {
      axios.get('/api/v1/users').then(r => {
        this.users    = r.data.users.map(u => ({ ...u, fullname: `${u.firstname} ${u.lastname}` }));
        this.hods     = this.users.filter(u => u.is_hod === 1);
        this.managers = this.users.filter(u => u.designation_id === 1);
      });
    },
    fetchLeaveTypes() {
      axios.get('/api/v1/leave-types').then(r => { this.leaveTypes = r.data.leaveTypes; });
    },
    fetchDashboardStatistics() {
      axios.get(`/api/v1/dashboard/${this.user.id}`).then(r => {
        this.statistics = r.data;
        this.hr = r.data.hr;
      });
    },
    fetchHolidays() {
      axios.get('/api/v1/holidays').then(r => {
        this.holidays = r.data.holidays || r.data || [];
      });
    },
    fetchLeaveBalances() {
      axios.get(`/api/v1/leave-balances?user_ids=${this.user.id}`).then(r => {
        this.leaveBalances = r.data.leaveBalances || [];
      });
    },
    async submitNewLeave() {
      const { valid } = await this.$refs.createLeaveForm.validate();
      if (!valid) return;
      const requested = parseFloat(this.newLeave.days);
      const available = parseFloat(this.statistics.leaveBalance);
      if (requested > available) {
        this.$toastr.warning(`Your available balance is only ${available} days.`, 'Insufficient Balance');
        return;
      }
      this.isLoading = true;
      const fd = new FormData();
      Object.entries({
        user_id: this.user.id, leave_type_id: this.newLeave.leave_type_id,
        from: this.newLeave.from, to: this.newLeave.to, phone: this.newLeave.phone,
        days: this.newLeave.days, manager: this.newLeave.manager, hod: this.newLeave.hod,
        comment: this.newLeave.comment,
      }).forEach(([k, v]) => fd.append(k, v));
      if (this.newLeave.document?.[0]) fd.append('document', this.newLeave.document[0]);
      axios.post('/api/v1/leaves', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        .then(r => { this.$toastr.success(r.data.message); this.applyLeaveModal = false; this.fetchDashboardStatistics(); })
        .catch(e => { this.$toastr.error(e.response?.data?.error || e.message); })
        .finally(() => { this.isLoading = false; });
    },
    fmtMonth: d => new Date(d).toLocaleString('default', { month: 'short' }),
    fmtDay:   d => new Date(d).getDate(),
    getEventColor(name) {
      const n = name.toLowerCase();
      if (n.includes('birthday')) return '#9C27B0';
      if (n.includes('meeting'))  return '#2196F3';
      if (n.includes('training')) return '#3F51B5';
      return '#FF5252';
    },
  },
};
</script>

<style scoped>
/* ── Layout ── */
.emp-dashboard { background: #f0f4f8; min-height: 100vh; position: relative; overflow: hidden; }

/* ── Ambient orbs ── */
.bg-orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
.orb-1 { width: 500px; height: 500px; background: rgba(13,138,188,.08); top: -150px; right: -100px; }
.orb-2 { width: 400px; height: 400px; background: rgba(108,92,231,.07); bottom: 0; left: -120px; }
.orb-3 { width: 300px; height: 300px; background: rgba(0,184,148,.06); top: 40%; right: 10%; }

/* ── Hero ── */
.hero-card { border-radius: 20px !important; border: none !important; box-shadow: 0 8px 32px rgba(13,138,188,.25) !important; }
.hero-gradient { position: absolute; inset: 0; background: linear-gradient(135deg,#0D8ABC 0%,#6c5ce7 60%,#00b894 100%); }
.hero-avatar { border: 3px solid rgba(255,255,255,.4); box-shadow: 0 4px 16px rgba(0,0,0,.2); }
.tracking-wide { letter-spacing: .08em; }

/* ── Glass cards ── */
.glass-card {
  background: rgba(255,255,255,.82) !important;
  backdrop-filter: blur(16px);
  border-radius: 16px !important;
  border: 1px solid rgba(255,255,255,.6) !important;
  box-shadow: 0 4px 24px rgba(0,0,0,.06) !important;
  transition: transform .25s, box-shadow .25s;
}
.glass-card:hover { transform: translateY(-3px); box-shadow: 0 8px 32px rgba(0,0,0,.1) !important; }

/* ── Stat cards ── */
.stat-card { position: relative; overflow: hidden; }
.stat-bar { position: absolute; bottom: 0; left: 0; height: 3px; border-radius: 0 2px 2px 0; opacity: .6; transition: width .8s ease; }

/* ── Icon pill ── */
.icon-pill {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0,0,0,.15);
}

/* ── Profile ── */
.profile-header-strip { height: 6px; background: linear-gradient(90deg,#0D8ABC,#6c5ce7,#00b894); }
.profile-avatar { border: 3px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,.1); }
.info-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }

/* ── Calendar ── */
.mini-cal { width: 100%; }
.upcoming-item { display: flex; align-items: center; gap: 10px; }
.date-badge {
  min-width: 38px; height: 38px; background: rgba(13,138,188,.08); border-radius: 10px;
  display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1; flex-shrink: 0;
}
.text-xxs { font-size: .6rem !important; }

/* ── Quick access tiles ── */
.quick-tile {
  display: flex; flex-direction: column; align-items: center;
  text-decoration: none; color: inherit; padding: 10px 4px;
  border-radius: 12px; transition: background .2s, transform .2s; cursor: pointer;
}
.quick-tile:hover { background: rgba(13,138,188,.06); transform: translateY(-3px); }
.tile-icon {
  width: 48px; height: 48px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 12px rgba(0,0,0,.15);
}

/* ── Animations ── */
@keyframes fadeUp { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
.animate-up { animation: fadeUp .45s ease both; }

/* ── Leave balances scroll ── */
.leave-scroll { max-height: 270px; overflow-y: auto; }
.leave-scroll::-webkit-scrollbar { width: 4px; }
.leave-scroll::-webkit-scrollbar-thumb { background: rgba(13,138,188,.25); border-radius: 4px; }

/* ── Misc ── */
.shadow-glow { box-shadow: 0 4px 20px rgba(13,138,188,.35) !important; }
.border-s { border-left: 1px solid rgba(0,0,0,.06); }
.text-purple { color: #6c5ce7 !important; }
</style>
