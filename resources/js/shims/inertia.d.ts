import '@inertiajs/core'

declare module '@inertiajs/core' {
  interface PageProps {
    auth: {
      user: {
        id: string
        name: string
        email: string
        avatar: string | null
      }
    } | null
    flash: {
      success: string | null
      error: string | null
    }
  }
}

export {}
