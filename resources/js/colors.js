const sectionBgBase = 'bg-gradient-to-tr'
export const sectionBgLogin = `${sectionBgBase} from-teal-500 via-teal-400 to-green-300`
export const sectionBgLoginDark = `${sectionBgBase} from-purple-900 via-pink-900 to-red-900`
export const sectionBgError = `${sectionBgBase} from-pink-400 via-red-500 to-yellow-500`
export const sectionBgErrorDark = `${sectionBgBase} from-pink-900 via-red-900 to-yellow-900`

export const colorsBgLight = {
  white: 'bg-white text-black',
  light: 'bg-white text-black dark:bg-slate-900/70 dark:text-white',
  contrast: 'bg-gray-800 text-white dark:bg-white dark:text-black',
  success: 'bg-emerald-500 border-emerald-500 text-white',
  danger: 'bg-red-500 border-red-500 text-white',
  warning: 'bg-yellow-500 border-yellow-500 text-white',
  info: 'bg-blue-500 border-blue-500 text-white'
}

export const colorsText = {
  white: 'text-black dark:text-gray-100',
  light: 'text-gray-700 dark:text-gray-400',
  contrast: 'dark:text-white',
  success: 'text-emerald-500',
  danger: 'text-red-500',
  warning: 'text-yellow-500',
  info: 'text-blue-500'
}

export const colorsOutline = {
  white: [colorsText.white, 'border-gray-100'],
  light: [colorsText.light, 'border-gray-100'],
  contrast: [colorsText.contrast, 'border-gray-900 dark:border-gray-100'],
  success: [colorsText.success, 'border-emerald-500'],
  danger: [colorsText.danger, 'border-red-500'],
  warning: [colorsText.warning, 'border-yellow-500'],
  info: [colorsText.info, 'border-blue-500']
}

export const getButtonColor = (color, isOutlined, hasHover) => {
  const colors = {
    bg: {
      white: 'bg-white text-black',
      contrast: 'bg-gray-800 text-white dark:bg-white dark:text-black',
      light: 'bg-gray-50 text-black',
      success: 'bg-emerald-600 dark:bg-emerald-500 text-white',
      danger: 'bg-red-600 dark:bg-red-500 text-white',
      warning: 'bg-yellow-600 dark:bg-yellow-500 text-white',
      info: 'bg-blue-600 dark:bg-blue-500 text-white'
    },
    bgHover: {
      white: 'hover:bg-gray-50',
      contrast: 'hover:bg-gray-900 hover:dark:bg-gray-100',
      light: 'hover:bg-gray-200',
      success: 'hover:bg-emerald-700 hover:border-emerald-700 hover:dark:bg-emerald-600 hover:dark:border-emerald-600',
      danger: 'hover:bg-red-700 hover:border-red-700 hover:dark:bg-red-600 hover:dark:border-red-600',
      warning: 'hover:bg-yellow-700 hover:border-yellow-700 hover:dark:bg-yellow-600 hover:dark:border-yellow-600',
      info: 'hover:bg-blue-700 hover:border-blue-700 hover:dark:bg-blue-600 hover:dark:border-blue-600'
    },
    borders: {
      white: 'border-gray-100',
      contrast: 'border-gray-900 dark:border-gray-100',
      light: 'border-gray-100 dark:border-slate-700',
      success: 'border-emerald-600 dark:border-emerald-500',
      danger: 'border-red-600 dark:border-red-500',
      warning: 'border-yellow-600 dark:border-yellow-500',
      info: 'border-blue-600 dark:border-blue-500'
    },
    text: {
      white: 'text-black dark:text-gray-100',
      contrast: 'dark:text-gray-100',
      light: 'text-gray-700 dark:text-gray-400',
      success: 'text-emerald-600 dark:text-emerald-500',
      danger: 'text-red-600 dark:text-red-500',
      warning: 'text-yellow-600 dark:text-yellow-500',
      info: 'text-blue-600 dark:text-blue-500'
    },
    outlineHover: {
      white: 'hover:bg-gray-100 hover:text-gray-900 dark:hover:text-gray-900',
      contrast: 'hover:bg-gray-800 hover:text-gray-100 hover:dark:bg-gray-100 hover:dark:text-black',
      light: 'hover:bg-gray-100 hover:text-gray-900 dark:hover:text-gray-900',
      success: 'hover:bg-emerald-600 hover:text-white hover:text-white hover:dark:text-white hover:dark:border-emerald-600',
      danger: 'hover:bg-red-600 hover:text-white hover:text-white hover:dark:text-white hover:dark:border-red-600',
      warning: 'hover:bg-yellow-600 hover:text-white hover:text-white hover:dark:text-white hover:dark:border-yellow-600',
      info: 'hover:bg-blue-600 hover:text-white hover:dark:text-white hover:dark:border-blue-600'
    }
  }

  if (!colors.bg[color]) {
    return color
  }

  const base = [
    isOutlined ? colors.text[color] : colors.bg[color],
    colors.borders[color]
  ]

  if (hasHover) {
    base.push(isOutlined ? colors.outlineHover[color] : colors.bgHover[color])
  }

  return base
}
