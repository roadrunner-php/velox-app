import axiosClient from './axiosClient'

export interface GenerateBinaryRequest {
  plugins: string[]
  target_os: OS
  target_arch: Architecture
}

export type OS = 'linux' | 'darwin' | 'windows' | 'freebsd'
export type Architecture = 'amd64' | 'arm64' | '386' | 'arm'

export interface PlatformOption {
  os: OS
  arch: Architecture
  label: string
  description: string
  icon: string
  popular?: boolean
}

/**
 * Generate and download RoadRunner binary for specified platform
 */
export async function generateBinary(request: GenerateBinaryRequest): Promise<Blob> {
  const response = await axiosClient.post<Blob>('/binary/generate', request, {
    responseType: 'blob',
    timeout: 300000, // 5 minutes for binary generation
  })

  return response.data
}

/**
 * Download blob as file
 */
export function downloadBlob(blob: Blob, filename: string): void {
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

/**
 * Get binary filename based on platform
 */
export function getBinaryFilename(os: OS, arch: Architecture): string {
  const extension = os === 'windows' ? '.exe' : ''
  return `rr-${os}-${arch}${extension}`
}

/**
 * Available platform options for binary generation
 */
export const PLATFORM_OPTIONS: PlatformOption[] = [
  {
    os: 'linux',
    arch: 'amd64',
    label: 'Linux x64',
    description: 'Most common Linux servers and desktops',
    icon: '🐧',
    popular: true,
  },
  {
    os: 'linux',
    arch: 'arm64',
    label: 'Linux ARM64',
    description: 'Modern ARM servers (AWS Graviton, Raspberry Pi 4+)',
    icon: '🐧',
    popular: true,
  },
  {
    os: 'darwin',
    arch: 'amd64',
    label: 'macOS x64',
    description: 'Intel-based Macs',
    icon: '🍎',
  },
  {
    os: 'darwin',
    arch: 'arm64',
    label: 'macOS ARM64',
    description: 'Apple Silicon (M1, M2, M3)',
    icon: '🍎',
    popular: true,
  },
  {
    os: 'windows',
    arch: 'amd64',
    label: 'Windows x64',
    description: 'Windows 10/11 64-bit',
    icon: '🪟',
  },
  {
    os: 'freebsd',
    arch: 'amd64',
    label: 'FreeBSD x64',
    description: 'FreeBSD servers',
    icon: '👹',
  },
]

/**
 * Detect current platform
 */
export function detectPlatform(): { os: OS; arch: Architecture } {
  const userAgent = navigator.userAgent.toLowerCase()
  const platform = navigator.platform.toLowerCase()

  let os: OS = 'linux'
  let arch: Architecture = 'amd64'

  // Detect OS
  if (platform.includes('mac') || userAgent.includes('mac')) {
    os = 'darwin'
  } else if (platform.includes('win') || userAgent.includes('win')) {
    os = 'windows'
  } else if (userAgent.includes('freebsd')) {
    os = 'freebsd'
  }

  // Detect architecture (rough estimation)
  if (platform.includes('arm') || userAgent.includes('arm')) {
    arch = 'arm64'
  }

  return { os, arch }
}
