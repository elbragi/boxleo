<template>
    <div>
        <div id="attendanceChart"></div>
    </div>
</template>
<script>
import ApexCharts from 'apexcharts';
export default {
    name: 'AttendanceGraph',
    mounted() {
        this.fetchAttendanceDataAndRenderChart();
    },
    methods: {
        async fetchAttendanceDataAndRenderChart() {
            try {
                const response = await axios.get('/web-api/attendance-analytics');
                const attendanceData = response.data;
                const daysOfWeek = Object.keys(attendanceData);
                const inTimeData = daysOfWeek.map(day => attendanceData[day][0].in_time);
                const lateData = daysOfWeek.map(day => attendanceData[day][0].late);
                const onLeaveData = daysOfWeek.map(day => attendanceData[day][0].on_leave);
                this.renderChart(daysOfWeek, inTimeData, lateData, onLeaveData);
            } catch (error) {
                console.error('Error fetching attendance data:', error);
            }
        },
        renderChart(daysOfWeek, inTimeData, lateData, onLeaveData) {
            const options = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                    stacked: false,
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                series: [
                    {
                        name: 'In Time',
                        data: inTimeData,
                        color: '#10B981'
                    },
                    {
                        name: 'Late',
                        data: lateData,
                        color: '#F43F5E'
                    },
                    {
                        name: 'On Leave',
                        data: onLeaveData,
                        color: '#3B82F6'
                    },
                ],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [20, 100]
                    }
                },
                xaxis: {
                    categories: daysOfWeek,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontWeight: 600
                        }
                    }
                },
                grid: {
                    borderColor: 'rgba(0,0,0,0.05)',
                    strokeDashArray: 4,
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontWeight: 700,
                    itemMargin: { horizontal: 15 }
                },
                title: {
                    text: 'Weekly Attendance Trends',
                    align: 'left',
                    margin: 20,
                    style: {
                        fontSize: '18px',
                        fontWeight: 700,
                        color: '#1e293b'
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: { show: true }
                }
            };

            const chart = new ApexCharts(document.querySelector('#attendanceChart'), options);
            chart.render();
        }
    }
}
</script>
