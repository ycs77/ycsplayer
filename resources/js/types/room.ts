export enum RoomType {
  Video = 'video',
  Audio = 'audio',
}

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
  avatar: string
  role: string
  online: boolean
}

export interface Media {
  id: string
  name: string
  src: string
  converting: boolean
  thumbnail: string | null
  preview: string | null
}
