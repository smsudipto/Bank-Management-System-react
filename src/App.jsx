import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { BankProvider } from './context/BankContext'
import AppLayout from './components/AppLayout'
import Dashboard from './pages/Dashboard'
import Deposit from './pages/Deposit'
import Withdraw from './pages/Withdraw'
import Transactions from './pages/Transactions'
import NotFound from './pages/NotFound'

function App() {
  return (
    <BrowserRouter>
      <BankProvider>
        <Routes>
          <Route element={<AppLayout />}>
            <Route path="/" element={<Dashboard />} />
            <Route path="/deposit" element={<Deposit />} />
            <Route path="/withdraw" element={<Withdraw />} />
            <Route path="/transactions" element={<Transactions />} />
            <Route path="/not-found" element={<NotFound />} />
            <Route path="*" element={<Navigate to="/not-found" replace />} />
          </Route>
        </Routes>
      </BankProvider>
    </BrowserRouter>
  )
}

export default App
