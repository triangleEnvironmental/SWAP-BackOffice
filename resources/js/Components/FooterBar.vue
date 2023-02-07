<script setup>
import { computed } from 'vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import JustboilLogo from '@/Components/JustboilLogo.vue'

const year = computed(() => new Date().getFullYear())
</script>

<template>
  <footer
    class="py-2 px-6 dark:bg-gray-900/70"
  >
    <BaseLevel>
      <div class="text-center md:text-left">
        <b>&copy; {{ year }}, <a
          href="https://waste-tracker.com/"
          target="_blank"
        >SWAP</a>.</b>
        All rights reserved.
      </div>
      <div class="md:py-2">
<!--        Logo here-->
      </div>
    </BaseLevel>
  </footer>
</template>
