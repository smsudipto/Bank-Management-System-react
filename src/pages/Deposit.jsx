import { ArrowDownToLine, ShieldCheck } from 'lucide-react'
import AmountForm from '../components/AmountForm'
import { useBank } from '../context/useBank'

function Deposit() {
  const { deposit } = useBank()
  return <div className="page-content"><div className="page-heading"><div><p className="eyebrow">Move money</p><h1>Deposit money</h1><p className="page-description">Add funds to your SudiptoBank account securely.</p></div></div><div className="form-layout"><div className="card form-card"><div className="metric-icon deposit"><ArrowDownToLine size={19} /></div><h2 style={{ marginTop: 18 }}>Add to your balance</h2><p>Enter the amount you would like to deposit. Your balance and activity will update instantly.</p><AmountForm type="deposit" onSubmit={(amount) => { deposit(amount); return Promise.resolve(true) }} /></div><div className="card info-card"><ShieldCheck size={23} /><h3>Safe and simple</h3><p>This is a simulated banking environment for demonstration purposes.</p><ul><li>Amounts are stored in BDT.</li><li>Transactions are saved locally.</li><li>All activity is shown in your history.</li></ul></div></div></div>
}

export default Deposit
