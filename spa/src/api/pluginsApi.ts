import axiosClient from './axiosClient'

export interface Plugin {
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
}

export interface PluginCategory {
  value: string
  label: string
}

export interface PluginListParams {
  category?: string
  source?: string
  search?: string
}

export interface PluginDependency {
  plugin: string
  type: string
  message: string
  severity: 'error' | 'warning' | 'info'
  conflicting_plugins: string[]
}

export interface PluginDependencyResponse {
  resolved_dependencies: Plugin[]
  dependency_count: {
    resolved: number
  }
  conflicts: PluginDependency[]
  is_valid: boolean
}

export interface GenerateConfigRequest {
  plugins: string[]
  format?: 'toml' | 'json' | 'docker' | 'dockerfile'
}

export const fetchPlugins = (params?: PluginListParams) =>
  axiosClient.get<{ data: Plugin[] }>('/plugins', { params })

export const fetchPluginByName = (name: string) => axiosClient.get<Plugin>(`/plugin/${name}`)

export const fetchPluginCategories = () =>
  axiosClient.get<{ data: PluginCategory[] }>('/plugins/categories')

export const fetchPluginDependencies = (name: string) =>
  axiosClient.get<PluginDependencyResponse>(`/plugin/${name}/dependencies`)

export const generateConfigFromPlugins = (data: GenerateConfigRequest) =>
  axiosClient.post<string>('/plugins/generate-config', data, {
    headers: {
      Accept: 'text/plain',
    },
  })
