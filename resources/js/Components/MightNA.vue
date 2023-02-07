<template>
    <template v-if="havingValue">
        <slot>
            {{ value }}
        </slot>
    </template>
    <template v-else>
        <span class="text-gray-400">
            (N/A)
        </span>
    </template>
</template>

<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
    value: {
        required: true
    },
    factor: {
        type: [Boolean, null],
        default: null
    }
})

const havingValue = computed(() => {
    if (props.factor !== null) {
        return props.factor
    } else {
        return !!props.value
    }
})

</script>

<style scoped>

</style>
