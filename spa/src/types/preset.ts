export type PresetSelectionState = 'none' | 'manual' | 'dependency' | 'conflict'

export interface PresetSelectionInfo {
  name: string
  state: PresetSelectionState
  selectedBy?: string[] // Which presets caused this to be auto-selected
  requiredBy?: string[] // Which presets require this as a dependency
}

export interface EnhancedPreset {
  name: string
  display_name: string
  description: string
  plugins: string[]
  plugin_count: number
  tags: string[]
  is_official: boolean
  priority: number
  // Enhanced selection properties
  selectionState?: PresetSelectionState
  dependencyInfo?: {
    resolved: boolean
    dependencies: string[]
    conflicts: string[]
  }
}
