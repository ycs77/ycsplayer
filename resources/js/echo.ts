import LaravelEcho from 'laravel-echo'
import Pusher from 'pusher-js'

const Echo = new LaravelEcho({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
})

export { Echo, Pusher }
