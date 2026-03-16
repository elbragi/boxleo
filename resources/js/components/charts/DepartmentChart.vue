<template>
  <div class="department-distribution pa-4 d-flex flex-column h-100">
    <div class="d-flex justify-space-between align-start mb-4">
      <div>
        <h3 class="text-subtitle-1 font-weight-bold text-dark-emphasis mb-0">Department Distribution</h3>
        <p class="text-caption text-medium-emphasis mb-0">Staffing spread across {{ departments.length }} teams</p>
      </div>
      <v-chip size="x-small" color="primary" variant="tonal" class="font-weight-bold">{{ totalEmployees }} Employees</v-chip>
    </div>
    
    <div class="scrollable-distribution-container pr-2">
      <div class="distribution-list">
        <div v-for="(dept, index) in departmentsWithPercentages" :key="dept.id" class="dept-item mb-4">
          <div class="d-flex justify-space-between align-center mb-1">
            <span class="dept-name text-caption font-weight-bold">{{ dept.name }}</span>
            <div class="d-flex align-center">
              <span class="dept-count text-caption font-weight-black text-primary mr-2">{{ dept.users.length }}</span>
              <span class="text-xxs text-medium-emphasis">({{ dept.percentage }}%)</span>
            </div>
          </div>
          <v-progress-linear
            :model-value="dept.percentage"
            :color="getDeptColor(index)"
            height="6"
            rounded
            class="slim-progress-premium"
          ></v-progress-linear>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'DepartmentDistribution',
  data() {
    return {
      departments: [],
      totalEmployees: 0,
      menu: false
    };
  },
  mounted() {
    this.fetchDepartments();
  },
  computed: {
    sortedDepartments() {
      return [...this.departments].sort((a, b) => b.users.length - a.users.length);
    },
    topThree() {
      return this.sortedDepartments.slice(0, 3);
    },
    departmentsWithPercentages() {
      return this.sortedDepartments.map(dept => ({
        ...dept,
        percentage: this.getPercentage(dept.users.length)
      }));
    }
  },
  methods: {
    async fetchDepartments() {
      try {
        const response = await axios.get('/web-api/departments');
        this.departments = response.data.departments;
        this.totalEmployees = this.departments.reduce((acc, dept) => acc + dept.users.length, 0);
      } catch (error) {
        console.error('Error fetching departments:', error);
      }
    },
    getPercentage(count) {
      if (this.totalEmployees === 0) return 0;
      return Math.round((count / this.totalEmployees) * 100);
    },
    getDeptColor(index) {
      const colors = ['#0D8ABC', '#10B981', '#F59E0B', '#8B5CF6', '#3B82F6', '#EF4444'];
      return colors[index % colors.length];
    }
  }
}
</script>

<style scoped>
.scrollable-distribution-container {
  max-height: 280px;
  overflow-y: auto;
  flex-grow: 1;
}

/* Custom Scrollbar for premium feel */
.scrollable-distribution-container::-webkit-scrollbar {
  width: 4px;
}

.scrollable-distribution-container::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.02);
  border-radius: 10px;
}

.scrollable-distribution-container::-webkit-scrollbar-thumb {
  background: rgba(var(--v-theme-primary), 0.2);
  border-radius: 10px;
}

.scrollable-distribution-container::-webkit-scrollbar-thumb:hover {
  background: rgba(var(--v-theme-primary), 0.4);
}

.dept-item {
  transition: transform 0.2s ease;
}

.dept-item:hover {
  transform: translateX(4px);
}

.dept-name {
  color: #475569;
  letter-spacing: 0.2px;
}

.slim-progress-premium {
  background-color: rgba(0,0,0,0.04);
}

.text-xxs {
  font-size: 0.65rem !important;
}

.tracking-wider {
  letter-spacing: 1px;
}
</style>
