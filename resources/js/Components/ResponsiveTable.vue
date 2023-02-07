<script setup>
import TableCheckboxCell from '@/Components/TableCheckboxCell.vue'

import {ref} from "vue";

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },

  titles: {
    type: Array,
    required: true,
  },

  getLabel: {
    type: Function,
    default: (item) => item.name ?? item,
  },

  getId: {
    type: Function,
    default: (item) => item.id ?? item,
  },

  checkable: {
    type: Boolean,
    default: true,
  },

  getRowClass: {
    type: [Function, null],
    default: null,
  }
})

const checkedRows = ref([])

const remove = (arr, cb) => {
  const newArr = []

  arr.forEach(item => {
    if (!cb(item)) {
      newArr.push(item)
    }
  })

  return newArr
}

const checked = (isChecked, client) => {
  if (isChecked) {
    checkedRows.value.push(client)
  } else {
    checkedRows.value = remove(checkedRows.value, row => row.id === client.id)
  }
}

</script>
<template>
  <div
    v-if="checkedRows.length"
    class="p-3 bg-gray-100/50 dark:bg-gray-800"
  >
    <span
      v-for="checkedRow in checkedRows"
      :key="getId(checkedRow)"
      class="inline-block px-2 py-1 rounded-sm mr-2 text-sm bg-gray-100 dark:bg-gray-700"
    >
          {{ getLabel(checkedRow) }}
    </span>
  </div>

  <table>
    <thead>
    <tr>
      <th v-if="checkable"/>
      <th v-for="title in titles">{{ title }}</th>
    </tr>
    </thead>
    <tbody>
      <template v-if="items.length > 0">
        <tr
          v-for="(row, index) in items"
          :key="getId(row)"
          :class="getRowClass ? getRowClass(row) : ''"
        >
          <TableCheckboxCell
            v-if="checkable"
            @checked="checked($event, row)"
          />
          <slot name="row" v-bind:row="{row, index}"></slot>
        </tr>
      </template>
      <template v-else>
        <tr>
          <td class="text-center justify-center py-10 text-gray-500" :colspan="titles.length + (checkable ? 1 : 0)">
            ( No data )
          </td>
        </tr>
      </template>
    </tbody>
  </table>
</template>
