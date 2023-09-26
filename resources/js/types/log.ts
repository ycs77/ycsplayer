export interface ServerStatus extends Record<string, any> {
  timestamp: number | null
  datetime: number | null
  current_time: number | null
  is_clicked_big_button: boolean | null
  paused: boolean | null
}

export interface Log<T> {
  message: string
  context: T
}

export interface ServerLog extends Log<{
  roomId: string
  mode: string
  user: string
}> {}

export interface ClientLog extends Log<any> {}
