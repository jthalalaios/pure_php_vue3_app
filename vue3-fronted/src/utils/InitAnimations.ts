export function splitTextAnimations() {
  // Simple reveal animation for elements with data-split attribute
  const els = document.querySelectorAll('[data-split]')
  els.forEach((el, idx) => {
    (el as HTMLElement).style.opacity = '0'
    (el as HTMLElement).style.transform = 'translateY(8px)'
    setTimeout(() => {
      (el as HTMLElement).style.transition = 'opacity .6s ease, transform .6s ease'
      (el as HTMLElement).style.opacity = '1'
      (el as HTMLElement).style.transform = 'none'
    }, 80 * idx)
  })
}
