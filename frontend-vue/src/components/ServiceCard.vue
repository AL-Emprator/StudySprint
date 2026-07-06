<template>
  <div class="service-card" :class="{ 'glow-violet': index % 2 === 0, 'glow-cyan': index % 2 !== 0 }">
    <div class="icon-container">
      <div class="icon-glow"></div>
      <slot name="icon">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
        </svg>
      </slot>
    </div>
    <h3 class="title">{{ title }}</h3>
    <p class="description">{{ description }}</p>
    <a href="#" class="learn-more">
      {{ $t('services.learnMore') }}
      <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M5 12h14M12 5l7 7-7 7" />
      </svg>
    </a>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  description: String,
  index: Number
});
</script>

<style scoped>
.service-card {
  background: var(--bg-card);
  border: var(--border-card);
  padding: 32px;
  border-radius: 20px;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: relative;
  overflow: hidden;
}

.service-card:hover {
  transform: translateY(-4px);
  border-color: var(--border-card-hover);
  box-shadow: 0 8px 32px rgba(108, 99, 255, 0.15);
}

[data-theme='light'] .service-card:hover {
  box-shadow: 0 8px 32px rgba(91, 82, 238, 0.10);
}

.icon-container {
  width: 40px;
  height: 40px;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.icon {
  width: 24px;
  height: 24px;
  z-index: 1;
  transition: filter 0.3s;
}

.service-card:hover .icon {
  filter: brightness(1.2);
}

.glow-violet .icon { color: #6C63FF; }
.glow-cyan .icon { color: #00D4FF; }

.icon-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  height: 100%;
  border-radius: 50%;
  filter: blur(12px);
  opacity: 0.2;
}

.glow-violet .icon-glow { background: #6C63FF; }
.glow-cyan .icon-glow { background: #00D4FF; }

.title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
}

.description {
  font-size: 14px;
  color: var(--text-secondary);
  line-height: 1.6;
  flex: 1;
}

.learn-more {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-weight: 600;
  font-size: 13px;
  color: var(--accent);
  margin-top: 8px;
}

.arrow {
  width: 16px;
  height: 16px;
  transition: transform 0.2s;
}

.learn-more:hover .arrow {
  transform: translateX(4px);
}
</style>
