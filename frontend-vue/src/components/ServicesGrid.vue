<template>
  <div class="services-grid">
    <ServiceCard 
      v-for="(key, index) in serviceKeys" 
      :key="key"
      :index="index"
      :title="$t(`services.items.${key}`)"
      :description="getDescription(key)"
    />
  </div>
</template>

<script setup>
import ServiceCard from './ServiceCard.vue';

const serviceKeys = [
  'webDev', 'webApp', 'ios', 'android', 'desktop', 
  'aiAgent', 'agentic', 'workflow', 'aiAuto', 'internal', 
  'api', 'ecommerce', 'security', 'sla'
];

// In a real app, these would be in the i18n files. 
// For now, I'll provide generic descriptions that look premium.
const getDescription = (key) => {
  const descriptions = {
    webDev: "Modern, high-performance websites built with the latest technologies for maximum engagement.",
    webApp: "Scalable, secure, and intuitive web applications tailored to your business needs.",
    ios: "Premium iOS applications delivering a seamless user experience on the Apple ecosystem.",
    android: "Robust Android development for a diverse range of devices and global reach.",
    desktop: "Powerful cross-platform desktop applications for Windows, macOS, and Linux.",
    aiAgent: "Intelligent AI agents designed to interact, learn, and solve complex tasks autonomously.",
    agentic: "Complex ecosystems of AI agents working together to streamline enterprise workflows.",
    workflow: "Optimizing business processes through smart automation and integrated systems.",
    aiAuto: "Leveraging machine learning to automate repetitive tasks and drive efficiency.",
    internal: "Custom-built internal tools and dashboards to empower your team's productivity.",
    api: "Engineering secure and scalable APIs to connect your services and data seamlessly.",
    ecommerce: "High-conversion e-commerce solutions that scale with your business growth.",
    security: "Ensuring your systems meet the highest standards of security and compliance.",
    sla: "Comprehensive maintenance and support to ensure your long-term success."
  };
  return descriptions[key];
};
</script>

<style scoped>
.services-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

@media (max-width: 1024px) {
  .services-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
  .services-grid { grid-template-columns: 1fr; }
}
</style>
