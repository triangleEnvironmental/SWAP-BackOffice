<template>
  <ConfirmModal ref="confirmModalRef"/>

  <div v-if="!loading && (!commentList || commentList.length === 0)">
    <div class="text-center py-6 text-gray-500">
      No comments
    </div>
  </div>

  <div v-for="comment in commentList" class="border-t px-2 py-4">
    <div class="flex items-start">
      <div class="flex items-start grow">
        <UserAvatar :username="comment.commenter.full_name"
                    class="w-8 h-8"
                    :avatar="comment.commenter.profile_photo_url"/>
        <div class="ml-2">
          <h6 class="font-bold">{{ comment.commenter.full_name }}</h6>
          <p v-if="comment.created_at" class="text-gray">
            {{ $helper.fromNow(comment.created_at) }}
          </p>
        </div>
      </div>
      <BaseButton v-if="comment.can_delete"
                  small
                  @click="onDelete(comment.id)"
                  :icon="Icons.delete"
                  outline
                  color="danger"/>
    </div>

    <blockquote class="bg-gray-50 dark:bg-gray-800 py-3 px-2 border-l-4 mt-2 dark:border-gray-500">
      <p>
        {{ comment.text }}
      </p>
    </blockquote>

    <div v-if="comment.medias && comment.medias.length > 0"
         class="grid grid-cols-3 gap-2 mt-3">
      <photo-provider>
        <div v-for="media in comment.medias"
             class="aspect-square rounded-md bg-gray-100 dark:bg-gray-800">
          <photo-consumer :src="media.media_url">
            <img :src="media.media_url"
                 class="aspect-square object-cover rounded-md"
                 alt="Comment image">
          </photo-consumer>
        </div>
      </photo-provider>
    </div>
  </div>

  <div v-if="commentListUrl" class="text-center my-4">
    <template v-if="loading">
      <span class="text-gray">
        Loading ...
      </span>
    </template>
    <BaseButton v-else
                label="Load more"
                outline
                @click="fetchComments"
                color="info"/>
  </div>
</template>

<script setup>
import BaseButton from '@/Components/BaseButton.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import {onMounted, ref} from "vue";
import {useToast} from "vue-toastification";
import {Inertia} from "@inertiajs/inertia";

const props = defineProps({
  initialUrl: {
    type: String,
    required: true,
  }
})

const toast = useToast()
const commentList = ref([])
const loading = ref(false)
let commentListUrl = ref(props.initialUrl)
const confirmModalRef = ref()

const onDelete = async (id) => {
  const confirm = await confirmModalRef.value?.openModal({
    title: 'Delete comment',
    content: 'Are you sure to delete this comment?'
  })
  if (confirm) {
    Inertia.delete(
      route('comment.delete', {id}),
      {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          const find = commentList.value.filter(c => c.id === id)
          if (find.length > 0) {
            commentList.value.splice(commentList.value.indexOf(find[0]), 1)
          }
        }
      }
    )
  }
}

const fetchComments = async () => {
  try {
    loading.value = true
    const response = await axios.get(commentListUrl.value)
    commentList.value = commentList.value.concat(response.data.data)
    commentListUrl.value = response.data.next_page_url
  } catch (e) {
    toast.error(e.toString());
  } finally {
    loading.value = false
  }
}

const updateNewComments = async () => {
  try {
    const response = await axios.get(props.initialUrl)
    for (let i = response.data.data.length - 1; i >= 0; i--) {
      const comment = response.data.data[i]
      if (commentList.value.some(c => c.id === comment.id)) {
        continue
      }
      commentList.value.unshift(comment)
    }
  } catch (e) {
    toast.error(e.toString());
  }
}

onMounted(() => {
  fetchComments()
})

defineExpose({
  updateNewComments
})
</script>

<style scoped>

</style>
