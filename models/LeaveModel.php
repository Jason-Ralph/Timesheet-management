<?php
// models/LeaveModel.php
require_once __DIR__ . '/../config/Database.php';

class LeaveModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Applies for a new leave.
     *
     * @param array $data Associative array containing leave details.
     * @return bool Returns true on success.
     * @throws Exception Throws exception on failure.
     */
    public function applyLeave($data) {
        $sql = "INSERT INTO leaves 
                (username, leave_type, reason, start_date, end_date, total_days) 
                VALUES 
                (:username, :leave_type, :reason, :start_date, :end_date, :total_days)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username'     => $data['username'],
            'leave_type'   => $data['leave_type'],
            'reason'       => $data['reason'] ?? null,
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
            'total_days'   => $data['total_days']
        ]);
    }

    /**
     * Retrieves all leave applications for a specific user.
     *
     * @param string $username The username to filter leaves.
     * @return array Returns an array of leave records.
     */
    public function getLeavesByUsername($username) {
        $sql = "SELECT * FROM leaves WHERE username = :username ORDER BY applied_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves all pending leave applications.
     *
     * @return array Returns an array of pending leave records.
     */
    public function getPendingLeaves() {
        $sql = "SELECT * FROM leaves WHERE status = 'pending' ORDER BY applied_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Approves a leave application.
     *
     * @param int    $leave_id     The ID of the leave application.
     * @param string $approver     The username of the approver (director).
     * @param string $comments     Optional comments from the approver.
     * @return bool Returns true on success.
     * @throws Exception Throws exception on failure.
     */
    public function approveLeave($leave_id, $approver, $comments = '') {
        // Start Transaction
        $this->db->beginTransaction();

        try {
            // Update the leave status to approved
            $sql = "UPDATE leaves 
                    SET status = 'approved', approved_by = :approved_by, approved_at = NOW(), comments = :comments 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'approved_by' => $approver,
                'comments'    => $comments,
                'id'          => $leave_id
            ]);

            // Subtract the leave days from user's balance
            $leave = $this->getLeaveById($leave_id);
            if (!$leave) {
                throw new Exception("Leave application not found.");
            }

            $this->updateUserLeaveBalance($leave['username'], $leave['leave_type'], $leave['total_days']);

            // Commit Transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback on error
            $this->db->rollBack();
            throw new Exception("Failed to approve leave: " . $e->getMessage());
        }
    }

    /**
     * Rejects a leave application.
     *
     * @param int    $leave_id The ID of the leave application.
     * @param string $approver The username of the approver (director).
     * @param string $comments Optional comments from the approver.
     * @return bool Returns true on success.
     * @throws Exception Throws exception on failure.
     */
    public function rejectLeave($leave_id, $approver, $comments = '') {
        $sql = "UPDATE leaves 
                SET status = 'rejected', approved_by = :approved_by, approved_at = NOW(), comments = :comments 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'approved_by' => $approver,
            'comments'    => $comments,
            'id'          => $leave_id
        ]);
    }

    /**
     * Retrieves a leave application by its ID.
     *
     * @param int $leave_id The ID of the leave application.
     * @return array|false Returns the leave record or false if not found.
     */
    public function getLeaveById($leave_id) {
        $sql = "SELECT * FROM leaves WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $leave_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates the user's leave balance based on approved leave.
     *
     * @param string $username   The username of the user.
     * @param string $leave_type The type of leave.
     * @param float  $days       The number of leave days to subtract.
     * @return void
     * @throws Exception Throws exception on failure.
     */
    private function updateUserLeaveBalance($username, $leave_type, $days) {
        // Assuming you have a 'leave_balances' table to track each user's leave
        $sql = "UPDATE leave_balances 
                SET `$leave_type` = `$leave_type` - :days 
                WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'days'     => $days,
            'username' => $username
        ]);
    }

    /**
     * Retrieves leave balances for a user.
     *
     * @param string $username The username to retrieve balances for.
     * @return array|false Returns an associative array of leave balances or false if not found.
     */
    public function getLeaveBalances($username) {
        $sql = "SELECT * FROM leave_balances WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Initializes leave balances for a new user.
     *
     * @param string $username The username of the new user.
     * @return bool Returns true on success.
     */
    public function initializeLeaveBalances($username) {
        $sql = "INSERT INTO leave_balances 
                (username, sick, annual, study, family_responsibility, other) 
                VALUES 
                (:username, 0, 0, 0, 0, 0)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['username' => $username]);
    }

    /**
     * Accrues leave for all users.
     *
     * @param string $leave_type The type of leave to accrue.
     * @param float  $days       Number of days to accrue.
     * @return void
     */
    public function accrueLeave($leave_type, $days) {
        $sql = "UPDATE leave_balances SET `$leave_type` = `$leave_type` + :days";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['days' => $days]);
    }
}
?>
