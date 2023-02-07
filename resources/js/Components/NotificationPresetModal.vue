<template>
  <CardBoxModal
    v-model="computedValue"
    ref="modalRef"
    :icon="Icons.preset"
    title="Select a notification preset"
    :has-button="false"
    :has-cancel="true"
    :small="false"
  >
    <template v-if="presetOptions !== null">
      <!--      <paginated-table :data="presetOptions"-->
      <!--                       :checkable="false"-->
      <!--                       :titles="['Title', 'Description']"-->
      <!--                       :get-id="(item) => item.id"-->
      <!--                       :get-label="(item) => item.title">-->
      <!--        <template v-slot:row="{row: {row, index}}">-->
      <!--          <td data-label="Title">-->
      <!--            {{ row.title }}-->
      <!--          </td>-->
      <!--          <td data-label="Description">-->
      <!--            {{ row.description }}-->
      <!--          </td>-->
      <!--          <td data-label="Actions">-->
      <!--            <BaseButtons-->
      <!--              type="justify-start lg:justify-end"-->
      <!--              no-wrap-->
      <!--            >-->
      <!--              <BaseButton label="Select"-->
      <!--                          color="info"-->
      <!--                          @click="select(row)"/>-->
      <!--            </BaseButtons>-->
      <!--          </td>-->
      <!--        </template>-->
      <!--      </paginated-table>-->
      <table>
        <thead>
        <tr>
          <th class="text-center">Title</th>
          <th class="text-center">Description</th>
        </tr>
        </thead>
        <tbody>
        <template v-if="presetOptions.length === 0">
          <tr><td colspan="2" class="text-center justify-center py-10 text-gray-500">
            ( No preset yet )
          </td></tr>
        </template>
        <tr v-for="option in presetOptions">
          <td data-label="Title">
            {{ option.title }}
          </td>
          <td data-label="Description">
            {{ option.description }}
          </td>
          <td>
            <BaseButton label="Select"
                        color="info"
                        @click="select(option)"/>
          </td>
        </tr>
        </tbody>
      </table>
    </template>
    <template v-else>
      <div class="text-center p-8">
        Loading ...
      </div>
    </template>
  </CardBoxModal>


</template>

<script setup>
import PaginatedTable from "@/Components/PaginatedTable.vue"
import CardBoxModal from "@/Components/CardBoxModal.vue"
import BaseButtons from "@/Components/BaseButtons.vue"
import BaseButton from "@/Components/BaseButton.vue"
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormFilePicker from '@/Components/FormFilePicker.vue'
import {ref, computed, watch, onMounted} from "vue";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import {useToast} from "vue-toastification";

const emit = defineEmits([
  'update:modelValue',
  'selected'
])

const toast = useToast()
const modalRef = ref()
const presetOptions = ref(null)

const props = defineProps({
  modelValue: [Boolean, String, null, Object],
  areaId: [String, Number],
})

// watch(() => props.modelValue, (isOpen) => {
//   if (isOpen) {
//
//   }
// })

const fetchOptions = async () => {
  try {
    const params = {}

    if (props.areaId) {
      params['area_id'] = props.areaId
    }

    const response = await axios.get('/notification-presets/options', {
      params: params
    })
    presetOptions.value = response.data.data
  } catch (e) {
    toast.error(e.toString());
  }
}

const select = (option) => {
  emit('update:modelValue', false)
  emit('selected', option)
}

const computedValue = computed({
  get: () => props.modelValue,
  set: value => {
    emit('update:modelValue', value)
  }
})

onMounted(() => {
  fetchOptions()
})
</script>

<style scoped>

</style>
