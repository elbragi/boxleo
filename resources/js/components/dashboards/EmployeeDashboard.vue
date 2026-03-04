<template>
  <v-container>
    <!-- Welcome Banner -->
    <v-row>
      <v-col cols="12">
        <v-card color="primary" class="text-center mb-4">
          <v-card-text>
            <h2 class="text-h5 text-white mb-0">
              Welcome back, {{ user.firstname }}!
            </h2>
            <p class="text-subtitle-2 text-white mb-0">Employee Self-Service Portal</p>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Dashboard Overview Cards -->
    <v-row>
      <!-- Employee Profile -->
      <v-col cols="12" md="6">
        <v-card class="h-100" elevation="2">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-account-circle</v-icon>
              Profile
            </v-card-title>
          </v-card-item>
          
          <v-card-text>
            <v-row align="center">
              <v-col cols="12" sm="auto" class="text-center">
                <v-avatar color="primary" size="100">
                  <v-img
                    :src="user.gender === 'Male' ? '/assets/img/male-avatar.svg' : '/assets/img/female-avatar.png'"
                    cover
                  ></v-img>
                </v-avatar>
              </v-col>
              
              <v-col>
                <h3 class="text-h5 font-weight-bold">{{ user.firstname }} {{ user.lastname }}</h3>
                <div class="text-subtitle-1 d-flex align-center">
                  <v-icon size="small" class="me-2">mdi-account-tie</v-icon>
                  {{ user.designation.name }}
                </div>
                <div class="text-subtitle-2 d-flex align-center">
                  <v-icon size="small" class="me-2">mdi-domain</v-icon>
                  {{ user.department.name }}
                </div>
                <div class="text-subtitle-2 d-flex align-center">
                  <v-icon size="small" class="me-2">mdi-map-marker</v-icon>
                  {{ user.office.name }}, {{ user.unit.name }}
                </div>
              </v-col>
            </v-row>
            
            <v-divider class="my-3"></v-divider>
            
            <div class="d-flex align-center mb-2">
              <v-icon size="small" color="blue" class="me-3">mdi-email</v-icon>
              <span>{{ user.email }}</span>
            </div>
            
            <div class="d-flex align-center mb-2">
              <v-icon size="small" color="green" class="me-3">mdi-phone</v-icon>
              <span>{{ user.phone }}</span>
            </div>
            
            <div class="d-flex align-center">
              <v-icon size="small" color="orange" class="me-3">mdi-calendar-check</v-icon>
              <span>Work Anniversary: {{ user.work_anniversary ?? 'Not Available' }}</span>
            </div>
          </v-card-text>
          
          <v-card-actions>
            <v-btn color="primary" variant="text" block prepend-icon="mdi-account-edit">
              Edit Profile
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>

      <!-- Leave Management -->
      <v-col cols="12" md="6">
        <v-card class="h-100" elevation="2">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-calendar-multiple</v-icon>
              Leave Management
            </v-card-title>
          </v-card-item>
          
          <v-card-text>
            <v-row>
              <v-col cols="12" sm="6">
                <v-card variant="outlined" class="mb-2">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-primary">{{ statistics.annualLeaveDaysAssigned }}</div>
                    <div class="text-caption">Total Days</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-card variant="outlined" class="mb-2">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-success">{{ statistics.leaveBalance }}</div>
                    <div class="text-caption">Available Days</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-card variant="outlined">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-info">{{ statistics.leaveTaken }}</div>
                    <div class="text-caption">Days Taken</div>
                  </v-card-text>
                </v-card>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-card variant="outlined">
                  <v-card-text class="text-center">
                    <div class="text-h5 font-weight-bold text-warning">{{ statistics.leaveRequests }}</div>
                    <div class="text-caption">Pending Requests</div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-card-text>
          
          <v-card-actions>
            <v-btn color="primary" block prepend-icon="mdi-calendar-plus" @click="applyLeaveModal = true">
              Apply for Leave
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <!-- Second Row Cards -->
    <v-row class="mt-4">
      <!-- Performance Card -->
      <v-col cols="12" md="4">
        <v-card elevation="2" height="100%" class="rounded-xl overflow-hidden">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-chart-line</v-icon>
              Performance
            </v-card-title>
          </v-card-item>
          <v-card-text>
            <v-list density="compact">
              <v-list-item>
                <template v-slot:prepend><v-icon color="amber-darken-2" size="small">mdi-trophy</v-icon></template>
                <v-list-item-title class="text-caption">Recent Awards</v-list-item-title>
                <v-list-item-subtitle class="text-right text-caption">---</v-list-item-subtitle>
              </v-list-item>
              <v-list-item>
                <template v-slot:prepend><v-icon color="blue" size="small">mdi-chart-line</v-icon></template>
                <v-list-item-title class="text-caption">Monthly Perf.</v-list-item-title>
                <v-list-item-subtitle class="text-right text-caption">--%</v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- HR Contact Card -->
      <v-col cols="12" md="4">
        <v-card elevation="2" height="100%" class="rounded-xl overflow-hidden">
          <v-card-item>
            <v-card-title class="pb-1">
              <v-icon color="primary" class="me-2">mdi-account-supervisor</v-icon>
              HR Contact
            </v-card-title>
          </v-card-item>
          <v-card-text>
            <div class="d-flex align-center mb-2">
              <v-avatar color="primary" size="32" class="me-2">
                <v-icon color="white" size="small">mdi-account-tie</v-icon>
              </v-avatar>
              <div>
                <div class="text-subtitle-2 font-weight-bold">{{ hr.firstname }}</div>
                <div class="text-tiny grey--text">HR Manager</div>
              </div>
            </div>
            <div class="text-caption d-flex align-center"><v-icon size="x-small" color="blue" class="me-2">mdi-email</v-icon>{{ hr.email }}</div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Smart Calendar Card (NEW) -->
      <v-col cols="12" md="4">
        <v-card class="glass-morphism rounded-xl elevation-4 h-100 border-light overflow-hidden">
          <v-card-title class="d-flex justify-space-between align-center px-4 py-2 bg-light-primary">
            <div class="d-flex align-center">
              <v-icon color="primary" class="mr-2" size="small">mdi-calendar-star</v-icon>
              <span class="text-subtitle-2 font-weight-bold primary--text">Smart Calendar</span>
            </div>
            <v-btn icon="mdi-open-in-new" variant="tonal" color="primary" size="x-small" href="/holidays" class="rounded-lg"></v-btn>
          </v-card-title>
          
          <div class="pa-2 d-flex justify-center bg-white-translucent">
            <VCalendar borderless transparent mini :attributes="calendarAttributes" class="mini-calendar custom-mini-calendar" />
          </div>
          
          <v-divider></v-divider>
          
          <div class="pa-2 bg-light-slate">
            <div class="text-tiny font-weight-bold grey--text mb-1 px-1">UPCOMING</div>
            <v-list dense bg-color="transparent" class="pa-0">
              <v-list-item v-for="item in upcomingEvents" :key="item.id" class="upcoming-event-item mb-1 rounded-lg pa-1">
                <template v-slot:prepend>
                  <div class="mini-date-badge mr-2">
                    <div class="text-tiny font-weight-bold primary--text">{{ new Date(item.date).toLocaleString('default', { month: 'short' }) }}</div>
                    <div class="text-subtitle-2 font-weight-black">{{ new Date(item.date).getDate() }}</div>
                  </div>
                </template>
                <v-list-item-title class="text-caption font-weight-bold primary--text truncate">{{ item.name }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Access Section -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card elevation="2">
          <v-card-text>
            <div class="text-h6 mb-2">Quick Access</div>
            <v-row>
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="primary" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-calendar</v-icon>
                    <div class="text-caption mt-1">Attendance</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="error" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-file-certificate</v-icon>
                    <div class="text-caption mt-1">Certificates</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="success" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-school</v-icon>
                    <div class="text-caption mt-1">Training</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="warning" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-account-group</v-icon>
                    <div class="text-caption mt-1">Team</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="info" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-notebook</v-icon>
                    <div class="text-caption mt-1">Policies</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="primary" block height="80" class="text-center" href="/holidays">
                  <div>
                    <v-icon>mdi-calendar-multiselect</v-icon>
                    <div class="text-caption mt-1">Smart Calendar</div>
                  </div>
                </v-btn>
              </v-col>
              
              <v-col cols="6" sm="4" md="2">
                <v-btn variant="outlined" color="secondary" block height="80" class="text-center">
                  <div>
                    <v-icon>mdi-cog</v-icon>
                    <div class="text-caption mt-1">Settings</div>
                  </div>
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Apply Leave Dialog -->
    <v-dialog v-model="applyLeaveModal" max-width="800" persistent>
      <v-card>
        <v-toolbar color="primary" density="compact">
          <v-toolbar-title>Apply for Leave</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click="applyLeaveModal = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text class="pt-4">
          <v-form ref="createLeaveForm">
            <v-row>
              <v-col cols="12" sm="6">
                <v-select
                  v-model="newLeave.leave_type_id"
                  :items="leaveTypes"
                  label="Type of Leave"
                  item-value="id"
                  item-title="name"
                  variant="outlined"
                  clearable
                  :rules="[v => !!v || 'Leave type is required']"
                  prepend-inner-icon="mdi-format-list-bulleted"
                ></v-select>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.days"
                  label="Number of Days"
                  type="number"
                  variant="outlined"
                  :rules="[v => !!v || 'Days are required', v => v > 0 || 'Days must be greater than 0']"
                  prepend-inner-icon="mdi-numeric"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.from"
                  label="Start Date"
                  type="date"
                  variant="outlined"
                  :rules="[v => !!v || 'Start date is required']"
                  prepend-inner-icon="mdi-calendar-start"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.to"
                  label="End Date"
                  type="date"
                  variant="outlined"
                  :rules="[v => !!v || 'End date is required']"
                  prepend-inner-icon="mdi-calendar-end"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-select
                  v-model="newLeave.manager"
                  :items="managers"
                  label="Line Manager"
                  item-value="id"
                  item-title="fullname"
                  variant="outlined"
                  clearable
                  :rules="[v => !!v || 'Line manager is required']"
                  prepend-inner-icon="mdi-account-supervisor"
                ></v-select>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-select
                  v-model="newLeave.hod"
                  :items="hods"
                  label="Head of Department"
                  item-value="id"
                  item-title="fullname"
                  variant="outlined"
                  clearable
                  :rules="[v => !!v || 'HOD is required']"
                  prepend-inner-icon="mdi-account-tie"
                ></v-select>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-text-field
                  v-model="newLeave.phone"
                  label="Contact Phone"
                  type="text"
                  placeholder="254..."
                  variant="outlined"
                  :rules="[v => !!v || 'Phone number is required', v => /^[+254]\d{9,12}$/.test(v) || 'Invalid phone number']"
                  prepend-inner-icon="mdi-phone"
                ></v-text-field>
              </v-col>
              
              <v-col cols="12" sm="6">
                <v-file-input
                  v-model="newLeave.document"
                  label="Supporting Document (Optional)"
                  accept=".pdf, .doc, .docx, .jpeg, .jpg, .png"
                  variant="outlined"
                  prepend-icon="mdi-paperclip"
                  clearable
                  show-size
                ></v-file-input>
              </v-col>
              
              <v-col cols="12">
                <v-textarea
                  v-model="newLeave.comment"
                  label="Additional Comments"
                  variant="outlined"
                  counter
                  clearable
                  prepend-inner-icon="mdi-comment-text"
                  auto-grow
                  rows="3"
                ></v-textarea>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        
        <v-divider></v-divider>
        
        <v-card-actions class="pa-4">
          <v-spacer></v-spacer>
          <v-btn color="error" variant="text" @click="applyLeaveModal = false" class="me-2">
            Cancel
          </v-btn>
          <v-btn 
            color="primary" 
            @click="submitNewLeave"
            :loading="isLoading"
            :disabled="isLoading"
          >
            Submit Application
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: 'EmployeeDashboard',
  props: {
    user: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      base_url: '/',
      applyLeaveModal: false,
      statistics: {
        teamMembers: 0,
        earlyAttendances: 0,
        lateAttendances: 0,
        annualLeaveDaysAssigned: 0,
        leaveBalance: 0,
        leaveTaken: 0,
        leaveRequests: 0,
        notifications: 0,
        totalNotifications: 0,
        teamMembersOnLeave: 0,
        totalAppliedLeaves: 0,
        totalApprovedLeaves: 0,
        recentLeave: '',
      },
      hr: {
        firstname: '',
        lastname: '',
        email: '',
        phone: ''
      },
      users: [],
      managers: [],
      hods: [],
      leaveTypes: [],
      holidays: [],
      newLeave: {
        leave_type_id: null,
        from: null,
        to: null,
        phone: null,
        days: null,
        manager: null,
        hod: null,
        document: null,
        comment: null,
      },
      isLoading: false,
    };
  },
  mounted() {
    this.fetchDashboardStatistics();
    this.fetchUsers();
    this.fetchLeaveTypes();
    this.fetchHolidays();
  },
  methods: {
    getPreviousMonth() {
      const now = new Date();
      const previousMonth = new Date(now.getFullYear(), now.getMonth() - 1);
      const month = previousMonth.toLocaleString('default', { month: 'long' });
      const year = previousMonth.getFullYear();
      return `${month} ${year}`;
    },
    fetchUsers() {
      const apiUrl = this.base_url + `api/v1/users`;
      axios.get(apiUrl)
        .then(response => {
          this.users = response.data.users.map(user => ({
            ...user,
            fullname: `${user.firstname} ${user.lastname}`,
          }));
          this.hods = this.users.filter(user => user.is_hod === 1);
          this.managers = this.users.filter(user => user.designation_id === 1);
        })
        .catch(error => {
          console.error('Error fetching users:', error);
        });
    },
    fetchLeaveTypes() {
      const apiUrl = this.base_url + `api/v1/leave-types`;
      axios.get(apiUrl)
        .then(response => {
          this.leaveTypes = response.data.leaveTypes;
        })
        .catch(error => {
          console.error('Error fetching leave Types:', error);
        });
    },
    fetchDashboardStatistics() {
      axios
        .get(`/api/v1/dashboard/${this.user.id}`)
        .then((response) => {
          this.statistics = response.data;
          this.hr = response.data.hr;
        })
        .catch((error) => {
          console.error('Error fetching dashboard statistics:', error);
        });
    },
    submitNewLeave() {
      if (this.$refs.createLeaveForm.validate()) {
        const requestedDays = parseFloat(this.newLeave.days);
        
        // Try to get balance from statistics, otherwise find in leaveTypes
        let availableBalance = parseFloat(this.statistics.leaveBalance);
        
        // If the balance in statistics is 0, double check if it's because it's not loaded or truly 0
        // The dashboard usually fetches 'Annual Leave' balance by default.
        if (this.newLeave.leave_type_id != 1) { // If not annual leave, we need to find the specific balance
           // In the dashboard, we might only have one balance in 'statistics.leaveBalance'
           // which is usually annual leave. 
           // For other types, it's safer to just let the backend handle it OR we could fetch all balances here.
           // However, to keep it simple and consistent with the backend:
        }

        if (requestedDays > availableBalance) {
          this.$toastr.warning(`You cannot apply for ${requestedDays} days. Your available balance is only ${availableBalance} days.`, "Insufficient Balance");
          return;
        }

        this.isLoading = true;
        const formData = new FormData();
        formData.append('user_id', this.user.id);
        formData.append('leave_type_id', this.newLeave.leave_type_id);
        formData.append('from', this.newLeave.from);
        formData.append('to', this.newLeave.to);
        formData.append('phone', this.newLeave.phone);
        formData.append('days', this.newLeave.days);
        formData.append('manager', this.newLeave.manager);
        formData.append('hod', this.newLeave.hod);
        formData.append('comment', this.newLeave.comment);
        if (this.newLeave.document && this.newLeave.document[0]) {
          formData.append('document', this.newLeave.document[0]);
        }
        axios.post('/api/v1/leaves', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
          .then(response => {
            this.isLoading = false;
            this.$toastr.success(response.data.message);
            this.applyLeaveModal = false;
            this.fetchDashboardStatistics();
          })
          .catch(error => {
            this.isLoading = false;
            if (error.response && error.response.data && error.response.data.error) {
              this.$toastr.error(error.response.data.error);
            } else {
              this.$toastr.error(error.message);
            }
          });
      }
    },
    fetchHolidays() {
      axios.get('/api/v1/holidays')
        .then(response => {
          this.holidays = response.data.holidays || response.data || [];
        });
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
.glass-morphism {
    background: rgba(255, 255, 255, 0.7) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.border-light {
    border: 1px solid #f1f5f9 !important;
}

.bg-light-primary {
    background-color: rgba(var(--v-theme-primary), 0.05) !important;
}

.bg-white-translucent {
    background-color: rgba(255, 255, 255, 0.5) !important;
}

.bg-light-slate {
    background-color: #f8fafc;
}

.mini-date-badge {
    min-width: 35px;
    height: 35px;
    background: white;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    line-height: 1;
    border: 1px solid #e2e8f0;
}

.text-tiny {
    font-size: 0.6rem !important;
}

.upcoming-event-item {
    background: white !important;
    border: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.upcoming-event-item:hover {
    transform: translateX(4px);
}

.custom-mini-calendar :deep(.vc-pane-container) {
    background: transparent !important;
}

.custom-mini-calendar :deep(.vc-title) {
    font-size: 0.8rem;
    font-weight: 700;
}

.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.text-subtitle-2 {
    font-size: 0.875rem !important;
    line-height: 1.25rem;
}
</style>