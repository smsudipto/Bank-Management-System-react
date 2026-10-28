import { useContext } from 'react'
import { BankContext } from './context'

export function useBank() {
  const context = useContext(BankContext)
  if (!context) throw new Error('useBank must be used within a BankProvider')
  return context
}
