<template>
  <v-card class="glass-card cheer-widget mb-4 animate-fade-in-up" style="animation-delay: 0.8s;">
    <div class="cheer-bg-glow"></div>
    <v-card-text class="pa-6 pb-8 text-center position-relative z-index-1">
      <div class="d-flex justify-center mb-4">
        <div class="cheer-icon-container">
          <v-icon color="warning" size="32" class="swing-animation">mdi-lightbulb-on</v-icon>
        </div>
      </div>
      
      <div class="quote-container px-2">
        <v-icon color="primary" size="24" class="quote-mark left-quote">mdi-format-quote-open</v-icon>
        <p class="cheer-text mb-4">"{{ currentQuote.text }}"</p>
        <v-icon color="primary" size="24" class="quote-mark right-quote">mdi-format-quote-close</v-icon>
      </div>
      
      <div class="cheer-author text-caption font-weight-bold text-medium-emphasis text-uppercase tracking-wider">
        — {{ currentQuote.author }}
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  name: 'DailyCheerWidget',
  data() {
    return {
      quotes: [
        { text: "Manifesting a productive day for you. ✨", author: "Vibe Check" },
        { text: "Main character energy only today. 🚀", author: "Daily Glow" },
        { text: "Don't forget to hydrate and slay. 💅", author: "Self Care Club" },
        { text: "Sending you positive vibes and good coffee. ☕", author: "Morning Brew" },
        { text: "POV: You're crushing your goals today. 🎯", author: "Manifest Station" },
        { text: "Secure the bag. 💰", author: "Hustle Hard" },
        { text: "Less stress, more success. 🌊", author: "Flow State" },
        { text: "Growth mindset activated. 🧠", author: "Mindset Matters" },
        { text: "Make today your best one yet. 🌟", author: "Daily Best" },
        { text: "Stay humble, hustle hard. 💪", author: "The Grind" },
        { text: "Energy is contagious. Make it positive. ⚡", author: "Positive Pulse" },
        { text: "Work hard, play hard. 🎮", author: "Balance Life" },
        { text: "You got this. No cap. 🧢", author: "Real Talk" },
        { text: "Building the future, one task at a time. 🏗️", author: "Future Builders" },
        { text: "Slay the day! 🐉", author: "Warrior Mode" },
        { text: "Vibe check: Passed. ✅", author: "Good Vibes" },
        { text: "Doing it for the plot. 📖", author: "Life Story" },
        { text: "Keep that same energy. 🔥", author: "Consistency" },
        { text: "Big moves only. 🏃‍♂️", author: "Action Taker" },
        { text: "Manifest it. Work for it. 🌈", author: "Dream Big" },
        { text: "Your impact is greater than you know. 🌎", author: "Impact Hub" },
        { text: "Leadership is an action, not a position. 🤝", author: "Lead Well" },
        { text: "Excellence is not a destination; it's a continuous journey. 🛤️", author: "Daily Excellence" },
        { text: "Strategic thinking, bold execution. 🎯", author: "Strategy First" },
        { text: "Innovation starts with a single question. ❓", author: "Innovate Daily" },
        { text: "Collaboration is the fuel for uncommon results. ⛽", author: "Team Power" },
        { text: "Success is built one decision at a time. ⚖️", author: "Decision Maker" },
        { text: "Your dedication sets the standard for the team. 🎖️", author: "Team Leader" },
        { text: "Progress over perfection, always. 📈", author: "Keep Growing" },
        { text: "Lead with empathy, execute with precision. ❤️", author: "Balanced Leader" }
      ]
    };
  },
  computed: {
    currentQuote() {
      // Pick a quote based on the day of the month (1-31), wrapping around if needed
      const dayOfMonth = new Date().getDate();
      const index = (dayOfMonth - 1) % this.quotes.length;
      return this.quotes[index];
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap');

.cheer-widget {
  background: linear-gradient(145deg, rgba(255,255,255,0.95), rgba(250,252,255,0.85)) !important;
  border: 1px solid rgba(13, 138, 188, 0.15) !important;
  box-shadow: 0 10px 30px rgba(13, 138, 188, 0.08) !important;
  overflow: hidden;
  height: 100%;
}

.cheer-bg-glow {
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at center, rgba(245, 158, 11, 0.08) 0%, transparent 50%);
  animation: rotateGlow 20s linear infinite;
  pointer-events: none;
}

@keyframes rotateGlow {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.cheer-icon-container {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #FFFBEB;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 2px 4px rgba(245, 158, 11, 0.2), 0 4px 12px rgba(245, 158, 11, 0.15);
  border: 2px solid rgba(245, 158, 11, 0.1);
}

.swing-animation {
  transform-origin: top center;
  animation: swing 4s ease-in-out infinite;
}

@keyframes swing {
  0%, 100% { transform: rotate(-10deg); }
  50% { transform: rotate(10deg); }
}

.quote-container {
  position: relative;
  display: inline-block;
}

.cheer-text {
  font-family: 'Caveat', cursive;
  font-size: 1.8rem;
  line-height: 1.3;
  color: #1e293b;
  text-shadow: 0 1px 2px rgba(0,0,0,0.05);
  letter-spacing: 0.5px;
  max-width: 90%;
  margin: 0 auto;
}

.quote-mark {
  position: absolute;
  opacity: 0.2;
}

.left-quote {
  top: -10px;
  left: -15px;
}

.right-quote {
  bottom: 0px;
  right: -15px;
}

.tracking-wider {
  letter-spacing: 1.5px;
  color: #0D8ABC !important;
}

.z-index-1 {
  position: relative;
  z-index: 1;
}
</style>
