import { ClipboardList } from 'lucide-react'
import TransactionItem from './TransactionItem'

function TransactionList({ transactions }) {
  if (!transactions.length) return <div className="empty-state"><ClipboardList className="empty-icon" size={30} /><p>No transactions yet. Your activity will appear here.</p></div>
  return <div className="transaction-list">{transactions.map((transaction) => <TransactionItem key={transaction.id} transaction={transaction} />)}</div>
}

export default TransactionList
