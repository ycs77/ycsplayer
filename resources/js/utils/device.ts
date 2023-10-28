declare global {
  interface Window {
    opera: any
    MSStream: any
  }
}

const userAgent = navigator.userAgent || navigator.vendor || window.opera

export const IS_ANDROID = /android/i.test(userAgent) || /adr/i.test(userAgent)

export const IS_iOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream

export const IS_MOBILE = IS_ANDROID || IS_iOS
