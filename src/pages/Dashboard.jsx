import { ArrowDownToLine, ArrowUpFromLine, TrendingDown, TrendingUp } from 'lucide-react'
import { Link } from 'react-router-dom'
import BalanceCard from '../components/BalanceCard'
import TransactionList from '../components/TransactionList'
import { useBank } from '../context/useBank'
import { formatCurrency } from '../utils/formatters'

function Dashboard() {
  const { accountHolder, totalDeposited, totalWithdrawn, transactions } = useBank()
  return <div className="page-content"><div className="page-heading"><div><p className="eyebrow">Good morning, {accountHolder.split(' ')[0]}</p><h1>Your financial overview</h1><p className="page-description">Keep track of your money and manage your account with ease.</p></div></div>
    <div className="dashboard-grid"><div><BalanceCard /><div className="metric-grid"><div className="card metric-card"><div className="metric-icon deposit"><TrendingUp size={19} /></div><div><div className="metric-label">Total deposited</div><div className="metric-value">{formatCurrency(totalDeposited)}</div></div></div><div className="card metric-card"><div className="metric-icon withdraw"><TrendingDown size={19} /></div><div><div className="metric-label">Total withdrawn</div><div className="metric-value">{formatCurrency(totalWithdrawn)}</div></div></div></div></div>
      <div className="card"><div className="card-heading"><h2 className="card-title">Quick actions</h2></div><div className="shortcuts"><Link className="shortcut deposit" to="/deposit"><ArrowDownToLine size={17} /> Deposit</Link><Link className="shortcut withdraw" to="/withdraw"><ArrowUpFromLine size={17} /> Withdraw</Link></div><div className="card-heading" style={{ marginTop: 32 }}><h2 className="card-title">Account status</h2></div><p className="page-description">Your SudiptoBank account is active and ready for everyday banking.</p></div></div>
    <div className="card" style={{ marginTop: 20 }}><div className="card-heading"><h2 className="card-title">Recent activity</h2><Link className="text-link" to="/transactions">View all</Link></div><TransactionList transactions={transactions.slice(0, 4)} /></div>
  </div>
}

export default Dashboard
