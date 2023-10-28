export function isPromise<T = void>(value: any): value is Promise<T> {
  return value !== undefined && value !== null && typeof value.then === 'function'
}

export function silencePromise(value: any) {
  if (isPromise(value)) {
    value.then(null, () => {})
  }
}

export function wrapPromise(value: any) {
  if (isPromise(value)) {
    return value
  }
  return Promise.resolve()
}
