import { defineStore } from 'pinia'
import * as pluginApi from '@/api/pluginsApi'
import type {
  Plugin,
  PluginCategory,
  PluginDependencyResponse,
  PluginListParams,
  GenerateConfigRequest,
} from '@/api/pluginsApi'
import type { PluginSelectionState, PluginSelectionInfo } from '@/types/plugin'

interface PluginWithSelectionInfo extends Plugin {
  selectionState: PluginSelectionState
  selectedBy: string[]
  requiredBy: string[]
}

export const usePluginsStore = defineStore('plugins', {
  state: () => ({
    plugins: [] as Plugin[],
    categories: [] as PluginCategory[],
    selectedPlugin: null as Plugin | null,
    dependencies: null as PluginDependencyResponse | null,
    configOutput: '' as string,
    loading: false,
    error: null as string | null,
    
    // Enhanced selection management
    selections: new Map<string, PluginSelectionInfo>(),
    dependencyCache: new Map<string, PluginDependencyResponse>(),
    loadingDependencies: new Set<string>(),
  }),

  getters: {
    // Get plugins with their selection information
    pluginsWithSelection(): PluginWithSelectionInfo[] {
      return this.plugins.map(plugin => {
        const selectionInfo = this.selections.get(plugin.name)
        return {
          ...plugin,
          selectionState: selectionInfo?.state || 'none',
          selectedBy: selectionInfo?.selectedBy || [],
          requiredBy: selectionInfo?.requiredBy || [],
        }
      })
    },

    // Get selected plugin names
    selectedPluginNames(): string[] {
      return Array.from(this.selections.entries())
        .filter(([, info]) => info.state !== 'none')
        .map(([name]) => name)
    },

    // Get manually selected plugins only
    manuallySelectedPlugins(): string[] {
      return Array.from(this.selections.entries())
        .filter(([, info]) => info.state === 'manual')
        .map(([name]) => name)
    },

    // Get all selected plugins (manual + dependencies)
    allSelectedPlugins(): string[] {
      return Array.from(this.selections.entries())
        .filter(([, info]) => info.state === 'manual' || info.state === 'dependency')
        .map(([name]) => name)
    },

    // Check if a plugin is loading dependencies
    isLoadingDependencies() {
      return (pluginName: string) => this.loadingDependencies.has(pluginName)
    },

    // Get cached dependencies for a plugin
    getCachedDependencies() {
      return (pluginName: string) => this.dependencyCache.get(pluginName)
    },
  },

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
      // Check cache first
      if (this.dependencyCache.has(name)) {
        return this.dependencyCache.get(name)!
      }

      // Avoid duplicate requests
      if (this.loadingDependencies.has(name)) {
        return
      }

      this.loadingDependencies.add(name)
      
      try {
        const res = await pluginApi.fetchPluginDependencies(name)
        const dependencyData = res.data
        
        // Cache the result
        this.dependencyCache.set(name, dependencyData)
        
        // Update the main dependencies if this is the selected plugin
        if (name === this.selectedPlugin?.name) {
          this.dependencies = dependencyData
        }
        
        return dependencyData
      } catch (e: any) {
        this.error = e.message
        throw e
      } finally {
        this.loadingDependencies.delete(name)
      }
    },

    async generateConfig(data: GenerateConfigRequest) {
      try {
        const res = await pluginApi.generateConfigFromPlugins(data)
        this.configOutput = res.data
      } catch (e: any) {
        this.error = e.message
        throw e
      }
    },

    // Enhanced selection management
    async togglePluginSelection(pluginName: string, includeDependencies = true) {
      const currentSelection = this.selections.get(pluginName)
      const currentState = currentSelection?.state || 'none'

      if (currentState === 'none') {
        // Select the plugin
        await this.selectPlugin(pluginName, includeDependencies)
      } else if (currentState === 'manual') {
        // Deselect manually selected plugin
        this.deselectPlugin(pluginName)
      } else if (currentState === 'dependency') {
        // For dependency plugins, only deselect if no other plugins require it
        const stillRequired = this.checkIfStillRequired(pluginName)
        if (!stillRequired) {
          this.deselectPlugin(pluginName)
        }
      }
    },

    async selectPlugin(pluginName: string, includeDependencies = true) {
      // Set this plugin as manually selected
      this.selections.set(pluginName, {
        name: pluginName,
        state: 'manual',
        selectedBy: [],
        requiredBy: [],
      })

      if (includeDependencies) {
        try {
          // Load and select dependencies
          const dependencyData = await this.loadDependencies(pluginName)
          await this.selectDependencies(pluginName, dependencyData.resolved_dependencies)
          
          // Handle conflicts
          if (dependencyData.conflicts.length > 0) {
            this.handleConflicts(pluginName, dependencyData.conflicts)
          }
        } catch (e) {
          console.error('Failed to load dependencies for', pluginName, e)
        }
      }
    },

    async selectDependencies(parentPlugin: string, dependencies: Plugin[]) {
      for (const dep of dependencies) {
        const existing = this.selections.get(dep.name)
        
        if (!existing || existing.state === 'none') {
          // Auto-select as dependency
          this.selections.set(dep.name, {
            name: dep.name,
            state: 'dependency',
            selectedBy: [parentPlugin],
            requiredBy: [parentPlugin],
          })
        } else if (existing.state === 'dependency') {
          // Add to the list of plugins that require this dependency
          const updatedInfo = {
            ...existing,
            selectedBy: [...new Set([...existing.selectedBy, parentPlugin])],
            requiredBy: [...new Set([...existing.requiredBy, parentPlugin])],
          }
          this.selections.set(dep.name, updatedInfo)
        }
        // If it's manually selected, leave it as is
      }
    },

    deselectPlugin(pluginName: string) {
      const selection = this.selections.get(pluginName)
      if (!selection) return

      // Remove the plugin selection
      this.selections.delete(pluginName)

      // Check all dependency plugins to see if they're still needed
      const allSelections = Array.from(this.selections.entries())
      
      for (const [depName, depInfo] of allSelections) {
        if (depInfo.state === 'dependency' && depInfo.requiredBy.includes(pluginName)) {
          // Remove this plugin from the requiredBy list
          const updatedRequiredBy = depInfo.requiredBy.filter(name => name !== pluginName)
          const updatedSelectedBy = depInfo.selectedBy.filter(name => name !== pluginName)
          
          if (updatedRequiredBy.length === 0) {
            // No longer required, remove it
            this.selections.delete(depName)
          } else {
            // Still required by other plugins
            this.selections.set(depName, {
              ...depInfo,
              selectedBy: updatedSelectedBy,
              requiredBy: updatedRequiredBy,
            })
          }
        }
      }
    },

    checkIfStillRequired(pluginName: string): boolean {
      const selection = this.selections.get(pluginName)
      return !!(selection && selection.requiredBy.length > 0)
    },

    handleConflicts(pluginName: string, conflicts: any[]) {
      // Mark conflicting plugins
      for (const conflict of conflicts) {
        if (conflict.conflicting_plugins) {
          for (const conflictingPlugin of conflict.conflicting_plugins) {
            const existing = this.selections.get(conflictingPlugin)
            if (existing) {
              this.selections.set(conflictingPlugin, {
                ...existing,
                state: 'conflict',
              })
            }
          }
        }
      }
    },

    // Clear all selections
    clearAllSelections() {
      this.selections.clear()
    },

    // Get selection info for a specific plugin
    getSelectionInfo(pluginName: string): PluginSelectionInfo | null {
      return this.selections.get(pluginName) || null
    },

    // Get plugins that would be affected by selecting a plugin
    async getSelectionPreview(pluginName: string): Promise<{
      toSelect: string[]
      conflicts: string[]
      newDependencies: string[]
      existingDependencies: string[]
    }> {
      try {
        const dependencyData = await this.loadDependencies(pluginName)
        const allToSelect = [pluginName, ...dependencyData.resolved_dependencies.map(d => d.name)]
        const conflicts = dependencyData.conflicts.flatMap(c => c.conflicting_plugins || [])
        
        // Split dependencies into new vs already selected
        const newDependencies: string[] = []
        const existingDependencies: string[] = []
        
        for (const depName of allToSelect) {
          const existing = this.selections.get(depName)
          if (!existing || existing.state === 'none') {
            newDependencies.push(depName)
          } else {
            existingDependencies.push(depName)
          }
        }
        
        return { 
          toSelect: allToSelect, 
          conflicts,
          newDependencies,
          existingDependencies
        }
      } catch (e) {
        return { 
          toSelect: [pluginName], 
          conflicts: [],
          newDependencies: [pluginName],
          existingDependencies: []
        }
      }
    },
  },
})
