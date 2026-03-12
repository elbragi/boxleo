<template>
    <div class="presence-widget pt-4">
        <div id="todayStats" class="stunning-donut-container"></div>
        <div class="stunning-presence-details px-4 pb-6 mt-2">
            <v-row spacing="3">
                <v-col cols="4">
                    <div class="stunning-badge in-time-badge">
                        <v-icon size="16" class="mb-1" color="success">mdi-check-circle</v-icon>
                        <div class="stunning-value text-success">{{ chartData.inTime }}</div>
                        <div class="stunning-label">In Time</div>
                    </div>
                </v-col>
                <v-col cols="4">
                    <div class="stunning-badge late-badge">
                        <v-icon size="16" class="mb-1" color="error">mdi-clock-alert</v-icon>
                        <div class="stunning-value text-error">{{ chartData.late }}</div>
                        <div class="stunning-label">Late</div>
                    </div>
                </v-col>
                <v-col cols="4">
                    <div class="stunning-badge absent-badge">
                        <v-icon size="16" class="mb-1" color="warning">mdi-account-off</v-icon>
                        <div class="stunning-value text-warning">{{ chartData.unregistered }}</div>
                        <div class="stunning-label">Absent</div>
                    </div>
                </v-col>
            </v-row>
        </div>
    </div>
</template>

<script>
import ApexCharts from 'apexcharts';

export default {
    name: 'AttendancePieChart',
    data() {
        return {
            loading: true,
            base_url: '/',
            usersCount: 0,
            chartData: {
                inTime: 0,
                late: 0,
                unregistered: 0
            },
       };
    },
    mounted() {
        this.fetchUsers();
        this.fetchAttendanceAndRenderChart();
    },
    methods: {
        fetchUsers() {
            const apiUrl = '/web-api/users';
            axios.get(apiUrl)
                .then(response => {
                    this.usersCount = response.data.users.length;
                })
                .catch(error => {
                    console.error('Error fetching users:', error);
                });
        },
        async fetchAttendanceAndRenderChart() {
            try {
                const response = await axios.get('/web-api/attendances');
                const attendance = response.data.attendances;

                const currentDate = new Date().toISOString().split('T')[0];
                const filteredAttendance = attendance.filter(record => record.attendance_date === currentDate);

                const inTimeCount = filteredAttendance.filter(record => record.status === 'In Time').length;
                const lateCount = filteredAttendance.filter(record => record.status === 'Late').length;
                this.chartData.inTime = inTimeCount;
                this.chartData.late = lateCount;
                this.chartData.unregistered = this.usersCount - (inTimeCount + lateCount);
                this.renderChart();
            } catch (error) {
                console.error('Error fetching attendance:', error);
            }
        },
        renderChart() {
            const total = this.chartData.inTime + this.chartData.late + this.chartData.unregistered;
            const presenceRate = total > 0 ? Math.round((this.chartData.inTime / total) * 100) : 0;

            const options = {
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'Inter, sans-serif',
                    dropShadow: {
                        enabled: true,
                        color: '#111',
                        top: 5,
                        left: 0,
                        blur: 8,
                        opacity: 0.15
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '75%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    color: '#64748b',
                                    offsetY: -5
                                },
                                value: {
                                    show: true,
                                    fontSize: '32px',
                                    fontWeight: 900,
                                    color: '#1e293b',
                                    offsetY: 8,
                                    formatter: (val) => presenceRate + '%'
                                },
                                total: {
                                    show: true,
                                    label: 'Presence',
                                    formatter: () => presenceRate + '%'
                                }
                            }
                        }
                    }
                },
                labels: ['In Time', 'Late', 'Unregistered'],
                series: [this.chartData.inTime, this.chartData.late, this.chartData.unregistered],
                colors: ['#10B981', '#F43F5E', '#F59E0B'],
                stroke: { 
                    width: 3,
                    colors: ['#fff']
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'horizontal',
                        shadeIntensity: 0.5,
                        gradientToColors: ['#34D399', '#FB7185', '#FCD34D'],
                        inverseColors: true,
                        opacityFrom: 1,
                        opacityTo: 1,
                        stops: [0, 100]
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: (val) => val + ' Employees' }
                }
            };

            const chart = new ApexCharts(document.querySelector('#todayStats'), options);
            chart.render();
        }
    }
}
</script>

<style scoped>
.stunning-donut-container {
    position: relative;
    z-index: 2;
}

.stunning-presence-details {
    margin-top: -20px;
    z-index: 1;
    position: relative;
}

.stunning-badge {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 16px;
    padding: 12px 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
}

.stunning-badge:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}

.in-time-badge { border-bottom: 3px solid #10B981; }
.late-badge { border-bottom: 3px solid #F43F5E; }
.absent-badge { border-bottom: 3px solid #F59E0B; }

.stunning-value {
    font-size: 1.5rem;
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: 2px;
}

.stunning-label {
    font-size: 0.65rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

