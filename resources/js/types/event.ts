export interface PlayerPlayedEvent {
  currentTime: number
  timestamp: number
}

export interface PlayerPausedEvent {
  currentTime: number
  timestamp: number
}

export interface PlayerSeekedEvent {
  paused: boolean
  currentTime: number
  timestamp: number
}

export interface PlayerTimeUpdateEvent {
  paused: boolean
  currentTime: number
  timestamp: number
}

export interface PlayerlistItemAddedEvent {
  roomId: string
}

export interface PlayerlistItemClickedEvent {
  roomId: string
}

export interface PlayerlistItemRemovedEvent {
  roomId: string
}

export interface RoomMediaConvertedEvent {
  roomId: string
  message: string
}
