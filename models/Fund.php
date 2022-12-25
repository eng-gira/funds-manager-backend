<?php
include_once DATA . "DB.php";
include_once INC . "functions.php";
include_once MODEL . "Model.php";
include_once MODEL . "Deposit.php";
include_once MODEL . "Withdrawal.php";
include_once MODEL . "User.php";


class Fund extends DB implements Model
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
    private int $userId;

    public function __construct($fundName, $fundPercentage, $balance, $size, $notes, $userId)
    {
        $this->fundName = $fundName;
        $this->fundPercentage = $fundPercentage;
        $this->balance = $balance;
        $this->size = $size;
        $this->notes = $notes;
        $this->userId = $userId;
    }

    public static function whereUserIdIs($userId): array|bool
    {
        $user = User::find(intval($userId));
        if ($user === false) return false;

        $conn = DB::connect();
        $sql = "SELECT * FROM funds WHERE userId = ?";

        if ($stmt = $conn->prepare($sql)) {
            $intvalUserId = intval($userId);
            $stmt->bind_param("i", $intvalUserId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $funds = [];

                while ($row = $result->fetch_assoc()) {
                        
                    $funds[count($funds)] = [
                        "id" => $row["id"], 
                        "fundName" => $row["fundName"], 
                        "fundPercentage" => floatval($row["fundPercentage"]),
                        "balance" => floatval($row["balance"]), "size" => $row["size"], "notes" => $row["notes"], "createdOn" => readableTimestamps($row["createdOn"]),
                        "updatedOn" => readableTimestamps($row["updatedOn"]), "lastDeposit" => readableTimestamps($row["lastDeposit"]), 
                        "lastWithdrawal" => readableTimestamps($row["lastWithdrawal"]),
                        "totalDeposits" => floatval($row["totalDeposits"]), "totalWithdrawals" => floatval($row["totalWithdrawals"]),
                        'userId' => intval($row["userId"]),
                    ];
                }

                return $funds;
            }
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
                        $totalDeposits,
                        $totalWithdrawals,
                        $userId
                    );
                    $stmt->fetch();

                    return [
                        "id" => $id, "fundName" => $fundName, "fundPercentage" => floatval($fundPercentage),
                        "balance" => floatval($balance), "size" => $size, "notes" => $notes, "createdOn" => readableTimestamps($createdOn),
                        "updatedOn" => readableTimestamps($updatedOn), "lastDeposit" => readableTimestamps($lastDeposit), "lastWithdrawal" => readableTimestamps($lastWithdrawal),
                        "totalDeposits" => floatval($totalDeposits), "totalWithdrawals" => floatval($totalWithdrawals),
                        'userId' => intval($userId)
                    ];
                }
            }
        }

        return false;
    }

    public function save(): bool
    {
        $userId = $this->userId;
        $allFunds = static::whereUserIdIs($userId);
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
            if (!is_numeric($this->fundPercentage) || $this->fundPercentage < 0 || ($totalPercentages + $this->fundPercentage > 100)) {
                return false;
            }
        }
        // Proceed
        $conn = DB::connect();
        $sql = "INSERT INTO funds (fundName, fundPercentage, balance, size, notes, createdOn, userId) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $createdOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $intvalUserId = intval($userId);
            $stmt->bind_param("ssssssi", $this->fundName, $this->fundPercentage, $this->balance, $this->size, $this->notes, $createdOn, 
                $intvalUserId);
            if ($stmt->execute()) return true;
        }

        return false;
    }

    // update: setters
    public static function setFundName($id, $newFundName): array|bool
    {
        // validations
        $fund = static::find($id);
        $userId = $fund['userId'];
        $allFunds = static::whereUserIdIs($userId);
        if ($allFunds !== false) {
            $existingFundsNames = array_map(function ($fund) {
                return $fund["fundName"];
            }, $allFunds);
        }
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

        $userId = $fund['userId'];
        $allFunds = static::whereUserIdIs($userId);
        if ($allFunds !== false) {
            $totalPercentages = 0.0;
            foreach ($allFunds as $f) {
                if (intval($f["id"]) == $id) continue;
                $totalPercentages += $f["fundPercentage"];
            }
            if (!is_numeric($newFundPercentage) || $newFundPercentage < 0 || ($totalPercentages + $newFundPercentage > 100)) {
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
    public static function deposit($id, $depositedAmount, $depositSource, $depositNotes, $userId): array|bool
    {
        // validations
        $fund = static::find($id);
        if ($fund === false) return false;

        if (!is_numeric($depositedAmount) || $depositedAmount <= 0) {
            return false;
        }
        // proceed
        $sql = "UPDATE funds SET balance = ?, updatedOn = ?, lastDeposit = ?, totalDeposits = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $lastDeposit = $updatedOn;
            $newBalance = floatval($fund["balance"]) + floatval(abs($depositedAmount));

            //
            $newTotalDeposits = floatval($fund["totalDeposits"]) + floatval(abs($depositedAmount));

            $stmt->bind_param("ssssi", $newBalance, $updatedOn, $lastDeposit, $newTotalDeposits, $id);

            if ($stmt->execute()) {
                self::logDeposit($depositSource, $id, $depositedAmount, $depositNotes, $userId);                
                
                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function withdraw($id, $withdrawnAmount, $withdrawalReason, $withdrawalNotes, $userId): array|bool
    {
        // validations
        $fund = static::find($id);
        if ($fund === false) return false;

        if (!is_numeric($withdrawnAmount) || $withdrawnAmount <= 0) {
            return false;
        }
        // proceed
        $sql = "UPDATE funds SET balance = ?, updatedOn = ?, lastWithdrawal = ?, totalWithdrawals = ? WHERE id = ?";
        $conn = DB::connect();
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $updatedOn = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s");
            $lastWithdrawal = $updatedOn;
            $newBalance = floatval($fund["balance"]) - floatval(abs($withdrawnAmount));

            //
            $newTotalWithdrawals = floatval($fund["totalWithdrawals"]) + floatval(abs($withdrawnAmount));

            $stmt->bind_param("ssssi", $newBalance, $updatedOn, $lastWithdrawal, $newTotalWithdrawals, $id);

            if ($stmt->execute()) {
                self::logWithdrawal($id, $withdrawnAmount, $withdrawalReason, $withdrawalNotes, $userId);

                // return the newly-updated fund
                return static::find($id);
            }
        }
        return false;
    }
    public static function depositToAll($amountToDistribute, $depositSource, $depositNotes, $userId): bool
    {
        // validate
        $allFunds = static::whereUserIdIs($userId);
        if ($allFunds === false) {
            return false;
        }
        foreach ($allFunds as $fund) {
            $addedBalance = floatval($fund["fundPercentage"] / 100) * floatval($amountToDistribute);

            if (self::deposit($fund["id"], $addedBalance, $depositSource, $depositNotes, $userId) === false) {
                // if any failed, return false
                return false;
            }

        }

        return true;
    }

    /**
     * Add the fund with the passed id to archive.
     */
    public static function archive($id): bool
    {
        /**
         * @todo implement this method
         */
        return false;
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

            /**
             * @todo announce to Deposit and Withdrawal that the fund, with id and name, was deleted. 
             * @todo in Deposit and in Withdrawal, replace the fund's name in the views with (Deleted) {Fund's Name}
             */
            // if ($stmt->execute()) {
            //     return true;
            // }
        }
        return false;
    }

    private static function logDeposit($depositSource, $depositedTo, $depositedAmount, $notes, $userId) {
        $deposit = new Deposit($depositSource, $depositedTo, $depositedAmount, $notes, $userId);
        return $deposit->save();
    }

    private static function logWithdrawal($fundId, $withdrawnAmount, $withdrawalReason, $withdrawalNotes, $userId) {
        $withdrawal = new Withdrawal($fundId, $withdrawnAmount, $withdrawalReason, $withdrawalNotes, $userId);
        return $withdrawal->save();
    }
}
