<?php
include_once DATA . "DB.php";
include_once MODEL . "Fund.php";
class Withdrawal extends DB
{
    private string $withdrawalReason;
    /**
     * @value an existing fund's id.
     */
    private string $withdrawnFrom;
    private float $withdrawnAmount; // stored in the DB as a string.
    private string $notes;

    public function __construct($withdrawnFrom, $withdrawnAmount, $withdrawalReason = "", $notes = "")
    {
        $this->withdrawnFrom = $withdrawnFrom;
        $this->withdrawnAmount = $withdrawnAmount;

        $this->withdrawalReason = $withdrawalReason;
        $this->notes = $notes;
    }

    public static function all(): array|bool
    {
        $withdrawals = [];

        $conn = DB::connect();

        $sql = "SELECT * FROM withdrawals";
        $result = $conn->query($sql);
        if ($result->num_rows != 0) {
            while ($row = $result->fetch_assoc()) {
                $fund = Fund::find(intval($row["withdrawnFrom"]));
                $withdrawnFromName = "";
                if ($fund === false) {
                    $withdrawnFromName = "Deleted";
                } else {
                    $withdrawnFromName = $fund["fundName"];
                }
                $withdrawals[count($withdrawals)] = [
                    "id" => $row["id"],
                    "withdrawalReason" => $row["withdrawalReason"],
                    "withdrawnFrom" => $withdrawnFromName,
                    "withdrawnAmount" => floatval($row["withdrawnAmount"]),
                    "notes" => $row["notes"],
                    "createdOn" => $row["createdOn"],
                    "updatedOn" => $row["updatedOn"],
                ];
            }

            return $withdrawals;
        }

        return false;
    }

    public static function find($id): array|bool
    {
        $conn = DB::connect();
        $sql = "SELECT * FROM withdrawals WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows != 0) {
                    $stmt->bind_result(
                        $id,
                        $withdrawalReason,
                        $withdrawnFrom,
                        $withdrawnAmount,
                        $notes,
                        $createdOn,
                        $updatedOn,
                    );
                    $stmt->fetch();

                    $fund = Fund::find(intval($withdrawnFrom));
                    $withdrawnFromName = "";
                    if ($fund === false) {
                        $withdrawnFromName = "Deleted";
                    } else {
                        $withdrawnFromName = $fund["fundName"];
                    }

                    return [
                        "id" => $id, "withdrawalReason" => $withdrawalReason, "withdrawnFrom" => $withdrawnFromName,
                        "withdrawnAmount" => floatval($withdrawnAmount), "notes" => $notes, "createdOn" => $createdOn,
                        "updatedOn" => $updatedOn,
                    ];
                }
            }
        }

        return false;
    }

    public static function whereFundIdIs($fundId): array|bool
    {
        $conn = DB::connect();
        $sql = "SELECT * FROM withdrawals WHERE withdrawnFrom = ?";

        if ($stmt = $conn->prepare($sql)) {
            $strFundId = strval($fundId);
            $stmt->bind_param("s", $strFundId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $withdrawals = [];

                while ($row = $result->fetch_assoc()) {

                    $fund = Fund::find(intval($row["withdrawnFrom"]));
                    $withdrawnFromName = "";
                    if ($fund === false) {
                        $withdrawnFromName = "Deleted";
                    } else {
                        $withdrawnFromName = $fund["fundName"];
                    }

                    $withdrawals[count($withdrawals)] = [
                        "id" => $row["id"], "withdrawalReason" => $row["withdrawalReason"], "withdrawnFrom" => $withdrawnFromName,
                        "withdrawnAmount" => $row["withdrawnAmount"], "notes" => $row["notes"], "createdOn" => $row["createdOn"],
                        "updatedOn" => $row["updatedOn"],
                    ];
                }

                return $withdrawals;
            }
        }

        return false;
    }

    public function save(): bool
    {
        // validation 1
        if (!is_numeric($this->withdrawnAmount) || $this->withdrawnAmount <= 0) return false;

        // proceed
        $conn = DB::connect();
        $sql = "INSERT INTO withdrawals (withdrawalReason, withdrawnFrom, withdrawnAmount, notes, createdOn) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $createdOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param(
                "sssss",
                $this->withdrawalReason,
                $this->withdrawnFrom,
                $this->withdrawnAmount,
                $this->notes,
                $createdOn
            );
            if ($stmt->execute()) return true;
        }

        return false;
    }
    // update: setter(s)
    public static function setNotes($id, $newNotes): array|bool
    {
        // validation
        $deposit = static::find($id);
        if ($deposit === false) return false;
        // proceed
        $sql = "UPDATE withdrawals SET notes = ?, updatedOn = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param("ssi", $newNotes, $updatedOn, $id);

            if ($stmt->execute()) {
                // return the newly-updated deposit record
                return static::find($id);
            }
        }
        return false;
    }
    public static function delete($id): bool
    {
        // validation
        $fund = static::find($id);
        if ($fund === false) return false;

        $conn = DB::connect();

        $sql = "DELETE FROM withdrawals WHERE id = ?";


        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return true;
            }
        }
        return false;
    }
}
