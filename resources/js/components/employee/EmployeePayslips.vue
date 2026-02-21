<template>
  <v-container fluid>
    <v-card elevation="2">
      <v-card-title class="d-flex align-center py-4 px-6">
        <v-icon color="primary" class="me-2">mdi-cash-multiple</v-icon>
        <span class="text-h5">My Payslips</span>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Search Payslips"
          variant="outlined"
          density="compact"
          hide-details
          class="max-width-300"
        ></v-text-field>
      </v-card-title>
      
      <v-divider></v-divider>
      
      <v-data-table
        :headers="headers"
        :items="payslips"
        :search="search"
        :loading="isLoading"
        hover
      >
        <template v-slot:item.period="{ item }">
          {{ getMonthName(item.month) }} {{ item.year }}
        </template>
        
        <template v-slot:item.basic_pay="{ item }">
          {{ formatCurrency(item.basic_pay) }}
        </template>
        
        <template v-slot:item.net_pay="{ item }">
          <span class="font-weight-bold text-primary">
            {{ formatCurrency(item.net_pay) }}
          </span>
        </template>
        
        <template v-slot:item.payment_status="{ item }">
          <v-chip
            :color="getPaymentStatusColor(item.payment_status)"
            size="small"
            class="text-uppercase"
          >
            {{ item.payment_status || 'Pending' }}
          </v-chip>
        </template>
        
        <template v-slot:item.actions="{ item }">
          <v-btn
            icon="mdi-download"
            variant="text"
            color="success"
            title="Download PDF"
            @click="downloadPayslip(item.id)"
          ></v-btn>
          <v-btn
            icon="mdi-eye"
            variant="text"
            color="info"
            title="View Details"
            @click="viewPayslip(item.id)"
          ></v-btn>
        </template>
      </v-data-table>
    </v-card>

    <!-- Payslip View Dialog -->
    <v-dialog v-model="payslipDialog" max-width="900px">
      <v-card v-if="currentPayslip">
        <v-toolbar color="primary" density="compact">
          <v-toolbar-title>Payslip - {{ getMonthName(currentPayslip.month) }} {{ currentPayslip.year }}</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon="mdi-download" @click="downloadPayslip(currentPayslip.id)"></v-btn>
          <v-btn icon="mdi-close" @click="payslipDialog = false"></v-btn>
        </v-toolbar>
        
        <v-card-text class="pa-6">
          <div class="payslip-preview border rounded pa-4">
             <!-- Simplified Preview -->
             <v-row>
               <v-col cols="6">
                 <div class="text-h6 text-primary">Boxleo Courier & Fulfillment</div>
                 <div class="text-body-2">Employee: {{ currentPayslip.user.firstname }} {{ currentPayslip.user.lastname }}</div>
                 <div class="text-body-2">ID: {{ currentPayslip.user.emp_id || currentPayslip.user.id }}</div>
               </v-col>
               <v-col cols="6" class="text-right">
                 <div class="text-h6">PAYSLIP</div>
                 <div class="text-subtitle-1">{{ getMonthName(currentPayslip.month) }} {{ currentPayslip.year }}</div>
               </v-col>
             </v-row>
             
             <v-divider class="my-4"></v-divider>
             
             <v-row>
               <v-col cols="6">
                 <div class="text-subtitle-1 font-weight-bold mb-2">Earnings</div>
                 <div class="d-flex justify-space-between mb-1">
                   <span>Basic Salary</span>
                   <span>{{ formatCurrency(currentPayslip.basic_pay) }}</span>
                 </div>
                 <div v-for="earning in currentPayslip.earnings" :key="earning.id" class="d-flex justify-space-between mb-1">
                   <span>{{ earning.earningType?.name || 'Allowance' }}</span>
                   <span>{{ formatCurrency(earning.amount) }}</span>
                 </div>
               </v-col>
               <v-col cols="6">
                 <div class="text-subtitle-1 font-weight-bold mb-2">Deductions</div>
                 <div v-for="deduction in currentPayslip.deductions" :key="deduction.id" class="d-flex justify-space-between mb-1">
                   <span>{{ deduction.deductionType?.name || 'Deduction' }}</span>
                   <span>{{ formatCurrency(deduction.amount) }}</span>
                 </div>
               </v-col>
             </v-row>
             
             <v-divider class="my-4"></v-divider>
             
             <div class="d-flex justify-space-between align-center bg-grey-lighten-4 pa-3 rounded">
               <span class="text-h6">Net Payable</span>
               <span class="text-h5 font-weight-bold text-primary">{{ formatCurrency(currentPayslip.net_pay) }}</span>
             </div>
          </div>
        </v-card-text>
        
        <v-card-actions class="pa-4 pt-0">
          <v-spacer></v-spacer>
          <v-btn color="primary" @click="downloadPayslip(currentPayslip.id)" prepend-icon="mdi-download">
            Download PDF
          </v-btn>
          <v-btn variant="text" @click="payslipDialog = false">Close</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
export default {
  name: 'EmployeePayslips',
  props: {
    user_id: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      search: '',
      isLoading: false,
      payslips: [],
      payslipDialog: false,
      currentPayslip: null,
      headers: [
        { title: 'Period', key: 'period', sortable: true },
        { title: 'Basic Pay', key: 'basic_pay', align: 'end' },
        { title: 'Net Payable', key: 'net_pay', align: 'end' },
        { title: 'Status', key: 'payment_status', align: 'center' },
        { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
      ]
    }
  },
  mounted() {
    this.fetchPayslips();
  },
  methods: {
    async fetchPayslips() {
      this.isLoading = true;
      try {
        const response = await axios.get(`/api/v1/payslips/${this.user_id}/with-user`);
        this.payslips = response.data.payslips || response.data; // Adjust based on API response structure
      } catch (error) {
        console.error('Error fetching payslips:', error);
      } finally {
        this.isLoading = false;
      }
    },
    async viewPayslip(id) {
      this.isLoading = true;
      try {
        const response = await axios.get(`/api/v1/payslips/${id}/with-user`);
        this.currentPayslip = response.data.payroll || response.data;
        this.payslipDialog = true;
      } catch (error) {
        console.error('Error fetching payslip details:', error);
      } finally {
        this.isLoading = false;
      }
    },
    downloadPayslip(id) {
      if (!id) return;
      window.open(`/employee-print-payslip/${id}?download=1`, '_blank');
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
      }).format(value);
    },
    getMonthName(monthNumber) {
      const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      return monthNames[monthNumber - 1];
    },
    getPaymentStatusColor(status) {
      const statusColors = {
        'Paid': 'success',
        'Pending': 'warning',
        'Failed': 'error',
        'Processing': 'info'
      };
      return statusColors[status] || 'grey';
    }
  }
}
</script>

<style scoped>
.max-width-300 {
  max-width: 300px;
}
.payslip-preview {
  font-family: 'Inter', sans-serif;
}
</style>
