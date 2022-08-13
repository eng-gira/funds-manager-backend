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

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        echo json_encode($funds);
    }

    /**
     * Store a new fund.
     */
    public static function store(): void
    {
        // Get the POSTed data.
        $data = json_decode(file_get_contents("php://input"));
        $fundName = $data->fundName;
        $fundPercentage = $data->fundPercentage;
        $balance = $data->balance != null ? $data->balance : 0.0;
        $size = $data->size != null ? $data->size : "Open";
        $notes = $data->notes != null ? $data->notes : "";
        $fund = new Fund($fundName, $fundPercentage, $balance, $size, $notes);
        $message = $fund->save();

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["message" => $message ? "Succesfully Created Resource" : "Failed to Create Resource"]);
    }

    public static function setFundName($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->fundName == null) return false;
        $result = Fund::setFundName($id, $data->fundName);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setFundPercentage($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->fundPercentage == null) return false;
        $result = Fund::setFundPercentage($id, $data->fundPercentage);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setSize($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->size == null) return false;
        $result = Fund::setSize($id, $data->size);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }
    public static function setNotes($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->notes == null) return false;
        $result = Fund::setNotes($id, $data->notes);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Deposit funds (POST).
     */
    public static function deposit($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->amount == null || !is_numeric($data->amount) || $data->depositedTo == null || $data->depositSource) return false;

        $fund = Fund::find($id);
        if ($fund === false) return false;
        $currentBalance = $fund["balance"];
        $newBalance = $currentBalance + abs($data->amount);

        $depositNotes = $data->notes == null ? "" : $data->notes;
        $deposit = new Deposit($data->depositSource, $data->depositedTo, $data->amount, $depositNotes);

        $result = Fund::setBalance($id, $newBalance) && $deposit->save();

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Withdraw funds (POST).
     */
    public static function withdraw($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->amount == null || !is_numeric($data->amount)) return false;
        $fund = Fund::find($id);
        if ($fund === false) return false;
        $currentBalance = $fund["balance"];
        $newBalance = $currentBalance - abs($data->amount);

        $withdrawalNotes = $data->notes == null ? "" : $data->notes;
        $withdrawalReason = $data->withdrawalReason == null ? "" : $data->withdrawalReason;
        $withdrawal = new Withdrawal($withdrawalReason, $id, abs($data->amount), $withdrawalNotes);

        $result = Fund::setBalance($id, $newBalance) && $withdrawal->save();

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    public static function setBalance($id)
    {
        $data = json_decode(file_get_contents("php://input"));
        if ($data->balance == null) return false;
        $result = Fund::setBalance($id, $data->balance);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : $result]);
    }

    /**
     * Transfer funds (POST)
     */
    public static function transfer(): void
    {
    }

    /**
     * Delete an existing fund.
     */
    public static function delete($id): void
    {
        $result = Fund::delete($id);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode(["result" => $result === false ? "Failed." : "Successfully Deleted Fund."]);
    }
}
