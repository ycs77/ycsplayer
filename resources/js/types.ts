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

export interface PlayerOperationEvent {
  type: PlayerType
  status: PlayStatus
}

export interface PlayerPlayedEvent extends PlayerOperationEvent {}
export interface PlayerPausedEvent extends PlayerOperationEvent {}
export interface PlayerSeekedEvent extends PlayerOperationEvent {}
