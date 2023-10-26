export enum PlayerType {
  Video = 'video',
  Audio = 'audio',
  YouTube = 'youtube',
}

export enum PlayerTrigger {
  Normal = 'normal',
  Click = 'click',
  Next = 'next',
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
