<template>
  <v-container fluid class="pa-0 staff-dev-wrap">

    <!-- ═══ HERO BANNER ════════════════════════════════════════════════════ -->
    <div class="sd-hero">
      <div class="sd-hero-content">
        <div class="sd-hero-text">
          <h1 class="sd-hero-title">Staff Development</h1>
          <p class="sd-hero-subtitle">Grow your skills, earn certificates, and track your learning journey.</p>
        </div>
        <div class="sd-hero-stats">
          <div class="sd-stat-chip" v-for="s in heroStats" :key="s.label">
            <v-icon :icon="s.icon" size="20" class="me-1"/>
            <span class="sd-stat-val">{{ s.value }}</span>
            <span class="sd-stat-lbl">{{ s.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ TAB NAV ════════════════════════════════════════════════════════ -->
    <div class="sd-tab-bar px-4">
      <v-tabs v-model="activeTab" color="white" slider-color="white" class="sd-tabs">
        <v-tab value="overview"><v-icon start>mdi-view-dashboard</v-icon>Overview</v-tab>
        <v-tab value="catalog"><v-icon start>mdi-bookshelf</v-icon>Course Catalog</v-tab>
        <v-tab value="my-learning"><v-icon start>mdi-school</v-icon>My Learning</v-tab>
        <v-tab value="certificates"><v-icon start>mdi-certificate</v-icon>Certificates</v-tab>
        <v-tab v-if="isAdmin" value="manage"><v-icon start>mdi-cog</v-icon>Manage</v-tab>
      </v-tabs>
    </div>

    <div class="pa-4">

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- TAB: OVERVIEW                                                      -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-if="activeTab === 'overview'">

        <!-- My progress cards -->
        <v-row class="mb-6">
          <v-col cols="12" sm="6" md="3" v-for="card in progressCards" :key="card.label">
            <v-card class="sd-progress-card" elevation="0" rounded="xl">
              <div class="sd-progress-card-icon" :style="{ background: card.gradient }">
                <v-icon :icon="card.icon" size="28" color="white"/>
              </div>
              <div class="sd-progress-card-body">
                <div class="sd-progress-card-val">{{ card.value }}</div>
                <div class="sd-progress-card-lbl">{{ card.label }}</div>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- External Learning Platforms (Moved Higher for Visibility) -->
        <div class="sd-section-header mb-3 mt-4">
          <span class="sd-section-title">External Learning Platforms</span>
          <p class="text-caption text-grey">Explore free and professional courses from our trusted partners</p>
        </div>
        <v-row class="mb-6">
          <v-col cols="12" sm="6">
            <v-card class="sd-ext-card alison-card" elevation="0" rounded="xl" href="https://alison.com" target="_blank"
              style="background-image: linear-gradient(to right, rgba(46, 125, 50, 0.95), rgba(46, 125, 50, 0.4)), url('/assets/img/development/alison.png'); background-size: cover; background-position: center;">
              <div class="sd-ext-icon">
                <v-icon icon="mdi-school-outline" size="32" color="white"/>
              </div>
              <div class="sd-ext-content">
                <div class="sd-ext-label">FREE LEARNING</div>
                <div class="sd-ext-name">Alison</div>
                <div class="sd-ext-desc">Access 4,000+ free online courses with certificates and diplomas.</div>
                <div class="sd-ext-footer">
                  <span>Visit alison.com</span>
                  <v-icon size="16">mdi-open-in-new</v-icon>
                </div>
              </div>
            </v-card>
          </v-col>
          <v-col cols="12" sm="6">
            <v-card class="sd-ext-card gl-card" elevation="0" rounded="xl" href="https://www.mygreatlearning.com" target="_blank"
              style="background-image: linear-gradient(to right, rgba(26, 35, 126, 0.95), rgba(26, 35, 126, 0.4)), url('/assets/img/development/great_learning.png'); background-size: cover; background-position: center;">
              <div class="sd-ext-icon">
                <v-icon icon="mdi-brain" size="32" color="white"/>
              </div>
              <div class="sd-ext-content">
                <div class="sd-ext-label">PROFESSIONAL GROWTH</div>
                <div class="sd-ext-name">My Great Learning</div>
                <div class="sd-ext-desc">Earn professional certificates from top world-class universities.</div>
                <div class="sd-ext-footer">
                  <span>Visit mygreatlearning.com</span>
                  <v-icon size="16">mdi-open-in-new</v-icon>
                </div>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Featured courses -->
        <div class="sd-section-header mb-3">
          <span class="sd-section-title">Featured Courses</span>
          <v-btn variant="text" color="primary" size="small" @click="activeTab='catalog'">
            View All <v-icon end>mdi-arrow-right</v-icon>
          </v-btn>
        </div>

        <v-row v-if="loadingCourses">
          <v-col cols="12" sm="6" md="4" v-for="n in 3" :key="n">
            <v-skeleton-loader type="card" rounded="xl"/>
          </v-col>
        </v-row>

        <v-row v-else>
          <v-col cols="12" sm="6" md="4" v-for="course in featuredCourses" :key="course.id">
            <CourseCard :course="course" @open="openCourse" @enroll="enrollCourse"/>
          </v-col>
          <v-col cols="12" v-if="featuredCourses.length === 0">
            <v-card rounded="xl" class="sd-empty-card" elevation="0">
              <v-icon size="56" color="grey-lighten-2">mdi-bookshelf</v-icon>
              <p class="text-grey mt-2">No featured courses yet.</p>
            </v-card>
          </v-col>
        </v-row>

        <!-- Continue Learning -->
        <template v-if="myCoursesList.length > 0">
          <div class="sd-section-header mb-3 mt-6">
            <span class="sd-section-title">Continue Learning</span>
            <v-btn variant="text" color="primary" size="small" @click="activeTab='my-learning'">
              View All <v-icon end>mdi-arrow-right</v-icon>
            </v-btn>
          </div>
          <v-row>
            <v-col cols="12" md="6" v-for="item in myCoursesList.slice(0,4)" :key="item.enrollment_id">
              <v-card class="sd-continue-card" elevation="0" rounded="xl" @click="openCourse(item.course)">
                <div class="sd-continue-thumb" :style="courseThumbStyle(item.course)">
                  <v-chip size="x-small" color="white" class="ma-2" label>{{ item.course.category }}</v-chip>
                </div>
                <div class="pa-3 flex-grow-1">
                  <div class="sd-continue-title">{{ item.course.title }}</div>
                  <div class="d-flex align-center mt-2 gap-2">
                    <v-progress-linear :model-value="item.progress" rounded height="6"
                      :color="item.progress === 100 ? 'success' : 'primary'" bg-color="grey-lighten-3" class="flex-grow-1"/>
                    <span class="sd-prog-pct">{{ item.progress }}%</span>
                  </div>
                  <div class="sd-continue-meta mt-1">
                    {{ item.completed_lessons }}/{{ item.total_lessons }} lessons
                    <v-chip v-if="item.status === 'completed'" size="x-small" color="success" class="ms-2">Completed</v-chip>
                  </div>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </template>

        <!-- Top Recommended Courses from Partners -->
        <div class="sd-section-header mb-3 mt-10">
          <div class="d-flex align-center gap-3">
            <span class="sd-section-title">Professional External Courses</span>
            <v-chip-group v-model="selectedExternalCategory" selected-class="primary--text" mandatory class="category-chips-group">
              <v-chip v-for="cat in externalCategories" :key="cat" :value="cat" size="small" variant="tonal" rounded="lg"
                class="px-4 font-weight-bold">
                {{ cat }}
              </v-chip>
            </v-chip-group>
          </div>
          <p class="text-caption text-grey">Start learning today with these highly-rated courses from our partners</p>
        </div>

        <v-row class="mb-6">
          <v-col cols="12" sm="6" md="4" v-for="(course, i) in filteredExternalCourses" :key="course.platform + '-' + i">
            <v-card class="sd-course-card shadow-sm" rounded="xl" :href="course.url" target="_blank">
              <v-img :src="course.thumb" height="160" cover class="align-end">
                <v-chip size="x-small" :color="course.platform === 'Alison' ? 'success' : 'indigo'" class="ma-2 font-weight-bold" label>
                  {{ course.platform.toUpperCase() }}
                </v-chip>
              </v-img>
              <div class="pa-4">
                <div class="d-flex align-center justify-space-between mb-1">
                  <div class="text-caption text-uppercase font-weight-bold" :class="course.platform === 'Alison' ? 'text-success' : 'text-indigo'">
                    {{ course.category }}
                  </div>
                  <v-chip v-if="course.is_free" size="x-small" variant="flat" color="grey-lighten-4" class="text-grey-darken-2 font-weight-bold">FREE</v-chip>
                </div>
                <div class="sd-course-title mb-2">{{ course.title }}</div>
                <div class="sd-course-footer">
                  <div class="d-flex align-center">
                    <v-icon size="14" color="amber" class="me-1">mdi-star</v-icon>
                    <span class="text-caption font-weight-bold">4.8</span>
                  </div>
                  <v-icon size="18" color="grey-lighten-1">mdi-arrow-top-right</v-icon>
                </div>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- TAB: COURSE CATALOG                                                -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-if="activeTab === 'catalog'">
        <!-- Filters -->
        <v-row class="mb-4" align="center">
          <v-col cols="12" md="4">
            <v-text-field v-model="catalogSearch" prepend-inner-icon="mdi-magnify" label="Search courses…"
              density="compact" variant="outlined" rounded hide-details clearable @input="debouncedFetchCourses"/>
          </v-col>
          <v-col cols="6" md="3">
            <v-select v-model="filterCategory" :items="['All', ...categories]" label="Category"
              density="compact" variant="outlined" rounded hide-details @update:modelValue="fetchCourses"/>
          </v-col>
          <v-col cols="6" md="3">
            <v-select v-model="filterLevel" :items="['All', 'Beginner', 'Intermediate', 'Advanced']" label="Level"
              density="compact" variant="outlined" rounded hide-details @update:modelValue="fetchCourses"/>
          </v-col>
          <v-col cols="12" md="2">
            <v-btn-toggle v-model="catalogView" mandatory density="compact" rounded="xl">
              <v-btn value="grid" icon="mdi-view-grid"/>
              <v-btn value="list" icon="mdi-view-list"/>
            </v-btn-toggle>
          </v-col>
        </v-row>

        <!-- Category chips -->
        <div class="sd-category-chips mb-4">
          <v-chip v-for="cat in ['All', ...categories]" :key="cat"
            :color="filterCategory === cat ? 'primary' : 'default'"
            :variant="filterCategory === cat ? 'flat' : 'outlined'"
            size="small" class="me-2 mb-1" @click="filterCategory = cat; fetchCourses()">
            {{ cat }}
          </v-chip>
        </div>

        <v-row v-if="loadingCourses">
          <v-col cols="12" :sm="catalogView==='grid'?6:12" :md="catalogView==='grid'?4:12" v-for="n in 6" :key="n">
            <v-skeleton-loader type="card" rounded="xl"/>
          </v-col>
        </v-row>

        <template v-else-if="allCourses.length > 0">
          <!-- Grid view -->
          <v-row v-if="catalogView === 'grid'">
            <v-col cols="12" sm="6" md="4" v-for="course in allCourses" :key="course.id">
              <CourseCard :course="course" @open="openCourse" @enroll="enrollCourse"/>
            </v-col>
          </v-row>

          <!-- List view -->
          <div v-else class="sd-list-view">
            <v-card v-for="course in allCourses" :key="course.id"
              class="sd-list-item mb-3" elevation="0" rounded="xl" @click="openCourse(course)">
              <div class="sd-list-thumb" :style="courseThumbStyle(course)">
                <span class="sd-level-badge" :class="levelClass(course.level)">{{ course.level }}</span>
              </div>
              <div class="sd-list-body">
                <div class="sd-list-cat text-caption text-primary">{{ course.category }}</div>
                <div class="sd-list-title">{{ course.title }}</div>
                <div class="sd-list-desc text-caption text-grey">{{ truncate(course.description, 100) }}</div>
                <div class="d-flex align-center gap-3 mt-2">
                  <span class="text-caption"><v-icon size="14">mdi-clock-outline</v-icon> {{ course.duration_hours }}h</span>
                  <span class="text-caption"><v-icon size="14">mdi-book-open-outline</v-icon> {{ course.lessons_count }} lessons</span>
                  <span class="text-caption"><v-icon size="14">mdi-account-group</v-icon> {{ course.enrollments_count }} enrolled</span>
                </div>
              </div>
              <div class="sd-list-action">
                <v-btn v-if="!course.enrolled" color="primary" rounded @click.stop="enrollCourse(course)">Enroll</v-btn>
                <v-btn v-else color="success" variant="tonal" rounded @click.stop="openCourse(course)">
                  {{ course.progress === 100 ? 'Completed' : 'Continue' }}
                </v-btn>
              </div>
            </v-card>
          </div>
        </template>

        <v-card v-else rounded="xl" class="sd-empty-card" elevation="0">
          <v-icon size="64" color="grey-lighten-2">mdi-magnify</v-icon>
          <p class="text-grey mt-3">No courses match your filters.</p>
        </v-card>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- TAB: MY LEARNING                                                   -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-if="activeTab === 'my-learning'">

        <!-- Top bar: filter tabs + enroll button -->
        <div class="d-flex align-center justify-space-between mb-4 flex-wrap gap-3">
          <v-tabs v-model="myLearningFilter" density="compact" v-if="!loadingMyCourses">
            <v-tab value="all">All ({{ myCoursesList.length }})</v-tab>
            <v-tab value="active">In Progress ({{ myCoursesInProgress.length }})</v-tab>
            <v-tab value="completed">Completed ({{ myCoursesCompleted.length }})</v-tab>
          </v-tabs>
          <v-spacer/>
          <v-btn color="primary" rounded prepend-icon="mdi-plus" @click="enrollDialog = true">
            Enroll in a Course
          </v-btn>
        </div>

        <v-row v-if="loadingMyCourses">
          <v-col cols="12" md="6" v-for="n in 4" :key="n">
            <v-skeleton-loader type="card" rounded="xl"/>
          </v-col>
        </v-row>

        <template v-else-if="myCoursesList.length > 0">
          <v-row>
            <v-col cols="12" md="6" v-for="item in myCoursesFiltered" :key="item.enrollment_id">
              <v-card class="sd-my-course-card" elevation="0" rounded="xl">
                <!-- Thumbnail -->
                <div class="sd-my-course-thumb" :style="courseThumbStyle(item.course)">
                  <div class="sd-my-course-overlay">
                    <v-progress-circular :model-value="item.progress" :size="64" :width="6"
                      :color="item.progress === 100 ? 'success' : 'white'" bg-color="rgba(255,255,255,0.3)">
                      <span class="text-white font-weight-bold text-caption">{{ item.progress }}%</span>
                    </v-progress-circular>
                  </div>
                  <v-chip size="x-small" color="white" class="ma-2" label>{{ item.course.category }}</v-chip>
                  <v-chip v-if="item.status === 'completed'" size="x-small" color="success" class="ma-2">
                    <v-icon start size="12">mdi-check-circle</v-icon>Done
                  </v-chip>
                </div>

                <div class="pa-4">
                  <div class="sd-my-course-title">{{ item.course.title }}</div>
                  <div class="d-flex align-center mt-1 mb-3 gap-2 text-caption text-grey">
                    <v-icon size="14">mdi-clock-outline</v-icon>{{ item.course.duration_hours }}h
                    <v-icon size="14" class="ms-2">mdi-book-open-outline</v-icon>{{ item.completed_lessons }}/{{ item.total_lessons }} lessons
                  </div>
                  <v-progress-linear :model-value="item.progress" rounded height="8"
                    :color="item.progress === 100 ? 'success' : 'primary'" bg-color="grey-lighten-3" class="mb-3"/>

                  <!-- Action buttons -->
                  <div class="d-flex gap-2 mb-3">
                    <v-btn color="primary" variant="flat" rounded size="small" @click="openCourse(item.course)" class="flex-grow-1">
                      {{ item.progress === 100 ? 'Review' : 'Continue Learning' }}
                    </v-btn>
                    <v-btn v-if="!item.has_certificate" color="success" variant="outlined" rounded size="small"
                      @click="openCertUpload(item)">
                      <v-icon>mdi-certificate-outline</v-icon>
                    </v-btn>
                    <v-btn v-else color="amber-darken-2" variant="outlined" rounded size="small"
                      @click="activeTab='certificates'">
                      <v-icon>mdi-certificate</v-icon>
                    </v-btn>
                  </div>

                  <!-- Notes section -->
                  <v-expansion-panels variant="accordion" class="mb-2">
                    <v-expansion-panel rounded="lg">
                      <v-expansion-panel-title class="text-caption font-weight-medium py-2" min-height="36">
                        <v-icon size="16" class="me-2">mdi-note-text-outline</v-icon>
                        My Notes {{ item.notes ? '·' : '' }}
                        <span v-if="item.notes" class="text-grey ms-1">{{ item.notes.substring(0, 30) }}{{ item.notes.length > 30 ? '…' : '' }}</span>
                      </v-expansion-panel-title>
                      <v-expansion-panel-text>
                        <v-textarea
                          :model-value="item.notes"
                          @update:model-value="val => item.notes = val"
                          @blur="saveNotes(item)"
                          placeholder="Add your study notes, key takeaways, or questions here…"
                          rows="4" auto-grow hide-details variant="outlined" density="compact"
                          class="mt-1" style="font-size:13px"
                        />
                        <div class="text-right mt-1">
                          <span class="text-caption text-grey">Auto-saves on blur</span>
                        </div>
                      </v-expansion-panel-text>
                    </v-expansion-panel>
                  </v-expansion-panels>

                  <!-- Weekly reminder toggle -->
                  <div class="d-flex align-center justify-space-between px-1 mt-1">
                    <div class="d-flex align-center gap-2">
                      <v-icon size="16" :color="item.reminder_enabled ? 'primary' : 'grey'">mdi-bell-outline</v-icon>
                      <span class="text-caption">Weekly reminder</span>
                    </div>
                    <div class="d-flex align-center gap-2">
                      <v-select v-if="item.reminder_enabled"
                        :model-value="item.reminder_day"
                        @update:model-value="val => updateReminder(item, val)"
                        :items="weekDays" density="compact" variant="outlined" hide-details
                        style="width:130px; font-size:12px"
                      />
                      <v-switch
                        :model-value="item.reminder_enabled"
                        @update:model-value="val => toggleReminder(item, val)"
                        color="primary" hide-details density="compact"
                      />
                    </div>
                  </div>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </template>

        <v-card v-else rounded="xl" class="sd-empty-card" elevation="0">
          <v-icon size="64" color="grey-lighten-2">mdi-school-outline</v-icon>
          <p class="text-grey mt-3">You haven't enrolled in any courses yet.</p>
          <div class="d-flex gap-3 mt-3 justify-center">
            <v-btn color="primary" rounded @click="enrollDialog = true" prepend-icon="mdi-plus">Enroll Now</v-btn>
            <v-btn variant="outlined" rounded @click="activeTab='catalog'">Browse Catalog</v-btn>
          </div>
        </v-card>
      </div>

      <!-- ── Enroll Dialog ─────────────────────────────────────────────────── -->
      <v-dialog v-model="enrollDialog" max-width="700" scrollable>
        <v-card rounded="xl">
          <div style="background: linear-gradient(135deg,#3b4fd8,#6c5ce7); padding:20px 24px;">
            <div class="d-flex align-center justify-space-between">
              <span class="text-white font-weight-bold" style="font-size:18px">Enroll in a Course</span>
              <v-btn icon="mdi-close" variant="text" color="white" size="small" @click="enrollDialog=false"/>
            </div>
            <v-text-field v-model="enrollSearch" placeholder="Search courses…" variant="solo" density="compact"
              prepend-inner-icon="mdi-magnify" hide-details rounded class="mt-3" bg-color="rgba(255,255,255,0.15)"
              style="color:white" clearable/>
          </div>

          <v-card-text class="pa-4" style="max-height:480px; overflow-y:auto;">
            <div v-if="loadingCatalog" class="text-center pa-8">
              <v-progress-circular indeterminate color="primary"/>
            </div>
            <v-row v-else>
              <v-col cols="12" sm="6" v-for="course in enrollableCourses" :key="course.id">
                <v-card variant="outlined" rounded="lg" class="pa-3 d-flex flex-column" style="height:100%">
                  <div class="d-flex align-center gap-2 mb-2">
                    <div :style="courseThumbStyle(course)" style="width:48px;height:48px;border-radius:8px;flex-shrink:0"/>
                    <div>
                      <div class="font-weight-medium" style="font-size:14px;line-height:1.3">{{ course.title }}</div>
                      <v-chip size="x-small" color="primary" variant="tonal" class="mt-1">{{ course.category }}</v-chip>
                    </div>
                  </div>
                  <div class="text-caption text-grey mb-3">
                    {{ course.level }} · {{ course.duration_hours }}h · {{ course.lessons_count }} lessons
                  </div>
                  <v-spacer/>
                  <v-btn v-if="!course.enrolled" color="primary" size="small" rounded variant="flat"
                    @click="enrollInCourse(course)" :loading="enrollingId === course.id">
                    Enroll
                  </v-btn>
                  <v-chip v-else size="small" color="success" variant="tonal" prepend-icon="mdi-check">
                    Enrolled
                  </v-chip>
                </v-card>
              </v-col>
              <v-col cols="12" v-if="enrollableCourses.length === 0">
                <div class="text-center text-grey pa-8">No courses match your search.</div>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-dialog>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- TAB: CERTIFICATES                                                  -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-if="activeTab === 'certificates'">
        <div class="d-flex align-center justify-space-between mb-4">
          <span class="sd-section-title">My Certificates ({{ myCertificates.length }})</span>
          <v-btn color="primary" rounded prepend-icon="mdi-upload" @click="openCertUploadGeneral">
            Upload Certificate
          </v-btn>
        </div>

        <v-row v-if="loadingCerts">
          <v-col cols="12" sm="6" md="4" v-for="n in 3" :key="n">
            <v-skeleton-loader type="card" rounded="xl"/>
          </v-col>
        </v-row>

        <v-row v-else-if="myCertificates.length > 0">
          <v-col cols="12" sm="6" md="4" v-for="cert in myCertificates" :key="cert.id">
            <v-card class="sd-cert-card" elevation="0" rounded="xl">
              <!-- Certificate preview -->
              <div class="sd-cert-preview" @click="viewCert(cert)">
                <div v-if="isImageCert(cert)" class="sd-cert-img-wrap">
                  <img :src="cert.url" class="sd-cert-img" :alt="cert.file_name"/>
                </div>
                <div v-else class="sd-cert-pdf-icon">
                  <v-icon size="56" color="red-lighten-1">mdi-file-pdf-box</v-icon>
                  <div class="text-caption mt-1 text-grey">{{ cert.file_name }}</div>
                </div>
                <div class="sd-cert-hover-overlay">
                  <v-icon color="white" size="32">mdi-eye</v-icon>
                </div>
              </div>
              <div class="pa-3">
                <div class="sd-cert-course-title">{{ cert.course.title }}</div>
                <div class="text-caption text-grey mb-2">{{ cert.course.category }}</div>
                <div v-if="cert.course_url" class="d-flex align-center text-caption mb-1">
                  <v-icon size="14" class="me-1" color="primary">mdi-link</v-icon>
                  <a :href="cert.course_url" target="_blank" class="text-primary text-decoration-none text-truncate" style="max-width:220px">{{ cert.course_url }}</a>
                </div>
                <div v-if="cert.issuer" class="d-flex align-center text-caption mb-1">
                  <v-icon size="14" class="me-1">mdi-office-building</v-icon>{{ cert.issuer }}
                </div>
                <div v-if="cert.issue_date" class="d-flex align-center text-caption mb-1">
                  <v-icon size="14" class="me-1">mdi-calendar</v-icon>Issued: {{ cert.issue_date }}
                </div>
                <div v-if="cert.expiry_date" class="d-flex align-center text-caption"
                  :class="isCertExpired(cert) ? 'text-error' : 'text-grey'">
                  <v-icon size="14" class="me-1">{{ isCertExpired(cert) ? 'mdi-alert' : 'mdi-calendar-check' }}</v-icon>
                  {{ isCertExpired(cert) ? 'Expired' : 'Expires' }}: {{ cert.expiry_date }}
                </div>
                <div class="d-flex gap-2 mt-3">
                  <v-btn color="primary" variant="tonal" size="x-small" rounded :href="cert.url" target="_blank">
                    <v-icon start size="14">mdi-download</v-icon>Download
                  </v-btn>
                  <v-btn color="error" variant="text" size="x-small" rounded @click="deleteCert(cert)">
                    <v-icon size="14">mdi-delete</v-icon>
                  </v-btn>
                </div>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <v-card v-else rounded="xl" class="sd-empty-card" elevation="0">
          <v-icon size="64" color="amber-lighten-3">mdi-certificate-outline</v-icon>
          <p class="text-grey mt-3">No certificates yet. Complete a course or upload one!</p>
        </v-card>
      </div>

      <!-- ══════════════════════════════════════════════════════════════════ -->
      <!-- TAB: MANAGE (Admin)                                                -->
      <!-- ══════════════════════════════════════════════════════════════════ -->
      <div v-if="activeTab === 'manage' && isAdmin">
        <div class="d-flex align-center justify-space-between mb-4">
          <span class="sd-section-title">Manage Courses</span>
          <v-btn color="primary" rounded prepend-icon="mdi-plus" @click="openCourseForm()">
            New Course
          </v-btn>
        </div>

        <!-- Admin stats row -->
        <v-row class="mb-4">
          <v-col cols="6" md="3" v-for="s in adminStats" :key="s.label">
            <v-card class="sd-admin-stat" elevation="0" rounded="xl" :color="s.color" variant="tonal">
              <v-card-text class="text-center">
                <v-icon size="28">{{ s.icon }}</v-icon>
                <div class="text-h5 font-weight-bold mt-1">{{ s.value }}</div>
                <div class="text-caption">{{ s.label }}</div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <v-data-table
          :headers="adminCourseHeaders"
          :items="adminCourses"
          :loading="loadingAdmin"
          class="sd-admin-table"
          rounded="xl"
          hover>
          <template #item.level="{ item }">
            <v-chip :color="levelColor(item.level)" size="small" variant="tonal">{{ item.level }}</v-chip>
          </template>
          <template #item.is_active="{ item }">
            <v-chip :color="item.is_active ? 'success' : 'grey'" size="small" variant="tonal">
              {{ item.is_active ? 'Active' : 'Inactive' }}
            </v-chip>
          </template>
          <template #item.is_featured="{ item }">
            <v-icon :color="item.is_featured ? 'amber' : 'grey-lighten-2'">
              {{ item.is_featured ? 'mdi-star' : 'mdi-star-outline' }}
            </v-icon>
          </template>
          <template #item.actions="{ item }">
            <v-btn icon="mdi-pencil" size="small" variant="text" color="primary" @click="openCourseForm(item)"/>
            <v-btn icon="mdi-book-edit-outline" size="small" variant="text" color="secondary" @click="openLessonsForm(item)"/>
            <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="deleteCourse(item)"/>
          </template>
        </v-data-table>
      </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- DIALOGS                                                             -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->

    <!-- Course Detail Dialog -->
    <v-dialog v-model="courseDialog" max-width="780" scrollable>
      <v-card v-if="selectedCourse" rounded="xl" class="sd-course-dialog">
        <!-- Header -->
        <div class="sd-dialog-hero" :style="courseThumbStyle(selectedCourse)">
          <v-btn icon="mdi-close" class="sd-dialog-close" @click="courseDialog=false" variant="flat" color="white" size="small"/>
          <div class="sd-dialog-hero-body">
            <v-chip size="x-small" color="white" class="mb-2">{{ selectedCourse.category }}</v-chip>
            <div class="sd-dialog-title">{{ selectedCourse.title }}</div>
            <div class="d-flex align-center gap-3 text-white mt-2 text-caption">
              <span><v-icon size="14">mdi-clock</v-icon> {{ selectedCourse.duration_hours }}h</span>
              <span><v-icon size="14">mdi-book-open-outline</v-icon> {{ (selectedCourse.lessons||[]).length }} lessons</span>
              <span><v-icon size="14">mdi-account-group</v-icon> {{ selectedCourse.enrollments_count }} enrolled</span>
              <v-chip size="x-small" :color="levelColor(selectedCourse.level)">{{ selectedCourse.level }}</v-chip>
            </div>
          </div>
        </div>

        <!-- Progress bar (if enrolled) -->
        <div v-if="selectedCourse.enrolled" class="px-5 pt-4">
          <div class="d-flex justify-space-between text-caption mb-1">
            <span>Progress</span>
            <span class="font-weight-bold">{{ selectedCourse.progress }}%</span>
          </div>
          <v-progress-linear :model-value="selectedCourse.progress" rounded height="10"
            :color="selectedCourse.progress===100?'success':'primary'" bg-color="grey-lighten-3"/>
        </div>

        <v-card-text class="pa-5">
          <p class="text-body-2 text-grey-darken-1 mb-4">{{ selectedCourse.description }}</p>

          <!-- Lessons -->
          <div class="sd-lessons-header mb-3">
            <v-icon color="primary" class="me-1">mdi-playlist-check</v-icon>
            <span class="font-weight-semibold">Course Lessons</span>
            <span class="text-caption text-grey ms-2">({{ (selectedCourse.lessons||[]).length }} lessons)</span>
          </div>

          <div v-if="(selectedCourse.lessons||[]).length === 0" class="text-grey text-caption pa-4 text-center">
            No lessons added yet.
          </div>

          <div v-else class="sd-lesson-list">
            <div v-for="(lesson, idx) in selectedCourse.lessons" :key="lesson.id"
              class="sd-lesson-item"
              :class="{ 'sd-lesson-done': lesson.completed, 'sd-lesson-locked': !selectedCourse.enrolled && !lesson.is_free_preview }">

              <div class="sd-lesson-num">
                <v-icon v-if="lesson.completed" color="success" size="20">mdi-check-circle</v-icon>
                <span v-else class="sd-lesson-idx">{{ idx + 1 }}</span>
              </div>
              <div class="sd-lesson-body">
                <div class="sd-lesson-title">{{ lesson.title }}</div>
                <div class="text-caption text-grey">
                  <v-icon size="12">mdi-clock</v-icon> {{ lesson.duration_minutes }} min
                  <v-chip v-if="lesson.is_free_preview" size="x-small" color="teal" class="ms-1">Preview</v-chip>
                </div>
              </div>
              <div class="sd-lesson-action">
                <v-btn v-if="selectedCourse.enrolled && !lesson.completed"
                  size="x-small" color="primary" variant="tonal" rounded
                  @click="markLessonComplete(lesson)">Mark Done</v-btn>
                <v-btn v-else-if="selectedCourse.enrolled && lesson.completed"
                  size="x-small" color="grey" variant="text" rounded
                  @click="unmarkLesson(lesson)">Undo</v-btn>
                <v-icon v-else-if="!lesson.is_free_preview" size="18" color="grey-lighten-2">mdi-lock</v-icon>
              </div>
            </div>
          </div>
        </v-card-text>

        <v-divider/>
        <v-card-actions class="pa-4 justify-space-between">
          <div>
            <v-btn v-if="selectedCourse.enrolled && !selectedCourse.certificate"
              color="success" variant="outlined" rounded prepend-icon="mdi-certificate-outline"
              @click="openCertUpload({ enrollment_id: selectedCourse.enrollment_id, course: selectedCourse })">
              Upload Certificate
            </v-btn>
            <v-chip v-if="selectedCourse.certificate" color="amber-darken-2" variant="tonal"
              prepend-icon="mdi-certificate">
              Certificate uploaded
            </v-chip>
          </div>
          <div class="d-flex gap-2">
            <v-btn variant="text" rounded @click="courseDialog=false">Close</v-btn>
            <v-btn v-if="!selectedCourse.enrolled" color="primary" rounded
              :loading="enrolling" @click="enrollCourse(selectedCourse)">
              Enroll Now
            </v-btn>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Certificate Upload Dialog -->
    <v-dialog v-model="certUploadDialog" max-width="480">
      <v-card rounded="xl">
        <v-card-title class="pa-4 text-h6">
          <v-icon color="amber-darken-2" class="me-2">mdi-certificate</v-icon>
          Upload Certificate
        </v-card-title>
        <v-divider/>
        <v-card-text class="pa-4">
          <v-form ref="certForm">

            <!-- Toggle: enrolled course vs custom -->
            <v-btn-toggle v-if="!certUploadEnrollmentId" v-model="certMode" mandatory rounded="xl" density="compact" class="mb-4 w-100">
              <v-btn value="enrolled" class="flex-grow-1">
                <v-icon start size="16">mdi-school</v-icon>My Enrolled Course
              </v-btn>
              <v-btn value="custom" class="flex-grow-1">
                <v-icon start size="16">mdi-pencil</v-icon>Custom Course
              </v-btn>
            </v-btn-toggle>

            <!-- Enrolled course dropdown -->
            <v-select v-if="!certUploadEnrollmentId && certMode === 'enrolled'"
              v-model="certEnrollmentId"
              :items="myCoursesList.map(c=>({title: c.course.title, value: c.enrollment_id}))"
              label="Select Course *" variant="outlined" density="compact" rounded class="mb-3"
              :rules="[v=>!!v||'Required']"/>

            <!-- Custom course name + URL -->
            <template v-if="!certUploadEnrollmentId && certMode === 'custom'">
              <v-text-field v-model="certCustomCourseName"
                label="Course Name *" variant="outlined" density="compact" rounded class="mb-3"
                prepend-inner-icon="mdi-book-open-outline"
                :rules="[v=>!!v||'Course name is required']"/>
              <v-text-field v-model="certCourseUrl"
                label="Course URL (optional)" variant="outlined" density="compact" rounded class="mb-3"
                prepend-inner-icon="mdi-link" placeholder="https://…"/>
            </template>

            <v-file-input v-model="certFile" label="Certificate File (PDF/Image) *"
              accept=".pdf,.jpg,.jpeg,.png" variant="outlined" density="compact" rounded
              prepend-icon="mdi-paperclip" :rules="[v=>!!v||'File required']" class="mb-3"/>

            <v-text-field v-model="certIssuer" label="Issuer / Institution" variant="outlined"
              density="compact" rounded class="mb-3"/>

            <v-row dense class="mb-3">
              <v-col cols="6">
                <v-text-field v-model="certIssueDate" label="Issue Date" type="date"
                  variant="outlined" density="compact" rounded/>
              </v-col>
              <v-col cols="6">
                <v-text-field v-model="certExpiryDate" label="Expiry Date" type="date"
                  variant="outlined" density="compact" rounded/>
              </v-col>
            </v-row>

            <v-checkbox v-model="certIsPublic" label="Share with team (visible to HR/Admin)"
              color="primary" density="compact"/>
          </v-form>
        </v-card-text>
        <v-divider/>
        <v-card-actions class="pa-4">
          <v-spacer/>
          <v-btn variant="text" rounded @click="certUploadDialog=false">Cancel</v-btn>
          <v-btn color="primary" rounded :loading="uploadingCert" @click="submitCertificate">Upload</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Course Form Dialog (Admin) -->
    <v-dialog v-model="courseFormDialog" max-width="600">
      <v-card rounded="xl">
        <v-card-title class="pa-4 text-h6">
          {{ editCourse?.id ? 'Edit Course' : 'New Course' }}
        </v-card-title>
        <v-divider/>
        <v-card-text class="pa-4">
          <v-form ref="courseForm">
            <v-text-field v-model="courseFormData.title" label="Course Title *"
              variant="outlined" density="compact" rounded class="mb-3"
              :rules="[v=>!!v||'Required']"/>

            <v-textarea v-model="courseFormData.description" label="Description"
              variant="outlined" density="compact" rounded rows="3" class="mb-3"/>

            <v-row dense class="mb-3">
              <v-col cols="6">
                <v-select v-model="courseFormData.category" :items="categories" label="Category *"
                  variant="outlined" density="compact" rounded :rules="[v=>!!v||'Required']"/>
              </v-col>
              <v-col cols="6">
                <v-select v-model="courseFormData.level" :items="['Beginner','Intermediate','Advanced']"
                  label="Level *" variant="outlined" density="compact" rounded :rules="[v=>!!v||'Required']"/>
              </v-col>
            </v-row>

            <v-row dense class="mb-3">
              <v-col cols="7">
                <v-text-field v-model="courseFormData.provider" label="Provider / Platform"
                  variant="outlined" density="compact" rounded/>
              </v-col>
              <v-col cols="5">
                <v-text-field v-model="courseFormData.duration_hours" label="Duration (hours)"
                  type="number" variant="outlined" density="compact" rounded min="0"/>
              </v-col>
            </v-row>

            <v-row dense>
              <v-col cols="6">
                <v-checkbox v-model="courseFormData.is_active" label="Active" color="primary" density="compact"/>
              </v-col>
              <v-col cols="6">
                <v-checkbox v-model="courseFormData.is_featured" label="Featured" color="amber" density="compact"/>
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-divider/>
        <v-card-actions class="pa-4">
          <v-spacer/>
          <v-btn variant="text" rounded @click="courseFormDialog=false">Cancel</v-btn>
          <v-btn color="primary" rounded :loading="savingCourse" @click="saveCourse">
            {{ editCourse?.id ? 'Update' : 'Create' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Lessons Form Dialog (Admin) -->
    <v-dialog v-model="lessonsFormDialog" max-width="700" scrollable>
      <v-card rounded="xl">
        <v-card-title class="pa-4 text-h6">
          Lessons for: {{ editCourse?.title }}
        </v-card-title>
        <v-divider/>
        <v-card-text class="pa-4">
          <div v-for="(lesson, idx) in lessonsFormData" :key="idx" class="sd-lesson-editor mb-3">
            <div class="d-flex align-center gap-2 mb-2">
              <v-chip size="x-small" color="primary">{{ idx + 1 }}</v-chip>
              <span class="text-caption font-weight-medium">Lesson {{ idx + 1 }}</span>
              <v-spacer/>
              <v-btn icon="mdi-delete" size="x-small" color="error" variant="text" @click="removeLesson(idx)"/>
            </div>
            <v-text-field v-model="lesson.title" label="Lesson Title *" variant="outlined"
              density="compact" rounded class="mb-2" :rules="[v=>!!v||'Required']"/>
            <v-row dense class="mb-2">
              <v-col cols="8">
                <v-text-field v-model="lesson.video_url" label="Video URL" variant="outlined"
                  density="compact" rounded prepend-inner-icon="mdi-play-circle"/>
              </v-col>
              <v-col cols="4">
                <v-text-field v-model="lesson.duration_minutes" label="Duration (min)"
                  type="number" variant="outlined" density="compact" rounded min="0"/>
              </v-col>
            </v-row>
            <v-textarea v-model="lesson.content" label="Content / Notes" variant="outlined"
              density="compact" rounded rows="2" class="mb-2"/>
            <v-checkbox v-model="lesson.is_free_preview" label="Free preview" color="teal" density="compact"/>
          </div>
          <v-btn variant="tonal" color="primary" rounded block prepend-icon="mdi-plus" @click="addLesson">
            Add Lesson
          </v-btn>
        </v-card-text>
        <v-divider/>
        <v-card-actions class="pa-4">
          <v-spacer/>
          <v-btn variant="text" rounded @click="lessonsFormDialog=false">Cancel</v-btn>
          <v-btn color="primary" rounded :loading="savingLessons" @click="saveLessons">Save Lessons</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Certificate Viewer Dialog -->
    <v-dialog v-model="certViewDialog" max-width="860">
      <v-card v-if="viewingCert" rounded="xl">
        <v-card-title class="pa-4 d-flex justify-space-between align-center">
          <span>{{ viewingCert.course.title }}</span>
          <v-btn icon="mdi-close" variant="text" @click="certViewDialog=false"/>
        </v-card-title>
        <v-divider/>
        <v-card-text class="pa-4 text-center">
          <img v-if="isImageCert(viewingCert)" :src="viewingCert.url" style="max-width:100%;border-radius:8px;"/>
          <div v-else class="pa-8">
            <v-icon size="80" color="red-lighten-1">mdi-file-pdf-box</v-icon>
            <p class="mt-2">PDF Certificate — <a :href="viewingCert.url" target="_blank">Open in new tab</a></p>
          </div>
        </v-card-text>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" rounded="xl" location="bottom right">
      <v-icon class="me-2">{{ snackbar.color === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle' }}</v-icon>
      {{ snackbar.text }}
    </v-snackbar>

  </v-container>
</template>

<script>
import CourseCard from './CourseCard.vue'

export default {
  name: 'StaffDevelopment',
  components: { CourseCard },

  props: {
    userId: { type: [Number, String], required: true },
    isAdmin: { type: Boolean, default: false },
  },

  data() {
    return {
      activeTab: 'overview',

      // Data
      allCourses: [],
      myCoursesList: [],
      myCertificates: [],
      stats: {},

      // Loaders
      loadingCourses: false,
      loadingMyCourses: false,
      loadingCerts: false,
      loadingAdmin: false,

      // Catalog filters
      catalogSearch: '',
      filterCategory: 'All',
      filterLevel: 'All',
      catalogView: 'grid',
      searchTimeout: null,

      // My Learning filter
      myLearningFilter: 'all',

      // Enroll dialog
      enrollDialog: false,
      enrollSearch: '',
      enrollingId: null,
      loadingCatalog: false,

      weekDays: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],

      // Course dialog
      courseDialog: false,
      selectedCourse: null,
      enrolling: false,

      // Certificate upload
      certUploadDialog: false,
      certUploadEnrollmentId: null,
      certEnrollmentId: null,
      certMode: 'enrolled',          // 'enrolled' | 'custom'
      certCustomCourseName: '',
      certCourseUrl: '',
      certFile: null,
      certIssuer: '',
      certIssueDate: '',
      certExpiryDate: '',
      certIsPublic: true,
      uploadingCert: false,

      // Certificate viewer
      certViewDialog: false,
      viewingCert: null,

      // Admin: course form
      courseFormDialog: false,
      editCourse: null,
      courseFormData: { title: '', description: '', category: '', level: 'Beginner', provider: '', duration_hours: 0, is_active: true, is_featured: false },
      savingCourse: false,
      adminCourses: [],

      // Admin: lessons form
      lessonsFormDialog: false,
      lessonsFormData: [],
      savingLessons: false,

      // Snackbar
      snackbar: { show: false, text: '', color: 'success' },

      categories: ['Leadership & Management', 'Technical Skills', 'Compliance & Safety', 'Soft Skills', 'Customer Service', 'Finance & Business', 'Health & Wellness', 'General'],
      
      externalCategories: ['All', 'Logistics', 'HR & Management', 'IT & Digital', 'Customer Service'],
      selectedExternalCategory: 'All',
      externalCourses: [
        { title: 'Supply Chain and Logistics Management', platform: 'Alison', category: 'Logistics', url: 'https://alison.com/course/supply-chain-and-logistics-management', thumb: '/assets/img/development/course_thumb.png', is_free: true },
        { title: 'Customer Service Essentials', platform: 'Great Learning', category: 'Customer Service', url: 'https://www.mygreatlearning.com/academy/learn-for-free/courses/customer-service-essentials', thumb: '/assets/img/development/alison.png', is_free: true },
        { title: 'Human Resource Management', platform: 'Great Learning', category: 'HR & Management', url: 'https://www.mygreatlearning.com/academy/learn-for-free/courses/human-resource-management', thumb: '/assets/img/development/hr_thumb.png', is_free: true },
        { title: 'Introduction to Cybersecurity', platform: 'Alison', category: 'IT & Digital', url: 'https://alison.com/course/introduction-to-cybersecurity', thumb: '/assets/img/development/it_thumb.png', is_free: true },
        { title: 'Diploma in Customer Service', platform: 'Alison', category: 'Customer Service', url: 'https://alison.com/course/diploma-in-customer-service', thumb: '/assets/img/development/great_learning.png', is_free: true },
        { title: 'Diploma in Human Resources', platform: 'Alison', category: 'HR & Management', url: 'https://alison.com/course/diploma-in-human-resources', thumb: '/assets/img/development/hr_thumb.png', is_free: true },
        { title: 'Logistics Management', platform: 'Great Learning', category: 'Logistics', url: 'https://www.mygreatlearning.com/academy/learn-for-free/courses/logistics-management', thumb: '/assets/img/development/course_thumb.png', is_free: true },
        { title: 'Digital Transformation & AI', platform: 'Alison', category: 'IT & Digital', url: 'https://alison.com/course/artificial-intelligence-for-supply-chains-and-logistics', thumb: '/assets/img/development/it_thumb.png', is_free: true },
      ],
    }
  },

  computed: {
    heroStats() {
      return [
        { icon: 'mdi-bookshelf', value: this.stats.my_enrollments ?? 0, label: 'Enrolled' },
        { icon: 'mdi-check-circle-outline', value: this.stats.my_completed ?? 0, label: 'Completed' },
        { icon: 'mdi-certificate-outline', value: this.stats.my_certificates ?? 0, label: 'Certificates' },
      ]
    },
    progressCards() {
      return [
        { label: 'Enrolled Courses', value: this.stats.my_enrollments ?? 0, icon: 'mdi-school', gradient: 'linear-gradient(135deg,#667eea,#764ba2)' },
        { label: 'Completed', value: this.stats.my_completed ?? 0, icon: 'mdi-check-circle', gradient: 'linear-gradient(135deg,#11998e,#38ef7d)' },
        { label: 'In Progress', value: this.stats.my_in_progress ?? 0, icon: 'mdi-progress-clock', gradient: 'linear-gradient(135deg,#f093fb,#f5576c)' },
        { label: 'Certificates', value: this.stats.my_certificates ?? 0, icon: 'mdi-certificate', gradient: 'linear-gradient(135deg,#f7971e,#ffd200)' },
      ]
    },
    adminStats() {
      return [
        { label: 'Total Courses', value: this.stats.total_courses ?? 0, icon: 'mdi-bookshelf', color: 'primary' },
        { label: 'Enrollments', value: this.stats.total_enrollments ?? 0, icon: 'mdi-account-school', color: 'secondary' },
        { label: 'Certificates', value: this.stats.total_certificates ?? 0, icon: 'mdi-certificate', color: 'warning' },
        { label: 'Completion Rate', value: (this.stats.completion_rate ?? 0) + '%', icon: 'mdi-chart-donut', color: 'success' },
      ]
    },
    featuredCourses() {
      return this.allCourses.filter(c => c.is_featured).slice(0, 6)
    },
    filteredExternalCourses() {
      if (this.selectedExternalCategory === 'All') return this.externalCourses;
      return this.externalCourses.filter(c => c.category === this.selectedExternalCategory);
    },
    myCoursesInProgress() {
      return this.myCoursesList.filter(c => c.status === 'active')
    },
    myCoursesCompleted() {
      return this.myCoursesList.filter(c => c.status === 'completed')
    },
    myCoursesFiltered() {
      if (this.myLearningFilter === 'active') return this.myCoursesInProgress
      if (this.myLearningFilter === 'completed') return this.myCoursesCompleted
      return this.myCoursesList
    },
    enrollableCourses() {
      const q = (this.enrollSearch || '').toLowerCase()
      return this.allCourses.filter(c =>
        !q || c.title.toLowerCase().includes(q) || c.category.toLowerCase().includes(q)
      )
    },
    adminCourseHeaders() {
      return [
        { title: 'Title', key: 'title', sortable: true },
        { title: 'Category', key: 'category', sortable: true },
        { title: 'Level', key: 'level' },
        { title: 'Lessons', key: 'lessons_count', align: 'center' },
        { title: 'Enrolled', key: 'enrollments_count', align: 'center' },
        { title: 'Status', key: 'is_active' },
        { title: 'Featured', key: 'is_featured', align: 'center' },
        { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
      ]
    },
  },

  watch: {
    activeTab(val) {
      if (val === 'catalog' && this.allCourses.length === 0) this.fetchCourses()
      if (val === 'my-learning') this.fetchMyCourses()
      if (val === 'certificates') this.fetchMyCertificates()
      if (val === 'manage') this.fetchAdminCourses()
    },
  },

  mounted() {
    this.fetchStats()
    this.fetchCourses()
    this.fetchMyCourses()
  },

  methods: {
    // ─── Fetch ────────────────────────────────────────────────────────────

    async fetchStats() {
      try {
        const { data } = await axios.get('/api/v1/staff-development/stats')
        this.stats = data
      } catch {}
    },

    async fetchCourses() {
      this.loadingCourses = true
      try {
        const { data } = await axios.get('/api/v1/staff-development/courses', {
          params: { search: this.catalogSearch, category: this.filterCategory, level: this.filterLevel }
        })
        this.allCourses = data
      } catch { this.showSnack('Failed to load courses', 'error') }
      finally { this.loadingCourses = false }
    },

    debouncedFetchCourses() {
      clearTimeout(this.searchTimeout)
      this.searchTimeout = setTimeout(() => this.fetchCourses(), 400)
    },

    async fetchMyCourses() {
      this.loadingMyCourses = true
      try {
        const { data } = await axios.get('/api/v1/staff-development/my-courses')
        this.myCoursesList = data
      } catch {}
      finally { this.loadingMyCourses = false }
    },

    async saveNotes(item) {
      try {
        await axios.put(`/api/v1/staff-development/enrollments/${item.enrollment_id}/notes`, {
          notes: item.notes
        })
      } catch {}
    },

    async toggleReminder(item, val) {
      item.reminder_enabled = val
      await this.updateReminder(item, item.reminder_day)
    },

    async updateReminder(item, day) {
      item.reminder_day = day
      try {
        await axios.put(`/api/v1/staff-development/enrollments/${item.enrollment_id}/reminder`, {
          reminder_enabled: item.reminder_enabled,
          reminder_day: item.reminder_day
        })
      } catch {}
    },

    async enrollInCourse(course) {
      this.enrollingId = course.id
      try {
        await axios.post(`/api/v1/staff-development/courses/${course.id}/enroll`)
        course.enrolled = true
        await this.fetchMyCourses()
        this.showSnack('Enrolled successfully!', 'success')
      } catch (e) {
        this.showSnack(e.response?.data?.message || 'Enrollment failed', 'error')
      } finally {
        this.enrollingId = null
      }
    },

    async fetchMyCertificates() {
      this.loadingCerts = true
      try {
        const { data } = await axios.get('/api/v1/staff-development/my-certificates')
        this.myCertificates = data
      } catch {}
      finally { this.loadingCerts = false }
    },

    async fetchAdminCourses() {
      this.loadingAdmin = true
      try {
        const { data } = await axios.get('/api/v1/staff-development/courses', { params: { category: 'All' } })
        this.adminCourses = data
      } catch {}
      finally { this.loadingAdmin = false }
    },

    // ─── Course Actions ───────────────────────────────────────────────────

    async openCourse(course) {
      try {
        const { data } = await axios.get(`/api/v1/staff-development/courses/${course.id}`)
        this.selectedCourse = data
        this.courseDialog = true
      } catch { this.showSnack('Failed to load course', 'error') }
    },

    async enrollCourse(course) {
      this.enrolling = true
      try {
        await axios.post(`/api/v1/staff-development/courses/${course.id}/enroll`)
        this.showSnack('Enrolled successfully!', 'success')
        await this.fetchCourses()
        await this.fetchMyCourses()
        await this.fetchStats()
        if (this.selectedCourse?.id === course.id) {
          const { data } = await axios.get(`/api/v1/staff-development/courses/${course.id}`)
          this.selectedCourse = data
        }
      } catch (e) {
        this.showSnack(e.response?.data?.message || 'Enrollment failed', 'error')
      }
      this.enrolling = false
    },

    async markLessonComplete(lesson) {
      try {
        const { data } = await axios.post(`/api/v1/staff-development/lessons/${lesson.id}/complete`)
        lesson.completed = true
        if (this.selectedCourse) this.selectedCourse.progress = data.progress
        if (data.course_completed) {
          this.showSnack('Course completed! 🎉 Upload your certificate.', 'success')
          this.selectedCourse.enrollment_status = 'completed'
        }
        this.fetchMyCourses()
        this.fetchStats()
      } catch { this.showSnack('Failed to mark lesson', 'error') }
    },

    async unmarkLesson(lesson) {
      try {
        const { data } = await axios.delete(`/api/v1/staff-development/lessons/${lesson.id}/complete`)
        lesson.completed = false
        if (this.selectedCourse) this.selectedCourse.progress = data.progress
        this.fetchMyCourses()
      } catch {}
    },

    // ─── Certificates ─────────────────────────────────────────────────────

    openCertUpload(enrollItem) {
      this.certUploadEnrollmentId = enrollItem.enrollment_id
      this.certEnrollmentId = enrollItem.enrollment_id
      this.certFile = null
      this.certIssuer = ''
      this.certIssueDate = ''
      this.certExpiryDate = ''
      this.certIsPublic = true
      this.certUploadDialog = true
    },

    openCertUploadGeneral() {
      this.certUploadEnrollmentId = null
      this.certEnrollmentId = null
      this.certMode = 'enrolled'
      this.certCustomCourseName = ''
      this.certCourseUrl = ''
      this.certFile = null
      this.certIssuer = ''
      this.certIssueDate = ''
      this.certExpiryDate = ''
      this.certIsPublic = true
      this.certUploadDialog = true
    },

    async submitCertificate() {
      const { valid } = await this.$refs.certForm.validate()
      if (!valid) return

      this.uploadingCert = true
      try {
        // ── External / custom course (no enrollment) ──────────────────────
        if (!this.certUploadEnrollmentId && this.certMode === 'custom') {
          const fd = new FormData()
          fd.append('file', this.certFile)
          fd.append('custom_course_name', this.certCustomCourseName)
          if (this.certCourseUrl) fd.append('course_url', this.certCourseUrl)
          if (this.certIssuer) fd.append('issuer', this.certIssuer)
          if (this.certIssueDate) fd.append('issue_date', this.certIssueDate)
          if (this.certExpiryDate) fd.append('expiry_date', this.certExpiryDate)
          fd.append('is_public', this.certIsPublic ? '1' : '0')

          await axios.post('/api/v1/staff-development/certificates/external', fd)
          this.showSnack('Certificate uploaded!', 'success')
          this.certUploadDialog = false
          this.fetchMyCertificates()
          this.fetchStats()
          return
        }

        // ── Enrolled course ───────────────────────────────────────────────
        const enrollmentId = this.certUploadEnrollmentId || this.certEnrollmentId
        if (!enrollmentId) { this.showSnack('Please select a course', 'error'); return }

        const fd = new FormData()
        fd.append('file', this.certFile)
        if (this.certIssuer) fd.append('issuer', this.certIssuer)
        if (this.certIssueDate) fd.append('issue_date', this.certIssueDate)
        if (this.certExpiryDate) fd.append('expiry_date', this.certExpiryDate)
        fd.append('is_public', this.certIsPublic ? '1' : '0')

        await axios.post(`/api/v1/staff-development/enrollments/${enrollmentId}/certificate`, fd)
        this.showSnack('Certificate uploaded!', 'success')
        this.certUploadDialog = false
        this.fetchMyCertificates()
        this.fetchMyCourses()
        this.fetchStats()
        if (this.selectedCourse && this.selectedCourse.enrollment_id === enrollmentId) {
          const { data } = await axios.get(`/api/v1/staff-development/courses/${this.selectedCourse.id}`)
          this.selectedCourse = data
        }
      } catch (e) {
        this.showSnack(e.response?.data?.message || 'Upload failed', 'error')
      }
      this.uploadingCert = false
    },

    async deleteCert(cert) {
      if (!confirm('Delete this certificate?')) return
      try {
        await axios.delete(`/api/v1/staff-development/certificates/${cert.id}`)
        this.showSnack('Certificate deleted', 'success')
        this.fetchMyCertificates()
        this.fetchStats()
      } catch { this.showSnack('Delete failed', 'error') }
    },

    viewCert(cert) {
      this.viewingCert = cert
      this.certViewDialog = true
    },

    // ─── Admin Actions ────────────────────────────────────────────────────

    openCourseForm(course = null) {
      this.editCourse = course
      if (course) {
        this.courseFormData = {
          title: course.title, description: course.description, category: course.category,
          level: course.level, provider: course.provider, duration_hours: course.duration_hours,
          is_active: course.is_active, is_featured: course.is_featured,
        }
      } else {
        this.courseFormData = { title: '', description: '', category: '', level: 'Beginner', provider: '', duration_hours: 0, is_active: true, is_featured: false }
      }
      this.courseFormDialog = true
    },

    async saveCourse() {
      const { valid } = await this.$refs.courseForm.validate()
      if (!valid) return
      this.savingCourse = true
      try {
        if (this.editCourse?.id) {
          await axios.put(`/api/v1/staff-development/courses/${this.editCourse.id}`, this.courseFormData)
          this.showSnack('Course updated!', 'success')
        } else {
          await axios.post('/api/v1/staff-development/courses', this.courseFormData)
          this.showSnack('Course created!', 'success')
        }
        this.courseFormDialog = false
        this.fetchAdminCourses()
        this.fetchCourses()
        this.fetchStats()
      } catch (e) { this.showSnack(e.response?.data?.message || 'Save failed', 'error') }
      this.savingCourse = false
    },

    async deleteCourse(course) {
      if (!confirm(`Delete "${course.title}"?`)) return
      try {
        await axios.delete(`/api/v1/staff-development/courses/${course.id}`)
        this.showSnack('Course deleted', 'success')
        this.fetchAdminCourses()
        this.fetchCourses()
        this.fetchStats()
      } catch { this.showSnack('Delete failed', 'error') }
    },

    openLessonsForm(course) {
      this.editCourse = course
      this.lessonsFormData = (course.lessons || []).map(l => ({ ...l }))
      if (this.lessonsFormData.length === 0) this.addLesson()
      this.lessonsFormDialog = true
    },

    addLesson() {
      this.lessonsFormData.push({ title: '', description: '', content: '', video_url: '', duration_minutes: 0, order: this.lessonsFormData.length + 1, is_free_preview: false })
    },

    removeLesson(idx) {
      this.lessonsFormData.splice(idx, 1)
    },

    async saveLessons() {
      this.savingLessons = true
      try {
        await axios.post(`/api/v1/staff-development/courses/${this.editCourse.id}/lessons`, { lessons: this.lessonsFormData })
        this.showSnack('Lessons saved!', 'success')
        this.lessonsFormDialog = false
        this.fetchAdminCourses()
      } catch (e) { this.showSnack('Save failed', 'error') }
      this.savingLessons = false
    },

    // ─── Helpers ──────────────────────────────────────────────────────────

    courseThumbStyle(course) {
      const gradients = {
        'Leadership & Management': 'linear-gradient(135deg,#667eea 0%,#764ba2 100%)',
        'Technical Skills': 'linear-gradient(135deg,#11998e 0%,#38ef7d 100%)',
        'Compliance & Safety': 'linear-gradient(135deg,#f093fb 0%,#f5576c 100%)',
        'Soft Skills': 'linear-gradient(135deg,#4facfe 0%,#00f2fe 100%)',
        'Customer Service': 'linear-gradient(135deg,#43e97b 0%,#38f9d7 100%)',
        'Finance & Business': 'linear-gradient(135deg,#f7971e 0%,#ffd200 100%)',
        'Health & Wellness': 'linear-gradient(135deg,#a8edea 0%,#fed6e3 100%)',
        'General': 'linear-gradient(135deg,#a1c4fd 0%,#c2e9fb 100%)',
      }
      return { background: gradients[course?.category] || gradients['General'] }
    },

    levelClass(level) {
      return { Beginner: 'level-beginner', Intermediate: 'level-inter', Advanced: 'level-adv' }[level] || ''
    },

    levelColor(level) {
      return { Beginner: 'success', Intermediate: 'warning', Advanced: 'error' }[level] || 'grey'
    },

    truncate(str, len) {
      if (!str) return ''
      return str.length > len ? str.slice(0, len) + '…' : str
    },

    isImageCert(cert) {
      return /\.(jpg|jpeg|png)$/i.test(cert.file_name)
    },

    isCertExpired(cert) {
      if (!cert.expiry_date) return false
      return new Date(cert.expiry_date) < new Date()
    },

    showSnack(text, color = 'success') {
      this.snackbar = { show: true, text, color }
    },
  },
}
</script>

<style scoped>
/* ═══ Hero ════════════════════════════════════════════════════════════════ */
.sd-hero {
  background: linear-gradient(135deg, #1a237e 0%, #283593 40%, #3949ab 70%, #5c6bc0 100%);
  padding: 32px 24px 0;
  color: white;
}
.sd-hero-content {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
}
.sd-hero-title {
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -0.5px;
  line-height: 1.1;
}
.sd-hero-subtitle {
  font-size: 0.95rem;
  opacity: 0.85;
  margin-top: 4px;
}
.sd-hero-stats {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.sd-stat-chip {
  display: flex;
  align-items: center;
  gap: 4px;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(8px);
  border-radius: 50px;
  padding: 6px 14px;
  font-size: 0.8rem;
}
.sd-stat-val { font-weight: 700; font-size: 1rem; margin-right: 2px; }
.sd-stat-lbl { opacity: 0.85; }

/* ═══ Tab bar ══════════════════════════════════════════════════════════════ */
.sd-tab-bar {
  background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
}
.sd-tabs { background: transparent !important; }
.sd-tabs :deep(.v-tab) { color: rgba(255,255,255,0.7) !important; text-transform: none; font-size: 0.9rem; }
.sd-tabs :deep(.v-tab--selected) { color: white !important; font-weight: 600; }

/* ═══ Section header ══════════════════════════════════════════════════════ */
.sd-section-header { display: flex; align-items: center; justify-content: space-between; }
.sd-section-title { font-size: 1.1rem; font-weight: 700; }

/* ═══ Progress cards ══════════════════════════════════════════════════════ */
.sd-progress-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 18px;
  border: 1px solid rgba(0,0,0,0.06);
}
.sd-progress-card-icon {
  width: 52px; height: 52px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.sd-progress-card-val { font-size: 1.6rem; font-weight: 800; line-height: 1; }
.sd-progress-card-lbl { font-size: 0.8rem; color: #757575; margin-top: 2px; }

/* ═══ Continue learning card ══════════════════════════════════════════════ */
.sd-continue-card {
  display: flex;
  cursor: pointer;
  border: 1px solid rgba(0,0,0,0.06);
  overflow: hidden;
  transition: box-shadow 0.2s;
}
.sd-continue-card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.10) !important; }
.sd-continue-thumb {
  width: 90px;
  flex-shrink: 0;
  display: flex;
  align-items: flex-start;
  padding: 6px;
}
.sd-continue-title { font-weight: 600; font-size: 0.9rem; line-height: 1.3; }
.sd-continue-meta { font-size: 0.75rem; color: #9e9e9e; }
.sd-prog-pct { font-size: 0.75rem; font-weight: 700; white-space: nowrap; }

/* ═══ Empty state ══════════════════════════════════════════════════════════ */
.sd-empty-card {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 56px;
  border: 2px dashed rgba(0,0,0,0.08);
  text-align: center;
}

/* ═══ Course List view ════════════════════════════════════════════════════ */
.sd-list-item {
  display: flex;
  align-items: center;
  border: 1px solid rgba(0,0,0,0.06);
  cursor: pointer;
  overflow: hidden;
  transition: box-shadow 0.2s;
}
.sd-list-item:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.10) !important; }
.sd-list-thumb {
  width: 100px; flex-shrink: 0; height: 80px;
  display: flex; align-items: flex-end; padding: 6px;
  position: relative;
}
.sd-list-body { flex: 1; padding: 12px 16px; }
.sd-list-cat { font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
.sd-list-title { font-size: 1rem; font-weight: 700; }
.sd-list-desc { margin-top: 2px; line-height: 1.4; }
.sd-list-action { padding: 12px 16px; flex-shrink: 0; }

/* ═══ Level badges ═══════════════════════════════════════════════════════ */
.sd-level-badge {
  position: absolute; bottom: 6px; right: 6px;
  font-size: 0.65rem; font-weight: 700;
  padding: 2px 6px; border-radius: 4px; color: white; text-transform: uppercase;
}
.level-beginner { background: #2e7d32; }
.level-inter { background: #f57c00; }
.level-adv { background: #c62828; }

/* ═══ My Courses ══════════════════════════════════════════════════════════ */
.sd-my-course-card {
  border: 1px solid rgba(0,0,0,0.06);
  overflow: hidden;
}
.sd-my-course-thumb {
  height: 140px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 10px;
  position: relative;
}
.sd-my-course-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0,0,0,0.2);
}
.sd-my-course-title { font-size: 1rem; font-weight: 700; line-height: 1.3; }

/* ═══ Certificates ════════════════════════════════════════════════════════ */
.sd-cert-card {
  border: 1px solid rgba(0,0,0,0.06);
  overflow: hidden;
}
.sd-cert-preview {
  height: 180px;
  display: flex; align-items: center; justify-content: center;
  background: #f5f5f5; position: relative; cursor: pointer; overflow: hidden;
}
.sd-cert-img-wrap { width: 100%; height: 100%; }
.sd-cert-img { width: 100%; height: 100%; object-fit: cover; }
.sd-cert-pdf-icon { display: flex; flex-direction: column; align-items: center; }
.sd-cert-hover-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity 0.2s;
}
.sd-cert-preview:hover .sd-cert-hover-overlay { opacity: 1; }
.sd-cert-course-title { font-weight: 700; font-size: 0.9rem; }

/* ═══ Course Dialog ═══════════════════════════════════════════════════════ */
.sd-dialog-hero {
  height: 200px;
  display: flex;
  align-items: flex-end;
  padding: 20px;
  position: relative;
}
.sd-dialog-close { position: absolute; top: 12px; right: 12px; }
.sd-dialog-hero-body { color: white; width: 100%; }
.sd-dialog-title { font-size: 1.4rem; font-weight: 800; text-shadow: 0 1px 4px rgba(0,0,0,0.3); }

/* ═══ Lessons ════════════════════════════════════════════════════════════ */
.sd-lessons-header { display: flex; align-items: center; }
.sd-lesson-list { border: 1px solid rgba(0,0,0,0.08); border-radius: 12px; overflow: hidden; }
.sd-lesson-item {
  display: flex; align-items: center; gap: 14px; padding: 12px 16px;
  border-bottom: 1px solid rgba(0,0,0,0.05);
  transition: background 0.15s;
}
.sd-lesson-item:last-child { border-bottom: none; }
.sd-lesson-item:hover { background: rgba(0,0,0,0.02); }
.sd-lesson-done { background: rgba(76, 175, 80, 0.04); }
.sd-lesson-locked { opacity: 0.5; }
.sd-lesson-num { width: 28px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sd-lesson-idx {
  width: 24px; height: 24px;
  border-radius: 50%;
  background: rgba(0,0,0,0.08);
  font-size: 0.75rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.sd-lesson-body { flex: 1; }
.sd-lesson-title { font-size: 0.9rem; font-weight: 500; }
.sd-lesson-action { flex-shrink: 0; }

/* ═══ Lesson editor (admin) ═══════════════════════════════════════════════ */
.sd-lesson-editor {
  background: rgba(0,0,0,0.02);
  border: 1px solid rgba(0,0,0,0.06);
  border-radius: 12px;
  padding: 14px;
}

/* ═══ Admin table ════════════════════════════════════════════════════════ */
.sd-admin-table { border: 1px solid rgba(0,0,0,0.06); }
.sd-admin-stat { text-align: center; }

/* ═══ Category chips ══════════════════════════════════════════════════════ */
.sd-category-chips { overflow-x: auto; white-space: nowrap; }

/* ═══ External Resources Cards ═══════════════════════════════════════════ */
.sd-ext-card {
  position: relative;
  overflow: hidden;
  padding: 24px;
  height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  border: 1px solid rgba(0,0,0,0.05);
}
.sd-ext-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.12) !important;
}
.sd-ext-icon {
  width: 48px; height: 48px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  z-index: 2;
}
.alison-card { background: linear-gradient(135deg, #2e7d32, #66bb6a); color: white; }
.gl-card { background: linear-gradient(135deg, #1a237e, #3f51b5); color: white; }

.sd-ext-content { z-index: 2; color: white; text-shadow: 0 1px 4px rgba(0,0,0,0.3); }
.sd-ext-label { font-size: 0.65rem; font-weight: 800; letter-spacing: 1px; opacity: 0.9; color: rgba(255,255,255,0.9); }
.sd-ext-name { font-size: 1.4rem; font-weight: 800; margin: 2px 0 6px; color: white; }
.sd-ext-desc { font-size: 0.85rem; opacity: 0.95; line-height: 1.4; max-width: 85%; color: white; }
.sd-ext-footer {
  margin-top: 12px; font-size: 0.75rem; font-weight: 700;
  display: flex; align-items: center; gap: 6px;
  color: white; opacity: 0.9;
}
.sd-ext-bg-icon {
  position: absolute; bottom: -20px; right: -20px;
  opacity: 0.1; transform: rotate(-15deg);
  z-index: 1; transition: all 0.4s;
}
.sd-ext-card:hover .sd-ext-bg-icon {
  transform: rotate(0deg) scale(1.1);
  opacity: 0.15;
}

/* ═══ Course Cards ═══════════════════════════════════════════════════════ */
.sd-course-card {
  transition: all 0.3s;
  cursor: pointer;
  border: 1px solid rgba(0,0,0,0.05) !important;
  overflow: hidden;
}
.sd-course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
.sd-course-title {
  font-weight: 700;
  font-size: 0.95rem;
  line-height: 1.3;
  height: 2.6rem;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.sd-course-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* ═══ Misc ════════════════════════════════════════════════════════════════ */
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
.staff-dev-wrap { background: #f8f9fa; min-height: 100vh; }
</style>
