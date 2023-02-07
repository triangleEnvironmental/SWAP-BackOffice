<template>
  <CardBoxModal
    :key="value"
    v-model="value"
    :title="'Write a comment'"
    button-label="Submit"
    :has-button="true"
    :close-on-done="false"
    @confirm="onConfirm"
    :has-cancel="true">

    <FormField class="mb-0" :error="errorText">
      <div class="flex items-center">
        <FormControl v-model="comment"
                     class="grow"
                     ref="inputRef"
                     autofocus
                     :icon="Icons.comment"
                     @keyup.enter="onConfirm"
                     placeholder="Type your comment here"/>
        <FormFilePicker title="Add photos"
                        color="contrast"
                        @update:modelValue="onSelectedFile"
                        class="ml-2"
                        outline
                        multiple
                        accept="image/png, image/jpg, image/jpeg;"
                        :icon="Icons.camera"/>
      </div>
    </FormField>

    <div class="grid grid-cols-12 gap-2 mt-4">
      <div v-for="photo in photos"
           class="comment-image col-span-4 aspect-square rounded-md bg-gray-100 border border-gray-400 relative">
        <img class="object-cover aspect-square rounded-md bg-gray-100"
             :src="getObjectUrl(photo)"
             alt="Comment photo">
        <div class="image-close-button invisible absolute p-1 right-0 top-0 bg-black bg-opacity-25 hover:bg-opacity-40 cursor-pointer text-white rounded-full flex items-center justify-center"
            @click="removeImage(photo)">
          <BaseIcon :path="Icons.close"/>
        </div>
      </div>
    </div>

  </CardBoxModal>
</template>

<script setup>
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import BaseButton from '@/Components/BaseButton.vue'
import FormField from '@/Components/FormField.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import FormControl from '@/Components/FormControl.vue'
import {computed, nextTick, ref, watch, reactive} from 'vue'

const comment = ref('')
const inputRef = ref()
const photos = ref([])
const errorText = ref('')

const emit = defineEmits([
  'update:modelValue',
  'submit'
])

const props = defineProps({
  modelValue: {
    type: Boolean,
  }
})

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    errorText.value = ''
    comment.value = ''
    photos.value = []
    nextTick(() => {
      inputRef.value?.focus()
    })
  }
})

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})

const getObjectUrl = (file) => {
  return URL.createObjectURL(file)
}

const onSelectedFile = (files) => {
  for (const file of files) {
    photos.value.push(file)
  }
}

const onConfirm = () => {
  if (comment.value?.trim()) {
    onSubmit()
  } else {
    errorText.value = 'Comment cannot be empty'
  }
}

const removeImage = (file) => {
  const find = photos.value.indexOf(file)
  if (find > -1) {
    photos.value.splice(find, 1)
  }
}

const onSubmit = () => {
  emit('submit', {
    text: comment.value,
    images: photos.value,
  })
}
</script>

<style scoped>
.comment-image:hover .image-close-button {
  @apply visible;
}
</style>
