export type PluginSelectionState = 'none' | 'manual' | 'dependency' | 'conflict'

export interface PluginSelectionInfo {
  name: string
  state: PluginSelectionState
  selectedBy?: string[] // Which plugins caused this to be auto-selected
  requiredBy?: string[] // Which plugins require this as a dependency
}

export interface EnhancedPlugin {
  name: string
  version: string
  owner: string
  repository: string
  source: 'official' | 'community'
  description: string
  category: string | null
  dependencies: string[]
  full_name: string
  repository_url: string
  is_official: boolean
  repository_type?: string
  folder?: string
  replace?: string
  // Enhanced selection properties
  selectionState?: PluginSelectionState
  dependencyInfo?: {
    resolved: boolean
    dependencies: string[]
    conflicts: string[]
  }
}
