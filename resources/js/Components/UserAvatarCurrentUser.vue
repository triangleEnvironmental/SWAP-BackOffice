<script setup>
import { computed } from 'vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import {usePage} from "@inertiajs/inertia-vue3";

const userName = computed(() => usePage().props.value.user.name)
const avatar = computed(() => usePage().props.value.user.profile_photo_url)
</script>

<template>
  <UserAvatar
      :username="userName"
      api="initials"
      :avatar="avatar"
  />
</template>
