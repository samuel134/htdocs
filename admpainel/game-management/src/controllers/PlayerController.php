class PlayerController {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function addPlayer($playerData) {
        $sql = "INSERT INTO players (name, points) VALUES (:name, :points)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $playerData['name'],
            ':points' => $playerData['points']
        ]);
    }

    public function editPlayer($playerId, $playerData) {
        $sql = "UPDATE players SET name = :name, points = :points WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $playerData['name'],
            ':points' => $playerData['points'],
            ':id' => $playerId
        ]);
    }

    public function deletePlayer($playerId) {
        $sql = "DELETE FROM players WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $playerId]);
    }

    public function getPlayer($playerId) {
        $sql = "SELECT * FROM players WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $playerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllPlayers() {
        $sql = "SELECT * FROM players";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}