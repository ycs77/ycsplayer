import LaravelEcho from 'laravel-echo'
import Pusher from 'pusher-js'
import axios from 'axios'

const Echo = new LaravelEcho({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
})

axios.interceptors.request.use(config => {
  if (Echo.socketId()) {
    config.headers['X-Socket-Id'] = Echo.socketId()
  }

  return config
})

function safeListenFn(fn: Function | undefined): Function {
  return fn ?? (() => {})
}

export type { Channel, PresenceChannel } from 'laravel-echo'

export { Echo, Pusher, safeListenFn }
