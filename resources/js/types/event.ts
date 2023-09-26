import type { PlayStatus } from './player'

export interface PlayerPlayedEvent {
  socketId: string
  roomId: string
  status: PlayStatus
  isFirst: boolean
}

export interface PlayerPausedEvent {
  socketId: string
  roomId: string
  status: PlayStatus
}

export interface PlayerSeekedEvent {
  socketId: string
  roomId: string
  status: PlayStatus
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
