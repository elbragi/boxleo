<template>
  <v-card class="sd-card" elevation="0" rounded="xl" @click="$emit('open', course)">
    <!-- Thumbnail -->
    <div class="sd-card-thumb" :style="thumbStyle">
      <div class="sd-card-badges">
        <v-chip size="x-small" color="white" label class="sd-cat-chip">{{ course.category }}</v-chip>
        <v-chip size="x-small" :color="levelColor" class="sd-lvl-chip" label>{{ course.level }}</v-chip>
      </div>
      <div v-if="course.is_featured" class="sd-featured-star">
        <v-icon color="amber" size="18">mdi-star</v-icon>
      </div>
      <!-- Progress overlay when enrolled -->
      <div v-if="course.enrolled" class="sd-card-prog-overlay">
        <v-progress-linear :model-value="course.progress" height="4"
          :color="course.progress===100?'success':'white'" bg-color="rgba(255,255,255,0.3)"/>
      </div>
    </div>

    <!-- Body -->
    <div class="sd-card-body">
      <div class="sd-card-title">{{ course.title }}</div>
      <div class="sd-card-desc text-caption text-grey-darken-1">{{ truncate(course.description, 80) }}</div>

      <div class="sd-card-meta">
        <span><v-icon size="13">mdi-clock-outline</v-icon> {{ course.duration_hours }}h</span>
        <span><v-icon size="13">mdi-book-open-outline</v-icon> {{ course.lessons_count }}</span>
        <span><v-icon size="13">mdi-account-group</v-icon> {{ course.enrollments_count }}</span>
      </div>
    </div>

    <!-- Footer -->
    <div class="sd-card-footer">
      <div v-if="course.provider" class="text-caption text-grey">{{ course.provider }}</div>
      <div class="sd-card-cta">
        <template v-if="course.enrolled">
          <v-chip v-if="course.progress===100" color="success" size="small" variant="tonal" prepend-icon="mdi-check-circle">
            Done
          </v-chip>
          <v-chip v-else color="primary" size="small" variant="tonal" prepend-icon="mdi-play-circle">
            {{ course.progress }}% done
          </v-chip>
        </template>
        <v-btn v-else color="primary" size="small" rounded variant="flat"
          @click.stop="$emit('enroll', course)">
          Enroll
        </v-btn>
      </div>
    </div>
  </v-card>
</template>

<script>
export default {
  name: 'CourseCard',
  props: {
    course: { type: Object, required: true },
  },
  emits: ['open', 'enroll'],
  computed: {
    thumbStyle() {
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
      return { background: gradients[this.course?.category] || gradients['General'] }
    },
    levelColor() {
      return { Beginner: 'success', Intermediate: 'warning', Advanced: 'error' }[this.course.level] || 'grey'
    },
  },
  methods: {
    truncate(str, len) {
      if (!str) return ''
      return str.length > len ? str.slice(0, len) + '…' : str
    },
  },
}
</script>

<style scoped>
.sd-card {
  display: flex;
  flex-direction: column;
  height: 100%;
  border: 1px solid rgba(0,0,0,0.07);
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  overflow: hidden;
}
.sd-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 32px rgba(0,0,0,0.12) !important;
}

.sd-card-thumb {
  height: 140px;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 10px;
}
.sd-card-badges { display: flex; gap: 6px; flex-wrap: wrap; }
.sd-cat-chip { font-size: 0.65rem !important; }
.sd-lvl-chip { font-size: 0.65rem !important; }
.sd-featured-star { position: absolute; top: 8px; right: 8px; }
.sd-card-prog-overlay { position: absolute; bottom: 0; left: 0; right: 0; }

.sd-card-body { padding: 14px 14px 6px; flex: 1; }
.sd-card-title { font-size: 0.95rem; font-weight: 700; line-height: 1.3; margin-bottom: 4px; }
.sd-card-desc { line-height: 1.4; }
.sd-card-meta {
  display: flex; gap: 10px; margin-top: 10px;
  color: #9e9e9e; font-size: 0.75rem;
  align-items: center;
}
.sd-card-meta span { display: flex; align-items: center; gap: 3px; }

.sd-card-footer {
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 10px 14px 14px;
  border-top: 1px solid rgba(0,0,0,0.04);
}
.sd-card-cta { display: flex; align-items: center; }
</style>
