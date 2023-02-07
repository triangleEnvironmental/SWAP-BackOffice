import {useForm} from "@inertiajs/inertia-vue3";

export default function useFilterForm(options) {
  for(let key in options) {
    if (route().params[key] !== undefined) {
      options[key] = route().params[key];
    }
  }

  const filterForm = useForm(options)

  const getAppliedFilterOptions = () => {
    return route().params
  }

  const getFilterOptions = () => {
    const filterOptions = {}
    for (let key in options) {
      if (![null, undefined, ''].includes(filterForm[key])) {
        filterOptions[key] = filterForm[key]
      }
    }
    return filterOptions
  }

  const getUrlParam = (option = {}) => {
    if (!option) {
      option = {}
    }
    const params = Object.assign({}, route().params)
    for (let key in options) {
      if (params.hasOwnProperty(key)) {
        delete params[key]
      }
    }
    for (let key in params) {
      if (option.remove && Array.isArray(option.remove)) {
        if (option.remove.includes(key)) {
          delete params[key]
        }
      }
    }
    return params
  }

  const applyFilter = () => {
    applyFilterWithOption()
  }

  const applyFilterWithOption = (option = {}) => {
    if (!option) {
      option = {}
    }
    if (option.remove && !option.remove.includes('page')) {
      option.remove.push('page')
    } else {
      option.remove = ['page']
    }
    filterForm.transform(data => {
      const filterOptions = {}
      for (let key in data) {
        if (option.remove && Array.isArray(option.remove)) {
          if (option.remove.includes(key)) {
            continue
          }
        }
        if (![null, undefined, '', false].includes(data[key])) { // [false] here is for assigned_me only
          filterOptions[key] = data[key]
        }
      }
      return filterOptions
    }).get(
      route(route().current(), getUrlParam(option)),
      {
        replace: true,
      }
    )
  }

  return {
    filterForm,
    applyFilter,
    applyFilterWithOption,
    getFilterOptions,
    getAppliedFilterOptions,
  }
}
