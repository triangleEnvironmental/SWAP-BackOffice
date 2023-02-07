<template>
  <CardBoxModal
    v-if="userTable"
    v-model="value"
    :title="'Assign this report to a user'"
    :has-button="false"
    :small="false"
    :has-cancel="true">

    <div v-if="activeId" class="grid grid-cols-12 gap-3">
      <div class="col-span-12 sm:col-span-6">
        <div @click="onClick(null)" class="rounded-2xl p-4 hover:bg-orange-100 cursor-pointer border">
          <div class="flex items-center">
            <BaseIcon class="mr-3" :path="Icons.userRemove"/>
            <div class="md:text-left overflow-hidden">
              <h4 class="text-lg text-ellipsis">Unassign</h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <h4 class="text-md font-bold">Select a user to assign:</h4>

    <!--    Admin users-->

    <template v-if="userTable.admin_users && userTable.admin_users.length > 0">
      <div class="grid grid-cols-12 gap-3">
        <div v-for="user in userTable.admin_users" class="col-span-12 sm:col-span-6">
          <div class="rounded-2xl p-4 hover:bg-blue-100 cursor-pointer border"
               :class="{
                  'bg-green-100': activeId === user.id,
               }"
               @click="onClick(user.id)">
            <div class="flex">
              <UserAvatar class="w-12 h-12 mr-3"
                          :avatar="user.profile_photo_url"
                          :username="user.name"/>
              <div class="md:text-left overflow-hidden">
                <h4 class="text-lg text-ellipsis">
                  {{ user.name }}
                  <BaseIcon v-if="activeId === user.id"
                            class="text-green-600"
                            :path="Icons.check"/>
                </h4>
                <p class="text-gray-500 dark:text-slate-400">
                  <template v-if="user.institution">
                    {{ user.institution.name_en }}
                  </template>
                </p>
                <PillTag v-if="user.role"
                         :text="user.role.name_en"
                         class="bg-blue-600 text-white"
                         small/>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!--    Institution users-->
    <template v-if="userTable.institutions && userTable.institutions.length > 0">
      <template v-for="institution in userTable.institutions">
        <template v-if="institution.users && institution.users.length > 0">
          <h4 class="font-bold flex items-center">
            <template v-if="institution.logo_url">
              <img :src="institution.logo_url"
                   class="inline-block mr-3"
                   width="25"
                   alt="Logo">
            </template>
            <template v-else>
              <div class="inline-block mr-3 h-8 w-8 rounded-full bg-gray-200"></div>
            </template>
            <span>
              {{ institution.name_en }}
            </span>
          </h4>
          <div class="grid grid-cols-12 gap-3">
            <div v-for="user in institution.users" class="col-span-12 sm:col-span-6">
              <div class="rounded-2xl p-4 hover:bg-blue-100 cursor-pointer border"
                   :class="{
                      'bg-green-100': activeId === user.id,
                   }"
                   @click="onClick(user.id)">
                <div class="flex">
                  <UserAvatar class="w-12 h-12 mr-3"
                              :avatar="user.profile_photo_url"
                              :username="user.name"/>
                  <div class="md:text-left overflow-hidden">
                    <h4 class="text-lg text-ellipsis">
                      {{ user.name }}
                      <BaseIcon v-if="activeId === user.id"
                                class="text-green-600"
                                :path="Icons.check"/>
                    </h4>
                    <p class="text-gray-500 dark:text-slate-400">
                      <template v-if="user.institution">
                        {{ user.institution.name_en }}
                      </template>
                    </p>
                    <PillTag v-if="user.role"
                             :text="user.role.name_en"
                             class="bg-blue-600 text-white"
                             small/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </template>
    </template>

  </CardBoxModal>
</template>

<script setup>
import CardBoxModal from '@/Components/CardBoxModal.vue'
import BaseLevel from '@/Components/BaseLevel.vue'
import PillTag from '@/Components/PillTag.vue'
import UserAvatar from '@/Components/UserAvatar.vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import {computed} from "vue";

const emit = defineEmits([
  'update:modelValue',
  'changed'
])

const props = defineProps({
  userTable: {
    type: Object,
    required: true,
  },
  activeId: {
    type: [String, Number],
    default: null,
  },
  modelValue: {
    type: Boolean,
  }
})

const onClick = (userId) => {
  if (userId !== props.activeId) {
    emit('changed', userId)
  }
}

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})

</script>

<style scoped>

</style>
