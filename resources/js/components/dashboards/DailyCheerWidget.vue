<template>
  <v-card class="cheer-card overflow-hidden" :style="cardStyle">
    <div class="glow-layer" :style="glowStyle"></div>

    <v-card-text class="pa-5 position-relative" style="z-index:1">
      <div class="d-flex align-center justify-space-between mb-4">
        <!-- Category badge -->
        <v-chip size="x-small" :color="current.color" variant="flat" class="font-weight-black text-uppercase tracking-wide px-3">
          <v-icon start size="12">{{ current.icon }}</v-icon>
          {{ current.category }}
        </v-chip>

        <!-- Next quote button -->
        <v-btn icon size="x-small" variant="text" :color="current.color" @click="next" title="Next quote">
          <v-icon size="16">mdi-refresh</v-icon>
        </v-btn>
      </div>

      <!-- Quote text -->
      <div class="quote-body mb-4 px-1">
        <span class="open-quote" :style="`color:${colorHex}`">&ldquo;</span>
        <p class="quote-text my-0">{{ current.text }}</p>
        <span class="close-quote" :style="`color:${colorHex}`">&rdquo;</span>
      </div>

      <!-- Author + day counter -->
      <div class="d-flex align-center justify-space-between">
        <div class="author-line" :style="`border-color:${colorHex}40`">
          <div class="author-dot" :style="`background:${colorHex}`"></div>
          <span class="text-caption font-weight-black text-uppercase" :style="`color:${colorHex}`">
            {{ current.author }}
          </span>
        </div>
        <span class="text-caption text-medium-emphasis">{{ todayLabel }}</span>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
const QUOTES = [
  // ── Reflection ───────────────────────────────────────────────────────────
  { text: "The longest journey you will ever take is the eighteen inches from your head to your heart.", author: "Andrew Bennett", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },
  { text: "Between stimulus and response there is a space. In that space lies our power to choose our response.", author: "Viktor Frankl", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },
  { text: "What you are is what you have been. What you'll be is what you do now.", author: "Buddha", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },
  { text: "The unexamined life is not worth living.", author: "Socrates", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },
  { text: "You cannot solve a problem with the same mind that created it.", author: "Albert Einstein", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },
  { text: "Almost everything will work again if you unplug it for a few minutes — including you.", author: "Anne Lamott", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },
  { text: "Your emotions are visitors. You don't have to invite them to stay.", author: "Rumi", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },
  { text: "Sit quietly sometimes. The answers you seek live in the silence between your thoughts.", author: "Boxleo", category: "Reflection", color: "deep-purple", hex: "#7c3aed", icon: "mdi-head-heart-outline" },

  // ── Tomorrow ─────────────────────────────────────────────────────────────
  { text: "The best time to plant a tree was twenty years ago. The second best time is now.", author: "Chinese Proverb", category: "Tomorrow", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-calendar-arrow-right" },
  { text: "You can't go back and change the beginning, but you can start where you are and change the ending.", author: "C.S. Lewis", category: "Tomorrow", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-calendar-arrow-right" },
  { text: "Tomorrow belongs to those who prepare for it today.", author: "Malcolm X", category: "Tomorrow", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-calendar-arrow-right" },
  { text: "Don't let yesterday take up too much of today.", author: "Will Rogers", category: "Tomorrow", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-calendar-arrow-right" },
  { text: "Your future is created by what you do today, not tomorrow.", author: "Robert Kiyosaki", category: "Tomorrow", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-calendar-arrow-right" },
  { text: "One year from now you will wish you had started today.", author: "Karen Lamb", category: "Tomorrow", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-calendar-arrow-right" },
  { text: "The future starts with the choices you make in ordinary moments.", author: "Boxleo", category: "Tomorrow", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-calendar-arrow-right" },

  // ── Growth ──────────────────────────────────────────────────────────────
  { text: "The only person you are destined to become is the person you decide to be.", author: "Ralph Waldo Emerson", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "You don't learn to walk by following rules. You learn by doing and falling over.", author: "Richard Branson", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Strive not to be a success, but rather to be of value.", author: "Albert Einstein", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Comfort is the enemy of growth. Stretch a little every day.", author: "Boxleo", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Don't be pushed around by the fears in your mind. Be led by the dreams in your heart.", author: "Roy T. Bennett", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Invest in yourself. Your career is the engine of your wealth.", author: "Paul Clitheroe", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Be not afraid of growing slowly. Be afraid only of standing still.", author: "Chinese Proverb", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "What would you attempt to do if you knew you could not fail?", author: "Robert H. Schuller", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },

  // ── Wisdom ──────────────────────────────────────────────────────────────
  { text: "It does not matter how slowly you go, as long as you do not stop.", author: "Confucius", category: "Wisdom", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-star-four-points" },
  { text: "Knowing yourself is the beginning of all wisdom.", author: "Aristotle", category: "Wisdom", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-star-four-points" },
  { text: "Life is what happens when you're busy making other plans.", author: "John Lennon", category: "Wisdom", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-star-four-points" },
  { text: "In the middle of every difficulty lies opportunity.", author: "Albert Einstein", category: "Wisdom", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-star-four-points" },
  { text: "The mind that opens to a new idea never returns to its original size.", author: "Albert Einstein", category: "Wisdom", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-star-four-points" },
  { text: "We are not human beings having a spiritual experience. We are spiritual beings having a human experience.", author: "Pierre Teilhard de Chardin", category: "Wisdom", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-star-four-points" },
  { text: "The man who moves a mountain begins by carrying away small stones.", author: "Confucius", category: "Wisdom", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-star-four-points" },

  // ── Motivation ──────────────────────────────────────────────────────────
  { text: "You are never too old to set another goal or to dream a new dream.", author: "C.S. Lewis", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "It always seems impossible until it's done.", author: "Nelson Mandela", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "Act as if what you do makes a difference. It does.", author: "William James", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "We generate fears while we sit. We overcome them by action.", author: "Dr. Henry Link", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "You don't have to be great to start, but you have to start to be great.", author: "Zig Ziglar", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "The secret of getting ahead is getting started.", author: "Mark Twain", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },

  // ── Resilience ──────────────────────────────────────────────────────────
  { text: "Fall seven times, stand up eight.", author: "Japanese Proverb", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "The bamboo that bends is stronger than the oak that resists.", author: "Japanese Proverb", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "A smooth sea never made a skilled sailor.", author: "Franklin D. Roosevelt", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "Character is not built in good times. It's revealed in tough ones.", author: "Boxleo", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "Every setback is a setup for a comeback.", author: "T.D. Jakes", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "You were given this life because you are strong enough to live it.", author: "Robin Sharma", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },

  // ── Purpose ─────────────────────────────────────────────────────────────
  { text: "The two most important days in your life are the day you are born and the day you find out why.", author: "Mark Twain", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "He who has a why to live can bear almost any how.", author: "Friedrich Nietzsche", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "The purpose of life is not to be happy, but to matter.", author: "Leo Rosten", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "Your job title tells people what you do. Your purpose tells people why it matters.", author: "Boxleo", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "Don't ask what the world needs. Ask what makes you come alive — and then go do it.", author: "Howard Thurman", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },

  // ── Excellence ──────────────────────────────────────────────────────────
  { text: "We are what we repeatedly do. Excellence, then, is not an act, but a habit.", author: "Aristotle", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "The difference between ordinary and extraordinary is that little extra.", author: "Jimmy Johnson", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "Do what you do so well that they'll want to see it again.", author: "Walt Disney", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "Excellence is doing ordinary things extraordinarily well.", author: "John W. Gardner", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "Take pride in how far you've come. Have faith in how far you can go.", author: "Michael Josephson", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },

  // ── Creativity ──────────────────────────────────────────────────────────
  { text: "Creativity is intelligence having fun.", author: "Albert Einstein", category: "Creativity", color: "blue-darken-1", hex: "#1d4ed8", icon: "mdi-palette-outline" },
  { text: "Every artist was first an amateur.", author: "Ralph Waldo Emerson", category: "Creativity", color: "blue-darken-1", hex: "#1d4ed8", icon: "mdi-palette-outline" },
  { text: "Creativity is not a talent. It is a way of operating.", author: "John Cleese", category: "Creativity", color: "blue-darken-1", hex: "#1d4ed8", icon: "mdi-palette-outline" },
  { text: "You can't use up creativity. The more you use, the more you have.", author: "Maya Angelou", category: "Creativity", color: "blue-darken-1", hex: "#1d4ed8", icon: "mdi-palette-outline" },
  { text: "The worst enemy of creativity is self-doubt.", author: "Sylvia Plath", category: "Creativity", color: "blue-darken-1", hex: "#1d4ed8", icon: "mdi-palette-outline" },

  // ── Real Talk ────────────────────────────────────────────────────────────
  { text: "Plot twist: you're actually further along than you think. 🤯", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },
  { text: "Not every day will be a 10. A 6 that shows up beats a 10 that doesn't. 💪", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },
  { text: "Monday called. You answered. That's already a win. ☕", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },
  { text: "Hot take: the you from a year ago would genuinely be impressed right now. 🔥", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },
  { text: "Your 9-to-5 is building your 5-to-9. Keep going. 🚀", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },
  { text: "Main character energy isn't arrogance. It's knowing your story matters. ✨", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },
  { text: "Romanticize your growth era. This chapter genuinely deserves it. 📖", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },
  { text: "You can't pour from an empty cup. Rest is not laziness — it's maintenance. 🧘", author: "Boxleo", category: "Real Talk", color: "pink-accent-2", hex: "#e91e8c", icon: "mdi-chat-processing-outline" },

  // ── Boxleo originals ─────────────────────────────────────────────────────
  { text: "Every delivery on time is a promise kept. You make that possible.", author: "Boxleo", category: "Boxleo", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-package-variant-closed" },
  { text: "The version of you that shows up today is the foundation of who you'll be tomorrow.", author: "Boxleo", category: "Boxleo", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-package-variant-closed" },
  { text: "Small consistent actions build a great life. Start with today.", author: "Boxleo", category: "Boxleo", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-package-variant-closed" },
  { text: "You are writing your story with every choice you make. Make it one worth reading.", author: "Boxleo", category: "Boxleo", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-package-variant-closed" },
  { text: "The best investment you'll ever make is in becoming a better version of yourself.", author: "Boxleo", category: "Boxleo", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-package-variant-closed" },
];

export default {
  name: 'DailyCheerWidget',
  data() {
    // Seed based on day so everyone sees the same quote, but allow manual cycling
    const dayIndex = (new Date().getDate() - 1 + new Date().getMonth() * 31) % QUOTES.length;
    return {
      index: dayIndex,
    };
  },
  computed: {
    current() { return QUOTES[this.index]; },
    colorHex() { return this.current.hex; },
    cardStyle() {
      return `background: linear-gradient(135deg, ${this.colorHex}10 0%, white 60%) !important;
              border: 1px solid ${this.colorHex}25 !important;`;
    },
    glowStyle() {
      return `background: radial-gradient(circle at 20% 20%, ${this.colorHex}18 0%, transparent 60%);`;
    },
    todayLabel() {
      return new Date().toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long' });
    },
  },
  methods: {
    next() {
      this.index = (this.index + 1) % QUOTES.length;
    },
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lora:ital,wght@1,600&display=swap');

.cheer-card {
  border-radius: 16px !important;
  box-shadow: 0 4px 24px rgba(0,0,0,.06) !important;
  transition: box-shadow .3s;
  backdrop-filter: blur(12px);
}
.cheer-card:hover { box-shadow: 0 8px 32px rgba(0,0,0,.1) !important; }

.glow-layer {
  position: absolute; inset: 0; pointer-events: none; border-radius: 16px;
}

.quote-body { position: relative; }

.open-quote, .close-quote {
  font-family: Georgia, serif; font-size: 3rem; line-height: 1;
  display: inline-block; opacity: .35; font-weight: 900;
}
.open-quote { vertical-align: text-bottom; margin-right: 4px; }
.close-quote { vertical-align: text-top; margin-left: 4px; }

.quote-text {
  font-family: 'Lora', Georgia, serif;
  font-size: 1.05rem;
  font-style: italic;
  line-height: 1.65;
  color: #1e293b;
  display: inline;
}

.author-line {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 10px; border-radius: 20px; border: 1px solid;
}
.author-dot {
  width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
}
.tracking-wide { letter-spacing: .06em; }
</style>
