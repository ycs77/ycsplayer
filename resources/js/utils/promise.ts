export function isPromise(value: any): boolean {
  return value !== undefined && value !== null && typeof value.then === 'function'
}

export function silencePromise(value: any) {
  if (isPromise(value)) {
    value.then(null, (e: any) => {})
  }
}
