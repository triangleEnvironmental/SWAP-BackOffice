<script>
import {h, defineComponent} from 'vue'

export default defineComponent({
  name: 'TableButtons',
  props: {
  },
  render() {
    const hasSlot = this.$slots && this.$slots.default

    return h(
      'div',
      {class: []},
      hasSlot
        ? this.$slots.default().reverse().map(element => {
          if (element && element.children && typeof element.children === 'object') {
            return h(
              div,
              {class: ['m-1']},
              element.children,
            )
          }

          return h(element, {class: ['m-1']})
        })
        : null
    )
  }
})
</script>
