import { useEffect, useMemo, useState } from 'react'
import { BankContext } from './context'

const STORAGE_KEY = 'bank-management-state'
const initialState = {
  accountHolder: 'Sudipto Mandal',
  accountNumber: '•••• 4829',
  balance: 0,
  transactions: [],
}

function isValidState(value) {
  return value && typeof value === 'object' && typeof value.balance === 'number' &&
    Array.isArray(value.transactions) && value.transactions.every((item) =>
      item && typeof item.id === 'string' && (item.type === 'deposit' || item.type === 'withdrawal') &&
      Number.isSafeInteger(item.amount) && item.amount > 0 && typeof item.createdAt === 'string')
}

function calculateBalance(transactions) {
  return transactions.reduce((balance, transaction) =>
    balance + (transaction.type === 'deposit' ? transaction.amount : -transaction.amount), 0)
}

function getInitialState() {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (!stored) return initialState
    const parsed = JSON.parse(stored)
    if (!isValidState(parsed)) return initialState
    const transactions = parsed.transactions.filter((transaction) => !transaction.id.startsWith('initial-'))
    return {
      ...parsed,
      accountHolder: initialState.accountHolder,
      transactions,
      balance: calculateBalance(transactions),
    }
  } catch {
    return initialState
  }
}

export function BankProvider({ children }) {
  const [state, setState] = useState(getInitialState)

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
  }, [state])

  const deposit = (amount) => {
    setState((current) => ({
      ...current,
      balance: current.balance + amount,
      transactions: [{ id: crypto.randomUUID(), type: 'deposit', amount, createdAt: new Date().toISOString() }, ...current.transactions],
    }))
  }

  const withdraw = (amount) => {
    let accepted = false
    setState((current) => {
      if (amount > current.balance) return current
      accepted = true
      return {
        ...current,
        balance: current.balance - amount,
        transactions: [{ id: crypto.randomUUID(), type: 'withdrawal', amount, createdAt: new Date().toISOString() }, ...current.transactions],
      }
    })
    return accepted
  }

  const value = useMemo(() => ({
    ...state,
    totalDeposited: state.transactions.filter((item) => item.type === 'deposit').reduce((total, item) => total + item.amount, 0),
    totalWithdrawn: state.transactions.filter((item) => item.type === 'withdrawal').reduce((total, item) => total + item.amount, 0),
    deposit,
    withdraw,
  }), [state])

  return <BankContext.Provider value={value}>{children}</BankContext.Provider>
}
