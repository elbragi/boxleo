<template>
  <v-container fluid>
    <v-row class="mb-2">
      <v-col class="text-right">
        <v-btn @click="openClockAction" :color="getClockColor" class="clock-btn" elevation="10">
          <v-icon>{{ getClockIcon }}</v-icon>
          <span class="button-text">{{ getClockText }}</span>
        </v-btn>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="12" lg="8">
        <v-card variant="outlined">
          <v-card-title>Attendance Records</v-card-title>
          <v-data-table :headers="headers" :items="attendances" item-value="id" :loading="loading">
            <template v-slot:item.is_present="{ item }">
              <td>
                <span :class="{ 'text-success': item.is_present, 'text-danger': !item.is_present }">
                  <v-icon>{{ item.is_present ? 'mdi-check' : 'mdi-close' }}</v-icon>
                  {{ item.is_present ? 'Present' : 'Absent' }}
                </span>
              </td>

            </template>
            <template v-slot:item.status="{ item }">
              <td>
                <span :class="{ 'text-danger': item.status === 'Late', 'text-success': item.status !== 'Late' }">
                  <v-icon>mdi-clock</v-icon>
                  {{ item.status }}
                </span>
              </td>
            </template>
            <template v-slot:item.clock_out_time="{ item }">
              <td>{{ item.clock_out_time ?? '00:00:00' }}</td>
            </template>
          </v-data-table>
        </v-card>
      </v-col>
      <v-col cols="12" lg="4">
        <v-card variant="outlined">
          <v-card-title>Attendance Overview</v-card-title>
          <v-col v-for="(stat, index) in statistics" :key="index">
            <v-card class="pa-4 rounded-lg elevation-2" style=" text-align: center;">
              <v-row align="center" justify="space-between">
                <v-icon :color="stat.color" size="36" class="mr-2">
                  {{ stat.icon }}
                </v-icon>

              </v-row>
              <v-divider class="my-2"></v-divider>
              <v-card-title class="text-h6 font-weight-bold">
                {{ stat.label }}
              </v-card-title>
              <v-card-subtitle class="text-h5 font-weight-medium" :style="{ color: stat.color }">
                {{ stat.value }}
              </v-card-subtitle>
            </v-card>

          </v-col>
        </v-card>
      </v-col>
    </v-row>
    <v-dialog v-model="addAttendanceModal" max-width="600px" persistent>
      <v-card class="elevation-3">
        <v-card-title class="d-flex justify-space-between align-center">
          <span class="text-h5 font-weight-bold primary--text">Register Attendance</span>
          <v-btn icon @click="closeAttendanceModal" class="ml-auto">
            <v-icon color="grey darken-1">mdi-close</v-icon>
          </v-btn>
        </v-card-title>
        <v-divider></v-divider>
        <v-card-text class="pt-4 pb-2 px-6">
          <v-form @submit.prevent="submitAttendanceForm">
            <v-select v-model="attendanceForm.attendance_type" :items="['clock_in', 'clock_out']"
              label="Attendance Type" outlined dense required class="mb-4">
            </v-select>
            <v-text-field v-model="attendanceForm.attendance_date" label="Attendance Date (Readonly)" type="date"
              :value="getCurrentDate()" outlined dense readonly required class="mb-4">
            </v-text-field>


            <!-- <v-text-field v-model="attendanceForm.time" label="Time (Readonly)" type="time" :value="currentTime"
              outlined dense readonly class="mb-4">
            </v-text-field> -->


            <v-text-field v-model="attendanceForm.serverTime" label="Time (Readonly)" type="time" outlined dense readonly class="mb-4">
            </v-text-field>


            <v-textarea v-model="attendanceForm.notes" label="Comment (if any)" outlined dense rows="2" class="mb-4">
            </v-textarea>
            <v-btn type="submit" :color="getAttendanceColor(attendanceForm.attendance_type)"
              class="white--text font-weight-bold" block>
              <v-icon left>{{ getAttendanceIcon(attendanceForm.attendance_type) }}</v-icon>
              {{ getAttendanceText(attendanceForm.attendance_type) }}
            </v-btn>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>
<script>
import { DateTime } from 'luxon';

export default {
  props: {
    userId: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      serverTime: '',
      userTimezone: 'Africa/Nairobi',

      headers: [
        { title: 'Attendance Date', value: 'attendance_date' },
        { title: 'Time In', value: 'clock_in_time' },
        { title: 'Attendance Status', value: 'is_present' },
        { title: 'Reporting Time', value: 'status' },
        { title: 'Time Out', value: 'clock_out_time' },
      ],

      statistics: [
        { icon: 'mdi-calendar-multiple', value: 0, label: 'Days Present', color: 'success' },
        { icon: 'mdi-timer-off', value: 0, label: 'Early Arrivals', color: 'info' },
        { icon: 'mdi-timer-sand', value: 0, label: 'Late Arrivals', color: 'danger' },
        { icon: 'mdi-calendar-star', value: 0, label: 'On Leave', color: 'warning' }
      ],
      deviceLatitude: null,
      deviceLongitude: null,

      attendances: [],
      loading: false,
      addAttendanceModal: false,
      attendanceForm: {
        attendance_type: '',
        attendance_date: '',
        time: '',
        notes: '',
        currentTime: '',
        serverTime: '',
      },
    };
  },
  created() {
    this.fetchAttendances();
    this.fetchUserTimezone();
    setInterval(this.updateCurrentTime, 1000);
  },
  mounted() {
    this.getDeviceCoordinates();
  },

  computed: {
    filteredAttendances() {
      return this.attendances.filter((attendance) =>
        Object.values(attendance)
          .some((val) =>
            val &&
            val.toString().toLowerCase().includes(this.search.toLowerCase())
          )
      );
    },
    getClockIcon() {
      return this.isClockedInToday ? 'mdi-clock-out' : 'mdi-clock-in';
    },
    getClockColor() {
      return this.isClockedInToday ? 'error' : 'success';
    },
    getClockText() {
      return this.isClockedInToday ? 'Clock Out' : 'Clock In';
    },
    todayAttendance() {
      const today = DateTime.now().setZone(this.userTimezone).toFormat('ccc dd MMM yyyy');
      return this.attendances.find(a => a.attendance_date === today);
    },
    isClockedInToday() {
      const att = this.todayAttendance;
      return !!(att && att.clock_in_time && (att.clock_out_time === '00:00:00' || !att.clock_out_time));
    },

  },
  methods: {


async fetchUserTimezone() {
  try {
    const timezoneRes = await axios.get('/api/v1/user-timezone');
    this.userTimezone = timezoneRes.data.timezone || 'Africa/Nairobi';
  } catch (err) {
    console.error('Failed to fetch user timezone:', err);
    this.userTimezone = 'Africa/Nairobi';
  }
},

async localizeTime() {
  try {
    const serverTimeRes = await axios.get('/api/v1/server-time');
    const serverTimeString = serverTimeRes.data.time;

    const serverTime = DateTime.fromFormat(serverTimeString, 'yyyy-MM-dd HH:mm:ss', {
      zone: 'Africa/Nairobi',
    });

    const localized = serverTime.setZone(this.userTimezone);
    return localized.toFormat('HH:mm:ss');
  } catch (err) {
    console.error('Error localizing time:', err);
    const now = new Date();
    return `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}:${now.getSeconds().toString().padStart(2, '0')}`;
  }
},
formatTime(date) {
  // Extract hours, minutes, and seconds
  const hours = date.getHours().toString().padStart(2, '0');
  const minutes = date.getMinutes().toString().padStart(2, '0');
  const seconds = date.getSeconds().toString().padStart(2, '0');
  // Return formatted time string
  return `${hours}:${minutes}:${seconds}`;
},


    getDeviceCoordinates() {
      if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
          (position) => {
            const { latitude, longitude } = position.coords;
            console.log('Device Coordinates:', latitude, longitude);
            this.deviceLatitude = latitude;
            this.deviceLongitude = longitude;
          },
          (error) => {
            console.log('Error getting device coordinates:', error);
          }
        );
      } else {
        console.log('Geolocation is not supported by this browser.');
      }
    },

    getAttendanceIcon(attendanceType) {
      return attendanceType === 'clock_in' ? 'mdi-clock-in' : 'mdi-clock-out';
    },
    getAttendanceColor(attendanceType) {
      return attendanceType === 'clock_in' ? 'success' : 'error';
    },
    getAttendanceTitle(attendanceType) {
      return attendanceType === 'clock_in' ? 'Clock In' : 'Clock Out';
    },
    getAttendanceText(attendanceType) {
      return attendanceType === 'clock_in' ? 'Clock In' : 'Clock Out';
    },
    openAddAttendanceModal(attendanceType) {
      this.attendanceForm.attendance_type = attendanceType;
      this.addAttendanceModal = true;
    },
    openClockAction() {
      if (this.isClockedInToday) {
        this.openAddAttendanceModal('clock_out');
      } else {
        this.openAddAttendanceModal('clock_in');
      }
    },
    getCurrentDate() {
      const currentDate = new Date().toISOString().substr(0, 10);
      return currentDate;
    },
    updateCurrentTime() {
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      const seconds = now.getSeconds().toString().padStart(2, '0');
      this.currentTime = `${hours}:${minutes}:${seconds}`;
    },
    async submitAttendanceForm() {
      // Re-fetch localized time at the moment of submission to ensure accuracy
      const freshTime = await this.localizeTime();
      this.serverTime = freshTime;
      this.attendanceForm.serverTime = freshTime;

      const formData = {
        attendance_type: this.attendanceForm.attendance_type,
        attendance_date: this.getCurrentDate(),
        time: this.serverTime,
        notes: this.attendanceForm.notes,
        user_id: this.userId,
        latitude: this.deviceLatitude,
        longitude: this.deviceLongitude
      };

      // Soft warning for early clock-out (allows proceeding)
      if (formData.attendance_type === 'clock_out' && this.isBefore5PM()) {
        if (!confirm('It is before 5 PM. Are you sure you want to clock out early?')) {
          return;
        }
      }

      const apiUrl = '/api/v1/attendances';
      axios.post(apiUrl, formData)
        .then(response => {
          this.$toastr.success(response.data.message);
          this.addAttendanceModal = false;
          this.fetchAttendances();
        })
        .catch(error => {
          const errorMessage = error.response && error.response.data.message ? error.response.data.message : 'Error adding attendance. Please try again.';
          this.$toastr.error(errorMessage);
          console.error('Error adding attendance:', error);
        });
    },

    isBefore5PM() {
      const currentTime = new Date();
      const hours = currentTime.getHours();
      return hours < 17; // Before 5 PM
    },

    fetchAttendances() {

      this.loading = true; // Start loading

      const apiUrl = `/api/v1/attendances/${this.userId}`;
      axios.get(apiUrl)
        .then(response => {
          this.attendances = response.data.attendances;
          this.statistics[0].value = this.attendances.filter(item => item.is_present).length;
          this.statistics[1].value = this.attendances.filter(item => item.status === 'In Time').length;
          this.statistics[2].value = this.attendances.filter(item => item.status === 'Late').length;
        })
        .catch(error => {
          console.error('Error fetching attendances:', error);
          this.$toastr.error('Error fetching attendances');
        }).finally(() => {
          this.loading = false; // Stop loading
        });


    },
    closeAttendanceModal() {
      this.addAttendanceModal = false;
    },
  },
};


</script>
<!-- <style scoped>
.my-card {
    margin: 60px;
}
</style> -->