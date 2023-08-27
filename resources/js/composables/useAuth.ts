export function useAuth() {
  const user = computed(() => usePage().props.auth?.user)

  return { user }
}
