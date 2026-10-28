import { ArrowUpFromLine, ShieldAlert } from 'lucide-react'
import { useState } from 'react'
import AmountForm from '../components/AmountForm'
import { useBank } from '../context/useBank'
import { formatCurrency } from '../utils/formatters'

function Withdraw() {
  const { balance, withdraw } = useBank()
  const [pendingAmount, setPendingAmount] = useState(null)
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')
  const submit = (amount) => {
    setSuccess('')
    if (amount > balance) { setError(`Insufficient balance. You can withdraw up to ${formatCurrency(balance)}.`); return Promise.resolve(false) }
    setError('')
    setPendingAmount(amount)
    return Promise.resolve(false)
  }
  const confirm = () => { withdraw(pendingAmount); setPendingAmount(null); setSuccess('Withdrawal completed successfully.') }
  return   <div className="page-content"><div className="page-heading"><div><p className="eyebrow">Move money</p><h1>Withdraw money</h1><p className="page-description">Take out funds from your available account balance.</p></div></div><div className="form-layout"><div className="card form-card"><div className="metric-icon withdraw"><ArrowUpFromLine size={19} /></div><h2 style={{ marginTop: 18 }}>Withdraw from balance</h2><p>Enter an amount and review it before confirming the withdrawal.</p>{success && <div className="feedback success" role="status">{success}</div>}{error && <div className="feedback error" role="alert">{error}</div>}<AmountForm type="withdrawal" onSubmit={submit} /></div><div className="card info-card"><ShieldAlert size={23} /><h3>Available balance</h3><div style={{ fontFamily: 'Space Grotesk', fontSize: 24, margin: '14px 0' }}>{formatCurrency(balance)}</div><p>Withdrawals cannot exceed your current balance.</p></div></div>
    {pendingAmount !== null && <div className="confirm-backdrop" role="presentation"><div className="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-title"><h2 id="confirm-title">Confirm withdrawal</h2><p>Please confirm that you want to withdraw the following amount from your account.</p><div className="confirm-amount">{formatCurrency(pendingAmount)}</div><div className="dialog-actions"><button className="cancel-button" onClick={() => setPendingAmount(null)}>Cancel</button><button className="submit-button" onClick={confirm}>Confirm withdrawal</button></div></div></div>}
  </div>
}

export default Withdraw
