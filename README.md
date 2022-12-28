# funds-manager-api

## Description

- A RESTful API for my funds-manager application.
- Uses JWT for authentication.
- It follows the MVC architecture (without views). 
- Uses a MySQL database.
- Was built using Core PHP only.

### Installation and Running

Run the command `git clone <repo>`
and start using the API.

## How to use this API
### The End-Points
- Get all funds (GET): /api/Fund/index
- Get a single fund by id (GET): /api/Fund/index/readSingle/{id}
- Store a fund (POST): /api/Fund/store
- Deposit funds (POST): /api/Fund/deposit
- Withdraw funds (POST): /api/Fund/withdraw
- Get deposits history (GET): /api/Fund/getDepositHistory/all
- Get withdrawals history (GET): /api/Fund/getWithdrawalsHistory/all"
- Get deposits history for a fund by id (GET): /api/Fund/getDepositsHistory/{id}
- Get withdrawals history for fund by id (GET): /api/Fund/getWithdrawalsHistory/{id}
- Update a fund's name (POST): /api/Fund/setFundName
- Update a fund's percentage (POST): /api/Fund/setFundPercentage
- Update a fund's size (POST): /api/Fund/setSize
- Update a fund's notes (POST): /api/Fund/setNotes