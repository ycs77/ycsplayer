import axios from 'axios'

export function usePlayerServerTimestamp(roomId: string) {
  const serverTimestamp = ref(0)
  const timestampFetched = ref(false)

  const offsetTimestamp = computed(() => {
    if (serverTimestamp.value > 0) {
      return serverTimestamp.value - Date.now()
    }
    return 0
  })

  function toServerTimestamp(clientTimestamp: number) {
    return clientTimestamp + offsetTimestamp.value
  }

  function toClientTimestamp(serverTimestamp: number) {
    return serverTimestamp - offsetTimestamp.value
  }

  axios.post(`/rooms/${roomId}/timestamp`).then(({ data }) => {
    serverTimestamp.value = data

    timestampFetched.value = true
  }).catch(() => {
    timestampFetched.value = true
  })

  return {
    serverTimestamp,
    offsetTimestamp,
    timestampFetched,
    toServerTimestamp,
    toClientTimestamp,
  }
}
