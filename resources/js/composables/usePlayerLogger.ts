import type { InjectionKey } from 'vue'
import type { ClientLog } from '@/types'

export interface PlayerLogger {
  logs: Ref<ClientLog[]>
  log(message: string, context?: any): void
}

export interface PlayerLoggerOptions {
  debug?: boolean
}

export const PlayerLoggerKey = Symbol('PlayerLogger') as InjectionKey<PlayerLogger>

export function usePlayerLogger(options: PlayerLoggerOptions = {}) {
  const { debug = false } = options

  const logs = ref([]) as Ref<ClientLog[]>

  function log(message: string, context?: any) {
    if (debug) {
      logs.value.push({ message, context })

      console.log(message, context)
    }
  }

  provide(PlayerLoggerKey, { logs, log })

  return { logs, log }
}

export function usePlayerLog() {
  return inject(PlayerLoggerKey)!
}
