export interface Log<T> {
  message: string
  context: T
}

export interface ClientLog extends Log<any> {}
