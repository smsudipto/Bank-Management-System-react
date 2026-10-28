# SudiptoBank — Bank Management System

SudiptoBank is a frontend-only bank management practice project developed with React and Vite. It simulates deposits and withdrawals with shared Context API state, BDT currency formatting, transaction history, and browser persistence. It has no backend, real banking connection, or real money transfer capability.

## Features

- Responsive banking dashboard with balance, account details, and financial summaries
- Deposit and withdrawal forms with operation-level validation
- Accessible withdrawal confirmation dialog
- Transaction history with clear deposit and withdrawal indicators
- React Router navigation and a designed not-found page
- Context API state shared across every route
- `localStorage` persistence across browser refreshes
- Integer minor-unit calculations to avoid monetary floating-point errors

## Technology

React, Vite, JavaScript, JSX, React Router DOM, Context API, React Hooks, CSS3, localStorage, and Lucide React.

## Structure

```text
src/
├── components/     Reusable layout, navigation, form, balance, and transaction UI
├── context/        Shared banking state, context, and persistence hook
├── pages/          Dashboard, deposit, withdrawal, history, and not-found routes
├── utils/          Currency, date, and amount validation helpers
├── App.jsx         Router and application composition
├── index.css       Responsive design system
└── main.jsx        Vite entry point
```

## Requirements

Use Node.js 20 or newer and npm.

## Installation and development

Clone the public repository after replacing the placeholder URL with the actual repository URL:

```bash
git clone <YOUR_PUBLIC_GITHUB_REPOSITORY_URL>
cd <YOUR_PROJECT_DIRECTORY>
npm install
npm run dev
```

Open the local URL printed by Vite.

## Production build

```bash
npm run build
npm run preview
```

## Routes

- `/` - Dashboard
- `/deposit` - Deposit money
- `/withdraw` - Withdraw money
- `/transactions` - Full transaction history

## Simulated banking behavior

This application does not connect to a bank, backend, payment provider, or real account. It starts with a zero balance and no transactions. Valid deposits increase the balance and valid withdrawals decrease it after confirmation. All values are displayed as Bangladeshi Taka (BDT).

The balance and transaction list are stored in the browser's localStorage for demonstration convenience. This is not a secure financial database and should not be used for real financial activity. Invalid or malformed saved data is ignored in favor of safe initial data.

## GitHub preparation

Review the project locally, create an empty public repository on GitHub, then run these commands from this project directory:

```bash
git init
git add .
git commit -m "Build SudiptoBank management system"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git push -u origin main
```

Replace the remote URL with your own repository URL. Do not commit credentials or environment secrets.
