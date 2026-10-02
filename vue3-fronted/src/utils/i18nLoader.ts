import type { I18n } from 'vue-i18n'

export async function loadLocaleMessages(i18n: I18n, locale: string) {
  try {
    // normalize language codes like el-gr -> el
    const normalized = locale.split('-')[0]
    const messages = await import(`../locales/${normalized}.json`)
    i18n.global.setLocaleMessage(normalized, messages.default || messages)
    i18n.global.locale.value = normalized
    return Promise.resolve()
  } catch (err) {
    console.warn('Failed to load locale', locale, err)
    return Promise.resolve()
  }
}
