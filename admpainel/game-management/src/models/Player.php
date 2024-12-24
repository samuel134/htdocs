class Player {
    private $id;
    private $name;
    private $points;
    private $status;

    public function __construct($id, $name, $points, $status) {
        $this->id = $id;
        $this->name = $name;
        $this->points = $points;
        $this->status = $status;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getPoints() {
        return $this->points;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setPoints($points) {
        $this->points = $points;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'points' => $this->points,
            'status' => $this->status,
        ];
    }
}