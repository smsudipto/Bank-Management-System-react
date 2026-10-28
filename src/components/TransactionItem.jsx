import { ArrowDownLeft, ArrowUpRight } from 'lucide-react'
import { formatCurrency, formatDate } from '../utils/formatters'

function TransactionItem({ transaction }) {
  const isDeposit = transaction.type === 'deposit'
  return <div className="transaction-row"><div className="transaction-leading"><div className={`transaction-icon ${isDeposit ? 'deposit' : 'withdraw'}`}>{isDeposit ? <ArrowDownLeft size={17} /> : <ArrowUpRight size={17} />}</div><div><div className="transaction-name">{isDeposit ? 'Deposit' : 'Withdrawal'}</div><div className="transaction-date">{formatDate(transaction.createdAt)}</div></div></div><span className={`transaction-amount ${isDeposit ? 'deposit' : 'withdraw'}`}>{isDeposit ? '+' : '-'}{formatCurrency(transaction.amount)}</span></div>
}

export default TransactionItem
