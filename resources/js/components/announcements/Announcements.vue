<template>
  <v-container fluid class="announcements-container pa-6">
    <!-- Hero Section: Featured Announcement -->
    <v-row v-if="featuredAnnouncement" class="mb-8">
      <v-col cols="12">
        <v-card class="hero-card announcement-card overflow-hidden animate-fade-in-up" border="0" elevation="10" @click="viewAnnouncement(featuredAnnouncement)">
          <div class="hero-bg-overlay"></div>
          <v-row no-gutters class="fill-height">
            <v-col cols="12" md="8" class="pa-8 d-flex flex-column justify-center relative">
              <v-chip color="secondary" size="small" class="mb-4 font-weight-black text-uppercase" variant="flat">Featured Update</v-chip>
              <h1 class="text-h3 font-weight-black text-white mb-4 hero-title">{{ featuredAnnouncement.subject }}</h1>
              <p class="text-h6 text-white opacity-80 mb-6 font-weight-regular line-clamp-3 hero-desc">
                {{ featuredAnnouncement.description }}
              </p>
              <div class="d-flex align-center">
                <v-avatar size="48" class="mr-3 border-2 border-white">
                  <v-img :src="`https://ui-avatars.com/api/?name=${featuredAnnouncement.author_name}&background=random`"></v-img>
                </v-avatar>
                <div>
                  <div class="text-subtitle-1 font-weight-bold text-white mb-0">{{ featuredAnnouncement.author_name }}</div>
                  <div class="text-caption text-white opacity-70">{{ formatDate(featuredAnnouncement.publish_date) }}</div>
                </div>
              </div>
            </v-col>
            <v-col cols="12" md="4" class="d-none d-md-flex align-center justify-center pa-8 relative">
              <v-icon size="160" color="rgba(255,255,255,0.15)" class="hero-icon-animate">mdi-bullhorn-variant</v-icon>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>

    <!-- Header & Filters -->
    <v-row class="mb-6 align-center">
      <v-col cols="12" md="6">
        <h2 class="text-h4 font-weight-black text-dark-emphasis mb-1">Internal Communications</h2>
        <p class="text-subtitle-1 text-medium-emphasis mb-0">Stay updated with the latest from Boxleo</p>
      </v-col>
      <v-col cols="12" md="6" class="text-md-right d-flex justify-md-end gap-4 align-center">
         <v-tabs v-model="activeTab" color="primary" class="modern-tabs mr-4" density="compact">
          <v-tab value="all" class="text-none">All</v-tab>
          <v-tab value="published" class="text-none">Recent</v-tab>
          <v-tab value="draft" class="text-none" v-if="permissions.includes('create announcement')">Drafts</v-tab>
        </v-tabs>
        <v-btn
          v-if="permissions.includes('create announcement')"
          color="primary"
          size="large"
          prepend-icon="mdi-plus"
          class="rounded-xl px-8 shadow-glow pulse-glow transition-all font-weight-black"
          @click="createAnnouncement"
        >
          Post New Update
        </v-btn>
      </v-col>
    </v-row>

    <!-- Search Bar -->
    <v-row class="mb-8">
      <v-col cols="12" md="6" lg="4">
        <v-text-field
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          placeholder="Search announcements..."
          variant="solo-filled"
          flat
          hide-details
          rounded="xl"
          class="modern-search-bar elevation-1"
        ></v-text-field>
      </v-col>
    </v-row>

    <!-- Announcements Grid -->
    <v-row v-if="filteredAnnouncements.length">
      <v-col v-for="(item, index) in filteredAnnouncements" :key="item.id" cols="12" sm="6" lg="4" class="pa-4">
        <v-card class="announcement-grid-card h-100 animate-fade-in-up" :style="{ 'animation-delay': `${index * 0.05}s` }" @click="viewAnnouncement(item)">
          <div class="card-status-indicator" :class="getStatusClass(item)"></div>
          <v-card-text class="pa-6">
            <div class="d-flex justify-space-between align-start mb-4">
              <v-chip :color="getStatusColor(item)" size="x-small" variant="flat" class="font-weight-black text-uppercase">{{ item.status }}</v-chip>
              <div v-if="permissions.includes('edit announcement')" class="d-flex gap-1" @click.stop>
                <v-btn icon="mdi-pencil" variant="tonal" color="primary" size="x-small" class="rounded-lg" @click="editAnnouncement(item)"></v-btn>
                <v-btn icon="mdi-delete" variant="tonal" color="error" size="x-small" class="rounded-lg ml-2" @click="deleteAnnouncement(item)"></v-btn>
              </div>
            </div>
            
            <h3 class="text-h6 font-weight-black text-dark-emphasis mb-2 line-clamp-2 leading-tight">{{ item.subject }}</h3>
            <p class="text-body-2 text-medium-emphasis mb-6 line-clamp-3 ellipsis-text">
              {{ item.description }}
            </p>
            
            <v-divider class="mb-4 op-10"></v-divider>
            
            <div class="d-flex align-center justify-space-between">
              <div class="d-flex align-center">
                <v-avatar size="32" class="mr-2 border shadow-sm">
                  <v-img :src="`https://ui-avatars.com/api/?name=${item.author_name}&background=random`"></v-img>
                </v-avatar>
                <div>
                  <div class="text-caption font-weight-bold mb-0 text-dark-emphasis">{{ item.author_name }}</div>
                  <div class="text-xxs text-medium-emphasis">{{ formatDateShort(item.publish_date) }}</div>
                </div>
              </div>
              <v-icon v-if="item.attachments && item.attachments.length" size="18" color="medium-emphasis">mdi-paperclip</v-icon>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Empty State -->
    <v-row v-else class="py-16 text-center">
      <v-col cols="12">
        <v-icon size="80" color="grey-lighten-2" class="mb-4">mdi-message-off</v-icon>
        <h3 class="text-h5 text-medium-emphasis font-weight-medium">No announcements found</h3>
        <p class="text-subtitle-2 text-disabled mt-2">Adjust your filters or search keywords</p>
      </v-col>
    </v-row>


    <!-- Creation Dialog: Overhauled for a Premium Experience -->
    <v-dialog v-model="showDialog" fullscreen transition="dialog-bottom-transition">
      <v-card class="creation-workspace-bg">
        <!-- Top Navigation & Action Bar -->
        <v-toolbar color="white" border="b" elevation="0" class="px-4 sticky-toolbar">
          <v-container class="d-flex align-center pa-0 py-2">
            <v-btn icon="mdi-arrow-left" variant="text" size="small" class="mr-4" @click="closeDialog"></v-btn>
            <v-toolbar-title class="text-h6 font-weight-black d-flex align-center">
              <v-icon color="primary" class="mr-2">mdi-bullhorn-outline</v-icon>
              {{ formTitle }}
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <div class="d-flex align-center gap-3">
               <v-btn color="primary" class="rounded-lg text-none px-8 font-weight-black shadow-glow" @click="publishAnnouncement">
                 <v-icon start>mdi-send-variant</v-icon> Publish Now
               </v-btn>
            </div>
          </v-container>
        </v-toolbar>

        <v-container fluid class="pa-0 fill-height">
          <v-row no-gutters class="fill-height">
            <!-- Left: Creative Workspace -->
            <v-col cols="12" md="7" lg="8" class="pa-10 workspace-scrollable">
              <div class="max-w-3xl mx-auto">
                <div class="mb-10 animate-fade-in-up">
                  <h2 class="text-h4 font-weight-black text-dark-emphasis mb-2">Create something impactful</h2>
                  <p class="text-subtitle-1 text-medium-emphasis">Fill in the details below to broadcast your message to the team.</p>
                </div>

                <!-- Section 1: The Message -->
                <v-card border="0" elevation="2" class="rounded-xl mb-6 shadow-sm overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s">
                  <div class="pa-1 bg-primary-lighten-5"></div>
                  <v-card-text class="pa-8">
                    <div class="d-flex align-center mb-6">
                      <div class="icon-circle bg-primary-lighten-4 mr-4">
                        <v-icon color="primary">mdi-format-text</v-icon>
                      </div>
                      <div class="text-h6 font-weight-black">1. Core Content</div>
                    </div>
                    
                    <v-text-field
                      v-model="editedAnnouncement.subject"
                      label="Captivating Subject line"
                      variant="outlined"
                      class="mb-6 modern-field-v2"
                      prepend-inner-icon="mdi-format-title"
                      placeholder="e.g. Important Update: New HR Policies for 2026"
                      persistent-placeholder
                    ></v-text-field>

                    <v-textarea
                      v-model="editedAnnouncement.description"
                      label="The Message"
                      variant="outlined"
                      class="mb-6 modern-field-v2"
                      prepend-inner-icon="mdi-text-subject"
                      placeholder="Describe the update in detail. Be clear and concise..."
                      persistent-placeholder
                      rows="10"
                      auto-grow
                    ></v-textarea>

                    <v-file-input
                      v-model="attachments"
                      label="Attach Resources"
                      multiple
                      variant="outlined"
                      class="modern-field-v2"
                      prepend-icon=""
                      prepend-inner-icon="mdi-paperclip-plus"
                      show-size
                      hint="Add PDFs, images or documents for further context"
                      persistent-hint
                    ></v-file-input>
                  </v-card-text>
                </v-card>

                <!-- Section 2: Timing -->
                <v-card border="0" elevation="2" class="rounded-xl mb-6 shadow-sm overflow-hidden animate-fade-in-up" style="animation-delay: 0.2s">
                  <v-card-text class="pa-8">
                    <div class="d-flex align-center mb-6">
                      <div class="icon-circle bg-warning-lighten-4 mr-4">
                        <v-icon color="warning">mdi-calendar-clock</v-icon>
                      </div>
                      <div class="text-h6 font-weight-black">2. Scheduling & Life</div>
                    </div>
                    
                    <v-row>
                      <v-col cols="12" sm="6">
                        <v-text-field
                          v-model="editedAnnouncement.expiration_date"
                          label="Auto-Expire Date"
                          type="date"
                          variant="outlined"
                          class="modern-field-v2"
                          prepend-inner-icon="mdi-calendar-remove"
                          hint="The announcement will be hidden after this date"
                          persistent-hint
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" class="d-flex align-center pl-sm-6">
                        <div class="text-caption text-medium-emphasis">
                          Setting an expiration date helps keep the dashboard clean. Updates typicaly stay active for 14-30 days.
                        </div>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>

                <!-- Section 3: Audience -->
                <v-card border="0" elevation="2" class="rounded-xl mb-6 shadow-sm overflow-hidden animate-fade-in-up" style="animation-delay: 0.3s">
                  <v-card-text class="pa-8">
                    <div class="d-flex align-center mb-6">
                      <div class="icon-circle bg-success-lighten-4 mr-4">
                        <v-icon color="success">mdi-account-group-outline</v-icon>
                      </div>
                      <div class="text-h6 font-weight-black">3. Targeting Audience</div>
                    </div>
                    
                    <v-autocomplete
                      v-model="formData.unit_ids"
                      :items="units"
                      label="Specific Branches"
                      variant="outlined"
                      multiple
                      item-title="name"
                      item-value="id"
                      placeholder="All Branches"
                      class="mb-6 modern-field-v2"
                      prepend-inner-icon="mdi-office-building-marker"
                      chips
                      closable-chips
                    />

                    <v-autocomplete
                      v-model="formData.department_ids"
                      :items="departments"
                      label="Specific Departments"
                      variant="outlined"
                      multiple
                      item-title="name"
                      item-value="id"
                      placeholder="All Departments"
                      class="modern-field-v2"
                      prepend-inner-icon="mdi-briefcase-variant-outline"
                      chips
                      closable-chips
                    />

                    <!-- Email Forwarding Option -->
                    <v-divider class="my-6"></v-divider>
                    <div class="d-flex align-center">
                      <v-checkbox
                        v-model="formData.send_email"
                        color="primary"
                        label="Forward to Audience Emails"
                        hide-details
                        density="compact"
                        class="send-email-checkbox"
                      ></v-checkbox>
                      <v-tooltip location="right">
                        <template v-slot:activator="{ props }">
                          <v-icon v-bind="props" size="18" color="medium-emphasis" class="ml-2">mdi-information-outline</v-icon>
                        </template>
                        <span>If checked, all members of the target audience will receive an email notification in addition to the dashboard update.</span>
                      </v-tooltip>
                    </div>
                  </v-card-text>
                </v-card>

                <!-- Bottom Actions: Duplicated for better accessibility -->
                <div class="d-flex justify-end gap-3 mb-16 animate-fade-in-up" style="animation-delay: 0.4s">
                   <v-btn variant="tonal" color="medium-emphasis" class="rounded-lg text-none px-6 font-weight-bold" @click="saveAnnouncement">
                     <v-icon start>mdi-content-save-outline</v-icon> Save as Draft
                   </v-btn>
                   <v-btn color="primary" class="rounded-lg text-none px-8 font-weight-black shadow-glow" @click="publishAnnouncement">
                     <v-icon start>mdi-send-variant</v-icon> Publish Now
                   </v-btn>
                </div>
              </div>
            </v-col>

            <!-- Right: Live Preview Sidebar -->
            <v-col cols="12" md="5" lg="4" class="preview-sidebar d-none d-md-flex flex-column pa-10 border-s">
              <div class="text-overline font-weight-black text-primary mb-6 d-flex align-center">
                <span class="live-blink mr-2"></span> REAL-TIME PREVIEW
              </div>
              
              <div class="preview-container flex-grow-1 d-flex flex-column justify-center align-center">
                <div class="device-frame">
                   <div class="device-screen pa-4">
                      <!-- Grid View Preview -->
                      <div class="text-caption font-weight-bold text-medium-emphasis mb-4">Board View Snippet</div>
                      <v-card class="announcement-grid-card shadow-lg bg-white overflow-hidden mb-10" border elevation="4">
                         <div class="card-status-indicator bg-primary"></div>
                         <v-card-text class="pa-4">
                            <v-chip color="primary" size="x-small" variant="flat" class="font-weight-black text-uppercase mb-2">PUBLISHED</v-chip>
                            <h3 class="text-subtitle-1 font-weight-black mb-1 line-clamp-2">
                              {{ editedAnnouncement.subject || 'Subject title...' }}
                            </h3>
                            <p class="text-caption text-medium-emphasis mb-4 line-clamp-2 opacity-60">
                              {{ editedAnnouncement.description || 'Short summary of your message will appear here...' }}
                            </p>
                            <div class="d-flex align-center">
                              <v-avatar size="24" class="mr-2 border bg-grey-lighten-4">
                                 <v-icon size="14">mdi-account</v-icon>
                              </v-avatar>
                              <div class="text-xxs font-weight-bold">Admin User</div>
                            </div>
                         </v-card-text>
                      </v-card>

                      <!-- Full View Preview Indicator -->
                      <div class="text-caption font-weight-bold text-medium-emphasis mb-4">Immersive View snippet</div>
                      <div class="full-view-mini shadow-lg rounded-lg overflow-hidden border">
                         <div class="pa-4 text-white text-center" style="background: #0D8ABC">
                            <div class="text-xxs font-weight-black opacity-80 mb-1">ANNOUNCEMENT</div>
                            <div class="text-caption font-weight-black truncate px-2">{{ editedAnnouncement.subject || 'Subject...' }}</div>
                         </div>
                         <div class="pa-4 bg-white">
                            <div class="mini-line mb-1" v-for="i in 4" :key="i"></div>
                            <div class="mini-line w-75" v-for="i in 1" :key="'w'+i"></div>
                         </div>
                      </div>
                   </div>
                </div>
              </div>
              
              <div class="mt-10 pa-6 bg-primary-lighten-5 rounded-xl border border-primary-lighten-4">
                 <div class="d-flex align-center mb-2">
                   <v-icon color="primary" size="20" class="mr-2">mdi-lightbulb-on-outline</v-icon>
                   <span class="text-subtitle-2 font-weight-black">Pro Tip</span>
                 </div>
                 <p class="text-caption text-medium-emphasis mb-0">
                   Use clear subject lines and provide all necessary details in the description to reduce follow-up questions from the team.
                 </p>
              </div>
            </v-col>
          </v-row>
        </v-container>
      </v-card>
    </v-dialog>

    <!-- Reading Mode: View Announcement Dialog -->
    <v-dialog v-model="showViewDialog" max-width="800px" transition="slide-y-transition">
      <v-card v-if="viewedAnnouncement" class="reader-card rounded-xl overflow-hidden shadow-2xl">
        <div class="reader-header" :style="{ backgroundColor: getStatusColorHex(viewedAnnouncement) }">
          <v-btn icon="mdi-close" color="white" variant="text" class="close-btn" @click="showViewDialog = false"></v-btn>
          <div class="header-content text-center pa-10 text-white">
             <v-chip color="white" variant="outlined" size="small" class="mb-4 font-weight-black op-80 text-uppercase">{{ viewedAnnouncement.status }}</v-chip>
             <h2 class="text-h3 font-weight-black mb-4">{{ viewedAnnouncement.subject }}</h2>
             <div class="d-flex justify-center align-center">
                <v-avatar size="44" class="mr-3 border-2 border-white border-opacity-20 shadow-lg">
                  <v-img :src="`https://ui-avatars.com/api/?name=${viewedAnnouncement.author_name}&background=random`"></v-img>
                </v-avatar>
                <div class="text-left">
                  <div class="text-subtitle-1 font-weight-bold op-90 leading-tight">{{ viewedAnnouncement.author_name }}</div>
                  <div class="text-caption op-70">{{ formatDate(viewedAnnouncement.publish_date) }}</div>
                </div>
             </div>
          </div>
        </div>
        
        <v-card-text class="pa-10 bg-white">
          <div class="announcement-body text-body-1 text-dark-emphasis leading-relaxed mb-8 preserve-whitespace">
            {{ viewedAnnouncement.description }}
          </div>

          <v-divider class="mb-8"></v-divider>

          <div v-if="viewedAnnouncement.attachments && viewedAnnouncement.attachments.length">
             <h4 class="text-subtitle-1 font-weight-black mb-4 d-flex align-center">
               <v-icon class="mr-2" color="primary">mdi-paperclip</v-icon> Attached Documents
             </h4>
             <v-row dense>
               <v-col v-for="attachment in viewedAnnouncement.attachments" :key="attachment.id" cols="12" sm="6">
                 <v-card border flat class="attachment-item-card rounded-lg pa-3 transition-colors hover-bg-light" @click="openAttachment(attachment.file_path)">
                   <div class="d-flex align-center">
                     <div class="file-icon-box bg-light-primary text-primary mr-3 rounded">
                       <v-icon size="20">{{ getFileIcon(attachment.file_type) }}</v-icon>
                     </div>
                     <div class="flex-grow-1 overflow-hidden">
                       <div class="text-caption font-weight-black text-truncate">{{ attachment.original_filename }}</div>
                       <div class="text-xxs text-medium-emphasis">{{ formatFileSize(attachment.file_size) }}</div>
                     </div>
                     <v-icon size="18" color="medium-emphasis">mdi-download</v-icon>
                   </div>
                 </v-card>
               </v-col>
             </v-row>
          </div>
          
          <!-- Metadata Chips -->
          <div class="mt-8 d-flex flex-wrap gap-2 pt-4 border-top">
             <div class="text-caption text-medium-emphasis mr-2 d-flex align-center">Target Groups:</div>
             <v-chip v-for="unit in viewedAnnouncement.units" :key="'u'+unit.id" size="x-small" label class="text-xxs font-weight-bold">Branch: {{ unit.name }}</v-chip>
             <v-chip v-for="dept in viewedAnnouncement.departments" :key="'d'+dept.id" size="x-small" label class="text-xxs font-weight-bold">Dept: {{ dept.name }}</v-chip>
          </div>
        </v-card-text>
        
        <v-card-actions class="pa-6 bg-slate-50 border-top justify-center">
          <v-btn color="primary" variant="tonal" class="rounded-lg px-8 py-2 font-weight-black" @click="showViewDialog = false">Finished Reading</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ModernAnnouncements',
  props: {
    user: Object,
    roles: Array,
    permissions: Array
  },

  data() {
    return {
      activeTab: 'published',
      showDialog: false,
      showViewDialog: false,
      search: '',
      announcements: [],
      departments: [],
      units: [],
      attachments: [],
      viewedAnnouncement: null,
      editedIndex: -1,
      editedAnnouncement: {
        subject: '',
        description: '',
        expiration_date: '',
        is_active: false,
      },
      defaultAnnouncement: {
        subject: '',
        description: '',
        expiration_date: '',
        is_active: false,
      },
      formData: {
        department_ids: [],
        unit_ids: [],
        send_email: true,
      }
    };
  },

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? 'Compose New Announcement' : 'Modify Announcement';
    },
    featuredAnnouncement() {
      // Find the latest published announcement
      return [...this.announcements]
        .filter(a => a.status === 'published' && a.is_active)
        .sort((a, b) => new Date(b.publish_date) - new Date(a.publish_date))[0];
    },
    filteredAnnouncements() {
      let filtered = [...this.announcements];
      
      // Filter by tab
      if (this.activeTab === 'published') {
        filtered = filtered.filter(a => a.status === 'published' && a.is_active);
      } else if (this.activeTab === 'draft') {
        filtered = filtered.filter(a => a.status === 'draft');
      }

      // Filter by search
      if (this.search) {
        const query = this.search.toLowerCase();
        filtered = filtered.filter(a => 
          a.subject.toLowerCase().includes(query) || 
          a.description.toLowerCase().includes(query) ||
          a.author_name.toLowerCase().includes(query)
        );
      }

      // Exclude featured from the grid if we are in "Recent" tab to avoid duplication
      if (this.activeTab === 'published' && this.featuredAnnouncement) {
        filtered = filtered.filter(a => a.id !== this.featuredAnnouncement.id);
      }

      return filtered.sort((a, b) => new Date(b.publish_date) - new Date(a.publish_date));
    }
  },

  mounted() {
    this.fetchAnnouncements();
    this.fetchDepartments();
    this.fetchUnits();
  },

  methods: {
    fetchAnnouncements() {
      axios.get('/api/v1/announcements')
        .then(response => {
          this.announcements = response.data;
        })
        .catch(error => console.error('Fetch Announcements Error:', error));
    },

    fetchDepartments() {
      axios.get('/api/v1/departments')
        .then(response => {
           if (response.data && response.data.departments) {
             this.departments = response.data.departments;
           }
        });
    },

    fetchUnits() {
      axios.get('/api/v1/branches')
        .then(response => {
          if (response.data && response.data.branches) {
            this.units = response.data.branches;
          }
        });
    },

    createAnnouncement() {
      this.editedIndex = -1;
      this.editedAnnouncement = Object.assign({}, this.defaultAnnouncement);
      this.attachments = [];
      this.formData = { department_ids: [], unit_ids: [], send_email: true };
      this.showDialog = true;
    },

    editAnnouncement(item) {
      this.editedIndex = this.announcements.indexOf(item);
      this.editedAnnouncement = Object.assign({}, item);

      this.formData = {
        department_ids: item.departments ? item.departments.map(dept => dept.id) : [],
        unit_ids: item.units ? item.units.map(unit => unit.id) : [],
        send_email: true
      };
      this.attachments = []; // Laravel handling of edit attachments varies, usually start fresh or manage existing ones
      this.showDialog = true;
    },

    viewAnnouncement(item) {
      this.viewedAnnouncement = item;
      this.showViewDialog = true;
    },

    deleteAnnouncement(item) {
      if (!confirm('Are you sure you want to remove this announcement permanently?')) return;
      axios.delete(`/api/v1/announcements/${item.id}`)
        .then(() => {
          const index = this.announcements.indexOf(item);
          this.announcements.splice(index, 1);
        })
        .catch(error => console.error('Delete Error:', error));
    },

    saveAnnouncement() { this.submitAnnouncement('save_draft'); },
    publishAnnouncement() { this.submitAnnouncement('publish'); },

    submitAnnouncement(action) {
      const fd = new FormData();
      for (const key in this.editedAnnouncement) {
        if (this.editedAnnouncement[key] !== null) fd.append(key, this.editedAnnouncement[key]);
      }
      fd.append('action', action);
      
      this.formData.department_ids.forEach(id => fd.append('department_ids[]', id));
      this.formData.unit_ids.forEach(id => fd.append('unit_ids[]', id));
      fd.append('send_email', this.formData.send_email ? 1 : 0);
      
      if (this.attachments && this.attachments.length) {
        for (let i = 0; i < this.attachments.length; i++) {
          fd.append('attachments[]', this.attachments[i]);
        }
      }

      const url = this.editedIndex > -1
        ? `/api/v1/announcements/${this.editedAnnouncement.id}?_method=PUT`
        : '/api/v1/announcements';

      axios.post(url, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
      .then(() => {
        this.fetchAnnouncements();
        this.closeDialog();
      })
      .catch(error => console.error('Submit Error:', error));
    },

    closeDialog() {
      this.showDialog = false;
    },

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'long', year: 'numeric' });
    },

    formatDateShort(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short' });
    },

    getStatusColor(item) {
      if (item.status === 'draft') return 'warning';
      if (!item.is_active) return 'error';
      return 'secondary';
    },

    getStatusColorHex(item) {
      if (item.status === 'draft') return '#F59E0B';
      if (!item.is_active) return '#EF4444';
      return '#0D8ABC';
    },

    getStatusClass(item) {
      if (item.status === 'draft') return 'bg-warning';
      if (!item.is_active) return 'bg-error';
      return 'bg-secondary';
    },

    getFileIcon(mime) {
      if (mime.includes('pdf')) return 'mdi-file-pdf-box';
      if (mime.includes('image')) return 'mdi-file-image';
      if (mime.includes('word')) return 'mdi-file-word';
      return 'mdi-file-document';
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return (bytes / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'KB', 'MB', 'GB'][i];
    },

    openAttachment(path) {
      window.open(`/storage/${path}`, '_blank');
    }
  }
}
</script>

<style scoped>
.announcements-container {
  background-color: #f8fafc;
  background-image: 
    radial-gradient(at 0% 0%, rgba(13, 138, 188, 0.05) 0px, transparent 50%),
    radial-gradient(at 50% 0%, rgba(16, 185, 129, 0.05) 0px, transparent 50%),
    radial-gradient(at 100% 0%, rgba(13, 138, 188, 0.05) 0px, transparent 50%);
  min-height: 100vh;
}

/* Card Styling */
.announcement-card, .announcement-grid-card {
  border-radius: 20px !important;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  overflow: hidden;
  border: 1px solid rgba(0,0,0,0.05) !important;
}

.announcement-grid-card {
  cursor: pointer;
  background: white;
}

.announcement-grid-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
  border-color: rgba(var(--v-theme-primary), 0.2) !important;
}

/* Hero Section */
.hero-card {
  min-height: 400px;
  background: linear-gradient(135deg, #0D8ABC 0%, #61DAFB 100%) !important;
  cursor: pointer;
}

.hero-bg-overlay {
  position: absolute;
  top: 0; right: 0; bottom: 0; left: 0;
  background-image: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.1) 0%, transparent 40%),
                    radial-gradient(circle at 20% 80%, rgba(0,0,0,0.05) 0%, transparent 40%);
}

.hero-title {
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
  letter-spacing: -1px;
}

.hero-icon-animate {
  animation: pulseBullhorn 4s infinite ease-in-out;
}

@keyframes pulseBullhorn {
  0%, 100% { transform: scale(1) rotate(-5deg); opacity: 0.15; }
  50% { transform: scale(1.1) rotate(5deg); opacity: 0.25; }
}

/* Status Indicators */
.card-status-indicator {
  height: 4px;
  width: 100%;
}

.bg-secondary { background-color: #0D8ABC !important; }

/* Tabs & Fields */
.modern-tabs :deep(.v-tab) {
  font-weight: 700 !important;
  letter-spacing: 0.5px;
  color: #64748b;
}

.modern-tabs :deep(.v-tab--selected) {
  color: #0D8ABC !important;
}

.modern-search-bar :deep(.v-field) {
  border-radius: 40px !important;
  background-color: white !important;
}

.modern-field :deep(.v-field__outline) {
  border-radius: 12px !important;
}

/* Reader Mode */
.reader-card {
  border: none !important;
}

.reader-header {
  position: relative;
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.reader-header h2 {
  line-height: 1.1;
  letter-spacing: -1.5px;
}

.close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(0,0,0,0.1);
}

.announcement-body {
  font-size: 1.15rem;
  color: #334155;
}

.preserve-whitespace {
  white-space: pre-wrap;
}

/* Animations */
.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out both;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-6 {
  display: -webkit-box;
  -webkit-line-clamp: 6;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.shadow-glow {
  box-shadow: 0 4px 15px rgba(13, 138, 188, 0.3) !important;
}

.pulse-glow {
  animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
  0% { box-shadow: 0 0 0 0 rgba(13, 138, 188, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(13, 138, 188, 0); }
  100% { box-shadow: 0 0 0 0 rgba(13, 138, 188, 0); }
}

.gap-4 { gap: 16px; }
.op-80 { opacity: 0.8; }
.op-70 { opacity: 0.7; }
.op-10 { opacity: 0.1; }

/* Creation Workspace Overhaul */
.creation-workspace-bg {
  background-color: #f1f5f9 !important;
}

.workspace-scrollable {
  height: calc(100vh - 64px);
  overflow-y: auto;
  scroll-behavior: smooth;
}

.preview-sidebar {
  background-color: #ffffff;
  height: calc(100vh - 64px);
}

.sticky-toolbar {
  position: sticky;
  top: 0;
  z-index: 100;
}

.icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-primary-lighten-4 { background-color: #e0f2fe; }
.bg-primary-lighten-5 { background-color: #f0f9ff; }
.bg-warning-lighten-4 { background-color: #fef3c7; }
.bg-success-lighten-4 { background-color: #dcfce7; }

.modern-field-v2 :deep(.v-field__outline) {
  border-radius: 12px !important;
  transition: all 0.2s;
  border-color: rgba(0,0,0,0.1) !important;
}

.modern-field-v2 :deep(.v-field--focused .v-field__outline) {
  border-color: #0D8ABC !important;
  box-shadow: 0 0 0 4px rgba(13, 138, 188, 0.1);
}

.modern-field-v2 :deep(.v-label) {
  font-weight: 600;
  color: #64748b;
}

.preview-container {
  height: 100%;
}

.device-frame {
  width: 320px;
  height: 600px;
  background-color: #1e293b;
  border-radius: 40px;
  padding: 12px;
  box-shadow: 0 50px 100px -20px rgba(0,0,0,0.25);
  position: relative;
}

.device-frame::before {
  content: '';
  position: absolute;
  top: 25px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 20px;
  background-color: #0f172a;
  border-radius: 10px;
  z-index: 2;
}

.device-screen {
  width: 100%;
  height: 100%;
  background-color: #f8fafc;
  border-radius: 30px;
  overflow-y: auto;
  position: relative;
}

.live-blink {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #ef4444;
  animation: blink 1.5s infinite;
}

@keyframes blink {
  0% { opacity: 1; }
  50% { opacity: 0.3; }
  100% { opacity: 1; }
}

.full-view-mini {
  height: 160px;
}

.mini-line {
  height: 4px;
  background-color: #e2e8f0;
  border-radius: 2px;
  margin-bottom: 6px;
}

.w-75 { width: 75%; }

.border-s {
  border-left: 1px solid rgba(0,0,0,0.05);
}

.max-w-3xl { max-width: 48rem; }
.shadow-sm { box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05) !important; }
</style>
