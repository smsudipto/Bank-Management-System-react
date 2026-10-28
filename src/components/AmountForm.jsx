import { useEffect, useRef, useState } from 'react'
import { CheckCircle2 } from 'lucide-react'
import { parseAmount } from '../utils/formatters'

function AmountForm({ type, onSubmit }) {
  const [value, setValue] = useState('')
  const [error, setError] = useState('')
  const [message, setMessage] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const inputRef = useRef(null)
  const isDeposit = type === 'deposit'

  useEffect(() => { inputRef.current?.focus() }, [])

  const handleSubmit = async (event) => {
    event.preventDefault()
    setMessage('')
    const result = parseAmount(value)
    if (result.error) { setError(result.error); return }
    setError('')
    setSubmitting(true)
    const completed = await onSubmit(result.amount)
    setSubmitting(false)
    if (completed) { setValue(''); setMessage(`${isDeposit ? 'Deposit' : 'Withdrawal'} completed successfully.`) }
  }

  return <form onSubmit={handleSubmit} noValidate>
    {message && <div className="feedback success" role="status"><CheckCircle2 size={15} /> {message}</div>}
    {error && <div className="feedback error" role="alert">{error}</div>}
    <label className="amount-label" htmlFor={`${type}-amount`}>Amount</label>
    <div className="amount-input-wrap"><span className="currency-prefix">৳</span><input ref={inputRef} className="amount-input" id={`${type}-amount`} inputMode="decimal" placeholder="0.00" value={value} onChange={(event) => { setValue(event.target.value); setError(''); setMessage('') }} aria-describedby={`${type}-note`} /></div>
    <p className="form-note" id={`${type}-note`}>Enter an amount in BDT, up to 2 decimal places.</p>
    <button className="submit-button" type="submit" disabled={submitting}>{submitting ? 'Processing...' : `Confirm ${isDeposit ? 'deposit' : 'withdrawal'}`}</button>
  </form>
}

export default AmountForm
