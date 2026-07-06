<template>
  <div class="hero-3d-container">
    <!-- Subtle radial gradient behind canvas -->
    <div class="canvas-bg" :class="theme"></div>
    
    <TresCanvas clear-color="transparent" window-size>
      <TresPerspectiveCamera :position="[0, 0, 5]" :fov="45" />
      
      <!-- Lighting -->
      <template v-if="theme === 'dark'">
        <TresAmbientLight color="#6C63FF" :intensity="0.4" />
        <TresPointLight :position="[3, 3, 3]" color="#6C63FF" :intensity="3" />
        <TresPointLight :position="[-3, -2, 2]" color="#00D4FF" :intensity="2" />
        <TresPointLight :position="[0, 5, 0]" color="#ffffff" :intensity="0.5" />
      </template>
      <template v-else>
        <TresAmbientLight color="#ffffff" :intensity="1.2" />
        <TresPointLight :position="[3, 3, 3]" color="#5B52EE" :intensity="4" />
        <TresPointLight :position="[-3, -2, 2]" color="#0099BB" :intensity="3" />
        <TresPointLight :position="[0, 5, 0]" color="#ffffff" :intensity="2.0" />
        <TresDirectionalLight :position="[0, 10, 5]" color="#ffffff" :intensity="1.5" />
      </template>

      <!-- Central Wireframe Sphere -->
      <TresMesh>
        <TresSphereGeometry :args="[2, 32, 32]" />
        <TresMeshBasicMaterial 
          :color="theme === 'dark' ? '#6C63FF' : '#4B44CC'" 
          :opacity="theme === 'dark' ? 0.35 : 0.55" 
          transparent 
          wireframe 
        />
      </TresMesh>

      <!-- Inner Solid Sphere -->
      <TresMesh>
        <TresSphereGeometry :args="[1.8, 32, 32]" />
        <TresMeshStandardMaterial 
          :color="theme === 'dark' ? '#00D4FF' : '#0099BB'" 
          :opacity="theme === 'dark' ? 0.08 : 0.18" 
          transparent 
        />
      </TresMesh>

      <!-- Particles -->
      <TresPoints>
        <TresBufferGeometry>
          <TresBufferAttribute
            attach="attributes-position"
            :count="1000"
            :array="particlesPosition"
            :item-size="3"
          />
        </TresBufferGeometry>
        <TresPointsMaterial 
          :color="theme === 'dark' ? '#6C63FF' : '#4B44CC'" 
          :size="theme === 'dark' ? 0.025 : 0.030" 
          :opacity="theme === 'dark' ? 0.7 : 0.9" 
          transparent 
        />
      </TresPoints>

      <!-- Orbiting Nodes and Lines would go here (simplified for now) -->
      <TresOrbitControls />
    </TresCanvas>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { TresCanvas } from '@tresjs/core';
import { OrbitControls as TresOrbitControls } from '@tresjs/cientos';

const props = defineProps({
  theme: {
    type: String,
    default: 'dark'
  }
});

const particlesPosition = computed(() => {
  const positions = new Float32Array(1000 * 3);
  for (let i = 0; i < 1000; i++) {
    positions[i * 3] = (Math.random() - 0.5) * 10;
    positions[i * 3 + 1] = (Math.random() - 0.5) * 10;
    positions[i * 3 + 2] = (Math.random() - 0.5) * 10;
  }
  return positions;
});
</script>

<style scoped>
.hero-3d-container {
  position: relative;
  width: 100%;
  height: 580px;
}

.canvas-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: -1;
  pointer-events: none;
}

.canvas-bg.dark {
  background: radial-gradient(ellipse, rgba(108,99,255,0.15), transparent 70%);
}

.canvas-bg.light {
  background: radial-gradient(ellipse, rgba(91,82,238,0.10), transparent 70%);
}

@media (max-width: 768px) {
  .hero-3d-container {
    height: 280px;
  }
}
</style>
