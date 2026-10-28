import TransactionList from '../components/TransactionList'
import { useBank } from '../context/useBank'

function Transactions() {
  const { transactions } = useBank()
  return <div className="page-content"><div className="page-heading"><div><p className="eyebrow">Your activity</p><h1>Transaction history</h1><p className="page-description">Review every deposit and withdrawal from your account.</p></div></div><div className="card history-card"><div className="history-header"><h2 className="card-title">All transactions</h2><span className="history-count">{transactions.length} {transactions.length === 1 ? 'transaction' : 'transactions'}</span></div><div className="history-list"><TransactionList transactions={transactions} /></div></div></div>
}

export default Transactions
