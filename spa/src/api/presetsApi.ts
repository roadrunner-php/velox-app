import axiosClient from './axiosClient'

export interface Preset {
  name: string
  display_name: string
  description: string
  plugins: string[]
  plugin_count: number
  tags: string[]
  is_official: boolean
  priority: number
}

export interface PresetFilters {
  available_tags?: string[]
}

export interface PresetCollection {
  data: Preset[]
  meta: {
    total: number
    filters: PresetFilters
  }
}

export interface PresetQuery {
  tags?: string
  search?: string
  official?: 'yes' | '1' | 'on'
}

export interface GenerateConfigFromPresetsRequest {
  presets: string[]
  format?: 'toml' | 'json' | 'docker' | 'dockerfile'
}

export const fetchPresets = (params?: PresetQuery) =>
  axiosClient.get<PresetCollection>('/presets', { params })

export const generateConfigFromPresets = (data: GenerateConfigFromPresetsRequest) =>
  axiosClient.post<string>('/presets/generate-config', data, {
    headers: { Accept: 'text/plain' },
    responseType: 'text',
  })
