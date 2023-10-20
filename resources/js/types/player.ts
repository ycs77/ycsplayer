export enum PlayerType {
  Video = 'video',
  Audio = 'audio',
  YouTube = 'youtube',
}

export interface PlaylistItem {
  id: string
  type: PlayerType
  title: string
  url: string
  thumbnail: string | null
  preview: string | null
}

export interface PlaylistItemForm extends Record<string, any> {
  type: PlayerType
  title: string
  url: string
  media_id: string | null
}
