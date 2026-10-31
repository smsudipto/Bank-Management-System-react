document.querySelector('[data-open-menu]')?.addEventListener('click', () => {
  document.querySelector('#sidebar')?.classList.add('open')
})

document.querySelector('[data-close-menu]')?.addEventListener('click', () => {
  document.querySelector('#sidebar')?.classList.remove('open')
})

document.querySelectorAll('form[method="post"]').forEach((form) => {
  form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]')
    if (button) {
      button.disabled = true
      button.textContent = 'Processing...'
    }
  })
})
