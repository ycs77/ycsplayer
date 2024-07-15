import type { InjectionKey, MaybeRefOrGetter } from 'vue'
import type { ClientLog } from '@/types'

export interface PlayerLogger {
  logs: Ref<ClientLog[]>
  log: (message: string, context?: any) => void
}

export const PlayerLoggerKey = Symbol('PlayerLogger') as InjectionKey<PlayerLogger>

export function usePlayerLogger(debug: MaybeRefOrGetter<boolean> = false) {
  const _debug = toRef(debug)

  const logs = ref([]) as Ref<ClientLog[]>

  function log(message: string, context?: any) {
    if (_debug.value) {
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
