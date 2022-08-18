# funds-manager-api

## Description

- A RESTful API for my funds-manager application.
- It follows the MVC architecture (without views). 
- Uses a MySQL database.
- Was built using Core PHP only.
- Hosted on Heroku.

### Install

Run git clone <repo>

### Run

Start using the API

## How to use this API
### The End-Points
- Get all funds (GET): https://funds-manager-backend.herokuapp.com/api/Fund/index
- Get a single fund by id (GET): https://funds-manager-backend.herokuapp.com/api/Fund/index/readSingle/{id}
- Store a fund (POST): https://funds-manager-backend.herokuapp.com/api/Fund/store
- Deposit funds (POST): https://funds-manager-backend.herokuapp.com/api/Fund/deposit
- Withdraw funds (POST): https://funds-manager-backend.herokuapp.com/api/Fund/withdraw
- Get deposits history (GET): https://funds-manager-backend.herokuapp.com/api/Fund/getDepositHistory/all
- Get withdrawals history (GET): https://funds-manager-backend.herokuapp.com/api/Fund/getWithdrawalsHistory/all"
- Get deposits history for a fund by id (GET): https://funds-manager-backend.herokuapp.com/api/Fund/getDepositsHistory/{id}
- Get withdrawals history for fund by id (GET): https://funds-manager-backend.herokuapp.com/api/Fund/getWithdrawalsHistory/{id}
- Update a fund's name (POST): https://funds-manager-backend.herokuapp.com/api/Fund/setFundName
- Update a fund's percentage (POST): https://funds-manager-backend.herokuapp.com/api/Fund/setFundPercentage
- Update a fund's size (POST): https://funds-manager-backend.herokuapp.com/api/Fund/setSize
- Update a fund's notes (POST): https://funds-manager-backend.herokuapp.com/api/Fund/setNotes