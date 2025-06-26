import { defineStore } from 'pinia'
import * as pluginApi from '@/api/pluginsApi'
import type {
  Plugin,
  PluginCategory,
  PluginDependencyResponse,
  PluginListParams,
  GenerateConfigRequest,
} from '@/api/pluginsApi'

export const usePluginsStore = defineStore('plugins', {
  state: () => ({
    plugins: [] as Plugin[],
    categories: [] as PluginCategory[],
    selectedPlugin: null as Plugin | null,
    dependencies: null as PluginDependencyResponse | null,
    configOutput: '' as string,
    loading: false,
    error: null as string | null,
  }),

  actions: {
    async loadPlugins(params?: PluginListParams) {
      this.loading = true
      this.error = null
      try {
        const res = await pluginApi.fetchPlugins(params)
        this.plugins = res.data.data
      } catch (e: any) {
        this.error = e.message
      } finally {
        this.loading = false
      }
    },

    async loadPlugin(name: string) {
      try {
        const res = await pluginApi.fetchPluginByName(name)
        this.selectedPlugin = res.data
      } catch (e: any) {
        this.error = e.message
      }
    },

    async loadCategories() {
      try {
        const res = await pluginApi.fetchPluginCategories()
        this.categories = res.data.data
      } catch (e: any) {
        this.error = e.message
      }
    },

    async loadDependencies(name: string) {
      try {
        const res = await pluginApi.fetchPluginDependencies(name)
        this.dependencies = res.data
      } catch (e: any) {
        this.error = e.message
      }
    },

    async generateConfig(data: GenerateConfigRequest) {
      try {
        const res = await pluginApi.generateConfigFromPlugins(data)
        this.configOutput = res.data
      } catch (e: any) {
        this.error = e.message
      }
    },
  },
})
