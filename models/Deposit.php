<?php
include_once DATA . "DB.php";
include_once MODEL . "Fund.php";
include_once MODEL . "Model.php";
include_once MODEL . "User.php";

class Deposit extends DB implements Model
{
    private string $depositSource;
    /**
     */
    private string $depositedTo;
    private float $depositedAmount; // stored in the DB as a string.
    private string $notes;
    private int $userId;

    /**
     * @param string $depositSource
     * @param int $depositedTo - fund's id.
     * @param float $depositedAmount
     * @param string $notes
     * @param int $userId
     */
    public function __construct($depositSource, $depositedTo, $depositedAmount, $notes, $userId)
    {
        $this->depositSource = $depositSource;

    
        $this->depositedTo = intval($depositedTo);
    

        $this->depositedAmount = $depositedAmount;
        $this->notes = $notes;
        $this->userId = $userId;
    }

    public static function whereUserIdIs($userId): array|bool
    {
        $user = User::find(intval($userId));
        if ($user === false) return false;

        $conn = DB::connect();
        $sql = "SELECT * FROM deposits WHERE userId = ?";

        if ($stmt = $conn->prepare($sql)) {
            $intvalUserId = intval($userId);
            $stmt->bind_param("i", $intvalUserId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $deposits = [];

                while ($row = $result->fetch_assoc()) {
                    $depositPortion = floatval($row["depositedAmount"]);
                        
                    $fund = Fund::find(intval($row["depositedTo"]));
                    if ($fund === false) {
                        $depositedToModified = "Deleted";
                    } else {
                        $depositedToModified = $fund["fundName"];
                    }

                    $deposits[count($deposits)] = [
                        "id" => $row["id"], "depositSource" => $row["depositSource"], "depositedTo" => $depositedToModified,
                        "depositedAmount" => $depositPortion, "notes" => $row["notes"], "createdOn" => readableTimestamps($row["createdOn"]),
                        "updatedOn" => readableTimestamps($row["updatedOn"]), 'userId' => intval($userId)
                    ];
                }

                return $deposits;
            }
        }

        return false;
    }

    public static function find($id): array|bool
    {
        $conn = DB::connect();
        $sql = "SELECT * FROM deposits WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows != 0) {
                    $stmt->bind_result(
                        $id,
                        $depositSource,
                        $depositedTo,
                        $depositedAmount,
                        $notes,
                        $createdOn,
                        $updatedOn,
                        $userId
                    );
                    $stmt->fetch();

                    $depositedToModified = "Deleted";
                   
                    $fund = Fund::find(intval($depositedTo));
                    if ($fund === false) {
                        $depositedToModified = "Deleted";
                    } else {
                        $depositedToModified = $fund["fundName"];
                    }
                    

                    return [
                        "id" => $id, "depositSource" => $depositSource, "depositedTo" => $depositedToModified,
                        "depositedAmount" => floatval($depositedAmount), "notes" => $notes, "createdOn" => readableTimestamps($createdOn),
                        "updatedOn" => readableTimestamps($updatedOn),
                        "userId" => intval($userId)
                    ];
                }
            }
        }

        return false;
    }

    public static function whereFundIdIs($fundId): array|bool
    {
        $fund = Fund::find(intval($fundId));
        if ($fund === false) return false;

        $conn = DB::connect();
        $sql = "SELECT * FROM deposits WHERE depositedTo = ?";

        if ($stmt = $conn->prepare($sql)) {
            $intvalFundId = intval($fundId);
            $stmt->bind_param("i", $intvalFundId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $deposits = [];

                while ($row = $result->fetch_assoc()) {
                    $depositPortion = floatval($row["depositedAmount"]);
                    $depositedToModified = $fund["fundName"];

                    $deposits[count($deposits)] = [
                        "id" => $row["id"], "depositSource" => $row["depositSource"], "depositedTo" => $depositedToModified,
                        "depositedAmount" => $depositPortion, "notes" => $row["notes"], "createdOn" => readableTimestamps($row["createdOn"]),
                        "updatedOn" => readableTimestamps($row["updatedOn"]), 
                        "userId" => intval($row["userId"])
                    ];
                }

                return $deposits;
            }
        }

        return false;
    }

    public function save(): bool
    {
        // validation 1
        if (!is_numeric($this->depositedAmount) || $this->depositedAmount <= 0) return false;

        // proceed
        $conn = DB::connect();
        $sql = "INSERT INTO deposits (depositSource, depositedTo, depositedAmount, notes, createdOn, userId) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $createdOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $intvalUserId = intval($this->userId);
            $stmt->bind_param(
                "sssssi",
                $this->depositSource,
                $this->depositedTo,
                $this->depositedAmount,
                $this->notes,
                $createdOn,
                $intvalUserId
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
        $sql = "UPDATE deposits SET notes = ?, updatedOn = ? WHERE id = ?";
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

        $sql = "DELETE FROM deposits WHERE id = ?";


        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return true;
            }
        }
        return false;
    }
}
