export interface Room {
  id: string
  type: RoomType
  name: string
  auto_play: boolean
  auto_remove: boolean
  note?: string | null
}

export interface RoomMember {
  id: string
  name: string
  email: string
  avatar: string | null
  role: string
  online: boolean
}

export interface PlaylistItem {
  id: string
  type: PlayerType
  title: string
  url: string
  thumbnail: string | null
  preview: string | null
}

export interface Media {
  id: string
  name: string
  src: string
  thumbnail: string | null
  preview: string | null
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
  timestamp: number
  current_time: number | null
  is_clicked_big_button: boolean
  paused: boolean
}

export interface PlaylistItemForm extends Record<string, any> {
  type: PlayerType
  title: string
  url: string
  media_id: string | null
}

export interface RoomOnlineMembersUpdated {
  roomId: string
}

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
