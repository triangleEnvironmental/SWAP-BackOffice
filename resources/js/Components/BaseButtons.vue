<script>
import {h, defineComponent} from 'vue'

export default defineComponent({
  name: 'BaseButtons',
  props: {
    noWrap: Boolean,
    type: {
      type: String,
      default: 'justify-start'
    },
    classAddon: {
      type: String,
      default: 'mr-3 last:mr-0 mb-3'
    },
    mb: {
      type: String,
      default: '-mb-3'
    }
  },
  render() {
    const hasSlot = this.$slots && this.$slots.default

    const parentClass = [
      'flex',
      'items-center',
      this.type,
      this.noWrap ? 'flex-nowrap' : 'flex-wrap'
    ]

    if (this.mb) {
      parentClass.push(this.mb)
    }

    const addOnClassToElement = (element) => {
      return element.children.map(child => {
        if (child.el === null && child.children && typeof child.children === 'object') {
          return h(child, {}, addOnClassToElement(child))
        }
        return h(child, {class: [this.classAddon]})
      })
    }

    return h(
      'div',
      {class: parentClass},
      hasSlot
        ? this.$slots.default().map(element => {
          if (element && element.children && typeof element.children === 'object') {
            return h(
              element,
              {},
              addOnClassToElement(element))
          }

          return h(element, {class: [this.classAddon]})
        })
        : null
    )
  }
})
</script>
