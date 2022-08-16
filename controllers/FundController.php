<?php

include_once MODEL . "Fund.php";
include_once MODEL . "Deposit.php";
include_once MODEL . "Withdrawal.php";
class FundController
{
    /**
     * Send all funds in JSON.
     */
    public static function index(): void
    {

        $funds = Fund::all();

        header('Content-Type: application/json');

        echo json_encode($funds);
    }

    public static function readSingle($id)
    {
        header('Access-Control-Allow-Origin: *');

        $fund = Fund::find($id);
        if ($fund !== false) {
            header('Content-Type: application/json');
            echo json_encode($fund);
        } else {
            return false;
        }
    }

    /**
     * Store a new fund.
     */
    public static function store(): void
    {
        header('Access-Control-Allow-Origin: *');

        // Get the POSTed data.
        $data = json_decode(file_get_contents("php://input"));
        $fundName = $data->fundName;
        $fundPercentage = $data->fundPercentage;
        $balance = isset($data->balance) ? $data->balance : 0.0;
        $size = isset($data->size) ? $data->size : "Open";
        $notes = isset($data->notes) ? $data->notes : "";
        $fund = new Fund($fundName, $fundPercentage, $balance, $size, $notes);
        $message = $fund->save();

        header('Content-Type: application/json');
        echo json_encode(["message" => $message ? "Succesfully Created Resource" : "Failed to Create Resource"]);
    }

    public static function setFundName()
    {
        header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->fundName)) return false;
        $id = intval($data->id);
        $result = Fund::setFundName($id, $data->fundName);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setFundPercentage()
    {
        header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->fundPercentage)) return false;
        $id = intval($data->id);
        $result = Fund::setFundPercentage($id, $data->fundPercentage);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setSize()
    {
        header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->size)) return false;
        $id = intval($data->id);
        $result = Fund::setSize($id, $data->size);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setNotes()
    {
        header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->notes)) return false;
        $id = intval($data->id);
        $result = Fund::setNotes($id, $data->notes);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Deposit funds (POST).
     */
    public static function deposit()
    {
        header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->depositedAmount, $data->depositedTo, $data->depositSource) || !is_numeric($data->depositedAmount)) return false;

        $depositNotes = isset($data->notes) ? $data->notes : "";
        $deposit = new Deposit($data->depositSource, $data->depositedTo, $data->depositedAmount, $depositNotes);
        $result = false;
        if (strval($data->depositedTo) != "all") {
            // deposit to a specific fund
            $result = Fund::deposit(intval($data->depositedTo), $data->depositedAmount) && $deposit->save();
        } else {
            $result = Fund::depositToAll($data->depositedAmount) && $deposit->save();
        }

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Withdraw funds (POST).
     */
    public static function withdraw()
    {
        header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->withdrawnAmount) || !is_numeric($data->withdrawnAmount) || !isset($data->withdrawnFrom)) return false;

        $withdrawalNotes = isset($data->notes) ? $data->notes : "";
        $withdrawalReason = isset($data->withdrawalReason) ? $data->withdrawalReason : "";
        $withdrawal = new Withdrawal(intval($data->withdrawnFrom), abs($data->withdrawnAmount), $withdrawalReason, $withdrawalNotes);

        $result = Fund::withdraw(intval($data->withdrawnFrom), $data->withdrawnAmount) && $withdrawal->save();

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    // public static function setBalance($id)
    // {
    //     header('Access-Control-Allow-Origin: *');
    //     $data = json_decode(file_get_contents("php://input"));
    //     if (!isset($data->balance)) return false;
    //     $result = Fund::setBalance($id, $data->balance);

    //     header('Content-Type: application/json');
    //     echo json_encode(["result" => $result === false ? "Failed." : $result]);
    // }

    /**
     * Transfer funds (POST)
     */
    public static function transfer(): void
    {
    }

    public static function getDepositsHistory($for)
    {
        header('Access-Control-Allow-Origin: *');
        if ($for == "all") {
            $depositsHistory = Deposit::all();
            header('Content-Type: application/json');
            echo json_encode($depositsHistory !== false ? $depositsHistory : []);
            return;
        } else {
            $depositsHistoryForFund = Deposit::whereFundIdIs($for);
            header('Content-Type: application/json');
            echo json_encode($depositsHistoryForFund !== false ? $depositsHistoryForFund : []);
            return;
        }
    }

    public static function getWithdrawalsHistory($for)
    {
        header('Access-Control-Allow-Origin: *');
        if ($for == "all") {
            $withdrawalsHistory = Withdrawal::all();
            header('Content-Type: application/json');
            echo json_encode($withdrawalsHistory !== false ? $withdrawalsHistory : []);
        } else {
            $withdrawalsHistoryForFund = Withdrawal::whereFundIdIs($for);
            header('Content-Type: application/json');
            echo json_encode($withdrawalsHistoryForFund !== false ? $withdrawalsHistoryForFund : []);
            return;
        }
    }

    /**
     * Delete an existing fund.
     */
    public static function delete($id): void
    {
        header('Access-Control-Allow-Origin: *');
        $result = Fund::delete($id);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : "Successfully Deleted Fund."]);
    }
}
