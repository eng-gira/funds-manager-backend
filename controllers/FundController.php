<?php

include_once MODEL . "Fund.php";
include_once MODEL . "Deposit.php";
include_once MODEL . "Withdrawal.php";
include_once SERVICE . "Auth.php";
include_once INC . "functions.php";
class FundController
{
    /**
     * Send all funds in JSON.
     */
    public static function index(): void
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        $funds = Fund::whereUserIdIs(Auth::userId());

        header('Content-Type: application/json');

        echo json_encode($funds);
    }

    public static function readSingle($id)
    {
        // header('Access-Control-Allow-Origin: *');
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        $fund = Fund::find($id);
        if ($fund !== false) {
            if($fund['userId'] != Auth::userId()) {
                http_response_code(403);
                return false;
            }

            header('Content-Type: application/json');
            echo json_encode($fund);
        } else {
            http_response_code(551);
            return false;
        }
    }

    /**
     * Store a new fund.
     */
    public static function store(): void
    {
        // header('Access-Control-Allow-Origin: *');

        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // Get the POSTed data.
        $data = json_decode(file_get_contents("php://input"));
        $fundName = $data->fundName;
        $fundPercentage = $data->fundPercentage;
        $balance = isset($data->balance) ? $data->balance : 0.0;
        $size = isset($data->size) ? $data->size : "Open";
        $notes = isset($data->notes) ? $data->notes : "";
        $fund = new Fund($fundName, $fundPercentage, $balance, $size, $notes, Auth::userId());

        $message = $fund->save();

        header('Content-Type: application/json');
        echo json_encode(["message" => $message ? "Succesfully Created Resource" : "Failed to Create Resource"]);
    }

    public static function setFundName()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->fundName)) return false;
        $id = intval($data->id);

        $fund = Fund::find($id);
        if(!$fund) { http_response_code(551); return false; }
        if($fund['userId'] != Auth::userId()) {
            http_response_code(403);
            return false;
        }

        $result = Fund::setFundName($id, $data->fundName);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setFundPercentage()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->fundPercentage)) return false;
        $id = intval($data->id);
        $fund = Fund::find($id);
        if(!$fund) { http_response_code(551); return false; }
        if($fund['userId'] != Auth::userId()) {
            http_response_code(403);
            return false;
        }
        $result = Fund::setFundPercentage($id, $data->fundPercentage);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setSize()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->size)) return false;
        $id = intval($data->id);
        $fund = Fund::find($id);
        if(!$fund) { http_response_code(551); return false; }
        if($fund['userId'] != Auth::userId()) {
            http_response_code(403);
            return false;
        }
        $result = Fund::setSize($id, $data->size);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setNotes()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->notes)) return false;
        $id = intval($data->id);
        $fund = Fund::find($id);
        if(!$fund) { http_response_code(551); return false; }
        if($fund['userId'] != Auth::userId()) {
            http_response_code(403);
            return false;
        }
        $result = Fund::setNotes($id, $data->notes);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Deposit funds (POST).
     */
    public static function deposit()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->depositedAmount, $data->depositedTo, $data->depositSource) || !is_numeric($data->depositedAmount)) return false;

        $depositNotes = isset($data->notes) ? $data->notes : "";
        $result = false;
        if (strval($data->depositedTo) != "all") {
            // deposit to a specific fund
            $fund = Fund::find(intval($data->depositedTo));
            if($fund['userId'] != Auth::userId()) {
                http_response_code(403);
                return false;
            }
            $result = Fund::deposit(intval($data->depositedTo), $data->depositedAmount, $data->depositSource, $depositNotes, Auth::userId());
        } else {
            // Deposits to all funds of Auth::userId()
            $result = Fund::depositToAll($data->depositedAmount, $data->depositSource, $depositNotes, Auth::userId());
        }

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Withdraw funds (POST).
     */
    public static function withdraw()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->withdrawnAmount) || !is_numeric($data->withdrawnAmount) || !isset($data->withdrawnFrom)) return false;

        $withdrawalNotes = isset($data->notes) ? $data->notes : "";
        $withdrawalReason = isset($data->withdrawalReason) ? $data->withdrawalReason : "";

        $fund = Fund::find(intval($data->withdrawnFrom));
        if($fund['userId'] != Auth::userId()) {
            http_response_code(403);
            return false;
        }
        $result = Fund::withdraw(intval($data->withdrawnFrom), $data->withdrawnAmount, $withdrawalReason, $withdrawalNotes, Auth::userId());

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    // public static function setBalance($id)
    // {
    // //     header('Access-Control-Allow-Origin: *');
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
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        if ($for == "all") {
            $depositsHistory = Deposit::whereUserIdIs(Auth::userId());
            header('Content-Type: application/json');
            echo json_encode($depositsHistory !== false ? $depositsHistory : []);
            return;
        } else {
            $fund = Fund::find(intval($for));
            if($fund['userId'] != Auth::userId()) {
                http_response_code(403);
                return false;
            }
            $depositsHistoryForFund = Deposit::whereFundIdIs($for);
            header('Content-Type: application/json');
            echo json_encode($depositsHistoryForFund !== false ? $depositsHistoryForFund : []);
            return;
        }
    }

    public static function getWithdrawalsHistory($for)
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        if ($for == "all") {
            $withdrawalsHistory = Withdrawal::whereUserIdIs(Auth::userId());
            header('Content-Type: application/json');
            echo json_encode($withdrawalsHistory !== false ? $withdrawalsHistory : []);
        } else {
            $fund = Fund::find(intval($for));
            if($fund['userId'] != Auth::userId()) {
                http_response_code(403);
                return false;
            }
            $withdrawalsHistoryForFund = Withdrawal::whereFundIdIs($for);
            header('Content-Type: application/json');
            echo json_encode($withdrawalsHistoryForFund !== false ? $withdrawalsHistoryForFund : []);
            return;
        }
    }
    public static function getWithdrawalById($id)
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');

        $withdrawal = Withdrawal::find($id);
        if ($withdrawal !== false) {
            if($withdrawal['userId'] != Auth::userId()) {
                http_response_code(403);
                return false;
            }
            header('Content-Type: application/json');
            echo json_encode($withdrawal);
        } else {
            return false;
        }
    }

    public static function setWithdrawalNotes()
    {

        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');

        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id)) return false;
        $id = intval($data->id);

        $withdrawal = Withdrawal::find($id);
        if($withdrawal['userId'] != Auth::userId()) {
            http_response_code(403);
            return false;
        }

        $result = Withdrawal::setNotes($id, $data->notes);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function getDepositById($id)
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');

        $deposit = Deposit::find($id);
        if ($deposit !== false) {
            if($deposit['userId'] != Auth::userId()) {
                http_response_code(403);
                return false;
            }
    
            header('Content-Type: application/json');
            echo json_encode($deposit);
        } else {
            return false;
        }
    }

    public static function setDepositNotes()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }
        // header('Access-Control-Allow-Origin: *');

        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id)) return false;
        $id = intval($data->id);

        $deposit = Deposit::find($id);
        if($deposit['userId'] != Auth::userId()) {
            http_response_code(403);
            return false;
        }

        $result = Deposit::setNotes($id, $data->notes);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Export the funds data (with withdrawals and deposits) in to file(s).
     */
    public static function export()
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }  

        $userId = Auth::userId();

        $funds = Fund::whereUserIdIs($userId);
        $withdrawals = Withdrawal::whereUserIdIs($userId);
        $deposits = Deposit::whereUserIdIs($userId);

        $currentDateTime = date("Y") . "-" . date("m") . "-" . date("d") . "_" . date("H") . "-" . date("i") . "-" . date("s");
        $fundsFileName = "funds_$currentDateTime";
        $withdrawalsFileName = "withdrawals_$currentDateTime";
        $depositsFileName = "deposits_$currentDateTime";

        file_put_contents($fundsFileName, json_encode($funds));
        file_put_contents($withdrawalsFileName, json_encode($withdrawals));
        file_put_contents($depositsFileName, json_encode($deposits));
    }

    /**
     * Delete an existing fund.
     */
    public static function delete($id): void
    {
        if(Auth::userId() === false) {
            http_response_code(401);
            exit;
        }

        // header('Access-Control-Allow-Origin: *');
        $result = Fund::delete($id);

        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : "Successfully Deleted Fund."]);
    }
}
