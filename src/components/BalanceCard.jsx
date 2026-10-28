import { useBank } from '../context/useBank'
import { formatCurrency } from '../utils/formatters'

function BalanceCard() {
  const { balance, accountNumber } = useBank()
  return <div className="balance-card"><span className="balance-label">Available balance</span><div className="balance-amount">{formatCurrency(balance)}</div><span className="account-number">Account number <strong>{accountNumber}</strong></span></div>
}

export default BalanceCard
