<?php
include_once DATA . "DB.php";
include_once MODEL . "Fund.php";
class Deposit extends DB
{
    private string $depositSource;
    /**
     * @value either an existing fund's id or "all"
     */
    private string $depositedTo;
    private float $depositedAmount; // stored in the DB as a string.
    private string $notes;

    public function __construct($depositSource, $depositedTo, $depositedAmount, $notes = "")
    {
        $this->depositSource = $depositSource;

        if ($depositedTo != "all") {
            $fund = Fund::find(intval($depositedTo));
            $this->depositedTo = $fund["fundName"];
        } else {
            $this->depositedTo = $depositedTo;
        }

        $this->depositedAmount = $depositedAmount;
        $this->notes = $notes;
    }

    public static function all(): array|bool
    {
        $deposits = [];

        $conn = DB::connect();

        $sql = "SELECT * FROM deposits";
        $result = $conn->query($sql);
        if ($result->num_rows != 0) {
            while ($row = $result->fetch_assoc()) {
                $deposits[count($deposits)] = [
                    "id" => $row["id"],
                    "depositSource" => $row["depositSource"],
                    "depositedTo" => $row["depositedTo"],
                    "depositedAmount" => floatval($row["depositedAmount"]),
                    "notes" => $row["notes"],
                    "createdOn" => $row["createdOn"],
                    "updatedOn" => $row["updatedOn"],
                ];
            }

            return $deposits;
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
                    );
                    $stmt->fetch();

                    return [
                        "id" => $id, "depositSource" => $depositSource, "depositedTo" => $depositedTo,
                        "depositedAmount" => floatval($depositedAmount), "notes" => $notes, "createdOn" => $createdOn,
                        "updatedOn" => $updatedOn,
                    ];
                }
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
        $sql = "INSERT INTO deposits (depositSource, depositedTo, depositedAmount, notes, createdOn) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $createdOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param(
                "sssss",
                $this->depositSource,
                $this->depositedTo,
                $this->depositedAmount,
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
