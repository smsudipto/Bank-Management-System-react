import { BarChart3, CreditCard, LayoutDashboard, List, X } from 'lucide-react'
import { NavLink } from 'react-router-dom'

const links = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/deposit', label: 'Deposit money', icon: CreditCard },
  { to: '/withdraw', label: 'Withdraw money', icon: BarChart3 },
  { to: '/transactions', label: 'Transaction history', icon: List },
]

function Navbar({ open, onNavigate }) {
  return (
    <aside className={`sidebar ${open ? 'open' : ''}`}>
      <div className="brand"><div className="brand-mark">N</div><div><span className="brand-name">SudiptoBank</span><span className="brand-subtitle">PERSONAL BANKING</span></div><button className="mobile-menu" onClick={onNavigate} aria-label="Close navigation"><X size={20} color="white" /></button></div>
      <span className="nav-label">Menu</span>
      <nav className="nav-list" aria-label="Main navigation">
        {links.map(({ to, label, icon: Icon }) => <NavLink key={to} to={to} end={to === '/'} onClick={onNavigate} className={({ isActive }) => `nav-link ${isActive ? 'active' : ''}`}><Icon size={18} strokeWidth={1.8} /><span>{label}</span></NavLink>)}
      </nav>
      <div className="sidebar-footer">Your finances, simplified.<br />Securely managed for you.</div>
    </aside>
  )
}

export default Navbar
