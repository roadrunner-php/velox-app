import { defineStore } from 'pinia'
import { watch } from 'vue'
import * as presetApi from '@/api/presetsApi'
import type { Preset, PresetQuery, GenerateConfigFromPresetsRequest } from '@/api/presetsApi'
import type { PresetSelectionState, PresetSelectionInfo } from '@/types/preset'

interface PresetWithSelectionInfo extends Preset {
  selectionState: PresetSelectionState
  selectedBy: string[]
  requiredBy: string[]
}

export const usePresetsStore = defineStore('presets', {
  state: () => ({
    presets: [] as Preset[],
    configOutput: '' as string,
    loading: false,
    error: null as string | null,

    // Enhanced selection management
    selections: new Map<string, PresetSelectionInfo>(),
    dependencyCache: new Map<string, string[]>(), // Cache for preset plugin overlaps
    loadingDependencies: new Set<string>(),

    // URL sync state
    urlSyncEnabled: false,
    route: null as any,
    router: null as any,
    isUpdatingFromUrl: false, // Prevent recursive updates
  }),

  getters: {
    // Get presets with their selection information
    presetsWithSelection(): PresetWithSelectionInfo[] {
      return this.presets.map(preset => {
        const selectionInfo = this.selections.get(preset.name)
        return {
          ...preset,
          selectionState: selectionInfo?.state || 'none',
          selectedBy: selectionInfo?.selectedBy || [],
          requiredBy: selectionInfo?.requiredBy || [],
        }
      })
    },

    // Get selected preset names
    selectedPresetNames(): string[] {
      return Array.from(this.selections.entries())
        .filter(([, info]) => info.state !== 'none')
        .map(([name]) => name)
    },

    // Get manually selected presets only
    manuallySelectedPresets(): string[] {
      return Array.from(this.selections.entries())
        .filter(([, info]) => info.state === 'manual')
        .map(([name]) => name)
    },

    // Get all selected presets (manual + dependencies)
    allSelectedPresets(): string[] {
      return Array.from(this.selections.entries())
        .filter(([, info]) => info.state === 'manual' || info.state === 'dependency')
        .map(([name]) => name)
    },

    // Check if a preset is loading dependencies
    isLoadingDependencies() {
      return (presetName: string) => this.loadingDependencies.has(presetName)
    },

    // Get cached dependencies for a preset
    getCachedDependencies() {
      return (presetName: string) => this.dependencyCache.get(presetName)
    },

    // Get selection summary
    selectionSummary() {
      const manual = this.manuallySelectedPresets
      const total = this.allSelectedPresets
      const dependencies = total.length - manual.length

      // Calculate total unique plugins across all selected presets
      const allPlugins = new Set<string>()
      for (const presetName of total) {
        const preset = this.presets.find(p => p.name === presetName)
        if (preset) {
          preset.plugins.forEach(plugin => allPlugins.add(plugin))
        }
      }

      return {
        manual: manual.length,
        dependencies,
        total: total.length,
        totalPlugins: allPlugins.size,
      }
    },
  },

  actions: {
    // Initialize URL sync
    initUrlSync(route: any, router: any) {
      this.route = route
      this.router = router
      this.urlSyncEnabled = true

      // Load selections from URL on init
      this.loadSelectionsFromUrl()

      // Watch URL changes, but ONLY external ones
      if (route && router) {
        watch(
          () => route.query.presets,
          (newValue, oldValue) => {
            // Ignore changes if we are updating the URL ourselves
            if (this.isUpdatingFromUrl) return

            console.log('🌐 External preset URL change detected:', { old: oldValue, new: newValue })
            this.loadSelectionsFromUrl()
          }
        )
      }
    },

    // Load selections from URL
    async loadSelectionsFromUrl() {
      if (!this.route || this.isUpdatingFromUrl) return

      this.isUpdatingFromUrl = true

      try {
        const presetsParam = this.route.query.presets

        // Get new preset list from URL
        const newPresetNames = !presetsParam ? [] : (
          Array.isArray(presetsParam)
            ? presetsParam.flatMap((p: string) => p.split(',')).filter(Boolean)
            : String(presetsParam).split(',').filter(Boolean)
        )

        // Get current manually selected presets
        const currentManualPresets = this.manuallySelectedPresets

        // Check if the list has changed (avoid unnecessary updates)
        const hasChanged =
          newPresetNames.length !== currentManualPresets.length ||
          !newPresetNames.every(name => currentManualPresets.includes(name))

        if (!hasChanged) {
          return
        }

        // Remove only those manual presets that are not in the new list
        for (const presetName of currentManualPresets) {
          if (!newPresetNames.includes(presetName)) {
            this.deselectPreset(presetName)
          }
        }

        // Add new presets
        for (const name of newPresetNames) {
          if (this.presets.some(p => p.name === name) && !currentManualPresets.includes(name)) {
            await this.selectPreset(name, true)
          }
        }

      } finally {
        this.isUpdatingFromUrl = false
      }
    },

    // Update URL with current selections (only manual selections)
    updateUrl() {
      if (!this.urlSyncEnabled || !this.route) return

      const manualPresets = this.manuallySelectedPresets
      const currentPresetsInUrl = this.route.query.presets

      // Check if URL needs to be updated
      const newUrlValue = manualPresets.length === 0 ? undefined : manualPresets.join(',')
      const currentUrlValue = Array.isArray(currentPresetsInUrl)
        ? currentPresetsInUrl.join(',')
        : currentPresetsInUrl

      // If values are the same, don't update URL
      if (newUrlValue === currentUrlValue) {
        return
      }

      // Mark that we are updating the URL
      this.isUpdatingFromUrl = true

      // Create new URL with query string manually (without encoding commas)
      const baseUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname}`
      const searchParams = new URLSearchParams(window.location.search)

      // Remove presets parameter from existing params
      searchParams.delete('presets')

      // Build final URL
      let finalUrl = baseUrl
      const otherParams = searchParams.toString()
      const presetsParam = manualPresets.length > 0 ? `presets=${manualPresets.join(',')}` : ''

      // Build query string
      const queryParts = [otherParams, presetsParam].filter(Boolean)
      if (queryParts.length > 0) {
        finalUrl += '?' + queryParts.join('&')
      }

      // Use native history API instead of Vue Router
      window.history.replaceState(null, '', finalUrl)

      // Reset flag asynchronously
      setTimeout(() => {
        this.isUpdatingFromUrl = false
      }, 0)
    },

    async loadPresets(params?: PresetQuery) {
      this.loading = true
      this.error = null
      try {
        const res = await presetApi.fetchPresets(params)
        this.presets = res.data.data

        // Reload selections from URL after presets loaded
        if (this.urlSyncEnabled) {
          await this.loadSelectionsFromUrl()
        }
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
        throw e
      }
    },

    // Enhanced selection management
    async togglePresetSelection(presetName: string, includeDependencies = true) {
      const currentSelection = this.selections.get(presetName)
      const currentState = currentSelection?.state || 'none'

      if (currentState === 'none') {
        // Select the preset
        await this.selectPreset(presetName, includeDependencies)
      } else if (currentState === 'manual') {
        // Deselect manually selected preset
        this.deselectPreset(presetName)
      } else if (currentState === 'dependency') {
        // For dependency presets, only deselect if no other presets require it
        const stillRequired = this.checkIfStillRequired(presetName)
        if (!stillRequired) {
          this.deselectPreset(presetName)
        }
      }

      // Always update URL after selection change
      this.updateUrl()
    },

    async selectPreset(presetName: string, includeDependencies = true) {
      // Set this preset as manually selected
      this.selections.set(presetName, {
        name: presetName,
        state: 'manual',
        selectedBy: [],
        requiredBy: [],
      })

      if (includeDependencies) {
        try {
          // For presets, we can analyze plugin overlaps as dependencies
          await this.analyzePresetRelationships(presetName)
        } catch (e) {
          console.error('Failed to analyze preset relationships for', presetName, e)
        }
      }
    },

    async analyzePresetRelationships(presetName: string) {
      const selectedPreset = this.presets.find(p => p.name === presetName)
      if (!selectedPreset) return

      // Find presets that have significant plugin overlap (could be considered related)
      const relatedPresets: string[] = []
      const conflictingPresets: string[] = []

      for (const otherPreset of this.presets) {
        if (otherPreset.name === presetName) continue

        const overlap = this.calculatePluginOverlap(selectedPreset.plugins, otherPreset.plugins)

        // If there's significant overlap (>50% of plugins), consider it related
        if (overlap.percentage > 0.5 && overlap.common.length > 2) {
          // Check if this preset would be complementary (adds new plugins) or conflicting
          const uniquePlugins = otherPreset.plugins.filter(p => !selectedPreset.plugins.includes(p))

          if (uniquePlugins.length > 0) {
            relatedPresets.push(otherPreset.name)
          }
        }

        // Check for conflicts (same tag but different approach, like different web frameworks)
        if (this.checkPresetConflict(selectedPreset, otherPreset)) {
          conflictingPresets.push(otherPreset.name)
        }
      }

      // Cache the analysis
      this.dependencyCache.set(presetName, relatedPresets)

      // Mark conflicting presets
      for (const conflictName of conflictingPresets) {
        const existing = this.selections.get(conflictName)
        if (existing) {
          this.selections.set(conflictName, {
            ...existing,
            state: 'conflict',
          })
        }
      }
    },

    calculatePluginOverlap(plugins1: string[], plugins2: string[]) {
      const common = plugins1.filter(p => plugins2.includes(p))
      const total = new Set([...plugins1, ...plugins2]).size

      return {
        common,
        percentage: common.length / Math.min(plugins1.length, plugins2.length),
        totalUnique: total,
      }
    },

    checkPresetConflict(preset1: Preset, preset2: Preset): boolean {
      // Check for conflicting tags (e.g., both are web frameworks)
      const conflictingTags = [
        ['web', 'api'], // Different web approaches
        ['grpc', 'http'], // Different communication protocols
        ['mysql', 'postgresql'], // Different databases
      ]

      for (const [tag1, tag2] of conflictingTags) {
        const has1 = preset1.tags?.some(t => t.toLowerCase().includes(tag1.toLowerCase()))
        const has2 = preset2.tags?.some(t => t.toLowerCase().includes(tag2.toLowerCase()))

        if (has1 && has2) {
          return true
        }
      }

      return false
    },

    deselectPreset(presetName: string) {
      const selection = this.selections.get(presetName)
      if (!selection) return

      // Remove the preset selection
      this.selections.delete(presetName)

      // Check all dependency presets to see if they're still needed
      const allSelections = Array.from(this.selections.entries())

      for (const [depName, depInfo] of allSelections) {
        if (depInfo.state === 'dependency' && depInfo.requiredBy?.includes(presetName)) {
          // Remove this preset from the requiredBy list
          const updatedRequiredBy = depInfo.requiredBy.filter(name => name !== presetName)
          const updatedSelectedBy = depInfo.selectedBy?.filter(name => name !== presetName) || []

          if (updatedRequiredBy.length === 0) {
            // No longer required, remove it
            this.selections.delete(depName)
          } else {
            // Still required by other presets
            this.selections.set(depName, {
              ...depInfo,
              selectedBy: updatedSelectedBy,
              requiredBy: updatedRequiredBy,
            })
          }
        }
      }
    },

    checkIfStillRequired(presetName: string): boolean {
      const selection = this.selections.get(presetName)
      return !!(selection && selection.requiredBy && selection.requiredBy.length > 0)
    },

    // Clear all selections
    clearAllSelections() {
      this.selections.clear()
      this.updateUrl()
    },

    // Get selection info for a specific preset
    getSelectionInfo(presetName: string): PresetSelectionInfo | null {
      return this.selections.get(presetName) || null
    },

    // Get presets that would be affected by selecting a preset
    async getSelectionPreview(presetName: string): Promise<{
      toSelect: string[]
      conflicts: string[]
      newDependencies: string[]
      existingDependencies: string[]
      pluginSummary: {
        current: number
        new: number
        total: number
      }
    }> {
      try {
        await this.analyzePresetRelationships(presetName)

        const relatedPresets = this.dependencyCache.get(presetName) || []
        const allToSelect = [presetName, ...relatedPresets]

        // Find conflicts
        const selectedPreset = this.presets.find(p => p.name === presetName)
        const conflicts: string[] = []

        if (selectedPreset) {
          for (const otherPreset of this.presets) {
            if (this.checkPresetConflict(selectedPreset, otherPreset)) {
              const existing = this.selections.get(otherPreset.name)
              if (existing && existing.state !== 'none') {
                conflicts.push(otherPreset.name)
              }
            }
          }
        }

        // Split into new vs existing
        const newDependencies: string[] = []
        const existingDependencies: string[] = []

        for (const presetName of allToSelect) {
          const existing = this.selections.get(presetName)
          if (!existing || existing.state === 'none') {
            newDependencies.push(presetName)
          } else {
            existingDependencies.push(presetName)
          }
        }

        // Calculate plugin summary
        const currentPlugins = new Set<string>()
        for (const presetName of this.allSelectedPresets) {
          const preset = this.presets.find(p => p.name === presetName)
          if (preset) {
            preset.plugins.forEach(plugin => currentPlugins.add(plugin))
          }
        }

        const newPlugins = new Set<string>()
        for (const presetName of newDependencies) {
          const preset = this.presets.find(p => p.name === presetName)
          if (preset) {
            preset.plugins.forEach(plugin => {
              if (!currentPlugins.has(plugin)) {
                newPlugins.add(plugin)
              }
            })
          }
        }

        return {
          toSelect: allToSelect,
          conflicts,
          newDependencies,
          existingDependencies,
          pluginSummary: {
            current: currentPlugins.size,
            new: newPlugins.size,
            total: currentPlugins.size + newPlugins.size,
          }
        }
      } catch (e) {
        return {
          toSelect: [presetName],
          conflicts: [],
          newDependencies: [presetName],
          existingDependencies: [],
          pluginSummary: {
            current: 0,
            new: 0,
            total: 0,
          }
        }
      }
    },

    // Load dependencies (for compatibility with plugin system)
    async loadDependencies(presetName: string) {
      if (this.loadingDependencies.has(presetName)) return

      this.loadingDependencies.add(presetName)

      try {
        await this.analyzePresetRelationships(presetName)
      } finally {
        this.loadingDependencies.delete(presetName)
      }
    },
  },
})
