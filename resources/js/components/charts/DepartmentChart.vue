<template>
  <div class="department-distribution pa-4">
    <div class="d-flex justify-space-between align-center mb-4">
      <h3 class="text-subtitle-1 font-weight-bold text-dark-emphasis">Department Distribution</h3>
      <v-chip size="x-small" color="primary" variant="tonal" class="font-weight-bold">{{ totalEmployees }} Employees</v-chip>
    </div>
    
    <div class="distribution-list">
      <div v-for="(dept, index) in sortedDepartments" :key="dept.id" class="dept-item mb-3">
        <div class="d-flex justify-space-between align-center mb-1">
          <span class="dept-name text-caption font-weight-bold">{{ dept.name }}</span>
          <span class="dept-count text-caption font-weight-black text-primary">{{ dept.users.length }}</span>
        </div>
        <v-tooltip location="top">
          <template v-slot:activator="{ props }">
            <div v-bind="props" class="progress-wrapper">
              <v-progress-linear
                :model-value="getPercentage(dept.users.length)"
                :color="getDeptColor(index)"
                height="6"
                rounded
                class="slim-progress"
              ></v-progress-linear>
            </div>
          </template>
          <span>{{ getPercentage(dept.users.length) }}% of workforce</span>
        </v-tooltip>
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
      totalEmployees: 0
    };
  },
  mounted() {
    this.fetchDepartments();
  },
  computed: {
    sortedDepartments() {
      return [...this.departments].sort((a, b) => b.users.length - a.users.length).slice(0, 6);
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
      const colors = ['primary', 'info', 'success', 'purple', 'warning', 'error'];
      return colors[index % colors.length];
    }
  }
}
</script>

<style scoped>
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
.slim-progress {
  background-color: rgba(0,0,0,0.03);
}
.progress-wrapper {
  cursor: pointer;
}
</style>
