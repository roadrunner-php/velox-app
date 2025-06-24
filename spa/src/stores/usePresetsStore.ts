import { defineStore } from 'pinia'
import * as presetApi from '@/api/presetsApi'
import type { Preset, PresetQuery, GenerateConfigFromPresetsRequest } from '@/api/presetsApi'

export const usePresetsStore = defineStore('presets', {
  state: () => ({
    presets: [] as Preset[],
    configOutput: '' as string,
    loading: false,
    error: null as string | null,
  }),

  actions: {
    async loadPresets(params?: PresetQuery) {
      this.loading = true
      this.error = null
      try {
        const res = await presetApi.fetchPresets(params)
        this.presets = res.data.data
      } catch (e: any) {
        this.error = e.message
      } finally {
        this.loading = false
      }
    },

    async generateConfig(data: GenerateConfigFromPresetsRequest) {
      try {
        const res = await presetApi.generateConfigFromPresets(data)
        this.configOutput = res.data
      } catch (e: any) {
        this.error = e.message
      }
    },
  },
})
