<?php
require_once __DIR__ . '/../config/Database.php';

class ClientModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

	
  //---------------------------------------------------------------------------GET ALL CLIENTS-------------------------------------------------------------------------------

    public function getClients() {
    $sql = "SELECT * FROM clients";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

	
  //---------------------------------------------------------------------------GET CLIENTS LOGO BY CLIENT NAME FROM TASKS-------------------------------------------------------------------------------

    public function getClientLogoByName($clientName)
{
    // Assuming a PDO database connection is already set up in this model
    $query = "SELECT clientLogo FROM clients WHERE client_name = :clientName LIMIT 1";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':clientName', $clientName, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchColumn(); // Fetch only the clientLogo column
}
	
	
  //---------------------------------------------------------------------------GET ALL CLIENTS THAT ARENT DLETED-------------------------------------------------------------------------------

    public function getAllClients() {
    $sql = "SELECT * FROM clients WHERE isDeleted = '' OR isDeleted = 0";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

  //---------------------------------------------------------------------------ADD A NEW CLIENT-------------------------------------------------------------------------------

    public function addClient($data) {
        $sql = "INSERT INTO clients (client_name, accountExecutive, contactEmail, contactPhone, contactName, contactTitle, 
                secondaryContactEmail, secondaryContactName, secondaryContactPhone, secondaryContactTitle, clientLogo, 
                clientAddress, clientJoinDate, ClientYearEnd) 
                VALUES (:client_name, :accountExecutive, :contactEmail, :contactPhone, :contactName, :contactTitle, 
                :secondaryContactEmail, :secondaryContactName, :secondaryContactPhone, :secondaryContactTitle, :clientLogo, 
                :clientAddress, :clientJoinDate, :ClientYearEnd)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

  //---------------------------------------------------------------------------EDIT EXISTING CLIENT-------------------------------------------------------------------------------

    public function updateClient($id, $data) {
        $sql = "UPDATE clients SET client_name = :client_name, accountExecutive = :accountExecutive, 
                contactEmail = :contactEmail, contactPhone = :contactPhone, contactName = :contactName, contactTitle = :contactTitle,
                secondaryContactEmail = :secondaryContactEmail, secondaryContactName = :secondaryContactName, 
                secondaryContactPhone = :secondaryContactPhone, secondaryContactTitle = :secondaryContactTitle, 
                clientLogo = :clientLogo, clientAddress = :clientAddress, clientJoinDate = :clientJoinDate, 
                ClientYearEnd = :ClientYearEnd 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

  //---------------------------------------------------------------------------PERMANENTLY DELETE CLIENT-------------------------------------------------------------------------------

    public function permanentDeleteClient($id) {
        $sql = "DELETE FROM clients WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
	
	
  //---------------------------------------------------------------------------SHOW CLIENTS IN RECYCLE BIN-------------------------------------------------------------------------------

	public function getDeletedClients() {
    $sql = "SELECT * FROM clients WHERE isDeleted = 1";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
	
	
  //---------------------------------------------------------------------------RESTORE CLIENTS FROM RECYCLE BIN-------------------------------------------------------------------------------

public function restoreClient($id) {
    $sql = "UPDATE clients SET isDeleted = 0 WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
}	

  //---------------------------------------------------------------------------SEND CLIENTS TO RECYCLE BIN-------------------------------------------------------------------------------

public function softDeleteClient($id) {
    $sql = "UPDATE clients SET isDeleted = 1 WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
}	
	
  //---------------------------------------------------------------------------GET CLIENT BY ID-------------------------------------------------------------------------------
	
public function getClientById($id) {
    $sql = "SELECT * FROM clients WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}	

	
	
	
}
?>
