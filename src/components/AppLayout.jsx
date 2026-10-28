import { useState } from 'react'
import { Menu } from 'lucide-react'
import { Outlet } from 'react-router-dom'
import Navbar from './Navbar'
import { useBank } from '../context/useBank'

function AppLayout() {
  const [menuOpen, setMenuOpen] = useState(false)
  const { accountHolder } = useBank()
  const initials = accountHolder.split(' ').map((part) => part[0]).join('')

  return (
    <div className="app-shell">
      <Navbar open={menuOpen} onNavigate={() => setMenuOpen(false)} />
      <div className="main-area">
        <header className="topbar">
          <button className="mobile-menu" onClick={() => setMenuOpen(true)} aria-label="Open navigation"><Menu size={21} /></button>
          <span className="topbar-title">Personal banking overview</span>
          <div className="profile">
            <div className="avatar">{initials}</div>
            <div><span className="profile-name">{accountHolder}</span><span className="profile-role">Premium account</span></div>
          </div>
        </header>
        <main><Outlet /></main>
      </div>
    </div>
  )
}

export default AppLayout
