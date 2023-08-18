export enum PlayerType {
  Video = 'video',
  Audio = 'audio',
  YouTube = 'youtube',
}

export interface PlayStatus {
  timestamp: number
  current_time: number
  is_started: boolean
  paused: boolean
}

export interface MediaOperationEvent {
  type: PlayerType
  status: PlayStatus
}

export interface MediaPlayedEvent extends MediaOperationEvent {}
export interface MediaPausedEvent extends MediaOperationEvent {}
export interface MediaSeekedEvent extends MediaOperationEvent {}
