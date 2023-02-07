<template>
<!--  <photo-provider>-->
<!--    <photo-consumer v-for="(url2, i) in imageUrls" :intro="url2" :key="url2" :src="url2">-->
<!--      <img :src="url2" alt="Report image">-->
<!--    </photo-consumer>-->
<!--  </photo-provider>-->


  <photo-provider>
    <Carousel :items-to-show="1">
      <Slide v-for="(url, index) in imageUrls" :key="index" :index="index">
        <photo-consumer :src="url">
          <img :style="imageStyle" :src="url" alt="Report image">
        </photo-consumer>
      </Slide>

      <template #addons>
        <Navigation />
        <Pagination />
      </template>
    </Carousel>
  </photo-provider>
</template>

<script setup>
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';

const props = defineProps({
  imageUrls: {
    type: Array,
    default: [],
  },
  imageStyle: {
    type: String,
    default: '',
  }
})
</script>

<style scoped>

</style>
