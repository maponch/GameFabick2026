import { useDisplay } from 'vuetify'

export function useResponsive() {
  const display = useDisplay()

  return {
    ...display,
    isMobile: display.smAndDown,
    isDesktop: display.mdAndUp,
  }
}