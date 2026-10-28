export function formatCurrency(minorUnits) {
  return new Intl.NumberFormat('en-BD', { style: 'currency', currency: 'BDT', maximumFractionDigits: 2 }).format(minorUnits / 100)
}

export function formatDate(date) {
  return new Intl.DateTimeFormat('en-BD', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(date))
}

export function parseAmount(value) {
  const normalized = value.trim()
  if (!normalized) return { error: 'Please enter an amount.' }
  if (!/^\d+(\.\d{1,2})?$/.test(normalized)) return { error: 'Enter a valid amount with up to 2 decimal places.' }
  const amount = Math.round(Number(normalized) * 100)
  if (!Number.isSafeInteger(amount) || amount <= 0) return { error: 'Enter an amount greater than zero.' }
  return { amount }
}
