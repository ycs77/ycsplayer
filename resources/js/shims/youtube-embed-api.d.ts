// namespace YT {
//   export declare class Player {
//     constructor(id: string, options: {
//       width?: number
//       height?: number
//       videoId?: string
//       playerVars?: {
//         autoplay?: 0 | 1
//         cc_lang_pref?: string
//         cc_load_policy?: 0 | 1
//         color?: 'red' | 'white'
//         controls?: 0 | 1
//         disablekb?: 0 | 1
//         enablejsapi?: 0 | 1
//         end?: number
//         fs?: 0 | 1
//         hl?: string
//         iv_load_policy?: 1 | 3
//         list?: string
//         listType?: ListType
//         loop?: 0 | 1
//         origin?: string
//         playlist?: string
//         playsinline?: 0 | 1
//         rel?: 0 | 1
//         start?: number
//         widget_referrer?: string
//       }
//       events?: {
//         onReady?(e: ReadyEvent): void
//         onStateChange?(e: StateChangeEvent): void
//         onPlaybackQualityChange?(e: PlayerQualityEvent): void
//         onPlaybackRateChange?(e: PlaybackRateChangeEvent): void
//         onError?(e: ErrorEvent): void
//         onApiChange?(e: ApiChangeEvent): void
//       }
//     })

//     cueVideoById(videoId: string, startSeconds?: number): void
//     cueVideoById(options: {
//       videoId: string
//       startSeconds?: number
//       endSeconds?: numbe
//     }): void

//     loadVideoById(videoId: string, startSeconds?: number): void
//     loadVideoById(options: {
//       videoId: string
//       startSeconds?: number
//       endSeconds?: numbe
//     }): void

//     cueVideoByUrl(mediaContentUrl: string, startSeconds?: number): void
//     cueVideoByUrl(options: {
//       mediaContentUrl: string
//       startSeconds?: number
//       endSeconds?: number
//     }): void

//     loadVideoByUrl(mediaContentUrl: string, startSeconds?: number): void
//     loadVideoByUrl(options: {
//       mediaContentUrl: string
//       startSeconds?: number
//       endSeconds?: number
//     }): void

//     cuePlaylist(playlist: string|string[], index?: number, startSeconds?: number): void
//     cuePlaylist(options: {
//       listType: ListType
//       playlist: string|string[]
//       index?: number
//       startSeconds?: number
//     }): void

//     loadPlaylist(playlist: string|string[], index?: number, startSeconds?: number): void
//     loadPlaylist(options: {
//       listType: ListType
//       playlist: string|string[]
//       index?: number
//       startSeconds?: number
//     }): void

//     playVideo(): void
//     pauseVideo(): void
//     stopVideo(): void
//     seekTo(seconds: number, allowSeekAhead: boolean): void

//     nextVideo(): void
//     previousVideo(): void
//     playVideoAt(index: number): void
//   }

//   export enum PlayerState {
//     UNSTARTED = -1,
//     ENDED = 0,
//     PLAYING = 1,
//     PAUSED = 2,
//     BUFFERING = 3,
//     CUED = 5,
//   }

//   export type PlayerQuality = 'small' | 'medium' | 'large' | 'hd720' | 'hd1080' | 'highres'

//   export type ListType = 'playlist' | 'user_uploads'

//   export interface Event<T = any> {
//     data: T
//     target: Player
//   }

//   export interface ReadyEvent extends Event<null> {}

//   export interface StateChangeEvent extends Event<PlayerState> {}

//   export interface PlayerQualityEvent extends Event<PlayerQuality> {}

//   export interface PlaybackRateChangeEvent extends Event<number> {}

//   export interface ErrorEvent extends Event<number> {}

//   export interface ApiChangeEvent extends Event {}
// }

export {}
