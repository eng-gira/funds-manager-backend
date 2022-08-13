<?php
include_once DATA . "DB.php";

class Fund extends DB
{
    /**
     * @var string fundName
     * @length 3+
     */
    private string $fundName;
    private float $fundPercentage;
    private float $balance;
    private string $size;
    private string $notes;
    private string $createdOn;
    private string $updatedOn;
    private string $lastDeposit;
    private string $lastWithdrawal;

    public function __construct($fundName, $fundPercentage, $balance = 0.0, $size = "Open", $notes = "")
    {
        $this->fundName = $fundName;
        $this->fundPercentage = $fundPercentage;
        $this->balance = $balance;
        $this->size = $size;
        $this->notes = $notes;
    }

    public static function all(): array|bool
    {
        $funds = [];

        $conn = DB::connect();

        $sql = "SELECT * FROM funds";
        $result = $conn->query($sql);
        if ($result->num_rows != 0) {
            while ($row = $result->fetch_assoc()) {
                $funds[count($funds)] = [
                    "id" => $row["id"],
                    "fundName" => $row["fundName"],
                    "fundPercentage" => floatval($row["fundPercentage"]),
                    "balance" => floatval($row["balance"]),
                    "size" => floatval($row["size"]),
                    "notes" => $row["notes"],
                    "createdOn" => $row["createdOn"],
                    "updatedOn" => $row["updatedOn"],
                    "lastDeposit" => $row["lastDeposit"],
                    "lastWithdrawal" => $row["lastWithdrawal"],
                ];
            }

            return $funds;
        }

        return false;
    }

    public static function find($id): array|bool
    {
        $conn = DB::connect();
        $sql = "SELECT * FROM funds WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows != 0) {
                    $stmt->bind_result(
                        $id,
                        $fundName,
                        $fundPercentage,
                        $balance,
                        $size,
                        $notes,
                        $createdOn,
                        $updatedOn,
                        $lastDeposit,
                        $lastWithdrawal,
                    );
                    $stmt->fetch();

                    return [
                        "id" => $id, "fundName" => $fundName, "fundPercentage" => floatval($fundPercentage),
                        "balance" => floatval($balance), "size" => floatval($size), "notes" => $notes, "createdOn" => $createdOn,
                        "updatedOn" => $updatedOn, "lastDeposit" => $lastDeposit, "lastWithdrawal" => $lastWithdrawal,
                    ];
                }
            }
        }

        return false;
    }

    public function save(): bool
    {
        $allFunds = static::all();
        if ($allFunds !== false) {
            $existingFundsNames = array_map(function ($fund) {
                return $fund["fundName"];
            }, $allFunds);
        }
        // validation 1
        if (!is_string($this->fundName) || strlen($this->fundName) < 3 || ($allFunds !== false && in_array($this->fundName, $existingFundsNames))) {
            return false;
        }
        // validation 2
        $totalPercentages = 0.0;
        if ($allFunds !== false) {
            foreach ($allFunds as $f) $totalPercentages += $f["fundPercentage"];
            if (!is_numeric($this->fundPercentage) || $this->fundPercentage <= 0 || ($totalPercentages + $this->fundPercentage > 100)) {
                return false;
            }
        }
        // Proceed
        $conn = DB::connect();
        $sql = "INSERT INTO funds (fundName, fundPercentage, balance, size, notes, createdOn) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $createdOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param("ssssss", $this->fundName, $this->fundPercentage, $this->balance, $this->size, $this->notes, $createdOn);
            if ($stmt->execute()) return true;
        }

        return false;
    }

    // update: setters
    public static function setFundName($id, $newFundName): array|bool
    {
        $allFunds = static::all();
        if ($allFunds !== false) {
            $existingFundsNames = array_map(function ($fund) {
                return $fund["fundName"];
            }, $allFunds);
        }

        // validations
        $fund = static::find($id);
        if ($fund === false || !is_string($newFundName) || strlen($newFundName) < 3 || ($allFunds !== false && in_array($newFundName, $existingFundsNames))) {
            return false;
        }
        // proceed
        $sql = "UPDATE funds SET fundName = ?, updatedOn = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param("ssi", $newFundName, $updatedOn, $id);

            if ($stmt->execute()) {
                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function setFundPercentage($id, $newFundPercentage): array|bool
    {
        // validations
        $fund = static::find($id);
        if ($fund === false) return false;

        $allFunds = static::all();
        if ($allFunds !== false) {
            $totalPercentages = 0.0;
            foreach ($allFunds as $f) $totalPercentages += $f["fundPercentage"];
            if (!is_numeric($newFundPercentage) || $newFundPercentage <= 0 || ($totalPercentages + $newFundPercentage > 100)) {
                return false;
            }
        }
        // proceed
        $sql = "UPDATE funds SET fundPercentage = ?, updatedOn = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param("ssi", $newFundPercentage, $updatedOn, $id);

            if ($stmt->execute()) {
                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function setSize($id, $newSize): array|bool
    {
        // validations
        $fund = static::find($id);
        if ($fund === false) return false;

        if (is_numeric($newSize) && $newSize <= 0) {
            return false;
        }
        // proceed
        $sql = "UPDATE funds SET size = ?, updatedOn = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param("ssi", $newSize, $updatedOn, $id);

            if ($stmt->execute()) {
                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function setNotes($id, $newNotes): array|bool
    {
        // validation
        $fund = static::find($id);
        if ($fund === false) return false;
        // proceed
        $sql = "UPDATE funds SET notes = ?, updatedOn = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $stmt->bind_param("ssi", $newNotes, $updatedOn, $id);

            if ($stmt->execute()) {
                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function deposit($id, $depositedAmount): array|bool
    {
        // validations
        $fund = static::find($id);
        if ($fund === false) return false;

        if (!is_numeric($depositedAmount) || $depositedAmount <= 0) {
            return false;
        }
        // proceed
        $sql = "UPDATE funds SET balance = ?, updatedOn = ?, lastDeposit = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $lastDeposit = $updatedOn;
            $newBalance = floatval($fund["balance"]) + floatval(abs($depositedAmount));
            $stmt->bind_param("sssi", $newBalance, $updatedOn, $lastDeposit, $id);

            if ($stmt->execute()) {
                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function withdraw($id, $withdrawnAmount): array|bool
    {
        // validations
        $fund = static::find($id);
        if ($fund === false) return false;

        if (!is_numeric($withdrawnAmount) || $withdrawnAmount <= 0) {
            return false;
        }
        // proceed
        $sql = "UPDATE funds SET balance = ?, updatedOn = ?, lastWithdrawal = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $lastWithdrawal = $updatedOn;
            $newBalance = floatval($fund["balance"]) - floatval(abs($withdrawnAmount));
            $stmt->bind_param("sssi", $newBalance, $updatedOn, $lastWithdrawal, $id);

            if ($stmt->execute()) {
                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function depositToAll($amount): bool
    {
        // validate
        $allFunds = static::all();
        if ($allFunds === false) {
            return false;
        }

        // proceed
        $conn = DB::connect();

        foreach ($allFunds as $fund) {
            $sql = "UPDATE funds SET balance = ?, updatedOn = ?, lastDeposit = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $addedBalance = floatval($fund["fundPercentage"] / 100) * floatval($amount);
                $newBalance = floatval($fund["balance"]) + $addedBalance;
                $id = $fund["id"];
                $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
                $lastDeposit = $updatedOn;
                $stmt->bind_param("sssi", $newBalance, $updatedOn, $lastDeposit, $id);

                if (!$stmt->execute()) {
                    // if any failed, return false
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public static function delete($id): bool
    {
        // validation
        $fund = static::find($id);
        if ($fund === false) return false;

        $conn = DB::connect();

        $sql = "DELETE FROM funds WHERE id = ?";


        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                return true;
            }
        }
        return false;
    }
}
