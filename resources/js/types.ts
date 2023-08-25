export interface Room {
  id: number
  type: RoomType
  title: string
  auto_play: boolean
  auto_remove: boolean
  current_playing_id?: number | null
  note?: string | null
}

export interface PlaylistItem {
  id: number
  type: PlayerType
  title: string
  url: string
  thumbnail: string | null
}

export interface Media {
  id: number
  name: string
  src?: string
  thumbnail: string | null
}

export enum RoomType {
  Video = 'video',
  Audio = 'audio',
}

export enum PlayerType {
  Video = 'video',
  Audio = 'audio',
  YouTube = 'youtube',
}

export interface PlayStatus {
  current_playing_id: number | null
  timestamp: number
  current_time: number | null
  is_clicked_big_button: boolean
  paused: boolean
}

export interface PlayerPlayedEvent {
  socketId: string
  roomId: number
  status: PlayStatus
  isFirst: boolean
}

export interface PlayerPausedEvent {
  socketId: string
  roomId: number
  status: PlayStatus
}

export interface PlayerSeekedEvent {
  socketId: string
  roomId: number
  status: PlayStatus
}

export interface PlayerlistItemClickedEvent {
  roomId: number
}
