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
  // ── Wisdom ──────────────────────────────────────────────────────────────
  { text: "The only way to do great work is to love what you do.", author: "Steve Jobs", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },
  { text: "It does not matter how slowly you go, as long as you do not stop.", author: "Confucius", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },
  { text: "Knowing yourself is the beginning of all wisdom.", author: "Aristotle", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },
  { text: "The secret of getting ahead is getting started.", author: "Mark Twain", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },
  { text: "Life is what happens when you're busy making other plans.", author: "John Lennon", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },
  { text: "In the middle of every difficulty lies opportunity.", author: "Albert Einstein", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },
  { text: "The mind that opens to a new idea never returns to its original size.", author: "Albert Einstein", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },
  { text: "Clarity comes from engagement, not thought.", author: "Marie Forleo", category: "Wisdom", color: "deep-purple", hex: "#7c3aed", icon: "mdi-star-four-points" },

  // ── Motivation ──────────────────────────────────────────────────────────
  { text: "Success is not final, failure is not fatal — it is the courage to continue that counts.", author: "Winston Churchill", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "The future belongs to those who believe in the beauty of their dreams.", author: "Eleanor Roosevelt", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "Don't watch the clock; do what it does — keep going.", author: "Sam Levenson", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "You are never too old to set another goal or to dream a new dream.", author: "C.S. Lewis", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "It always seems impossible until it's done.", author: "Nelson Mandela", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "Act as if what you do makes a difference. It does.", author: "William James", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "We generate fears while we sit. We overcome them by action.", author: "Dr. Henry Link", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },
  { text: "You don't have to be great to start, but you have to start to be great.", author: "Zig Ziglar", category: "Motivation", color: "orange-darken-2", hex: "#ea580c", icon: "mdi-lightning-bolt" },

  // ── Teamwork ────────────────────────────────────────────────────────────
  { text: "Alone we can do so little; together we can do so much.", author: "Helen Keller", category: "Teamwork", color: "teal-darken-1", hex: "#0D8ABC", icon: "mdi-account-group" },
  { text: "Coming together is a beginning, staying together is progress, working together is success.", author: "Henry Ford", category: "Teamwork", color: "teal-darken-1", hex: "#0D8ABC", icon: "mdi-account-group" },
  { text: "If everyone is moving forward together, then success takes care of itself.", author: "Henry Ford", category: "Teamwork", color: "teal-darken-1", hex: "#0D8ABC", icon: "mdi-account-group" },
  { text: "Great things in business are never done by one person — they're done by a team.", author: "Steve Jobs", category: "Teamwork", color: "teal-darken-1", hex: "#0D8ABC", icon: "mdi-account-group" },
  { text: "None of us is as smart as all of us.", author: "Ken Blanchard", category: "Teamwork", color: "teal-darken-1", hex: "#0D8ABC", icon: "mdi-account-group" },
  { text: "Teamwork divides the task and multiplies the success.", author: "Boxleo Team", category: "Teamwork", color: "teal-darken-1", hex: "#0D8ABC", icon: "mdi-account-group" },
  { text: "The strength of the team is each individual member. The strength of each member is the team.", author: "Phil Jackson", category: "Teamwork", color: "teal-darken-1", hex: "#0D8ABC", icon: "mdi-account-group" },

  // ── Leadership ──────────────────────────────────────────────────────────
  { text: "A good leader takes a little more than their share of the blame, a little less than their share of the credit.", author: "Arnold H. Glasow", category: "Leadership", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-crown-outline" },
  { text: "The function of leadership is to produce more leaders, not more followers.", author: "Ralph Nader", category: "Leadership", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-crown-outline" },
  { text: "Leadership is not about being in charge. It is about taking care of those in your charge.", author: "Simon Sinek", category: "Leadership", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-crown-outline" },
  { text: "People buy into the leader before they buy into the vision.", author: "John C. Maxwell", category: "Leadership", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-crown-outline" },
  { text: "A leader is one who knows the way, goes the way, and shows the way.", author: "John C. Maxwell", category: "Leadership", color: "indigo-darken-1", hex: "#4338ca", icon: "mdi-crown-outline" },

  // ── Growth ──────────────────────────────────────────────────────────────
  { text: "The only person you are destined to become is the person you decide to be.", author: "Ralph Waldo Emerson", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Growth is never by mere chance; it is the result of forces working together.", author: "James Cash Penney", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "You don't learn to walk by following rules. You learn by doing and falling over.", author: "Richard Branson", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Strive not to be a success, but rather to be of value.", author: "Albert Einstein", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Your limitation — it's only your imagination.", author: "Unknown", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Comfort is the enemy of growth. Stretch a little every day.", author: "Boxleo", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },
  { text: "Don't be pushed around by the fears in your mind. Be led by the dreams in your heart.", author: "Roy T. Bennett", category: "Growth", color: "green-darken-2", hex: "#16a34a", icon: "mdi-sprout" },

  // ── Excellence ──────────────────────────────────────────────────────────
  { text: "We are what we repeatedly do. Excellence, then, is not an act, but a habit.", author: "Aristotle", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "Quality is not an act, it is a habit.", author: "Aristotle", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "Perfection is not attainable, but if we chase perfection we can catch excellence.", author: "Vince Lombardi", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "The difference between ordinary and extraordinary is that little extra.", author: "Jimmy Johnson", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "Do what you do so well that they'll want to see it again.", author: "Walt Disney", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },
  { text: "Excellence is doing ordinary things extraordinarily well.", author: "John W. Gardner", category: "Excellence", color: "amber-darken-3", hex: "#d97706", icon: "mdi-medal-outline" },

  // ── Resilience ──────────────────────────────────────────────────────────
  { text: "Fall seven times, stand up eight.", author: "Japanese Proverb", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "The bamboo that bends is stronger than the oak that resists.", author: "Japanese Proverb", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "Hard times never last, but hard people do.", author: "Robert H. Schuller", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "A smooth sea never made a skilled sailor.", author: "Franklin D. Roosevelt", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "Character is not built in good times. It's revealed in tough ones.", author: "Boxleo", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },
  { text: "Every setback is a setup for a comeback.", author: "T.D. Jakes", category: "Resilience", color: "red-darken-2", hex: "#dc2626", icon: "mdi-shield-half-full" },

  // ── Purpose ─────────────────────────────────────────────────────────────
  { text: "The two most important days in your life are the day you are born and the day you find out why.", author: "Mark Twain", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "Your work is going to fill a large part of your life. Be satisfied.", author: "Steve Jobs", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "He who has a why to live can bear almost any how.", author: "Friedrich Nietzsche", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "The purpose of life is not to be happy, but to matter.", author: "Leo Rosten", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },
  { text: "Your job title tells people what you do. Your purpose tells people why it matters.", author: "Boxleo", category: "Purpose", color: "pink-darken-1", hex: "#db2777", icon: "mdi-compass-outline" },

  // ── Creativity ──────────────────────────────────────────────────────────
  { text: "Creativity is intelligence having fun.", author: "Albert Einstein", category: "Creativity", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-palette-outline" },
  { text: "Innovation distinguishes between a leader and a follower.", author: "Steve Jobs", category: "Creativity", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-palette-outline" },
  { text: "Every artist was first an amateur.", author: "Ralph Waldo Emerson", category: "Creativity", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-palette-outline" },
  { text: "Think left and think right and think low and think high. Oh, the thinks you can think if only you try.", author: "Dr. Seuss", category: "Creativity", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-palette-outline" },
  { text: "Creativity is not a talent. It is a way of operating.", author: "John Cleese", category: "Creativity", color: "cyan-darken-2", hex: "#0891b2", icon: "mdi-palette-outline" },

  // ── Boxleo originals ─────────────────────────────────────────────────────
  { text: "Every delivery delivered on time is a promise kept. You make that possible.", author: "Boxleo", category: "Boxleo", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-package-variant-closed" },
  { text: "Behind every satisfied customer is a team that cared enough to get it right.", author: "Boxleo", category: "Boxleo", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-package-variant-closed" },
  { text: "Boxleo moves fast because you show up. Thank you.", author: "Boxleo", category: "Boxleo", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-package-variant-closed" },
  { text: "The route to excellence begins at your desk, every single morning.", author: "Boxleo", category: "Boxleo", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-package-variant-closed" },
  { text: "Small consistent actions build a great company. You are one of those actions.", author: "Boxleo", category: "Boxleo", color: "teal-darken-2", hex: "#0f766e", icon: "mdi-package-variant-closed" },
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
